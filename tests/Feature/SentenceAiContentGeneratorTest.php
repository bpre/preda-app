<?php

namespace Tests\Feature;

use App\Enums\Website\ContactCategories;
use App\Models\Website\Bank;
use App\Models\Website\Contact;
use App\Models\Website\Sentence;
use App\Services\Website\SentenceAiContentGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SentenceAiContentGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_structured_content_with_openai_responses_api(): void
    {
        config()->set('website.features.sentence_content_generator.enabled', true);
        config()->set('services.openai.api_key', 'test-openai-key');
        config()->set('services.openai.sentence_generator_ai_enabled', true);
        config()->set('services.openai.sentence_generator_model', 'gpt-5-mini');
        config()->set('services.openai.sentence_generator_creative', true);

        Http::fake([
            'api.openai.com/v1/responses' => Http::response([
                'output' => [[
                    'content' => [[
                        'type' => 'output_text',
                        'text' => json_encode([
                            'label' => 'Nieważna umowa Własny Kąt Hipoteczny z 2008 r. - wygrywamy w Sądzie Okręgowym w Zielonej Górze',
                            'excerpt' => 'Sąd Okręgowy w Zielonej Górze uwzględnił powództwo kredytobiorców dotyczące umowy Własny Kąt Hipoteczny z 2008 r.',
                            'metatitle' => 'Nieważna umowa Własny Kąt Hipoteczny z 2008 r. - wyrok Sądu Okręgowego w Zielonej Górze',
                            'metadescription' => 'Wyrok w sprawie I C 321/26: nieważna umowa Własny Kąt Hipoteczny z 2008 r.',
                            'content' => '<p>Wyrokiem z dnia 8 kwietnia 2026 r. Sąd Okręgowy w Zielonej Górze uwzględnił powództwo kredytobiorców dotyczące umowy Własny Kąt Hipoteczny z 2008 r.</p>',
                        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    ]],
                ]],
            ]),
        ]);

        $sentence = $this->sentence();
        $this->publishedExample($sentence, 'I C 100/26', '2026-04-09');
        $this->publishedExample($sentence, 'I C 101/26', '2026-04-08');
        $this->publishedExample($sentence, 'I C 102/26', '2026-04-07');
        $this->publishedExample($sentence, 'I ACa 200/26', '2026-04-10', 2);

        $generated = app(SentenceAiContentGenerator::class)->generate($sentence);

        $this->assertStringContainsString('Sądzie Okręgowym w Zielonej Górze', $generated['label']);
        $this->assertStringContainsString('Własny Kąt Hipoteczny', $generated['content']);
        $this->assertArrayHasKey('content_generated_at', $generated);

        Http::assertSent(function ($request): bool {
            $data = $request->data();
            $payload = json_decode(data_get($data, 'input.0.content.0.text'), true);

            return $request->url() === 'https://api.openai.com/v1/responses'
                && $request->hasHeader('Authorization', 'Bearer test-openai-key')
                && $data['model'] === 'gpt-5-mini'
                && str_contains($data['instructions'], 'nie dopisuj formuł typu "Wynik sprawy: wygrana kredytobiorców"')
                && str_contains($data['instructions'], 'lawyer_representation_verb')
                && str_contains($data['instructions'], 'style_mode = creative')
                && data_get($data, 'text.format.type') === 'json_schema'
                && data_get($data, 'text.format.strict') === true
                && data_get($payload, 'style_mode') === 'creative'
                && data_get($payload, 'grammar_hints.lawyer_representation_verb') === 'reprezentowała'
                && data_get($payload, 'grammar_hints.bank_current_forms.form_z') === 'z PKO BP S.A.'
                && count(data_get($payload, 'style_examples')) === 3
                && collect(data_get($payload, 'style_examples'))->every(fn (array $example): bool => $example['instance'] === '1')
                && ! str_contains(json_encode(data_get($payload, 'style_examples'), JSON_UNESCAPED_UNICODE), 'ABC/EXAMPLE');
        });
    }

    public function test_it_sanitizes_ai_output_before_saving(): void
    {
        config()->set('website.features.sentence_content_generator.enabled', true);
        config()->set('services.openai.api_key', 'test-openai-key');
        config()->set('services.openai.sentence_generator_ai_enabled', true);

        Http::fake([
            'api.openai.com/v1/responses' => Http::response([
                'output_text' => json_encode([
                    'label' => 'Nieważna umowa nr ABC/123 zawarta dnia 12 marca 2008 roku',
                    'excerpt' => 'Wygrana w sprawie umowy nr ABC/123.',
                    'metatitle' => 'Wyrok dotyczący umowy nr ABC/123',
                    'metadescription' => 'Wyrok dotyczący umowy zawartej dnia 12 marca 2008 roku.',
                    'content' => '<p>Umowa nr ABC/123 zawarta dnia 12 marca 2008 roku jest nieważna.</p><script>alert(1)</script>',
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]),
        ]);

        $generated = app(SentenceAiContentGenerator::class)->generate($this->sentence());

        $this->assertStringNotContainsString('ABC/123', implode(' ', [
            $generated['label'],
            $generated['excerpt'],
            $generated['metatitle'],
            $generated['metadescription'],
            $generated['content'],
        ]));
        $this->assertStringNotContainsString('12 marca 2008', $generated['content']);
        $this->assertStringNotContainsString('<script>', $generated['content']);
    }

    public function test_it_is_not_configured_when_sentence_ai_is_disabled(): void
    {
        config()->set('website.features.sentence_content_generator.enabled', true);
        config()->set('services.openai.api_key', 'test-openai-key');
        config()->set('services.openai.sentence_generator_ai_enabled', false);

        $this->assertFalse(app(SentenceAiContentGenerator::class)->isConfigured());
    }

    public function test_it_is_not_configured_when_sentence_content_generator_module_is_disabled(): void
    {
        config()->set('website.features.sentence_content_generator.enabled', false);
        config()->set('services.openai.api_key', 'test-openai-key');
        config()->set('services.openai.sentence_generator_ai_enabled', true);

        $this->assertFalse(app(SentenceAiContentGenerator::class)->isConfigured());
    }

    private function sentence(): Sentence
    {
        $court = Contact::query()->create([
            'category' => ContactCategories::SAD->value,
            'label' => 'SO Zielona Góra',
            'sort_name' => 'SO Zielona Góra',
            'slug' => 'so-zielona-gora',
        ]);

        $judge = Contact::query()->create([
            'category' => ContactCategories::SEDZIA->value,
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'label' => 'SSO Jan Kowalski',
            'sort_name' => 'Kowalski Jan',
            'slug' => 'jan-kowalski',
        ]);

        $bank = $this->bank('PKO BP S.A.', 'pko-bp');
        $previousBank = $this->bank('PKO BP S.A.', 'pko-bp-poprzednio');

        return Sentence::query()->create([
            'instance' => 1,
            'sign' => 'I C 321/26',
            'court_id' => $court->id,
            'judge_id' => $judge->id,
            'bank_id' => $bank->id,
            'bank_previously_id' => $previousBank->id,
            'lawsuit_date' => '2025-01-02',
            'sentence_date' => '2026-04-08',
            'credit_year' => '2008',
            'credit_name' => 'własny Kąt Hipoteczny z 2008 r.',
            'currency' => 'CHF',
            'wps' => '100000',
            'hearings' => '1',
            'claim' => 'ustalenie nieważności',
            'result' => 'wygrana kredytobiorców',
            'lawyer' => 'adw. Wiktoria Rajzynger',
            'ruling_points' => [
                ['text' => 'ustalił nieważność umowy kredytu nr ABC/123 zawartej dnia 12 marca 2008 roku;'],
            ],
        ]);
    }

    private function bank(string $label, string $slug): Bank
    {
        return Bank::query()->create([
            'bank' => $label,
            'label' => $label,
            'form_a' => $label,
            'form_e' => $label,
            'form_w' => 'w ' . $label,
            'form_z' => 'z ' . $label,
            'slug' => $slug,
        ]);
    }

    private function publishedExample(Sentence $sentence, string $sign, string $sentenceDate, int $instance = 1): Sentence
    {
        return Sentence::query()->create([
            'instance' => $instance,
            'sign' => $sign,
            'court_id' => $sentence->court_id,
            'judge_id' => $sentence->judge_id,
            'bank_id' => $sentence->bank_id,
            'bank_previously_id' => $sentence->bank_previously_id,
            'lawsuit_date' => '2025-01-02',
            'appeal_date' => $instance === 2 ? '2025-05-02' : null,
            'sentence_date' => $sentenceDate,
            'credit_year' => '2008',
            'credit_name' => 'własny Kąt Hipoteczny z 2008 r.',
            'currency' => 'CHF',
            'wps' => '100000',
            'hearings' => '1',
            'claim' => 'ustalenie nieważności',
            'result' => 'wygrana kredytobiorców',
            'lawyer' => 'adw. Wiktoria Rajzynger',
            'label' => 'Przykładowy wpis ' . $sign,
            'excerpt' => 'Przykładowa zajawka dotycząca umowy nr ABC/EXAMPLE.',
            'content' => '<p>Przykładowa treść wpisu dotycząca umowy nr ABC/EXAMPLE zawartej dnia 12 marca 2008 roku.</p>',
            'metatitle' => 'Przykładowy meta title ' . $sign,
            'metadescription' => 'Przykładowy meta description.',
            'is_published' => true,
        ]);
    }
}
