<?php

namespace Tests\Feature;

use App\Enums\Website\ContactCategories;
use App\Models\Website\Bank;
use App\Models\Website\Contact;
use App\Models\Website\Sentence;
use App\Models\Website\SentenceContentTemplate;
use App\Services\Website\SentenceContentGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SentenceContentGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_draft_content_and_meta_from_sentence_fields(): void
    {
        [$court, $judge, $bank, $previousBank] = $this->dictionary();

        $sentence = Sentence::query()->create([
            'instance' => 1,
            'sign' => 'I C 123/26',
            'court_id' => $court->id,
            'judge_id' => $judge->id,
            'bank_id' => $bank->id,
            'bank_previously_id' => $previousBank->id,
            'lawsuit_date' => '2025-01-02',
            'sentence_date' => '2026-04-08',
            'credit_year' => '2008',
            'credit_name' => 'Umowa GE Money Banku z 2008 r.',
            'currency' => 'CHF',
            'wps' => '100000',
            'hearings' => '1',
            'claim' => 'ustalenie nieważności i zapłata',
            'result' => 'wygrana kredytobiorców',
            'lawyer' => 'adw. Wiktoria Rajzynger',
            'ruling_points' => [
                ['text' => 'ustalił nieważność umowy kredytu nr ABC/123 zawartej dnia 12 marca 2008 roku z GE Money Bankiem;'],
                ['text' => 'kosztami postępowania obciążył bank.'],
            ],
            'judgment_publication_mode' => 'closed_session',
            'reasoning_source' => 'oral',
            'court_reasoning_summary' => 'Sąd wskazał, że klauzule przeliczeniowe pozostawiały bankowi swobodę w ustalaniu kursu.',
            'evidence_scope' => ['borrower_hearing', 'documents', 'expert_omitted'],
            'security_granted' => true,
            'security_note' => 'Sąd udzielił zabezpieczenia przez wstrzymanie płatności rat.',
            'content_generator_flags' => [
                'counterclaim_dismissed',
                'setoff_dismissed',
            ],
            'credit_payoff' => 250000,
            'credit_profit' => 180000,
        ]);

        $generated = app(SentenceContentGenerator::class)->generate($sentence);

        $this->assertStringContainsString('Nieważna umowa GE Money Banku z 2008 r.', $generated['label']);
        $this->assertStringContainsString('wygrywamy w Sądzie Okręgowym w Legnicy', $generated['label']);
        $this->assertStringContainsString('Sądu Okręgowego w Legnicy', $generated['metatitle']);
        $this->assertStringContainsString('I C 123/26', $generated['metadescription']);
        $this->assertStringContainsString('<h2>Ustne motywy rozstrzygnięcia</h2>', $generated['content']);
        $this->assertStringContainsString('Sąd udzielił zabezpieczenia', $generated['content']);
        $this->assertStringContainsString('Kredytobiorców reprezentowała adw. Wiktoria Rajzynger.', $generated['content']);
        $this->assertStringContainsString('powództw', $generated['content']);
        $this->assertStringContainsString('potrącen', $generated['content']);
        $this->assertStringContainsString('180 000 zł', $generated['content']);
        $this->assertStringNotContainsString('Wynik sprawy', $generated['excerpt']);
        $this->assertStringNotContainsString('Ostateczny efekt ekonomiczny zależy', $generated['content']);
        $this->assertStringNotContainsString('ABC/123', $generated['content']);
        $this->assertStringNotContainsString('12 marca 2008', $generated['content']);
    }

    public function test_it_generates_second_instance_content_with_parent_sentence_context(): void
    {
        [$court, $judge, $bank, $previousBank] = $this->dictionary();

        $appealCourt = Contact::query()->create([
            'category' => ContactCategories::SAD->value,
            'label' => 'Sąd Apelacyjny we Wrocławiu',
            'sort_name' => 'Sąd Apelacyjny we Wrocławiu',
            'slug' => 'sad-apelacyjny-we-wroclawiu',
        ]);

        $parent = Sentence::query()->create([
            'instance' => 1,
            'sign' => 'I C 700/23',
            'court_id' => $court->id,
            'judge_id' => $judge->id,
            'bank_id' => $bank->id,
            'bank_previously_id' => $previousBank->id,
            'lawsuit_date' => '2023-04-10',
            'sentence_date' => '2024-08-30',
            'credit_year' => '2008',
            'credit_name' => 'Umowa GE Money Banku z 2008 r.',
            'currency' => 'CHF',
            'wps' => '100000',
            'hearings' => '1',
            'claim' => 'ustalenie',
            'result' => 'wygrana kredytobiorców',
            'lawyer' => 'adw. Bartosz Pręda',
        ]);

        $sentence = Sentence::query()->create([
            'instance' => 2,
            'parent_id' => $parent->id,
            'sign' => 'I ACa 4717/24',
            'court_id' => $appealCourt->id,
            'judge_id' => $judge->id,
            'bank_id' => $bank->id,
            'bank_previously_id' => $previousBank->id,
            'appeal_date' => '2024-10-10',
            'sentence_date' => '2026-04-08',
            'credit_year' => '2008',
            'credit_name' => 'Umowa GE Money Banku z 2008 r.',
            'currency' => 'CHF',
            'wps' => '100000',
            'hearings' => '1',
            'claim' => 'oddalenie apelacji',
            'result' => 'prawomocna wygrana kredytobiorców',
            'lawyer' => 'adw. Bartosz Pręda',
        ]);

        $generated = app(SentenceContentGenerator::class)->generate($sentence);

        $this->assertStringContainsString('Prawomocnie wygrywamy w Sądzie Apelacyjnym we Wrocławiu', $generated['label']);
        $this->assertStringContainsString('oddalił apelację banku od korzystnego dla kredytobiorców wyroku Sądu Okręgowego w Legnicy', $generated['content']);
        $this->assertStringContainsString('I C 700/23', $generated['content']);
    }

    public function test_it_normalizes_abbreviated_court_and_credit_product_name(): void
    {
        [, $judge, $bank, $previousBank] = $this->dictionary();

        $court = Contact::query()->create([
            'category' => ContactCategories::SAD->value,
            'label' => 'SO Zielona Góra',
            'sort_name' => 'SO Zielona Góra',
            'slug' => 'so-zielona-gora',
        ]);

        $sentence = Sentence::query()->create([
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
            'lawyer' => 'adw. Bartosz Pręda',
        ]);

        $generated = app(SentenceContentGenerator::class)->generate($sentence);

        $this->assertStringContainsString('Nieważna umowa Własny Kąt Hipoteczny z 2008 r.', $generated['label']);
        $this->assertStringContainsString('Sądzie Okręgowym w Zielonej Górze', $generated['label']);
        $this->assertStringNotContainsString('SO Zielona Góra', $generated['label']);
    }

    public function test_it_uses_sentence_content_template_for_matching_event(): void
    {
        SentenceContentTemplate::query()->create([
            'name' => 'Niestandardowe powództwo wzajemne',
            'is_active' => true,
            'section' => 'procedural_events',
            'all_of' => ['counterclaim_dismissed'],
            'priority' => 999,
            'selection_mode' => 'first',
            'content' => 'Szablon z bazy: {bank} przegrał również powództwo wzajemne w sprawie {sign}.',
        ]);

        [$court, $judge, $bank, $previousBank] = $this->dictionary();

        $sentence = Sentence::query()->create([
            'instance' => 1,
            'sign' => 'I C 555/26',
            'court_id' => $court->id,
            'judge_id' => $judge->id,
            'bank_id' => $bank->id,
            'bank_previously_id' => $previousBank->id,
            'lawsuit_date' => '2025-01-02',
            'sentence_date' => '2026-04-08',
            'credit_year' => '2008',
            'credit_name' => 'Umowa GE Money Banku z 2008 r.',
            'currency' => 'CHF',
            'wps' => '100000',
            'hearings' => '1',
            'claim' => 'ustalenie nieważności',
            'result' => 'wygrana kredytobiorców',
            'lawyer' => 'adw. Bartosz Pręda',
            'content_generator_flags' => ['counterclaim_dismissed'],
        ]);

        $generated = app(SentenceContentGenerator::class)->generate($sentence);

        $this->assertStringContainsString('Szablon z bazy: Bank BPH S.A. przegrał również powództwo wzajemne w sprawie I C 555/26.', $generated['content']);
    }

    public function test_more_specific_evidence_template_wins_over_generic_template(): void
    {
        SentenceContentTemplate::query()->create([
            'name' => 'Dowody ogólne',
            'is_active' => true,
            'section' => 'evidence',
            'all_of' => ['documents'],
            'priority' => 200,
            'selection_mode' => 'first',
            'content' => 'Ogólny szablon dowodowy.',
        ]);

        SentenceContentTemplate::query()->create([
            'name' => 'Dowody szczególne',
            'is_active' => true,
            'section' => 'evidence',
            'all_of' => ['documents', 'borrower_hearing'],
            'priority' => 200,
            'selection_mode' => 'first',
            'content' => 'Szczególny szablon dowodowy dla dokumentów i przesłuchania.',
        ]);

        [$court, $judge, $bank, $previousBank] = $this->dictionary();

        $sentence = Sentence::query()->create([
            'instance' => 1,
            'sign' => 'I C 556/26',
            'court_id' => $court->id,
            'judge_id' => $judge->id,
            'bank_id' => $bank->id,
            'bank_previously_id' => $previousBank->id,
            'lawsuit_date' => '2025-01-02',
            'sentence_date' => '2026-04-08',
            'credit_year' => '2008',
            'credit_name' => 'Umowa GE Money Banku z 2008 r.',
            'currency' => 'CHF',
            'wps' => '100000',
            'hearings' => '1',
            'claim' => 'ustalenie nieważności',
            'result' => 'wygrana kredytobiorców',
            'lawyer' => 'adw. Bartosz Pręda',
            'evidence_scope' => ['documents', 'borrower_hearing'],
        ]);

        $generated = app(SentenceContentGenerator::class)->generate($sentence);

        $this->assertStringContainsString('Szczególny szablon dowodowy dla dokumentów i przesłuchania.', $generated['content']);
        $this->assertStringNotContainsString('Ogólny szablon dowodowy.', $generated['content']);
    }

    /**
     * @return array{0: Contact, 1: Contact, 2: Bank, 3: Bank}
     */
    private function dictionary(): array
    {
        $court = Contact::query()->create([
            'category' => ContactCategories::SAD->value,
            'label' => 'Sąd Okręgowy w Legnicy',
            'sort_name' => 'Sąd Okręgowy w Legnicy',
            'slug' => 'sad-okregowy-w-legnicy',
        ]);

        $judge = Contact::query()->create([
            'category' => ContactCategories::SEDZIA->value,
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'label' => 'SSO Jan Kowalski',
            'sort_name' => 'Kowalski Jan',
            'slug' => 'jan-kowalski',
        ]);

        $bank = $this->bank('Bank BPH S.A.', 'bank-bph');
        $previousBank = $this->bank('GE Money Bank S.A.', 'ge-money-bank');

        return [$court, $judge, $bank, $previousBank];
    }

    private function bank(string $label, string $slug): Bank
    {
        return Bank::query()->create([
            'bank' => $label,
            'label' => $label,
            'form_a' => $label,
            'form_e' => $label,
            'form_w' => $label,
            'form_z' => $label,
            'slug' => $slug,
        ]);
    }
}
