<?php

namespace App\Services\Website;

use App\Models\Website\Bank;
use App\Models\Website\Sentence;
use App\Support\Website\WebsiteFeatures;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class SentenceAiContentGenerator
{
    public function __construct(
        private readonly SentenceContentGenerator $fallbackGenerator,
    ) {}

    public function isConfigured(): bool
    {
        return WebsiteFeatures::sentenceContentGeneratorEnabled()
            && filter_var(config('services.openai.sentence_generator_ai_enabled', false), FILTER_VALIDATE_BOOLEAN)
            && filled(config('services.openai.api_key'));
    }

    /**
     * @return array<string, mixed>
     */
    public function generate(Sentence $sentence): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Generator AI nie jest skonfigurowany.');
        }

        $sentence->loadMissing([
            'bank',
            'bank_previously',
            'court',
            'judge',
            'parent.bank',
            'parent.bank_previously',
            'parent.court',
        ]);

        $fallback = $this->fallbackGenerator->generate($sentence);

        $response = Http::withToken((string) config('services.openai.api_key'))
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('services.openai.sentence_generator_timeout', 60))
            ->post('https://api.openai.com/v1/responses', [
                'model' => (string) config('services.openai.sentence_generator_model', 'gpt-5-mini'),
                'instructions' => $this->instructions(),
                'input' => [[
                    'role' => 'user',
                    'content' => [[
                        'type' => 'input_text',
                        'text' => json_encode($this->payload($sentence, $fallback), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    ]],
                ]],
                'text' => [
                    'format' => [
                        'type' => 'json_schema',
                        'name' => 'sentence_content',
                        'strict' => true,
                        'schema' => $this->schema(),
                    ],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException($this->errorMessage($response->json(), $response->status()));
        }

        $generated = $this->parseResponse($response->json());

        return $this->normalizeGenerated($generated, $fallback);
    }

    private function instructions(): string
    {
        return <<<'PROMPT'
Jesteś redaktorem strony polskiej kancelarii adwokackiej opisującej wyroki w sprawach kredytów waloryzowanych kursem waluty obcej.

Wygeneruj szkic publikacji po polsku. Tekst ma brzmieć naturalnie, ale rzeczowo i ostrożnie. Nie pisz językiem reklamy. Nie dopisuj faktów, których nie ma w danych wejściowych.

Traktuj local_draft wyłącznie jako pomocniczy brudnopis faktów i struktury. Nie kopiuj jego zdań bez redakcji, jeżeli brzmią technicznie, sztucznie albo powtarzają tę samą informację.

W danych wejściowych otrzymasz style_mode oraz style_examples. style_examples to przykładowe opublikowane wpisy z tej samej instancji co generowany wyrok. Używaj ich do zachowania stylu, długości, rytmu nagłówków i poziomu szczegółowości, ale nie kopiuj ich faktów do nowego wpisu.

Tryby pracy:
- style_mode = strict: trzymaj się możliwie blisko struktury, tonu i sposobu prowadzenia wpisu z przykładów; dostosuj tylko fakty do aktualnego wyroku,
- style_mode = creative: potraktuj przykłady jako punkt odniesienia, ale możesz swobodniej ułożyć tytuł, lead, kolejność akapitów i nagłówki, o ile tekst pozostaje rzeczowy, zgodny z faktami i stylem kancelarii.

Zasady bezwzględne:
- nie umieszczaj imion, nazwisk ani danych kredytobiorców,
- nie umieszczaj numeru umowy ani dokładnej daty zawarcia umowy,
- jeżeli w danych wejściowych pojawi się numer umowy albo dokładna data umowy, usuń je,
- możesz używać roku umowy, sygnatury sprawy, sądu, banku, daty wyroku i publicznych danych procesowych,
- nie twórz kwot, dat, zarzutów, rozstrzygnięć ani elementów uzasadnienia, których nie ma w danych,
- jeśli czegoś brakuje, napisz zachowawczo.

Styl i redakcja:
- excerpt ma być krótką, naturalną zajawką; nie dopisuj formuł typu "Wynik sprawy: wygrana kredytobiorców", jeżeli z poprzedniego zdania wynika już, że sąd uwzględnił powództwo, ustalił nieważność umowy albo oddalił apelację banku,
- unikaj powtórzeń w rodzaju "uwzględnił powództwo" + "wygrana kredytobiorców" w tym samym polu,
- używaj odmian banku z pola grammar_hints.bank_current_forms, np. "w sporze z Getin Bankiem", a nie "przeciwko Getin Bank",
- przy pełnomocniku użyj czasownika z pola grammar_hints.lawyer_representation_verb; jeżeli wartość to "reprezentowała", napisz "Kredytobiorców reprezentowała adw. ...",
- sekcję "Korzyść z wygrania sprawy" pisz konkretnie i spokojnie: wskaż szacowaną korzyść oraz ewentualnie kwotę wypłaconego kredytu; nie dodawaj zdań sugerujących, że po wygranej "dalej nic nie wiadomo" albo że efekt wyroku jest niepewny,
- nie używaj zdania "Ostateczny efekt ekonomiczny zależy od rozliczeń stron po zakończeniu sprawy".

Tytuły mają być naturalne, np. "Nieważna umowa Własny Kąt Hipoteczny z 2008 r. - wygrywamy w Sądzie Okręgowym w Zielonej Górze", a nie techniczne składanki.

Pole content zwróć jako prosty HTML zgodny z edytorem: używaj tylko tagów p, ol, ul, li, h2, h3, strong, em i br.
PROMPT;
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Sentence $sentence, array $fallback): array
    {
        $styleMode = $this->styleMode();

        return [
            'style_mode' => $styleMode,
            'source_data' => [
                'instance' => $sentence->instance,
                'sign' => $this->plain($sentence->sign),
                'court' => $this->plain($sentence->court?->label),
                'judge' => $this->plain($sentence->judge?->label),
                'sentence_date' => $sentence->sentence_date,
                'lawsuit_date' => $sentence->lawsuit_date,
                'appeal_date' => $sentence->appeal_date,
                'bank_current' => $this->plain($sentence->bank?->label),
                'bank_previous' => $this->plain($sentence->bank_previously?->label),
                'credit_year' => $this->plain($sentence->credit_year),
                'credit_name' => $this->plain($sentence->credit_name),
                'currency' => $this->plain($sentence->currency),
                'is_paid_off' => (bool) $sentence->is_paid_off,
                'paid_off_year' => $this->plain($sentence->paid_off_year),
                'claim' => $this->plain($sentence->claim),
                'result' => $this->plain($sentence->result),
                'lawyer' => $this->plain($sentence->lawyer),
                'wps' => $this->plain($sentence->wps),
                'hearings' => $this->plain($sentence->hearings),
                'credit_payoff' => $sentence->credit_payoff,
                'credit_profit' => $sentence->credit_profit,
                'judgment_publication_mode' => $sentence->judgment_publication_mode,
                'reasoning_source' => $sentence->reasoning_source,
                'evidence_scope' => $sentence->evidence_scope ?? [],
                'security_granted' => (bool) $sentence->security_granted,
                'content_generator_flags' => $sentence->content_generator_flags ?? [],
            ],
            'grammar_hints' => [
                'bank_current_forms' => $this->bankForms($sentence->bank),
                'bank_previous_forms' => $this->bankForms($sentence->bank_previously),
                'lawyer_representation_verb' => $this->lawyerRepresentationVerb($sentence->lawyer),
            ],
            'safe_notes' => [
                'ruling_points' => $this->safeRepeaterTexts($sentence->ruling_points ?? []),
                'court_reasoning_summary' => $this->sanitizeCaseText($sentence->court_reasoning_summary),
                'security_note' => $this->sanitizeCaseText($sentence->security_note),
                'setoff_or_retention_note' => $this->sanitizeCaseText($sentence->setoff_or_retention_note),
                'counterclaim_note' => $this->sanitizeCaseText($sentence->counterclaim_note),
                'content_note' => $this->sanitizeCaseText($sentence->content_note),
            ],
            'parent_sentence' => $sentence->parent ? [
                'sign' => $this->plain($sentence->parent->sign),
                'court' => $this->plain($sentence->parent->court?->label),
                'sentence_date' => $sentence->parent->sentence_date,
                'bank_current' => $this->plain($sentence->parent->bank?->label),
                'bank_previous' => $this->plain($sentence->parent->bank_previously?->label),
                'credit_year' => $this->plain($sentence->parent->credit_year),
                'credit_name' => $this->plain($sentence->parent->credit_name),
                'result' => $this->plain($sentence->parent->result),
            ] : null,
            'local_draft' => [
                'label' => $this->plain($fallback['label'] ?? ''),
                'excerpt' => $this->plain($fallback['excerpt'] ?? ''),
                'metatitle' => $this->plain($fallback['metatitle'] ?? ''),
                'metadescription' => $this->plain($fallback['metadescription'] ?? ''),
                'content' => $this->sanitizeHtml($fallback['content'] ?? ''),
            ],
            'style_examples' => $this->styleExamples($sentence),
            'output_requirements' => [
                'label' => 'naturalny tytuł, bez kropki na końcu',
                'excerpt' => 'krótka zajawka bez powtarzania oczywistego wyniku sprawy',
                'metatitle' => 'tytuł SEO',
                'metadescription' => 'opis SEO do około 160 znaków',
                'content' => 'szkic wpisu HTML do ręcznego sprawdzenia przed publikacją',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function schema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => [
                'label',
                'excerpt',
                'metatitle',
                'metadescription',
                'content',
            ],
            'properties' => [
                'label' => ['type' => 'string'],
                'excerpt' => ['type' => 'string'],
                'metatitle' => ['type' => 'string'],
                'metadescription' => ['type' => 'string'],
                'content' => ['type' => 'string'],
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function parseResponse(array $body): array
    {
        $text = $body['output_text'] ?? null;

        if (! is_string($text)) {
            $chunks = [];

            foreach ($body['output'] ?? [] as $output) {
                foreach ($output['content'] ?? [] as $content) {
                    if (isset($content['text']) && is_string($content['text'])) {
                        $chunks[] = $content['text'];
                    }
                }
            }

            $text = implode('', $chunks);
        }

        $decoded = json_decode($text, true);

        if (! is_array($decoded)) {
            throw new RuntimeException('OpenAI zwróciło odpowiedź, której nie udało się odczytać jako JSON.');
        }

        return $decoded;
    }

    /**
     * @param  array<string, mixed>  $generated
     * @param  array<string, mixed>  $fallback
     * @return array<string, mixed>
     */
    private function normalizeGenerated(array $generated, array $fallback): array
    {
        $label = $this->limit($this->sanitizePlain($generated['label'] ?? ''), 180);
        $excerpt = $this->limit($this->sanitizePlain($generated['excerpt'] ?? ''), 350);
        $metatitle = $this->limit($this->sanitizePlain($generated['metatitle'] ?? ''), 250);
        $metadescription = $this->limit($this->sanitizePlain($generated['metadescription'] ?? ''), 170);
        $content = $this->sanitizeHtml($generated['content'] ?? '');

        return [
            'label' => $label !== '' ? $label : $fallback['label'],
            'excerpt' => $excerpt !== '' ? $excerpt : $fallback['excerpt'],
            'metatitle' => $metatitle !== '' ? $metatitle : $fallback['metatitle'],
            'metadescription' => $metadescription !== '' ? $metadescription : $fallback['metadescription'],
            'content' => $content !== '' ? $content : $fallback['content'],
            'content_generated_at' => now(),
        ];
    }

    private function errorMessage(?array $body, int $status): string
    {
        $message = data_get($body, 'error.message') ?: 'OpenAI API zwróciło błąd.';

        return "{$message} [HTTP {$status}]";
    }

    private function styleMode(): string
    {
        $creative = filter_var(config('services.openai.sentence_generator_creative', false), FILTER_VALIDATE_BOOLEAN);

        return $creative ? 'creative' : 'strict';
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function styleExamples(Sentence $sentence): array
    {
        return Sentence::query()
            ->where('is_published', true)
            ->where('instance', $sentence->instance)
            ->whereKeyNot($sentence->getKey())
            ->whereNotNull('label')
            ->whereNotNull('excerpt')
            ->whereNotNull('content')
            ->orderByDesc('sentence_date')
            ->orderByDesc('id')
            ->limit(3)
            ->get()
            ->map(fn (Sentence $example): array => [
                'id' => $example->id,
                'instance' => $this->plain($example->instance),
                'label' => $this->sanitizePlain($example->label),
                'excerpt' => $this->sanitizePlain($example->excerpt),
                'metatitle' => $this->sanitizePlain($example->metatitle),
                'metadescription' => $this->sanitizePlain($example->metadescription),
                'content' => $this->limitHtml($this->sanitizeHtml($example->content), 8000),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function bankForms(?Bank $bank): array
    {
        if (! $bank) {
            return [];
        }

        return [
            'label' => $this->plain($bank->label),
            'form_a' => $this->plain($bank->form_a),
            'form_e' => $this->plain($bank->form_e),
            'form_w' => $this->plain($bank->form_w),
            'form_z' => $this->plain($bank->form_z),
        ];
    }

    private function lawyerRepresentationVerb(mixed $lawyer): string
    {
        return $this->lawyerGenderHint($this->plain($lawyer)) === 'female'
            ? 'reprezentowała'
            : 'reprezentował';
    }

    private function lawyerGenderHint(string $lawyer): string
    {
        $lawyer = preg_replace('/\b(adw|mec)\.?\s*/iu', '', $lawyer) ?? $lawyer;
        $lawyer = preg_replace('/\br\.?\s*pr\.?\s*/iu', '', $lawyer) ?? $lawyer;
        $lawyer = preg_replace('/\bradca prawny\s*/iu', '', $lawyer) ?? $lawyer;

        $name = Str::of($lawyer)
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->explode(' ')
            ->filter()
            ->first();

        if (! is_string($name) || $name === '') {
            return 'unknown';
        }

        return Str::endsWith($name, 'a') ? 'female' : 'male';
    }

    /**
     * @param  array<int, mixed>  $items
     * @return array<int, string>
     */
    private function safeRepeaterTexts(array $items): array
    {
        return collect($items)
            ->map(function (mixed $item): string {
                $text = is_array($item) ? ($item['text'] ?? '') : $item;

                return $this->sanitizeCaseText($text);
            })
            ->filter()
            ->values()
            ->all();
    }

    private function sanitizeHtml(mixed $html): string
    {
        $html = strip_tags((string) $html, '<p><ol><ul><li><h2><h3><strong><em><br>');
        $html = $this->sanitizeCaseText($html);

        if ($html === '') {
            return '';
        }

        if (! Str::contains($html, ['<p>', '<ol>', '<ul>', '<h2>', '<h3>'])) {
            return collect(preg_split("/\R{2,}/", $html) ?: [$html])
                ->map(fn (string $paragraph): string => Str::squish($paragraph))
                ->filter()
                ->map(fn (string $paragraph): string => '<p>' . e($paragraph) . '</p>')
                ->implode('');
        }

        return $html;
    }

    private function sanitizePlain(mixed $text): string
    {
        return $this->sanitizeCaseText(strip_tags((string) $text));
    }

    private function limitHtml(string $html, int $limit): string
    {
        if (mb_strlen($html) <= $limit) {
            return $html;
        }

        return mb_substr($html, 0, $limit);
    }

    private function sanitizeCaseText(mixed $text): string
    {
        $text = Str::squish((string) $text);

        if ($text === '') {
            return '';
        }

        $text = preg_replace('/\s+nr(?:\.|umer)?\s+["„”]?[[:alnum:]\/.-]+["„”]?/iu', '', $text) ?? $text;
        $text = preg_replace('/zawart(a|ą|ej|ego|e|y)?\s+dnia\s+\d{1,2}\s+[[:alpha:]ąćęłńóśźż]+\s+(\d{4})\s*(?:r\.|roku)?/iu', 'zawartej w $2 r.', $text) ?? $text;
        $text = preg_replace('/(umow[aeyąę]?[^,.;]{0,80})\s+z dnia\s+\d{1,2}\s+[[:alpha:]ąćęłńóśźż]+\s+(\d{4})\s*(?:r\.|roku)?/iu', '$1 z $2 r.', $text) ?? $text;

        return Str::squish($text);
    }

    private function plain(mixed $text): string
    {
        return Str::squish(strip_tags((string) $text));
    }

    private function limit(string $text, int $limit): string
    {
        return rtrim(Str::limit(Str::squish($text), $limit, ''));
    }
}
