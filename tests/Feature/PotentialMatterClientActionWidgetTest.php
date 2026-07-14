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
use App\Services\Crm\PotentialMatterWorkflowService;
use App\Support\StageManager;
use Filament\Forms\Components\Select;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

class PotentialMatterClientActionWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_widget_actions_are_filtered_by_current_stage(): void
    {
        $matter = $this->createPotentialMatterOnDefaultStage();
        $workflow = app(PotentialMatterWorkflowService::class);

        $this->assertSame([
            PotentialMatterClientActionService::CONFIRM_QUALIFICATION => 'Wyślij potwierdzenie kwalifikacji sprawy',
            PotentialMatterClientActionService::REQUEST_ADDITIONAL_INFO => 'Wyślij prośbę o dodatkowe informacje',
            PotentialMatterClientActionService::SEND_CONTRACT_ANALYSIS => 'Wyślij analizę umowy',
        ], $workflow->availableOptions($matter));

        Livewire::test(PotentialMatterActionWidget::class, ['record' => $matter])
            ->assertSee('Podejmij działanie')
            ->assertSee('Wyślij analizę umowy')
            ->assertSee('Przygotuj maila')
            ->assertDontSee('Potwierdź')
            ->assertFormFieldExists(
                'selectedAction',
                fn (Select $field): bool => ! $field->isNative(),
            );

        $confirmationSentStage = TemplateStage::query()
            ->where('category', 'Potencjalna')
            ->where('label', 'Wysłano potwierdzenie kwalifikacji sprawy')
            ->firstOrFail();

        StageManager::setCurrentStage($matter->refresh(), $confirmationSentStage);

        $this->assertSame([
            PotentialMatterClientActionService::SEND_CONTRACT_ANALYSIS => 'Wyślij analizę umowy',
            PotentialMatterClientActionService::FOLLOW_UP_AFTER_QUALIFICATION => 'Wyślij follow-up po kwalifikacji',
            PotentialMatterClientActionService::SEND_OFFER => 'Wyślij ofertę',
        ], $workflow->availableOptions($matter->refresh()));

        Livewire::test(PotentialMatterActionWidget::class, ['record' => $matter->refresh()])
            ->assertSee('Podejmij działanie');

        $qualificationFollowUpStage = TemplateStage::query()
            ->where('category', 'Potencjalna')
            ->where('label', 'Follow-up (po kwalifikacji)')
            ->firstOrFail();

        StageManager::setCurrentStage($matter->refresh(), $qualificationFollowUpStage);

        $this->assertSame([
            PotentialMatterClientActionService::SEND_CONTRACT_ANALYSIS => 'Wyślij analizę umowy',
            PotentialMatterClientActionService::SEND_OFFER => 'Wyślij ofertę',
        ], $workflow->availableOptions($matter->refresh()));

        $additionalInfoRequestedStage = TemplateStage::query()
            ->where('category', 'Potencjalna')
            ->where('label', 'Wysłano prośbę o dodatkowe informacje')
            ->firstOrFail();

        StageManager::setCurrentStage($matter->refresh(), $additionalInfoRequestedStage);

        $this->assertSame([
            PotentialMatterClientActionService::CONFIRM_QUALIFICATION => 'Wyślij potwierdzenie kwalifikacji sprawy',
            PotentialMatterClientActionService::SEND_CONTRACT_ANALYSIS => 'Wyślij analizę umowy',
            PotentialMatterClientActionService::FOLLOW_UP_AFTER_INFO_REQUEST => 'Wyślij follow-up po prośbie o informacje',
        ], $workflow->availableOptions($matter->refresh()));

        Livewire::test(PotentialMatterActionWidget::class, ['record' => $matter->refresh()])
            ->assertSee('Podejmij działanie');

        $infoRequestFollowUpStage = TemplateStage::query()
            ->where('category', 'Potencjalna')
            ->where('label', 'Follow-up (prośba o informacje)')
            ->firstOrFail();

        StageManager::setCurrentStage($matter->refresh(), $infoRequestFollowUpStage);

        $this->assertSame([
            PotentialMatterClientActionService::CONFIRM_QUALIFICATION => 'Wyślij potwierdzenie kwalifikacji sprawy',
            PotentialMatterClientActionService::SEND_CONTRACT_ANALYSIS => 'Wyślij analizę umowy',
        ], $workflow->availableOptions($matter->refresh()));

        $analysisSentStage = TemplateStage::query()
            ->where('category', 'Potencjalna')
            ->where('label', 'Wysłano analizę umowy')
            ->firstOrFail();

        StageManager::setCurrentStage($matter->refresh(), $analysisSentStage);

        $this->assertSame([
            PotentialMatterClientActionService::FOLLOW_UP_AFTER_ANALYSIS => 'Wyślij follow-up po wysłaniu analizy',
            PotentialMatterClientActionService::SEND_OFFER => 'Wyślij ofertę',
        ], $workflow->availableOptions($matter->refresh()));

        Livewire::test(PotentialMatterActionWidget::class, ['record' => $matter->refresh()])
            ->assertSee('Podejmij działanie');

        $analysisFollowUpStage = TemplateStage::query()
            ->where('category', 'Potencjalna')
            ->where('label', 'Follow-up (po wysłaniu analizy)')
            ->firstOrFail();

        StageManager::setCurrentStage($matter->refresh(), $analysisFollowUpStage);

        $this->assertSame([
            PotentialMatterClientActionService::SEND_OFFER => 'Wyślij ofertę',
        ], $workflow->availableOptions($matter->refresh()));

        $offerSentStage = TemplateStage::query()
            ->where('category', 'Potencjalna')
            ->where('label', 'Wysłano ofertę')
            ->firstOrFail();

        StageManager::setCurrentStage($matter->refresh(), $offerSentStage);

        $this->assertSame([], $workflow->availableOptions($matter->refresh()));

        $meetingStage = TemplateStage::query()
            ->where('category', 'Potencjalna')
            ->where('label', 'Spotkanie z potencjalnym klientem')
            ->firstOrFail();

        StageManager::setCurrentStage($matter->refresh(), $meetingStage);

        $this->assertSame([
            PotentialMatterClientActionService::FOLLOW_UP_AFTER_MEETING => 'Wyślij follow-up po spotkaniu',
        ], $workflow->availableOptions($matter->refresh()));

        StageManager::setCurrentStage($matter->refresh(), $offerSentStage);

        $this->assertSame([
            PotentialMatterClientActionService::FOLLOW_UP_AFTER_MEETING => 'Wyślij follow-up po spotkaniu',
        ], $workflow->availableOptions($matter->refresh()));
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

    public function test_follow_up_mail_templates_are_available_and_render_placeholders(): void
    {
        $matter = $this->createPotentialMatterOnDefaultStage();
        $this->createLead($matter);

        $this->assertDatabaseHas('crm_mail_templates', [
            'action' => PotentialMatterClientActionService::FOLLOW_UP_AFTER_QUALIFICATION,
            'name' => 'Follow-up (po kwalifikacji)',
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('crm_mail_templates', [
            'action' => PotentialMatterClientActionService::FOLLOW_UP_AFTER_INFO_REQUEST,
            'name' => 'Follow-up (prośba o informacje)',
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('crm_mail_templates', [
            'action' => PotentialMatterClientActionService::FOLLOW_UP_AFTER_ANALYSIS,
            'name' => 'Follow-up (po wysłaniu analizy)',
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('crm_mail_templates', [
            'action' => PotentialMatterClientActionService::SEND_OFFER,
            'name' => 'Oferta',
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('crm_mail_templates', [
            'action' => PotentialMatterClientActionService::FOLLOW_UP_AFTER_MEETING,
            'name' => 'Follow-up (po spotkaniu)',
            'is_active' => true,
        ]);

        $payload = app(PotentialMatterClientActionService::class)->defaultPayload(
            $matter,
            PotentialMatterClientActionService::FOLLOW_UP_AFTER_QUALIFICATION,
        );

        $this->assertSame('Czy chce Pan/Pani kontynuować sprawę?', $payload['subject']);
        $this->assertStringContainsString('Jeżeli Pana decyzja jest aktualna', $payload['body']);

        $offerPayload = app(PotentialMatterClientActionService::class)->defaultPayload(
            $matter,
            PotentialMatterClientActionService::SEND_OFFER,
        );

        $this->assertSame('Oferta współpracy', $offerPayload['subject']);
        $this->assertStringContainsString('Przed przyjęciem sprawy do prowadzenia', $offerPayload['body']);

        $meetingFollowUpPayload = app(PotentialMatterClientActionService::class)->defaultPayload(
            $matter,
            PotentialMatterClientActionService::FOLLOW_UP_AFTER_MEETING,
        );

        $this->assertSame('Follow-up po spotkaniu', $meetingFollowUpPayload['subject']);
        $this->assertStringContainsString('wracam po naszym spotkaniu', $meetingFollowUpPayload['body']);
    }

    public function test_follow_up_action_updates_current_stage_to_follow_up_stage(): void
    {
        Notification::fake();

        $matter = $this->createPotentialMatterOnDefaultStage();
        $lead = $this->createLead($matter);

        $confirmationSentStage = TemplateStage::query()
            ->where('category', 'Potencjalna')
            ->where('label', 'Wysłano potwierdzenie kwalifikacji sprawy')
            ->firstOrFail();

        StageManager::setCurrentStage($matter->refresh(), $confirmationSentStage);

        $stage = app(PotentialMatterClientActionService::class)->send(
            matter: $matter->refresh(),
            action: PotentialMatterClientActionService::FOLLOW_UP_AFTER_QUALIFICATION,
            subject: 'Przypomnienie',
            body: '<p>Wracam do wiadomości.</p>',
        );

        Notification::assertSentOnDemand(
            LeadGeneratedMessage::class,
            fn (LeadGeneratedMessage $notification, array $channels, object $notifiable): bool => $channels === ['mail']
                && $notifiable->routes['mail'] === $lead->email
                && $notification->subject === 'Przypomnienie',
        );

        $this->assertSame('Follow-up (po kwalifikacji)', $stage->label);
        $this->assertSame($stage->getKey(), $matter->refresh()->current_template_stage_id);
        $this->assertSame([
            PotentialMatterClientActionService::SEND_CONTRACT_ANALYSIS => 'Wyślij analizę umowy',
            PotentialMatterClientActionService::SEND_OFFER => 'Wyślij ofertę',
        ], app(PotentialMatterWorkflowService::class)->availableOptions($matter->refresh()));
    }

    public function test_send_offer_action_updates_current_stage_to_offer_sent_stage(): void
    {
        Notification::fake();

        $matter = $this->createPotentialMatterOnDefaultStage();
        $lead = $this->createLead($matter);

        $analysisSentStage = TemplateStage::query()
            ->where('category', 'Potencjalna')
            ->where('label', 'Wysłano analizę umowy')
            ->firstOrFail();

        StageManager::setCurrentStage($matter->refresh(), $analysisSentStage);

        $stage = app(PotentialMatterClientActionService::class)->send(
            matter: $matter->refresh(),
            action: PotentialMatterClientActionService::SEND_OFFER,
            subject: 'Oferta współpracy',
            body: '<p>Przesyłam ofertę.</p>',
        );

        Notification::assertSentOnDemand(
            LeadGeneratedMessage::class,
            fn (LeadGeneratedMessage $notification, array $channels, object $notifiable): bool => $channels === ['mail']
                && $notifiable->routes['mail'] === $lead->email
                && $notification->subject === 'Oferta współpracy',
        );

        $this->assertSame('Wysłano ofertę', $stage->label);
        $this->assertSame($stage->getKey(), $matter->refresh()->current_template_stage_id);
        $this->assertSame([], app(PotentialMatterWorkflowService::class)->availableOptions($matter->refresh()));
    }

    public function test_follow_up_after_meeting_depends_on_completed_meeting_stage(): void
    {
        Notification::fake();

        $matter = $this->createPotentialMatterOnDefaultStage();
        $lead = $this->createLead($matter);
        $workflow = app(PotentialMatterWorkflowService::class);

        $this->assertArrayNotHasKey(
            PotentialMatterClientActionService::FOLLOW_UP_AFTER_MEETING,
            $workflow->availableOptions($matter->refresh()),
        );

        $meetingStage = TemplateStage::query()
            ->where('category', 'Potencjalna')
            ->where('label', 'Spotkanie z potencjalnym klientem')
            ->firstOrFail();
        $offerSentStage = TemplateStage::query()
            ->where('category', 'Potencjalna')
            ->where('label', 'Wysłano ofertę')
            ->firstOrFail();

        StageManager::setCurrentStage($matter->refresh(), $meetingStage);
        StageManager::setCurrentStage($matter->refresh(), $offerSentStage);

        $this->assertSame([
            PotentialMatterClientActionService::FOLLOW_UP_AFTER_MEETING => 'Wyślij follow-up po spotkaniu',
        ], $workflow->availableOptions($matter->refresh()));

        $stage = app(PotentialMatterClientActionService::class)->send(
            matter: $matter->refresh(),
            action: PotentialMatterClientActionService::FOLLOW_UP_AFTER_MEETING,
            subject: 'Follow-up po spotkaniu',
            body: '<p>Wracam po spotkaniu.</p>',
        );

        Notification::assertSentOnDemand(
            LeadGeneratedMessage::class,
            fn (LeadGeneratedMessage $notification, array $channels, object $notifiable): bool => $channels === ['mail']
                && $notifiable->routes['mail'] === $lead->email
                && $notification->subject === 'Follow-up po spotkaniu',
        );

        $this->assertSame('Follow-up (po spotkaniu)', $stage->label);
        $this->assertSame($stage->getKey(), $matter->refresh()->current_template_stage_id);
        $this->assertSame([], $workflow->availableOptions($matter->refresh()));
    }

    public function test_action_mail_resolves_square_brackets_for_lawyer_gender_and_curly_braces_for_client_gender(): void
    {
        $matter = $this->createPotentialMatterOnDefaultStage();
        $lawyer = User::factory()->create([
            'name' => 'Wiktoria Rajzynger',
            'is_active' => true,
            'is_employee' => true,
            'is_lawyer' => true,
        ]);

        $matter->forceFill(['lawyer_id' => $lawyer->getKey()])->save();
        $this->createLead($matter, [
            'name' => 'Jan Kowalski',
            'email' => 'jan@example.test',
        ]);

        CrmMailTemplate::query()
            ->where('action', PotentialMatterClientActionService::SEND_CONTRACT_ANALYSIS)
            ->update([
                'subject' => '[Przeanalizowałem|Przeanalizowałam] dokumenty, które {wysłał|wysłała} klient',
                'body' => '<p>[Sprawdziłem|Sprawdziłam] umowę, którą {przesłał|przesłała} {{pani_pana}}.</p>',
            ]);

        $payload = app(PotentialMatterClientActionService::class)->defaultPayload(
            $matter->refresh(),
            PotentialMatterClientActionService::SEND_CONTRACT_ANALYSIS,
        );

        $this->assertSame('Przeanalizowałam dokumenty, które wysłał klient', $payload['subject']);
        $this->assertSame('<p>Sprawdziłam umowę, którą przesłał Pana.</p>', $payload['body']);
    }

    public function test_widget_sends_client_mail_and_updates_current_stage(): void
    {
        Notification::fake();
        Storage::fake('local');

        $matter = $this->createPotentialMatterOnDefaultStage();
        $lead = $this->createLead($matter);
        $sender = User::factory()->create([
            'name' => 'Wiktoria Rajzynger',
            'email' => 'wiktoria.rajzynger@example.test',
            'is_active' => true,
            'is_employee' => true,
            'is_lawyer' => true,
        ]);

        $this->actingAs($sender);

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
            ->assertSee('Podejmij działanie');

        Notification::assertSentOnDemand(
            LeadGeneratedMessage::class,
            fn (LeadGeneratedMessage $notification, array $channels, object $notifiable): bool => $channels === ['mail']
                && $notifiable->routes['mail'] === $lead->email
                && $notification->subject === 'Potrzebujemy dodatkowych informacji'
                && $notification->body === '<p>Prosimy o dosłanie brakujących danych.</p>'
                && count($notification->attachments) === 1
                && $notification->attachments[0]['as'] === '2026.07.12 J. Kowalski - Analiza umowy.pdf'
                && $notification->replyToEmail === 'wiktoria.rajzynger@example.test'
                && $notification->replyToName === 'Wiktoria Rajzynger',
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

    public function test_client_action_service_rejects_actions_unavailable_on_current_stage(): void
    {
        Notification::fake();

        $matter = $this->createPotentialMatterOnDefaultStage();
        $this->createLead($matter);

        $analysisSentStage = TemplateStage::query()
            ->where('category', 'Potencjalna')
            ->where('label', 'Wysłano analizę umowy')
            ->firstOrFail();

        StageManager::setCurrentStage($matter->refresh(), $analysisSentStage);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('To działanie nie jest dostępne na aktualnym etapie sprawy.');

        app(PotentialMatterClientActionService::class)->send(
            matter: $matter->refresh(),
            action: PotentialMatterClientActionService::REQUEST_ADDITIONAL_INFO,
            subject: 'Potrzebujemy dodatkowych informacji',
            body: '<p>Prosimy o dosłanie brakujących danych.</p>',
        );
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
