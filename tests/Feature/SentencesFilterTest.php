<?php

namespace Tests\Feature;

use App\Enums\Website\ContactCategories;
use App\Enums\Website\Provinces;
use App\Livewire\Website\Sentences;
use App\Models\Website\Bank;
use App\Models\Website\City;
use App\Models\Website\Contact;
use App\Models\Website\Sentence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SentencesFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_sentences_can_be_filtered_by_bank_currency_and_court(): void
    {
        $fixtures = $this->createSentenceFixtures();

        Livewire::test(Sentences::class)
            ->set('filterData.bank_id', $fixtures['legacyBank']->id)
            ->assertSee('Wyrok Alpha')
            ->assertDontSee('Wyrok Beta')
            ->set('filterData.bank_id', null)
            ->set('filterData.currency', 'EUR')
            ->assertSee('Wyrok Beta')
            ->assertDontSee('Wyrok Alpha')
            ->set('filterData.currency', null)
            ->set('filterData.court_id', $fixtures['courtA']->id)
            ->assertSee('Wyrok Alpha')
            ->assertDontSee('Wyrok Beta');
    }

    public function test_sentence_search_matches_sign_judge_current_bank_and_previous_bank(): void
    {
        $this->createSentenceFixtures();

        $this->assertSearchShowsOnly('I C 123/26', 'Wyrok Alpha', 'Wyrok Beta');
        $this->assertSearchShowsOnly('Pozlewicz', 'Wyrok Beta', 'Wyrok Alpha');
        $this->assertSearchShowsOnly('Alpha Current', 'Wyrok Alpha', 'Wyrok Beta');
        $this->assertSearchShowsOnly('Legacy Original', 'Wyrok Alpha', 'Wyrok Beta');
    }

    public function test_sentence_filters_are_collapsed_by_default_and_show_active_filter_count(): void
    {
        $this->createSentenceFixtures();

        Livewire::test(Sentences::class)
            ->assertSee('Pokaż filtry')
            ->assertDontSee('Ukryj filtry')
            ->assertDontSee('Usuń zastosowane filtry')
            ->assertDontSee('Sygnatura, sędzia, bank...')
            ->assertDontSee('Wszystkie banki')
            ->call('toggleFilters')
            ->assertSee('Ukryj filtry')
            ->assertSee('Sygnatura, sędzia, bank...')
            ->assertSee('Wszystkie banki')
            ->set('search', 'Alpha')
            ->assertSee('Usuń zastosowane filtry (1)')
            ->set('filterData.currency', 'CHF')
            ->assertSee('Usuń zastosowane filtry (2)')
            ->call('clearFilters')
            ->assertDontSee('Usuń zastosowane filtry');
    }

    public function test_sentences_can_still_be_prefiltered_by_currency_component_parameter(): void
    {
        $this->createSentenceFixtures();

        Livewire::test(Sentences::class, ['more' => true, 'currency' => 'EUR'])
            ->assertSee('Wyrok Beta')
            ->assertDontSee('Wyrok Alpha');
    }

    public function test_more_sentences_link_can_point_to_currency_sentences_listing(): void
    {
        $this->createSentenceFixtures();

        Livewire::test(Sentences::class, [
            'more' => true,
            'currency' => 'EUR',
            'more_url' => 'wyroki/kredyty-euro',
        ])
            ->assertSee('Wyrok Beta')
            ->assertDontSee('Wyrok Alpha')
            ->assertSee('wyroki/kredyty-euro', false);

        Livewire::test(Sentences::class, [
            'more' => true,
            'currency' => 'CHF',
            'more_url' => 'wyroki/kredyty-frankowe',
        ])
            ->assertSee('Wyrok Alpha')
            ->assertDontSee('Wyrok Beta')
            ->assertSee('wyroki/kredyty-frankowe', false);
    }

    public function test_credit_pages_link_more_sentences_to_currency_listing(): void
    {
        $this->createSentenceFixtures();
        $this->createCity('Głogów', 'glogow');

        $this->get('/kredyty-frankowe')
            ->assertOk()
            ->assertSee('wyroki/kredyty-frankowe', false);

        $this->get('/kredyty-frankowe-glogow')
            ->assertOk()
            ->assertSee('wyroki/kredyty-frankowe', false);

        $this->get('/kredyty-euro')
            ->assertOk()
            ->assertSee('wyroki/kredyty-euro', false);

        $this->get('/kredyt-euro-kancelaria-glogow')
            ->assertOk()
            ->assertSee('wyroki/kredyty-euro', false);
    }

    public function test_euro_sentences_page_uses_eur_filter_and_euro_seo_headings(): void
    {
        $this->createSentenceFixtures();

        $this->get('/wyroki/kredyty-euro')
            ->assertOk()
            ->assertSee('<title>Wyroki w sprawach kredytów euro', false)
            ->assertSee('<meta name="description" content="Zobacz wyroki w sprawach kredytów euro', false)
            ->assertSee('Kredyty euro - wyroki naszej kancelarii')
            ->assertSee('Wyroki w sprawach kredytów euro')
            ->assertSee('Wyrok Beta')
            ->assertDontSee('Wyrok Alpha');

        $this->get('/mapa-strony')
            ->assertOk()
            ->assertSee('wyroki/kredyty-euro', false);

        $this->get('/wyroki/euro')
            ->assertRedirect('/wyroki/kredyty-euro');
    }

    public function test_chf_sentences_page_uses_chf_filter_and_frank_seo_headings(): void
    {
        $this->createSentenceFixtures();

        $this->get('/wyroki/kredyty-frankowe')
            ->assertOk()
            ->assertSee('<title>Wyroki w sprawach kredytów frankowych', false)
            ->assertSee('<meta name="description" content="Zobacz wyroki w sprawach kredytów frankowych', false)
            ->assertSee('Kredyty frankowe - wyroki naszej kancelarii')
            ->assertSee('Wyroki w sprawach kredytów frankowych')
            ->assertSee('Wyrok Alpha')
            ->assertDontSee('Wyrok Beta');

        $this->get('/mapa-strony')
            ->assertOk()
            ->assertSee('wyroki/kredyty-frankowe', false);
    }

    public function test_currency_sentences_page_redirects_to_base_listing_when_currency_filter_is_changed_or_removed(): void
    {
        Livewire::test(Sentences::class, ['category' => 'kredyty-euro'])
            ->assertSet('filterData.currency', 'EUR')
            ->set('filterData.currency', 'CHF')
            ->assertRedirectToRoute('wyroki');

        Livewire::test(Sentences::class, ['category' => 'kredyty-frankowe'])
            ->assertSet('filterData.currency', 'CHF')
            ->set('filterData.currency', 'EUR')
            ->assertRedirectToRoute('wyroki');

        Livewire::test(Sentences::class, ['category' => 'kredyty-euro'])
            ->assertSet('filterData.currency', 'EUR')
            ->call('clearFilters')
            ->assertRedirectToRoute('wyroki');
    }

    public function test_more_sentences_can_prioritize_bank_context_and_fill_with_recent_other_banks(): void
    {
        $fixtures = $this->createSentenceFixtures();

        $this->createSentence(
            label: 'Wyrok Current Legacy',
            slug: 'wyrok-current-legacy',
            sign: 'III C 333/26',
            currency: 'CHF',
            court: $fixtures['courtA'],
            judge: $fixtures['judgeA'],
            bank: $fixtures['legacyBank'],
            previousBank: null,
            date: '2026-05-10',
        );

        $this->createSentence(
            label: 'Wyrok Fallback Added First',
            slug: 'wyrok-fallback-added-first',
            sign: 'IV C 444/26',
            currency: 'CHF',
            court: $fixtures['courtB'],
            judge: $fixtures['judgeB'],
            bank: $fixtures['otherBank'],
            previousBank: null,
            date: '2026-04-10',
        );

        $this->createSentence(
            label: 'Wyrok Fallback Added Last',
            slug: 'wyrok-fallback-added-last',
            sign: 'V C 555/26',
            currency: 'CHF',
            court: $fixtures['courtB'],
            judge: $fixtures['judgeB'],
            bank: $fixtures['otherBank'],
            previousBank: null,
            date: '2026-03-10',
        );

        Livewire::test(Sentences::class, ['more' => true, 'relatedBankId' => $fixtures['legacyBank']->id])
            ->assertSeeInOrder([
                'Wyrok Alpha',
                'Wyrok Current Legacy',
                'Wyrok Fallback Added Last',
                'Wyrok Fallback Added First',
            ])
            ->assertDontSee('Wyrok Beta');
    }

    public function test_empty_sentence_results_use_neutral_text_color(): void
    {
        $this->createSentenceFixtures();

        Livewire::test(Sentences::class)
            ->set('search', 'brak takiego wyroku')
            ->assertSee('Brak wyników.')
            ->assertSee('text-secondary-700');
    }

    public function test_unpublished_related_banks_do_not_hide_sentences_from_listing_search_and_filters(): void
    {
        $fixtures = $this->createSentenceFixtures();

        $hiddenCurrentBank = $this->createBank('Hidden Current Bank', 'hidden-current-bank', false);
        $hiddenPreviousBank = $this->createBank('Hidden Previous Bank', 'hidden-previous-bank', false);
        $publishedCurrentBank = $this->createBank('Gamma Current Bank', 'gamma-current-bank');

        $this->createSentence(
            label: 'Wyrok Hidden Current',
            slug: 'wyrok-hidden-current',
            sign: 'III C 111/26',
            currency: 'CHF',
            court: $fixtures['courtA'],
            judge: $fixtures['judgeA'],
            bank: $hiddenCurrentBank,
            previousBank: null,
            date: '2026-03-10',
        );

        $this->createSentence(
            label: 'Wyrok Hidden Previous',
            slug: 'wyrok-hidden-previous',
            sign: 'III C 222/26',
            currency: 'CHF',
            court: $fixtures['courtA'],
            judge: $fixtures['judgeA'],
            bank: $publishedCurrentBank,
            previousBank: $hiddenPreviousBank,
            date: '2026-04-10',
        );

        Livewire::test(Sentences::class)
            ->assertSee('Wyrok Hidden Current')
            ->assertSee('Hidden Current Bank')
            ->assertDontSee('wyroki/bank/hidden-current-bank', false)
            ->set('search', 'Hidden Previous Bank')
            ->assertSee('Wyrok Hidden Previous')
            ->set('search', '')
            ->set('filterData.bank_id', $hiddenPreviousBank->id)
            ->assertSee('Wyrok Hidden Previous')
            ->set('filterData.bank_id', $hiddenCurrentBank->id)
            ->assertSee('Wyrok Hidden Current')
            ->assertDontSee('wyroki/bank/hidden-current-bank', false);
    }

    public function test_filter_options_only_include_relations_with_visible_published_sentences(): void
    {
        $fixtures = $this->createSentenceFixtures();

        $hiddenCurrentBank = $this->createBank('Only Hidden Current Bank', 'only-hidden-current-bank', false);
        $stalePreviousBank = $this->createBank('Stale Previous Bank', 'stale-previous-bank');
        $courtWithoutVisibleSentences = $this->createCourt(
            'Sąd Rejonowy bez widocznych wyroków',
            'sad-rejonowy-bez-widocznych-wyrokow',
        );

        $this->createSentence(
            label: 'Wyrok bez widocznych relacji',
            slug: 'wyrok-bez-widocznych-relacji',
            sign: 'IV C 444/26',
            currency: 'JPY',
            court: $courtWithoutVisibleSentences,
            judge: $fixtures['judgeA'],
            bank: $hiddenCurrentBank,
            previousBank: $stalePreviousBank,
            date: '2026-05-10',
        );

        $component = new Sentences();

        $this->assertContains('Legacy Original', $this->callPrivateMethod($component, 'bankFilterOptions'));
        $this->assertContains('Only Hidden Current Bank', $this->callPrivateMethod($component, 'bankFilterOptions'));
        $this->assertContains('Stale Previous Bank', $this->callPrivateMethod($component, 'bankFilterOptions'));
        $this->assertContains('Sąd Okręgowy w Testowie', $this->callPrivateMethod($component, 'courtFilterOptions'));
        $this->assertContains(
            'Sąd Rejonowy bez widocznych wyroków',
            $this->callPrivateMethod($component, 'courtFilterOptions'),
        );
        $this->assertContains('CHF', $this->callPrivateMethod($component, 'currencyFilterOptions'));
        $this->assertContains('JPY', $this->callPrivateMethod($component, 'currencyFilterOptions'));
    }

    public function test_sitemap_page_lists_sentences_with_duplicate_labels(): void
    {
        $fixtures = $this->createSentenceFixtures();

        $firstSentence = $this->createSentence(
            label: 'Ten sam tytuł wyroku',
            slug: 'ten-sam-tytul-pierwszy',
            sign: 'III C 333/26',
            currency: 'CHF',
            court: $fixtures['courtA'],
            judge: $fixtures['judgeA'],
            bank: $fixtures['currentBank'],
            previousBank: null,
            date: '2026-03-10',
        );

        $secondSentence = $this->createSentence(
            label: 'Ten sam tytuł wyroku',
            slug: 'ten-sam-tytul-drugi',
            sign: 'IV C 444/26',
            currency: 'CHF',
            court: $fixtures['courtA'],
            judge: $fixtures['judgeA'],
            bank: $fixtures['currentBank'],
            previousBank: null,
            date: '2026-04-10',
        );

        $this->get('/mapa-strony')
            ->assertOk()
            ->assertSee('wyrok/' . $firstSentence->slug, false)
            ->assertSee('wyrok/' . $secondSentence->slug, false);
    }

    private function assertSearchShowsOnly(string $search, string $expected, string $unexpected): void
    {
        Livewire::test(Sentences::class)
            ->set('search', $search)
            ->assertSee($expected)
            ->assertDontSee($unexpected);
    }

    private function callPrivateMethod(object $object, string $method): mixed
    {
        $reflection = new \ReflectionMethod($object, $method);
        $reflection->setAccessible(true);

        return $reflection->invoke($object);
    }

    private function createSentenceFixtures(): array
    {
        $currentBank = $this->createBank('Alpha Current', 'alpha-current');
        $legacyBank = $this->createBank('Legacy Original', 'legacy-original');
        $otherBank = $this->createBank('Beta Current', 'beta-current');

        $courtA = $this->createCourt('Sąd Okręgowy w Testowie', 'sad-okregowy-w-testowie');
        $courtB = $this->createCourt('Sąd Apelacyjny w Próbowie', 'sad-apelacyjny-w-probowie');

        $judgeA = $this->createJudge('Jacek', 'Saramaga', 'jacek-saramaga');
        $judgeB = $this->createJudge('Katarzyna', 'Pozlewicz', 'katarzyna-pozlewicz');

        $this->createSentence(
            label: 'Wyrok Alpha',
            slug: 'wyrok-alpha',
            sign: 'I C 123/26',
            currency: 'CHF',
            court: $courtA,
            judge: $judgeA,
            bank: $currentBank,
            previousBank: $legacyBank,
            date: '2026-01-10',
        );

        $this->createSentence(
            label: 'Wyrok Beta',
            slug: 'wyrok-beta',
            sign: 'II C 999/26',
            currency: 'EUR',
            court: $courtB,
            judge: $judgeB,
            bank: $otherBank,
            previousBank: null,
            date: '2026-02-10',
        );

        return compact('currentBank', 'legacyBank', 'otherBank', 'courtA', 'courtB', 'judgeA', 'judgeB');
    }

    private function createBank(string $label, string $slug, bool $isPublished = true): Bank
    {
        return Bank::create([
            'bank' => $label,
            'label' => $label,
            'form_a' => $label,
            'form_e' => $label,
            'form_w' => $label,
            'form_z' => $label,
            'slug' => $slug,
            'is_published' => $isPublished,
            'sort' => 1,
        ]);
    }

    private function createCourt(string $organization, string $slug): Contact
    {
        return Contact::create([
            'category' => ContactCategories::SAD->value,
            'organization' => $organization,
            'label' => $organization,
            'slug' => $slug,
        ]);
    }

    private function createCity(string $city, string $slug): City
    {
        return City::create([
            'city' => $city,
            'form_a' => $city,
            'form_e' => $city,
            'form_w' => $city,
            'form_z' => $city,
            'slug' => $slug,
            'so' => 'Sąd Okręgowy',
            'sa' => 'Sąd Apelacyjny',
            'km' => 'Kancelaria',
            'province' => Provinces::DOLNOSLASKIE->value,
            'is_published' => true,
        ]);
    }

    private function createJudge(string $firstName, string $lastName, string $slug): Contact
    {
        return Contact::create([
            'category' => ContactCategories::SEDZIA->value,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'slug' => $slug,
        ]);
    }

    private function createSentence(
        string $label,
        string $slug,
        string $sign,
        string $currency,
        Contact $court,
        Contact $judge,
        Bank $bank,
        ?Bank $previousBank,
        string $date,
    ): Sentence {
        return Sentence::create([
            'sign' => $sign,
            'sentence_date' => $date,
            'instance' => 'I',
            'court_id' => $court->id,
            'judge_id' => $judge->id,
            'bank_id' => $bank->id,
            'bank_previously_id' => $previousBank?->id,
            'credit_year' => '2008',
            'credit_name' => 'umowa kredytu',
            'wps' => '100000',
            'hearings' => '1',
            'result' => 'wygrana',
            'claim' => 'ustalenie',
            'lawyer' => 'PRĘDA',
            'label' => $label,
            'excerpt' => 'Opis wyroku ' . $label,
            'content' => 'Treść wyroku ' . $label,
            'slug' => $slug,
            'metatitle' => $label,
            'metadescription' => $label,
            'is_published' => true,
            'currency' => $currency,
        ]);
    }
}
