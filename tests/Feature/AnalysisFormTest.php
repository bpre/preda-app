<?php

namespace Tests\Feature;

use App\Livewire\Website\AnalysisForm;
use App\Models\User;
use App\Models\Website\Bank;
use App\Models\Website\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AnalysisFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_step_saves_lead_and_shows_optional_upload_when_contract_is_available(): void
    {
        $this->createPublishedBank();
        $this->createLeadRecipient();

        Livewire::test(AnalysisForm::class)
            ->set('data.name', 'Jan Kowalski')
            ->set('data.phone', '500 600 700')
            ->set('data.email', 'jan@example.test')
            ->set('data.postal_code', '67-200')
            ->set('data.bank', 'Bank Testowy')
            ->set('data.contract_year_range', '2007-2009')
            ->set('data.credit_currency', 'CHF')
            ->set('data.credit_amount_range', 'od 85.000 do 300.000 PLN')
            ->set('data.credit_status', 'nadal spłacam')
            ->set('data.has_contract', '1')
            ->set('data.policy', true)
            ->assertDontSee('Dodatkowe informacje?')
            ->assertSee('To pole nie jest obowiązkowe. Możesz jednak już teraz przekazać nam dodatkowe informacje')
            ->set('data.additional_info', 'Klient ma podpisany aneks do umowy.')
            ->call('create')
            ->assertSet('sent', true)
            ->assertSet('hasContract', true)
            ->assertSee('Możesz od razu przesłać skan umowy. Nie jest to obowiązkowe')
            ->assertSee('Załącz dokumenty')
            ->assertSee('Pomiń na razie ten krok')
            ->set('documentsUploaded', true)
            ->assertSee('Dokumenty zostały załączone.')
            ->assertSee('Dziękujemy. Skontaktujemy się z Tobą po przeanalizowaniu zgłoszenia i zapoznaniu się z dokumentami.')
            ->assertDontSee('Dziękujemy. Twoje zgłoszenie zostało przyjęte.')
            ->set('documentsUploaded', false)
            ->call('skipDocuments')
            ->assertSet('documentsSkipped', true)
            ->assertSee('Dziękujemy. Skontaktujemy się z Tobą po przeanalizowaniu zgłoszenia.')
            ->assertDontSee('Dziękujemy. Twoje zgłoszenie zostało przyjęte.');

        $this->assertDatabaseHas('leads', [
            'name' => 'Jan Kowalski',
            'email' => 'jan@example.test',
            'postal_code' => '67-200',
            'phone' => '500 600 700',
            'bank' => 'Bank Testowy',
            'contract_year_range' => '2007-2009',
            'credit_currency' => 'CHF',
            'credit_amount_range' => 'od 85.000 do 300.000 PLN',
            'credit_status' => 'nadal spłacam',
            'has_contract' => true,
        ]);
        $this->assertStringContainsString('Kwota kredytu: od 85.000 do 300.000 PLN', Lead::first()->message);
        $this->assertStringContainsString('Kod pocztowy: 67-200', Lead::first()->message);
        $this->assertStringContainsString('Dodatkowe informacje: Klient ma podpisany aneks do umowy.', Lead::first()->message);

        $this->assertNotNull(Lead::first()->documents_skipped_at);
    }

    public function test_first_step_does_not_show_upload_when_contract_is_not_available(): void
    {
        $this->createPublishedBank();
        $this->createLeadRecipient();

        Livewire::test(AnalysisForm::class)
            ->set('data.name', 'Anna Nowak')
            ->set('data.phone', '700 800 900')
            ->set('data.email', 'anna@example.test')
            ->set('data.postal_code', '59-100')
            ->set('data.bank', 'Bank Testowy')
            ->set('data.contract_year_range', 'nie pamiętam')
            ->set('data.credit_currency', 'nie wiem')
            ->set('data.credit_amount_range', 'poniżej 85.000 PLN')
            ->set('data.credit_status', 'nie wiem / chcę skonsultować')
            ->set('data.has_contract', '0')
            ->set('data.policy', true)
            ->call('create')
            ->assertSet('sent', true)
            ->assertSet('hasContract', false)
            ->assertSet('no_docs', true)
            ->assertSee('Dziękujemy. Twoje zgłoszenie zostało przyjęte.')
            ->assertSee('Skontaktujemy się z Tobą po przeanalizowaniu zgłoszenia.')
            ->assertDontSee('Dziękujemy. Skontaktujemy się z Tobą po przeanalizowaniu zgłoszenia.')
            ->assertDontSee('Zaznaczyłeś/zaznaczyłaś, że nie masz umowy kredytowej')
            ->assertDontSee('Załącz dokumenty')
            ->assertDontSee('Możesz od razu przesłać skan umowy. Nie jest to obowiązkowe');

        $this->assertDatabaseHas('leads', [
            'name' => 'Anna Nowak',
            'email' => 'anna@example.test',
            'postal_code' => '59-100',
            'bank' => 'Bank Testowy',
            'has_contract' => false,
        ]);
    }

    public function test_postal_code_is_required_and_must_match_expected_format(): void
    {
        $this->createPublishedBank();
        $this->createLeadRecipient();

        $this->fillValidAnalysisForm(Livewire::test(AnalysisForm::class), postalCode: '')
            ->call('create')
            ->assertHasErrors(['data.postal_code' => 'required']);

        $this->fillValidAnalysisForm(Livewire::test(AnalysisForm::class), postalCode: '12345')
            ->call('create')
            ->assertHasErrors(['data.postal_code' => 'regex']);
    }

    public function test_first_step_saves_marketing_attribution_data(): void
    {
        $this->createPublishedBank();
        $this->createLeadRecipient();

        $this->fillValidAnalysisForm(Livewire::test(AnalysisForm::class))
            ->set('attributionData', [
                'first_touch' => [
                    'url' => 'https://preda.info/kredyty-frankowe?utm_source=google&utm_medium=cpc&utm_campaign=chf_search&utm_term=kredyty%20frankowe&gclid=test-gclid',
                    'path' => '/kredyty-frankowe?utm_source=google&utm_medium=cpc&utm_campaign=chf_search&utm_term=kredyty%20frankowe&gclid=test-gclid',
                    'referrer' => null,
                    'params' => [
                        'utm_source' => 'google',
                        'utm_medium' => 'cpc',
                        'utm_campaign' => 'chf_search',
                        'utm_term' => 'kredyty frankowe',
                        'gclid' => 'test-gclid',
                    ],
                    'captured_at' => '2026-07-10T10:00:00+00:00',
                ],
                'last_touch' => [
                    'url' => 'https://preda.info/kredyty-frankowe?utm_source=google&utm_medium=cpc&utm_campaign=chf_search&utm_term=kredyty%20frankowe&gclid=test-gclid',
                    'path' => '/kredyty-frankowe?utm_source=google&utm_medium=cpc&utm_campaign=chf_search&utm_term=kredyty%20frankowe&gclid=test-gclid',
                    'referrer' => null,
                    'params' => [
                        'utm_source' => 'google',
                        'utm_medium' => 'cpc',
                        'utm_campaign' => 'chf_search',
                        'utm_term' => 'kredyty frankowe',
                        'gclid' => 'test-gclid',
                    ],
                    'captured_at' => '2026-07-10T10:05:00+00:00',
                ],
                'current_page' => [
                    'url' => 'https://preda.info/analiza',
                    'path' => '/analiza',
                    'captured_at' => '2026-07-10T10:06:00+00:00',
                ],
            ])
            ->call('create')
            ->assertSet('sent', true);

        $lead = Lead::query()->firstOrFail();

        $this->assertSame('google_ads', $lead->attribution_channel);
        $this->assertSame('google', $lead->attribution_source);
        $this->assertSame('cpc', $lead->attribution_medium);
        $this->assertSame('chf_search', $lead->attribution_campaign);
        $this->assertSame('kredyty frankowe', $lead->attribution_term);
        $this->assertSame('https://preda.info/analiza', $lead->attribution_conversion_page);
        $this->assertSame(['gclid' => 'test-gclid'], $lead->attribution_click_ids);
        $this->assertSame('Google Ads', $lead->attribution_summary);
    }

    public function test_sidebar_form_resets_only_after_process_is_complete(): void
    {
        $this->createPublishedBank();
        $this->createLeadRecipient();

        $this->fillValidAnalysisForm(Livewire::test(AnalysisForm::class, ['context' => 'sidebar']))
            ->call('create')
            ->assertSet('sent', true)
            ->assertSee('Załącz dokumenty')
            ->call('resetAfterSidebarCompletion')
            ->assertSet('sent', true)
            ->assertSee('Załącz dokumenty');

        $this->fillValidAnalysisForm(
            Livewire::test(AnalysisForm::class, ['context' => 'sidebar']),
            hasContract: '0',
            email: 'no-contract@example.test',
        )
            ->call('create')
            ->assertSet('sent', true)
            ->assertSee('Skontaktujemy się z Tobą po przeanalizowaniu zgłoszenia.')
            ->assertDontSee('Dziękujemy. Skontaktujemy się z Tobą po przeanalizowaniu zgłoszenia.')
            ->assertDontSee('Zaznaczyłeś/zaznaczyłaś, że nie masz umowy kredytowej')
            ->call('resetAfterSidebarCompletion')
            ->assertSet('sent', false)
            ->assertSet('hasContract', false)
            ->assertSee('Wyślij zgłoszenie do bezpłatnej analizy');

        $this->fillValidAnalysisForm(
            Livewire::test(AnalysisForm::class, ['context' => 'sidebar']),
            email: 'uploaded@example.test',
        )
            ->call('create')
            ->set('documentsUploaded', true)
            ->assertSee('Dokumenty zostały załączone.')
            ->call('resetAfterSidebarCompletion')
            ->assertSet('sent', false)
            ->assertSet('documentsUploaded', false)
            ->assertSee('Wyślij zgłoszenie do bezpłatnej analizy');

        $this->fillValidAnalysisForm(
            Livewire::test(AnalysisForm::class, ['context' => 'sidebar']),
            email: 'skipped@example.test',
        )
            ->call('create')
            ->call('skipDocuments')
            ->assertSet('documentsSkipped', true)
            ->assertSee('Dziękujemy. Skontaktujemy się z Tobą po przeanalizowaniu zgłoszenia.')
            ->call('resetAfterSidebarCompletion')
            ->assertSet('sent', false)
            ->assertSet('documentsSkipped', false)
            ->assertSee('Wyślij zgłoszenie do bezpłatnej analizy');
    }

    public function test_bank_search_results_always_include_unknown_bank_option(): void
    {
        $this->createPublishedBank();

        $matchingResults = $this->bankSearchResults('Testowy');

        $this->assertSame('Bank Testowy', $matchingResults['Bank Testowy']);
        $this->assertSame('Inny / nie pamiętam', $matchingResults['Inny / nie pamiętam']);

        $emptyResults = $this->bankSearchResults('tekst bez pasującego banku');

        $this->assertSame([
            'Inny / nie pamiętam' => 'Inny / nie pamiętam',
        ], $emptyResults);
    }

    private function createPublishedBank(): Bank
    {
        return Bank::create([
            'bank' => 'bank-testowy',
            'label' => 'Bank Testowy',
            'form_a' => 'Bank Testowy',
            'form_e' => 'Banku Testowego',
            'form_w' => 'Banku Testowym',
            'form_z' => 'Bankiem Testowym',
            'slug' => 'bank-testowy',
            'is_published' => true,
            'sort' => 1,
        ]);
    }

    private function createLeadRecipient(): User
    {
        return User::factory()->create([
            'email' => 'bartosz.preda@preda.info',
        ]);
    }

    private function fillValidAnalysisForm(
        mixed $component,
        string $hasContract = '1',
        string $email = 'jan@example.test',
        string $postalCode = '67-200',
    ): mixed
    {
        return $component
            ->set('data.name', 'Jan Kowalski')
            ->set('data.phone', '500 600 700')
            ->set('data.email', $email)
            ->set('data.postal_code', $postalCode)
            ->set('data.bank', 'Bank Testowy')
            ->set('data.contract_year_range', '2007-2009')
            ->set('data.credit_currency', 'CHF')
            ->set('data.credit_amount_range', 'od 85.000 do 300.000 PLN')
            ->set('data.credit_status', 'nadal spłacam')
            ->set('data.has_contract', $hasContract)
            ->set('data.policy', true);
    }

    private function bankSearchResults(string $search): array
    {
        $component = app(AnalysisForm::class);
        $method = new \ReflectionMethod($component, 'bankSearchResults');
        $method->setAccessible(true);

        return $method->invoke($component, $search);
    }
}
