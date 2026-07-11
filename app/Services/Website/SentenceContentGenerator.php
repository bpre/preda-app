<?php

namespace App\Services\Website;

use App\Models\Website\Sentence;
use App\Models\Website\SentenceContentTemplate;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SentenceContentGenerator
{
    /**
     * @return array<string, mixed>
     */
    public function generate(Sentence $sentence): array
    {
        $sentence->loadMissing([
            'bank',
            'bank_previously',
            'court',
            'judge',
            'parent.bank',
            'parent.bank_previously',
            'parent.court',
        ]);

        return [
            'label' => $this->label($sentence),
            'excerpt' => $this->excerpt($sentence),
            'content' => $this->content($sentence),
            'metatitle' => $this->metaTitle($sentence),
            'metadescription' => $this->metaDescription($sentence),
            'content_generated_at' => now(),
        ];
    }

    private function label(Sentence $sentence): string
    {
        $court = $this->courtLocativeName($sentence);
        $credit = $this->invalidAgreementPhrase($sentence);

        $label = match ((string) $sentence->instance) {
            '2' => "Prawomocnie wygrywamy w {$court} - {$credit}!",
            '3' => "Wygrana w postępowaniu kasacyjnym - {$credit}!",
            default => "{$credit} - wygrywamy w {$court}!",
        };

        return $this->limit($label, 180);
    }

    private function excerpt(Sentence $sentence): string
    {
        $court = $this->courtName($sentence);
        $credit = $this->creditObjectPhrase($sentence);
        $bank = $this->bankWithName($sentence);

        return $this->limit(match ((string) $sentence->instance) {
            '2' => "{$court} oddala apelację banku i potwierdza korzystny dla kredytobiorców wyrok dotyczący {$credit}.",
            '3' => "Korzystne rozstrzygnięcie w postępowaniu kasacyjnym dotyczącym {$credit} w sporze {$bank}.",
            default => "{$court} uwzględnia powództwo kredytobiorców dotyczące {$credit} w sporze {$bank}.",
        }, 300);
    }

    private function metaTitle(Sentence $sentence): string
    {
        $prefix = match ((string) $sentence->instance) {
            '2' => 'Prawomocna wygrana',
            '3' => 'Wygrana w postępowaniu kasacyjnym',
            default => 'Wygrana kredytobiorców',
        };

        $parts = [
            "{$prefix} z {$this->bankName($sentence)}",
            'wyrok ' . $this->courtGenitiveName($sentence),
        ];

        if (filled($sentence->sign)) {
            $parts[] = '(' . $this->plain($sentence->sign) . ')';
        }

        return $this->limit(implode(' - ', $parts), 250);
    }

    private function metaDescription(Sentence $sentence): string
    {
        $description = 'Wyrok';

        if (filled($sentence->sign)) {
            $description .= ' w sprawie ' . $this->plain($sentence->sign);
        }

        $description .= ': ' . $this->courtName($sentence);

        if ($date = $this->date($sentence->sentence_date)) {
            $description .= ' z dnia ' . $date;
        }

        $description .= ' dotyczący ' . $this->creditObjectPhrase($sentence) . '.';

        if (filled($sentence->result)) {
            $description .= ' ' . Str::ucfirst($this->plain($sentence->result)) . '.';
        }

        return $this->limit($description, 170);
    }

    private function content(Sentence $sentence): string
    {
        $blocks = [];
        $date = $this->date($sentence->sentence_date);
        $court = $this->courtName($sentence);

        $blocks[] = $this->paragraph($date
            ? "Wyrokiem z dnia {$date} {$court}:"
            : "Wyrokiem {$court}:"
        );

        $blocks[] = $this->orderedList($this->rulingPoints($sentence));

        if ($lead = $this->leadParagraph($sentence)) {
            $blocks[] = $this->paragraph($lead);
        }

        if ($mode = $this->publicationModeParagraph($sentence)) {
            $blocks[] = $this->paragraph($mode);
        }

        $blocks = array_merge($blocks, $this->reasoningBlocks($sentence));

        foreach ($this->evidenceParagraphs($sentence) as $paragraph) {
            $blocks[] = $this->paragraph($paragraph);
        }

        if ($sentence->security_granted) {
            foreach ($this->securityParagraphs($sentence) as $paragraph) {
                $blocks[] = $this->paragraph($paragraph);
            }
        }

        foreach ($this->proceduralFlagParagraphs($sentence) as $paragraph) {
            $blocks[] = $this->paragraph($paragraph);
        }

        foreach ([
            $sentence->setoff_or_retention_note,
            $sentence->counterclaim_note,
            $sentence->content_note,
        ] as $note) {
            if (filled($note)) {
                $blocks[] = $this->paragraph($this->sanitizeCaseText($note));
            }
        }

        if ($benefit = $this->benefitParagraph($sentence)) {
            $blocks[] = '<h2>Korzyść z wygrania sprawy</h2>';
            $blocks[] = $this->paragraph($benefit);
        }

        return implode('', array_filter($blocks));
    }

    /**
     * @return array<int, string>
     */
    private function rulingPoints(Sentence $sentence): array
    {
        $points = collect($sentence->ruling_points ?? [])
            ->map(function (mixed $point): string {
                $text = is_array($point) ? ($point['text'] ?? '') : $point;

                return $this->sanitizeCaseText($text);
            })
            ->filter()
            ->values()
            ->all();

        if ($points !== []) {
            return $points;
        }

        if ((string) $sentence->instance === '2') {
            return array_values(array_filter([
                $this->defaultAppealPoint($sentence),
                'kosztami postępowania apelacyjnego obciążył bank.',
            ]));
        }

        return array_values(array_filter([
            filled($sentence->claim)
                ? 'uwzględnił powództwo kredytobiorców w zakresie: ' . $this->plain($sentence->claim) . '.'
                : 'uwzględnił powództwo kredytobiorców.',
            'kosztami postępowania obciążył bank.',
        ]));
    }

    private function defaultAppealPoint(Sentence $sentence): string
    {
        $parent = $sentence->parent;

        if (! $parent) {
            return 'oddalił apelację banku od korzystnego dla kredytobiorców wyroku sądu I instancji.';
        }

        $parts = ['oddalił apelację banku od korzystnego dla kredytobiorców wyroku'];

        if ($parent->court?->label) {
            $parts[] = $this->courtGenitiveName($parent);
        } else {
            $parts[] = 'sądu I instancji';
        }

        if ($date = $this->date($parent->sentence_date)) {
            $parts[] = 'z dnia ' . $date;
        }

        if (filled($parent->sign)) {
            $parts[] = 'wydanego w sprawie ' . $this->plain($parent->sign);
        }

        return implode(' ', $parts) . '.';
    }

    private function leadParagraph(Sentence $sentence): string
    {
        $credit = $this->creditObjectPhrase($sentence);
        $bank = $this->bankName($sentence);
        $previousBank = $this->previousBankName($sentence);
        $currency = filled($sentence->currency) ? $this->plain($sentence->currency) : 'CHF';

        $paragraph = "Sprawa dotyczyła {$credit} powiązanej z kursem {$currency}";

        if ($previousBank !== $bank) {
            $paragraph .= ", zawartej pierwotnie z {$previousBank}";
        }

        $paragraph .= ", w której pozwanym był {$bank}.";

        if ($sentence->is_paid_off && filled($sentence->paid_off_year)) {
            $paragraph .= ' Kredyt został spłacony w ' . $this->plain($sentence->paid_off_year) . ' r.';
        }

        if (filled($sentence->lawyer)) {
            $paragraph .= ' Kredytobiorców ' . $this->lawyerRepresentationPhrase($sentence) . '.';
        }

        return $paragraph;
    }

    private function publicationModeParagraph(Sentence $sentence): ?string
    {
        return match ($sentence->judgment_publication_mode) {
            'closed_session' => 'Wyrok został wydany na posiedzeniu niejawnym.',
            'hearing' => filled($sentence->hearings)
                ? 'Do wydania wyroku doszło po przeprowadzeniu rozpraw: ' . $this->plain($sentence->hearings) . '.'
                : null,
            default => null,
        };
    }

    /**
     * @return array<int, string>
     */
    private function reasoningBlocks(Sentence $sentence): array
    {
        $summary = $this->sanitizeCaseText($sentence->court_reasoning_summary);

        if (! filled($summary)) {
            return [
                '<h2>Motywy rozstrzygnięcia</h2>',
                $this->paragraph('Sąd podzielił argumentację kredytobiorców dotyczącą abuzywnego charakteru postanowień umowy oraz skutków ich eliminacji z kontraktu. Rozstrzygnięcie wpisuje się w linię orzeczniczą dotyczącą nieważności umów kredytowych powiązanych z kursem waluty obcej.'),
            ];
        }

        $heading = match ($sentence->reasoning_source) {
            'oral' => 'Ustne motywy rozstrzygnięcia',
            'written' => 'Najważniejsze motywy rozstrzygnięcia',
            default => 'Motywy rozstrzygnięcia',
        };

        $blocks = ['<h2>' . e($heading) . '</h2>'];

        foreach ($this->paragraphs($summary) as $paragraph) {
            $blocks[] = $this->paragraph($paragraph);
        }

        return $blocks;
    }

    /**
     * @return array<int, string>
     */
    private function evidenceParagraphs(Sentence $sentence): array
    {
        $scope = collect($sentence->evidence_scope ?? []);

        if ($scope->isEmpty()) {
            return [];
        }

        $conducted = $this->evidenceLabels($scope->intersect([
            'borrower_hearing',
            'documents',
            'witnesses',
            'expert_opinion',
        ]));

        $omitted = $this->evidenceLabels($scope->intersect([
            'expert_omitted',
            'bank_witness_omitted',
        ]));

        $paragraphs = [];

        if ($conducted !== []) {
            $paragraphs[] = 'Postępowanie dowodowe obejmowało przede wszystkim ' . $this->join($conducted) . '.';
        }

        if ($omitted !== []) {
            $paragraphs[] = 'Sąd pominął ' . $this->join($omitted) . ', uznając je za zbędne dla rozstrzygnięcia sprawy.';
        }

        return $this->templateParagraphs($sentence, 'evidence', $paragraphs);
    }

    /**
     * @return array<int, string>
     */
    private function securityParagraphs(Sentence $sentence): array
    {
        if (filled($sentence->security_note)) {
            return [$this->sanitizeCaseText($sentence->security_note)];
        }

        return $this->templateParagraphs($sentence, 'security', [
            'W toku sprawy kredytobiorcy uzyskali zabezpieczenie roszczenia, co pozwoliło im czasowo wstrzymać wykonywanie spornych obowiązków wynikających z umowy.',
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function proceduralFlagParagraphs(Sentence $sentence): array
    {
        $flags = collect($sentence->content_generator_flags ?? []);

        $paragraphs = [];

        if ($flags->contains('counterclaim_dismissed')) {
            $paragraphs[] = $this->templateParagraphForCondition(
                $sentence,
                'procedural_events',
                'counterclaim_dismissed',
                'Bank wniósł w sprawie powództwo wzajemne, jednak sąd go nie uwzględnił.',
            );
        }

        if ($flags->contains('setoff_dismissed')) {
            $paragraphs[] = $this->templateParagraphForCondition(
                $sentence,
                'procedural_events',
                'setoff_dismissed',
                'Sąd nie uwzględnił podniesionego przez bank zarzutu potrącenia.',
            );
        }

        if ($flags->contains('retention_dismissed')) {
            $paragraphs[] = $this->templateParagraphForCondition(
                $sentence,
                'procedural_events',
                'retention_dismissed',
                'Sąd nie uwzględnił podniesionego przez bank zarzutu zatrzymania.',
            );
        }

        return array_values(array_filter($paragraphs));
    }

    private function benefitParagraph(Sentence $sentence): ?string
    {
        $parts = [];

        if (filled($sentence->credit_profit)) {
            $parts[] = 'szacowana korzyść kredytobiorców wynosi około ' . $this->money($sentence->credit_profit);
        }

        if (filled($sentence->credit_payoff)) {
            $parts[] = 'bank wypłacił kredyt w kwocie ' . $this->money($sentence->credit_payoff);
        }

        if ($parts === []) {
            return null;
        }

        $fallback = Str::ucfirst($this->join($parts)) . '.';

        return $this->templateParagraphs($sentence, 'benefit', [$fallback])[0] ?? $fallback;
    }

    /**
     * @param  array<int, string>  $fallbackParagraphs
     * @return array<int, string>
     */
    private function templateParagraphs(Sentence $sentence, string $section, array $fallbackParagraphs): array
    {
        $template = $this->selectTemplate($this->matchingTemplates($sentence, $section));

        if (! $template) {
            return $fallbackParagraphs;
        }

        $paragraphs = $this->renderTemplateParagraphs($sentence, $template->content);

        return $paragraphs !== [] ? $paragraphs : $fallbackParagraphs;
    }

    private function templateParagraphForCondition(Sentence $sentence, string $section, string $condition, string $fallback): string
    {
        $templates = $this->matchingTemplates($sentence, $section)
            ->filter(fn (SentenceContentTemplate $template): bool => $this->templateReferencesCondition($template, $condition));

        $template = $this->selectTemplate($templates);

        if (! $template) {
            return $fallback;
        }

        return $this->renderTemplateParagraphs($sentence, $template->content)[0] ?? $fallback;
    }

    /**
     * @return Collection<int, SentenceContentTemplate>
     */
    private function matchingTemplates(Sentence $sentence, string $section): Collection
    {
        $conditions = $this->conditionSet($sentence);

        return SentenceContentTemplate::query()
            ->where('is_active', true)
            ->where('section', $section)
            ->where(fn ($query) => $query
                ->whereNull('instance')
                ->orWhere('instance', (string) $sentence->instance))
            ->get()
            ->filter(fn (SentenceContentTemplate $template): bool => $this->templateMatchesConditions($template, $conditions))
            ->values();
    }

    /**
     * @param  Collection<int, SentenceContentTemplate>  $templates
     */
    private function selectTemplate(Collection $templates): ?SentenceContentTemplate
    {
        if ($templates->isEmpty()) {
            return null;
        }

        $bestScore = $templates->max(fn (SentenceContentTemplate $template): int => $this->templateScore($template));
        $pool = $templates
            ->filter(fn (SentenceContentTemplate $template): bool => $this->templateScore($template) === $bestScore)
            ->sortBy([
                ['sort', 'asc'],
                ['id', 'asc'],
            ])
            ->values();

        if ($pool->contains(fn (SentenceContentTemplate $template): bool => $template->selection_mode === 'random')) {
            return $pool->random();
        }

        return $pool->first();
    }

    private function templateScore(SentenceContentTemplate $template): int
    {
        return ((int) $template->priority * 100)
            + (count($template->all_of ?? []) * 10)
            + count($template->any_of ?? []);
    }

    /**
     * @param  array<int, string>  $conditions
     */
    private function templateMatchesConditions(SentenceContentTemplate $template, array $conditions): bool
    {
        $allOf = $template->all_of ?? [];
        $anyOf = $template->any_of ?? [];
        $noneOf = $template->none_of ?? [];

        if ($allOf !== [] && array_diff($allOf, $conditions) !== []) {
            return false;
        }

        if ($anyOf !== [] && array_intersect($anyOf, $conditions) === []) {
            return false;
        }

        if ($noneOf !== [] && array_intersect($noneOf, $conditions) !== []) {
            return false;
        }

        return true;
    }

    private function templateReferencesCondition(SentenceContentTemplate $template, string $condition): bool
    {
        return in_array($condition, $template->all_of ?? [], true)
            || in_array($condition, $template->any_of ?? [], true);
    }

    /**
     * @return array<int, string>
     */
    private function conditionSet(Sentence $sentence): array
    {
        $conditions = ['instance_' . $sentence->instance];

        if ($sentence->parent_id) {
            $conditions[] = 'parent_exists';
        }

        if ($sentence->security_granted) {
            $conditions[] = 'security_granted';
        }

        if ($sentence->is_paid_off) {
            $conditions[] = 'paid_off';
        }

        if ($sentence->credit_profit !== null) {
            $conditions[] = 'credit_profit_present';
        }

        if ($sentence->credit_payoff !== null) {
            $conditions[] = 'credit_payoff_present';
        }

        if ($sentence->judgment_publication_mode === 'closed_session') {
            $conditions[] = 'closed_session';
        }

        if ($sentence->judgment_publication_mode === 'hearing') {
            $conditions[] = 'hearing_publication';
        }

        if ($sentence->reasoning_source === 'oral') {
            $conditions[] = 'oral_reasons';
        }

        if ($sentence->reasoning_source === 'written') {
            $conditions[] = 'written_reasons';
        }

        return collect($conditions)
            ->merge($sentence->evidence_scope ?? [])
            ->merge($sentence->content_generator_flags ?? [])
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function renderTemplateParagraphs(Sentence $sentence, string $content): array
    {
        $content = $this->replacePlaceholders($sentence, $content);
        $content = strip_tags($content);

        return collect(preg_split("/\R{2,}/", trim($content)) ?: [])
            ->map(fn (string $paragraph): string => Str::squish($paragraph))
            ->filter()
            ->values()
            ->all();
    }

    private function replacePlaceholders(Sentence $sentence, string $content): string
    {
        return strtr($content, $this->placeholderValues($sentence));
    }

    /**
     * @return array<string, string>
     */
    private function placeholderValues(Sentence $sentence): array
    {
        return [
            '{court}' => $this->courtName($sentence),
            '{court_locative}' => $this->courtLocativeName($sentence),
            '{court_genitive}' => $this->courtGenitiveName($sentence),
            '{bank}' => $this->bankName($sentence),
            '{bank_form_a}' => $this->bankForm($sentence, 'form_a'),
            '{bank_form_e}' => $this->bankForm($sentence, 'form_e'),
            '{bank_form_w}' => $this->bankForm($sentence, 'form_w'),
            '{bank_form_z}' => $this->bankWithName($sentence),
            '{previous_bank}' => $this->previousBankName($sentence),
            '{credit_name}' => $this->creditHeadline($sentence),
            '{credit_object}' => $this->creditObjectPhrase($sentence),
            '{invalid_credit}' => $this->invalidAgreementPhrase($sentence),
            '{credit_year}' => $this->plain($sentence->credit_year),
            '{currency}' => $this->plain($sentence->currency),
            '{sign}' => $this->plain($sentence->sign),
            '{sentence_date}' => $this->date($sentence->sentence_date) ?? '',
            '{lawyer}' => $this->plain($sentence->lawyer),
            '{lawyer_phrase}' => $this->lawyerRepresentationPhrase($sentence),
            '{credit_profit}' => filled($sentence->credit_profit) ? $this->money($sentence->credit_profit) : '',
            '{credit_payoff}' => filled($sentence->credit_payoff) ? $this->money($sentence->credit_payoff) : '',
            '{parent_court}' => $sentence->parent ? $this->courtName($sentence->parent) : '',
            '{parent_sign}' => $this->plain($sentence->parent?->sign),
            '{hearings}' => $this->plain($sentence->hearings),
        ];
    }

    private function creditHeadline(Sentence $sentence): string
    {
        if (filled($sentence->credit_name)) {
            return $this->agreementPhrase($this->plain($sentence->credit_name));
        }

        $bank = $this->previousBankName($sentence);
        $year = filled($sentence->credit_year) ? ' z ' . $this->plain($sentence->credit_year) . ' r.' : '';

        return "umowa kredytowa {$bank}{$year}";
    }

    private function invalidAgreementPhrase(Sentence $sentence): string
    {
        return 'Nieważna ' . $this->creditHeadline($sentence);
    }

    private function agreementPhrase(string $creditName): string
    {
        $creditName = Str::squish($creditName);

        if ($creditName === '') {
            return 'umowa kredytowa';
        }

        if (Str::startsWith(Str::lower($creditName), ['umowa ', 'umowy '])) {
            return $this->lowerFirst($creditName);
        }

        return 'umowa ' . $this->upperFirst($creditName);
    }

    private function creditObjectPhrase(Sentence $sentence): string
    {
        $credit = $this->creditHeadline($sentence);

        if (Str::startsWith($credit, 'umowa kredytowa ')) {
            return 'umowy kredytowej ' . mb_substr($credit, mb_strlen('umowa kredytowa '));
        }

        if (Str::startsWith($credit, 'umowa ')) {
            return 'umowy ' . mb_substr($credit, mb_strlen('umowa '));
        }

        return $credit;
    }

    private function courtName(Sentence $sentence): string
    {
        return $this->normalizeCourtName($sentence->court?->label);
    }

    private function normalizeCourtName(?string $court): string
    {
        $court = Str::squish((string) $court);

        if ($court === '') {
            return 'sąd';
        }

        $shortcuts = [
            'SO ' => 'Sąd Okręgowy',
            'SR ' => 'Sąd Rejonowy',
            'SA ' => 'Sąd Apelacyjny',
        ];

        foreach ($shortcuts as $shortcut => $fullName) {
            if (! Str::startsWith($court, $shortcut)) {
                continue;
            }

            $city = Str::squish(Str::after($court, $shortcut));

            if ($city === 'Warszawa - Praga') {
                return "{$fullName} Warszawa-Praga w Warszawie";
            }

            return "{$fullName} {$this->cityCourtLocation($city)}";
        }

        return $court;
    }

    private function cityCourtLocation(string $city): string
    {
        return match ($city) {
            'Częstochowa' => 'w Częstochowie',
            'Gdańsk' => 'w Gdańsku',
            'Głogów' => 'w Głogowie',
            'Gorzów Wielkopolski' => 'w Gorzowie Wielkopolskim',
            'Jelenia Góra' => 'w Jeleniej Górze',
            'Kalisz' => 'w Kaliszu',
            'Legnica' => 'w Legnicy',
            'Leszno' => 'w Lesznie',
            'Lubin' => 'w Lubinie',
            'Łódź' => 'w Łodzi',
            'Poznań' => 'w Poznaniu',
            'Warszawa' => 'w Warszawie',
            'Wrocław' => 'we Wrocławiu',
            'Wschowa' => 'we Wschowie',
            'Zielona Góra' => 'w Zielonej Górze',
            default => 'w ' . $city,
        };
    }

    private function courtGenitiveName(Sentence $sentence): string
    {
        $court = $this->courtName($sentence);

        foreach ([
            'Sąd Okręgowy' => 'Sądu Okręgowego',
            'Sąd Rejonowy' => 'Sądu Rejonowego',
            'Sąd Apelacyjny' => 'Sądu Apelacyjnego',
        ] as $from => $to) {
            if (Str::startsWith($court, $from)) {
                return $to . mb_substr($court, mb_strlen($from));
            }
        }

        return $court;
    }

    private function courtLocativeName(Sentence $sentence): string
    {
        $court = $this->courtName($sentence);

        foreach ([
            'Sąd Okręgowy' => 'Sądzie Okręgowym',
            'Sąd Rejonowy' => 'Sądzie Rejonowym',
            'Sąd Apelacyjny' => 'Sądzie Apelacyjnym',
        ] as $from => $to) {
            if (Str::startsWith($court, $from)) {
                return $to . mb_substr($court, mb_strlen($from));
            }
        }

        return $court;
    }

    private function bankName(Sentence $sentence): string
    {
        return $sentence->bank?->label ?: $this->previousBankName($sentence);
    }

    private function bankWithName(Sentence $sentence): string
    {
        $form = $this->plain($sentence->bank?->form_z);

        if ($form !== '') {
            return Str::startsWith(Str::lower($form), 'z ') ? $form : 'z ' . $form;
        }

        return 'z ' . $this->bankName($sentence);
    }

    private function bankForm(Sentence $sentence, string $field): string
    {
        $form = $this->plain($sentence->bank?->{$field});

        return $form !== '' ? $form : $this->bankName($sentence);
    }

    private function previousBankName(Sentence $sentence): string
    {
        return $sentence->bank_previously?->label ?: 'bankiem';
    }

    private function lawyerRepresentationPhrase(Sentence $sentence): string
    {
        $lawyer = $this->plain($sentence->lawyer);
        $verb = $this->lawyerGenderHint($lawyer) === 'female' ? 'reprezentowała' : 'reprezentował';

        return "{$verb} {$lawyer}";
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

    private function date(mixed $date): ?string
    {
        if (! filled($date)) {
            return null;
        }

        return CarbonImmutable::parse($date)
            ->locale('pl')
            ->translatedFormat('j F Y') . ' r.';
    }

    private function money(mixed $amount): string
    {
        return number_format((float) $amount, 0, ',', ' ') . ' zł';
    }

    private function paragraph(string $text): string
    {
        return '<p>' . e(Str::squish($text)) . '</p>';
    }

    /**
     * @param  array<int, string>  $items
     */
    private function orderedList(array $items): string
    {
        $listItems = collect($items)
            ->filter()
            ->map(fn (string $item): string => '<li><p>' . e(Str::squish($item)) . '</p></li>')
            ->implode('');

        return '<ol start="1">' . $listItems . '</ol>';
    }

    /**
     * @return array<int, string>
     */
    private function paragraphs(string $text): array
    {
        return collect(preg_split("/\R{2,}/", trim($text)) ?: [])
            ->map(fn (string $paragraph): string => Str::squish($paragraph))
            ->filter()
            ->values()
            ->all();
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

    private function lowerFirst(string $text): string
    {
        if ($text === '') {
            return '';
        }

        return mb_strtolower(mb_substr($text, 0, 1)) . mb_substr($text, 1);
    }

    private function upperFirst(string $text): string
    {
        if ($text === '') {
            return '';
        }

        return mb_strtoupper(mb_substr($text, 0, 1)) . mb_substr($text, 1);
    }

    /**
     * @param  Collection<int, string>  $scope
     * @return array<int, string>
     */
    private function evidenceLabels(Collection $scope): array
    {
        $labels = [
            'borrower_hearing' => 'przesłuchanie kredytobiorców',
            'documents' => 'dokumenty złożone w sprawie',
            'witnesses' => 'zeznania świadków',
            'expert_opinion' => 'opinię biegłego',
            'expert_omitted' => 'dowód z opinii biegłego',
            'bank_witness_omitted' => 'dowód z zeznań świadków banku',
        ];

        return $scope
            ->map(fn (string $key): ?string => $labels[$key] ?? null)
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $items
     */
    private function join(array $items): string
    {
        $items = array_values(array_filter($items));

        if (count($items) <= 1) {
            return $items[0] ?? '';
        }

        $last = array_pop($items);

        return implode(', ', $items) . ' oraz ' . $last;
    }

    private function limit(string $text, int $limit): string
    {
        return rtrim(Str::limit(Str::squish($text), $limit, ''));
    }
}
