<?php

namespace Tests\Feature;

use App\Filament\Crm\Resources\CHFPotentialMatterResource\Widgets\PotentialMatterActionWidget;
use App\Models\CHFPotentialMatter;
use App\Models\CrmMailTemplate;
use App\Models\MatterGeneratedDocument;
use App\Models\TemplateStage;
use App\Models\User;
use App\Models\Website\Lead;
use App\Notifications\LeadGeneratedMessage;
use App\Services\Crm\PotentialMatterClientActionService;
use App\Support\StageManager;
use Filament\Forms\Components\Select;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PotentialMatterClientActionWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_widget_is_visible_only_on_default_crm_stage(): void
    {
        $matter = $this->createPotentialMatterOnDefaultStage();

        Livewire::test(PotentialMatterActionWidget::class, ['record' => $matter])
            ->assertSee('Podejmij działanie')
            ->assertSee('Wyślij analizę umowy')
            ->assertSee('Przygotuj maila')
            ->assertDontSee('Potwierdź')
            ->assertFormFieldExists(
                'selectedAction',
                fn (Select $field): bool => ! $field->isNative(),
            );

        $targetStage = TemplateStage::query()
            ->where('category', 'Potencjalna')
            ->where('label', 'Wysłano analizę umowy')
            ->firstOrFail();

        StageManager::setCurrentStage($matter->refresh(), $targetStage);

        Livewire::test(PotentialMatterActionWidget::class, ['record' => $matter->refresh()])
            ->assertDontSee('Podejmij działanie');
    }

    public function test_action_mail_uses_recipient_summary(): void
    {
        $matter = $this->createPotentialMatterOnDefaultStage();
        $this->createLead($matter);

        $this->assertSame(
            'Jan Kowalski <jan@example.test>',
            app(PotentialMatterClientActionService::class)->recipientSummary($matter),
        );
    }

    public function test_action_mail_uses_database_template_with_placeholders(): void
    {
        $matter = $this->createPotentialMatterOnDefaultStage();
        $this->createLead($matter);

        CrmMailTemplate::query()
            ->where('action', PotentialMatterClientActionService::REQUEST_ADDITIONAL_INFO)
            ->update([
                'subject' => '{Klient wysłał|Klientka wysłała} dane: {{bank}} / {{waluta_kredytu}} / {{rok_umowy}}',
                'body' => '<p>Dzień dobry,</p><p>{Potencjalny klient otrzymał|Potencjalna klientka otrzymała} wiadomość dotyczącą {{bank}}.</p><p>{{prawnik}}, {{funkcja}}<br>{{link_do_konsultacji}}</p>',
            ]);

        $payload = app(PotentialMatterClientActionService::class)->defaultPayload(
            $matter,
            PotentialMatterClientActionService::REQUEST_ADDITIONAL_INFO,
        );

        $this->assertSame('Klient wysłał dane: Bank Testowy / CHF / 2007-2009', $payload['subject']);
        $this->assertSame('<p>Dzień dobry,</p><p>Potencjalny klient otrzymał wiadomość dotyczącą Bank Testowy.</p><p>Jan Prawnik, Adwokat<br>https://calendar.example.test/jan</p>', $payload['body']);
    }

    public function test_action_mail_resolves_pani_pana_for_female_recipient(): void
    {
        $matter = $this->createPotentialMatterOnDefaultStage();
        $this->createLead($matter, [
            'name' => 'Anna Kowalska',
            'email' => 'anna@example.test',
        ]);

        CrmMailTemplate::query()
            ->where('action', PotentialMatterClientActionService::CONFIRM_QUALIFICATION)
            ->update([
                'subject' => '{Klient został powiadomiony|Klientka została powiadomiona}',
                'body' => '<p>Wiadomość dla {{pani_pana}}: {potencjalny klient otrzymał|potencjalna klientka otrzymała} informację.</p>',
            ]);

        $payload = app(PotentialMatterClientActionService::class)->defaultPayload(
            $matter,
            PotentialMatterClientActionService::CONFIRM_QUALIFICATION,
        );

        $this->assertSame('Klientka została powiadomiona', $payload['subject']);
        $this->assertSame('<p>Wiadomość dla Pani: potencjalna klientka otrzymała informację.</p>', $payload['body']);
    }

    public function test_widget_sends_client_mail_and_updates_current_stage(): void
    {
        Notification::fake();
        Storage::fake('local');

        $matter = $this->createPotentialMatterOnDefaultStage();
        $lead = $this->createLead($matter);
        Storage::disk('local')->put("matter-generated-documents/{$matter->getKey()}/analysis.pdf", '%PDF-test');

        $document = MatterGeneratedDocument::create([
            'matter_id' => $matter->getKey(),
            'type' => MatterGeneratedDocument::TYPE_CONTRACT_ANALYSIS,
            'filename' => '2026.07.12 J. Kowalski - Analiza umowy',
            'disk' => 'local',
            'path' => "matter-generated-documents/{$matter->getKey()}/analysis.pdf",
            'mime_type' => 'application/pdf',
            'generated_at' => now(),
        ]);

        Livewire::test(PotentialMatterActionWidget::class, ['record' => $matter])
            ->set('data.selectedAction', PotentialMatterClientActionService::REQUEST_ADDITIONAL_INFO)
            ->assertActionEnabled('sendClientMessage')
            ->callAction('sendClientMessage', data: [
                'subject' => 'Potrzebujemy dodatkowych informacji',
                'body' => '<p>Prosimy o dosłanie brakujących danych.</p>',
                'generated_document_ids' => [$document->getKey()],
            ])
            ->assertHasNoActionErrors()
            ->assertDontSee('Podejmij działanie');

        Notification::assertSentOnDemand(
            LeadGeneratedMessage::class,
            fn (LeadGeneratedMessage $notification, array $channels, object $notifiable): bool => $channels === ['mail']
                && $notifiable->routes['mail'] === $lead->email
                && $notification->subject === 'Potrzebujemy dodatkowych informacji'
                && $notification->body === '<p>Prosimy o dosłanie brakujących danych.</p>'
                && count($notification->attachments) === 1
                && $notification->attachments[0]['as'] === '2026.07.12 J. Kowalski - Analiza umowy.pdf',
        );

        $targetStage = TemplateStage::query()
            ->where('category', 'Potencjalna')
            ->where('label', 'Wysłano prośbę o dodatkowe informacje')
            ->firstOrFail();

        $this->assertSame($targetStage->getKey(), $matter->refresh()->current_template_stage_id);
        $this->assertDatabaseHas('stages', [
            'matter_id' => $matter->getKey(),
            'stage_id' => $targetStage->getKey(),
            'label' => 'Wysłano prośbę o dodatkowe informacje',
            'is_current' => true,
        ]);
    }

    public function test_generated_document_preview_uses_inline_response(): void
    {
        Storage::fake('local');

        $matter = $this->createPotentialMatterOnDefaultStage();
        Storage::disk('local')->put("matter-generated-documents/{$matter->getKey()}/analysis.pdf", '%PDF-test');

        $document = MatterGeneratedDocument::create([
            'matter_id' => $matter->getKey(),
            'type' => MatterGeneratedDocument::TYPE_CONTRACT_ANALYSIS,
            'filename' => '2026.07.12 J. Kowalski - Analiza umowy',
            'disk' => 'local',
            'path' => "matter-generated-documents/{$matter->getKey()}/analysis.pdf",
            'mime_type' => 'application/pdf',
            'generated_at' => now(),
        ]);

        $response = $this
            ->actingAs(User::factory()->create())
            ->get(route('matter-generated-documents.preview', $document));

        $response->assertOk();

        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringStartsWith('inline;', $response->headers->get('content-disposition'));
    }

    private function createPotentialMatterOnDefaultStage(): CHFPotentialMatter
    {
        $user = User::factory()->create([
            'name' => 'Jan Prawnik',
            'is_active' => true,
            'is_employee' => true,
            'is_lawyer' => true,
            'signature_title' => 'Adwokat',
            'consultation_url' => 'https://calendar.example.test/jan',
        ]);

        $defaultStage = TemplateStage::create([
            'category' => 'Potencjalna',
            'label' => 'Nowy lead',
            'parent' => 'Pozyskanie klienta',
            'sort' => 1,
            'is_chf_default' => true,
            'is_active' => true,
        ]);

        $matter = CHFPotentialMatter::create([
            'label' => 'Kowalski Jan / Bank Testowy',
            'lawyer_id' => $user->getKey(),
            'category' => 'CHF',
            'is_matter' => false,
            'userinfo' => [],
        ]);

        StageManager::setCurrentStage($matter, $defaultStage);

        return $matter->refresh();
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createLead(CHFPotentialMatter $matter, array $overrides = []): Lead
    {
        return Lead::create(array_merge([
            'name' => 'Jan Kowalski',
            'email' => 'jan@example.test',
            'phone' => '500 600 700',
            'postal_code' => '67-200',
            'bank' => 'Bank Testowy',
            'contract_year_range' => '2007-2009',
            'credit_currency' => 'CHF',
            'credit_amount_range' => 'od 85.000 do 300.000 PLN',
            'credit_status' => 'nadal spłacam',
            'has_contract' => true,
            'message' => 'Zgłoszenie testowe.',
            'potential_matter_id' => $matter->getKey(),
        ], $overrides));
    }
}
