<?php

namespace App\Services\Crm;

use App\Models\CrmClientMessage;
use App\Models\CrmMailPlaceholder;
use App\Models\CrmMailTemplate;
use App\Models\CrmWorkflowOffer;
use App\Models\Matter;
use App\Models\MatterGeneratedDocument;
use App\Models\TemplateStage;
use App\Models\User;
use App\Notifications\LeadGeneratedMessage;
use App\Support\StageManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class PotentialMatterClientActionService
{
    public const CONFIRM_QUALIFICATION = 'confirm_qualification';

    public const REQUEST_ADDITIONAL_INFO = 'request_additional_info';

    public const REQUEST_CERTIFICATE = 'request_certificate';

    public const SEND_CONTRACT_ANALYSIS = 'send_contract_analysis';

    public const SEND_POST_MEETING_BENEFITS_ANALYSIS = 'send_post_meeting_benefits_analysis';

    public const FOLLOW_UP_AFTER_QUALIFICATION = 'follow_up_after_qualification';

    public const FOLLOW_UP_AFTER_INFO_REQUEST = 'follow_up_after_info_request';

    public const FOLLOW_UP_AFTER_CERTIFICATE_REQUEST = 'follow_up_after_certificate_request';

    public const FOLLOW_UP_AFTER_ANALYSIS = 'follow_up_after_analysis';

    public const SEND_OFFER = 'send_offer';

    public const FOLLOW_UP_AFTER_OFFER = 'follow_up_after_offer';

    public const FOLLOW_UP_AFTER_MEETING = 'follow_up_after_meeting';

    public const FOLLOW_UP_AFTER_POST_MEETING_BENEFITS_ANALYSIS = 'follow_up_after_post_meeting_benefits_analysis';

    public const FINAL_FOLLOW_UP_BEFORE_CLOSING = 'final_follow_up_before_closing';

    private const FEMALE_FIRST_NAMES = [
        'agnieszka', 'aleksandra', 'alicja', 'amelia', 'anna', 'barbara', 'beata', 'bozena',
        'dagmara', 'danuta', 'dorota', 'edyta', 'ewa', 'gabriela', 'grazyna', 'hanna',
        'helena', 'iwona', 'izabela', 'jadwiga', 'janina', 'joanna', 'jolanta', 'julia',
        'justyna', 'kamila', 'karolina', 'katarzyna', 'kinga', 'krystyna', 'laura',
        'lidia', 'lucyna', 'magdalena', 'malgorzata', 'maria', 'marta', 'monika',
        'natalia', 'patrycja', 'paulina', 'renata', 'sylwia', 'teresa', 'urszula',
        'weronika', 'wiktoria', 'zofia',
    ];

    private const MALE_A_ENDING_FIRST_NAMES = [
        'barnaba', 'bonawentura', 'jarema', 'kuba', 'kosma',
    ];

    /**
     * @return array<string, string>
     */
    public function options(): array
    {
        return app(PotentialMatterWorkflowService::class)->options();
    }

    /**
     * @return array{subject: string, body: string}
     */
    public function defaultPayload(Matter $matter, string $action): array
    {
        $definition = app(PotentialMatterWorkflowService::class)->definition($action);
        $template = $this->activeTemplate($action);

        if ($template) {
            return [
                'subject' => $this->renderTemplate($matter, $template->subject),
                'body' => $this->renderTemplate($matter, $template->body, escapeHtml: true),
            ];
        }

        return [
            'subject' => $definition['subject'],
            'body' => $this->renderTemplate($matter, $this->defaultBody($matter, $action), escapeHtml: true),
        ];
    }

    public function label(string $action): string
    {
        return app(PotentialMatterWorkflowService::class)->definition($action)['label'];
    }

    public function recipientSummary(Matter $matter): ?string
    {
        $lead = $matter->sourceWebsiteLead()->first(['name', 'email']);

        if ($lead && filled($lead->email)) {
            return $this->formatRecipient($lead->name ?: $this->clientName($matter), $lead->email);
        }

        $contact = $matter->contacts()
            ->whereNotNull('contacts.email')
            ->where('contacts.email', '!=', '')
            ->first(['contacts.first_name', 'contacts.last_name', 'contacts.label', 'contacts.email']);

        if ($contact && filled($contact->email)) {
            return $this->formatRecipient($this->contactName($contact) ?: $this->clientName($matter), $contact->email);
        }

        return null;
    }

    public function send(
        Matter $matter,
        string $action,
        mixed $subject,
        mixed $body,
        array $generatedDocumentIds = [],
        ?User $sender = null,
        bool $attachDefaultOffer = false,
        ?string $workflowOfferId = null,
    ): TemplateStage {
        $workflow = app(PotentialMatterWorkflowService::class);
        $template = $this->activeTemplate($action);
        $email = $this->recipientEmail($matter);
        $recipientName = $this->recipientName($matter);
        $subject = trim((string) $subject);
        $body = trim((string) $body);
        $analysisWasCompleted = $workflow->matterHasStageKey($matter, PotentialMatterWorkflowService::ANALYSIS_SENT_STAGE);

        if (! $workflow->canPerform($matter, $action)) {
            throw new RuntimeException('To działanie nie jest dostępne na aktualnym etapie sprawy.');
        }

        if (blank($email)) {
            throw new RuntimeException('Nie znaleziono adresu e-mail klienta.');
        }

        if (blank($subject) || blank(strip_tags($body))) {
            throw new RuntimeException('Temat i treść wiadomości nie mogą być puste.');
        }

        $generatedAttachments = $this->selectedAttachments($matter, $generatedDocumentIds);
        $useDefaultWorkflowOffer = $attachDefaultOffer || ($action === self::SEND_OFFER && blank($workflowOfferId));
        $workflowOffer = $this->selectedWorkflowOffer($workflowOfferId, $useDefaultWorkflowOffer);
        $workflowOfferAttachment = $workflowOffer?->attachment();

        if (($workflowOfferId || $useDefaultWorkflowOffer) && ! $workflowOffer) {
            throw new RuntimeException('Nie znaleziono aktywnej oferty do załączenia.');
        }

        if ($workflowOffer && ! $workflowOfferAttachment) {
            throw new RuntimeException('Nie znaleziono pliku wybranej oferty.');
        }

        $attachments = [
            ...$generatedAttachments,
            ...($workflowOfferAttachment ? [$workflowOfferAttachment] : []),
        ];

        Notification::route('mail', $email)
            ->notify(new LeadGeneratedMessage(
                subject: $subject,
                body: $body,
                attachments: $attachments,
                replyToEmail: $sender?->email,
                replyToName: $sender?->name,
            ));

        $stage = $workflow->targetStageForAction($action);

        if (! $stage) {
            throw new RuntimeException('Nie znaleziono docelowego etapu dla działania CRM.');
        }

        StageManager::setCurrentStage($matter->refresh(), $stage, now());

        if ($workflowOffer || $action === self::SEND_OFFER) {
            $this->recordOfferSent(
                matter: $matter->refresh(),
                workflow: $workflow,
                targetStage: $stage,
                sender: $sender,
                sentConditionally: ! $analysisWasCompleted && $action !== self::SEND_CONTRACT_ANALYSIS,
            );
        }

        CrmClientMessage::create([
            'matter_id' => $matter->getKey(),
            'crm_mail_template_id' => $template?->getKey(),
            'action' => $action,
            'recipient_name' => $recipientName,
            'recipient_email' => $email,
            'subject' => $subject,
            'body' => $body,
            'target_stage_id' => $stage->getKey(),
            'default_offer_attached' => $workflowOfferAttachment !== null,
            'crm_workflow_offer_id' => $workflowOffer?->getKey(),
            'crm_workflow_offer_label' => $workflowOffer?->label,
            'default_offer_disk' => $workflowOfferAttachment ? ($workflowOffer->disk ?: 'local') : null,
            'default_offer_path' => $workflowOfferAttachment ? $workflowOffer->path : null,
            'default_offer_filename' => $workflowOfferAttachment ? $workflowOfferAttachment['as'] : null,
            'attachments' => $this->attachmentSnapshot($attachments),
            'sent_by' => $sender?->getKey(),
            'sent_at' => now(),
        ]);

        app(PotentialMatterNextActionService::class)->refresh($matter->refresh());

        return $stage;
    }

    private function activeTemplate(string $action): ?CrmMailTemplate
    {
        return CrmMailTemplate::query()
            ->where('action', $action)
            ->where('is_active', true)
            ->first();
    }

    private function selectedWorkflowOffer(?string $workflowOfferId, bool $useDefault): ?CrmWorkflowOffer
    {
        if (filled($workflowOfferId)) {
            return CrmWorkflowOffer::query()
                ->whereKey($workflowOfferId)
                ->where('is_active', true)
                ->first();
        }

        if (! $useDefault) {
            return null;
        }

        return CrmWorkflowOffer::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->orderBy('created_at')
            ->get()
            ->first(fn (CrmWorkflowOffer $offer): bool => $offer->hasFile());
    }

    private function recipientEmail(Matter $matter): ?string
    {
        $leadEmail = trim((string) $matter->sourceWebsiteLead()->value('email'));

        if ($leadEmail !== '') {
            return $leadEmail;
        }

        $contactEmail = trim((string) $matter->contacts()
            ->whereNotNull('contacts.email')
            ->where('contacts.email', '!=', '')
            ->value('contacts.email'));

        return $contactEmail === '' ? null : $contactEmail;
    }

    private function recipientName(Matter $matter): ?string
    {
        $lead = $matter->sourceWebsiteLead()->first(['name', 'email']);

        if ($lead && filled($lead->email)) {
            return trim((string) ($lead->name ?: $this->clientName($matter))) ?: null;
        }

        $contact = $matter->contacts()
            ->whereNotNull('contacts.email')
            ->where('contacts.email', '!=', '')
            ->first(['contacts.first_name', 'contacts.last_name', 'contacts.label', 'contacts.email']);

        if ($contact && filled($contact->email)) {
            return $this->contactName($contact) ?: $this->clientName($matter);
        }

        return $this->clientName($matter);
    }

    /**
     * @return array<int, array{path: string, as: string, mime: string}>
     */
    private function selectedAttachments(Matter $matter, array $generatedDocumentIds): array
    {
        $generatedDocumentIds = array_values(array_filter($generatedDocumentIds));

        if ($generatedDocumentIds === []) {
            return [];
        }

        return $matter->generatedDocuments()
            ->whereKey($generatedDocumentIds)
            ->get()
            ->filter(function (MatterGeneratedDocument $document): bool {
                return filled($document->path)
                    && Storage::disk($document->disk ?: 'local')->exists($document->path);
            })
            ->map(function (MatterGeneratedDocument $document): array {
                return [
                    'path' => Storage::disk($document->disk ?: 'local')->path($document->path),
                    'as' => $document->downloadFilename(),
                    'mime' => $document->mime_type ?: 'application/pdf',
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array{path: string, as?: string, mime?: string}>  $attachments
     * @return array<int, array{filename: string, mime: string}>
     */
    private function attachmentSnapshot(array $attachments): array
    {
        return collect($attachments)
            ->map(fn (array $attachment): array => [
                'filename' => $attachment['as'] ?? basename($attachment['path']),
                'mime' => $attachment['mime'] ?? 'application/pdf',
            ])
            ->values()
            ->all();
    }

    private function recordOfferSent(
        Matter $matter,
        PotentialMatterWorkflowService $workflow,
        TemplateStage $targetStage,
        ?User $sender,
        bool $sentConditionally,
    ): void {
        $offerStage = $workflow->stageForKey(PotentialMatterWorkflowService::OFFER_PRESENTED_STAGE);

        if ($offerStage->getKey() !== $targetStage->getKey()) {
            StageManager::saveStageDetails($matter->refresh(), $offerStage, [
                'date' => now()->toDateString(),
                'is_current' => false,
            ]);
        }

        $matter->refresh()->forceFill([
            'offer_sent_at' => now(),
            'offer_sent_by' => $sender?->getKey(),
            'offer_sent_conditionally' => (bool) ($matter->offer_sent_conditionally || $sentConditionally),
        ])->save();
    }

    private function defaultBody(Matter $matter, string $action): string
    {
        return match ($action) {
            self::CONFIRM_QUALIFICATION => <<<'HTML'
<p>Dzień dobry,</p>
<p>potwierdzamy, że sprawa została zakwalifikowana do dalszej analizy przez kancelarię.</p>
<p>W kolejnym kroku skontaktujemy się w sprawie dalszych informacji oraz dokumentów potrzebnych do oceny możliwych działań.</p>
<p>Z poważaniem,<br>Kancelaria Pręda</p>
HTML,
            self::REQUEST_ADDITIONAL_INFO => <<<'HTML'
<p>Dzień dobry,</p>
<p>do dalszej oceny sprawy potrzebujemy dodatkowych informacji.</p>
<p>Prosimy o przesłanie brakujących danych lub dokumentów, które pozwolą nam dokończyć analizę i wskazać możliwe dalsze kroki.</p>
<p>Z poważaniem,<br>Kancelaria Pręda</p>
HTML,
            self::REQUEST_CERTIFICATE => <<<'HTML'
<p>Dzień dobry,</p>
<p>do precyzyjnego określenia możliwych korzyści potrzebne będzie zaświadczenie z banku dotyczące wykonywania umowy kredytu.</p>
<p>W załączeniu przesyłamy wniosek, który można złożyć w banku.</p>
<p>Z poważaniem,<br>Kancelaria Pręda</p>
HTML,
            self::SEND_CONTRACT_ANALYSIS => <<<'HTML'
<p>Dzień dobry,</p>
<p>przesyłamy analizę przesłanej umowy kredytu.</p>
<p>{{akapit_o_korzysciach}}</p>
<p>{{akapit_o_ofercie}}</p>
<p>[uzupełnij treść analizy przed wysyłką]</p>
<p>Z poważaniem,<br>Kancelaria Pręda</p>
HTML,
            self::FOLLOW_UP_AFTER_QUALIFICATION => <<<'HTML'
<p>Dzień dobry,</p>
<p>wracam do wiadomości dotyczącej pozytywnej kwalifikacji sprawy.</p>
<p>Jeżeli {{pani_pana}} decyzja jest aktualna, proszę o krótką informację zwrotną, a wskażemy dalsze kroki.</p>
<p>Z poważaniem,<br>Kancelaria Pręda</p>
HTML,
            self::FOLLOW_UP_AFTER_INFO_REQUEST => <<<'HTML'
<p>Dzień dobry,</p>
<p>wracam do naszej prośby o dodatkowe informacje potrzebne do oceny sprawy.</p>
<p>Po ich otrzymaniu będziemy mogli dokończyć analizę i wskazać możliwe dalsze działania.</p>
<p>Z poważaniem,<br>Kancelaria Pręda</p>
HTML,
            self::FOLLOW_UP_AFTER_CERTIFICATE_REQUEST => <<<'HTML'
<p>Dzień dobry,</p>
<p>wracam do tematu zaświadczenia z banku. Banki zwykle mają 30 dni na wydanie takiego dokumentu, dlatego po tym czasie warto sprawdzić, czy zaświadczenie jest już gotowe.</p>
<p>Po jego otrzymaniu będziemy mogli dokładniej ocenić potencjalne korzyści z prowadzenia sprawy.</p>
<p>Z poważaniem,<br>Kancelaria Pręda</p>
HTML,
            self::FOLLOW_UP_AFTER_ANALYSIS => <<<'HTML'
<p>Dzień dobry,</p>
<p>wracam do przesłanej analizy umowy kredytu.</p>
<p>{{akapit_o_korzysciach}}</p>
<p>Jeżeli pojawiły się pytania albo chcą Państwo omówić możliwe dalsze kroki, pozostaję do dyspozycji.</p>
<p>Z poważaniem,<br>Kancelaria Pręda</p>
HTML,
            self::SEND_OFFER => <<<'HTML'
<p>Dzień dobry,</p>
<p>przesyłam propozycję dalszej współpracy w sprawie dotyczącej umowy kredytu.</p>
<p>Przed przyjęciem sprawy do prowadzenia musimy zapoznać się z dokumentami oraz porozmawiać o szczegółach sytuacji.</p>
<p>Jeżeli oferta jest dla Państwa interesująca, proszę o krótką informację zwrotną. Ustalimy wtedy dogodny termin rozmowy i zakres dokumentów potrzebnych do dalszej oceny.</p>
<p>Z poważaniem,<br>Kancelaria Pręda</p>
HTML,
            self::FOLLOW_UP_AFTER_OFFER => <<<'HTML'
<p>Dzień dobry,</p>
<p>wracam do przesłanej propozycji współpracy.</p>
<p>{{akapit_o_korzysciach}}</p>
<p>Jeżeli chcą Państwo kontynuować temat, proszę o krótką odpowiedź. Wskażemy wtedy dalsze kroki.</p>
<p>Z poważaniem,<br>Kancelaria Pręda</p>
HTML,
            self::FOLLOW_UP_AFTER_MEETING => <<<'HTML'
<p>Dzień dobry,</p>
<p>wracam po naszym spotkaniu dotyczącym sprawy kredytu.</p>
<p>Jeżeli są Państwo zainteresowani dalszą współpracą, proszę o krótką informację zwrotną. W kolejnym kroku wskażemy dokumenty potrzebne do przyjęcia sprawy i ustalimy dalszy plan działania.</p>
<p>Z poważaniem,<br>Kancelaria Pręda</p>
HTML,
            self::SEND_POST_MEETING_BENEFITS_ANALYSIS => <<<'HTML'
<p>Dzień dobry,</p>
<p>zgodnie z rozmową przesyłam podsumowanie potencjalnych korzyści związanych z prowadzeniem sprawy.</p>
<p>{{akapit_o_korzysciach}}</p>
<p>Jeżeli po zapoznaniu się z tym podsumowaniem chcą Państwo zlecić prowadzenie sprawy, proszę o krótką odpowiedź.</p>
<p>Z poważaniem,<br>Kancelaria Pręda</p>
HTML,
            self::FOLLOW_UP_AFTER_POST_MEETING_BENEFITS_ANALYSIS => <<<'HTML'
<p>Dzień dobry,</p>
<p>wracam do przesłanego po spotkaniu podsumowania potencjalnych korzyści.</p>
<p>{{akapit_o_korzysciach}}</p>
<p>Jeżeli temat jest aktualny, proszę o informację, czy chcą Państwo zlecić prowadzenie sprawy.</p>
<p>Z poważaniem,<br>Kancelaria Pręda</p>
HTML,
            self::FINAL_FOLLOW_UP_BEFORE_CLOSING => <<<'HTML'
<p>Dzień dobry,</p>
<p>{{kontekst_ostatniego_kontaktu}}</p>
<p>Ponieważ nie otrzymaliśmy odpowiedzi, na ten moment nie będziemy już ponawiać kontaktu w tej sprawie.</p>
<p>Jeżeli temat jest nadal aktualny, proszę po prostu odpisać na tę wiadomość - wrócimy do sprawy.</p>
<p>Z poważaniem,<br>Kancelaria Pręda</p>
HTML,
            default => throw new InvalidArgumentException('Nieznana akcja CRM.'),
        };
    }

    private function renderTemplate(Matter $matter, string $template, bool $escapeHtml = false): string
    {
        $template = strtr($template, $this->templateReplacements($matter, $escapeHtml));

        return $this->renderLawyerGenderVariants($matter, $this->renderRecipientGenderVariants($matter, $template));
    }

    /**
     * @return array<string, string>
     */
    private function templateReplacements(Matter $matter, bool $escapeHtml = false): array
    {
        $lead = $matter->sourceWebsiteLead()->first(['bank', 'credit_currency', 'contract_year_range']);
        $lawyer = $this->matterLawyer($matter);

        $replacements = [
            '{{pani_pana}}' => $this->recipientCasePhrase($matter),
            '{{bank}}' => trim((string) ($lead?->bank ?? '')),
            '{{waluta_kredytu}}' => trim((string) ($lead?->credit_currency ?? '')),
            '{{rok_umowy}}' => trim((string) ($lead?->contract_year_range ?? '')),
            '{{link_do_konsultacji}}' => trim((string) ($lawyer?->consultation_url ?? '')),
            '{{prawnik}}' => trim((string) ($lawyer?->name ?? '')),
            '{{funkcja}}' => trim((string) ($lawyer?->mail_signature_title ?? '')),
            '{{akapit_o_korzysciach}}' => $this->benefitsParagraph($matter, escapeHtml: false),
            '{{akapit_o_ofercie}}' => $this->offerParagraph($matter, escapeHtml: false),
            '{{kontekst_ostatniego_kontaktu}}' => $this->lastContactContext($matter, escapeHtml: false),
        ];

        if (! $escapeHtml) {
            return $replacements;
        }

        return [
            ...array_map(fn (string $value): string => e($value), $replacements),
            '{{akapit_o_korzysciach}}' => $this->benefitsParagraph($matter, escapeHtml: true),
            '{{akapit_o_ofercie}}' => $this->offerParagraph($matter, escapeHtml: true),
            '{{kontekst_ostatniego_kontaktu}}' => $this->lastContactContext($matter, escapeHtml: true),
        ];
    }

    private function benefitsParagraph(Matter $matter, bool $escapeHtml): string
    {
        if (! $matter->has_certificate) {
            return '';
        }

        $items = collect([
            'potencjalne korzyści' => $this->formatMoney($matter->potential_benefits_amount),
            'anulowanie przyszłych rat' => $this->formatMoney($matter->future_installments_cancellation_amount),
            'nadpłata do zwrotu' => $this->formatMoney($matter->overpayment_refund_amount),
        ])->filter();

        if ($items->isEmpty()) {
            return $this->renderMailPlaceholder(
                CrmMailPlaceholder::BENEFITS,
                CrmMailPlaceholder::BENEFITS_WITHOUT_AMOUNTS,
                [],
                $escapeHtml,
            );
        }

        $summary = $items
            ->map(fn (string $value, string $label): string => "{$label}: {$value}")
            ->implode('; ');

        return $this->renderMailPlaceholder(
            CrmMailPlaceholder::BENEFITS,
            CrmMailPlaceholder::BENEFITS_WITH_AMOUNTS,
            [
                '{{podsumowanie_korzysci}}' => $summary,
                '{{potencjalne_korzysci}}' => $this->formatMoney($matter->potential_benefits_amount) ?? '',
                '{{anulowanie_przyszlych_rat}}' => $this->formatMoney($matter->future_installments_cancellation_amount) ?? '',
                '{{nadplata_do_zwrotu}}' => $this->formatMoney($matter->overpayment_refund_amount) ?? '',
            ],
            $escapeHtml,
        );
    }

    private function offerParagraph(Matter $matter, bool $escapeHtml): string
    {
        if (! $matter->offer_sent_at) {
            return '';
        }

        return $this->renderMailPlaceholder(
            CrmMailPlaceholder::OFFER,
            $matter->offer_sent_conditionally
                ? CrmMailPlaceholder::OFFER_CONDITIONAL
                : CrmMailPlaceholder::OFFER_STANDARD,
            [
                '{{nazwa_oferty}}' => $this->lastSentOfferLabel($matter) ?? '',
            ],
            $escapeHtml,
        );
    }

    private function lastContactContext(Matter $matter, bool $escapeHtml): string
    {
        $message = $matter->crmClientMessages()
            ->orderByDesc('sent_at')
            ->orderByDesc('created_at')
            ->first(['action', 'subject', 'sent_at']);

        if ($message) {
            $date = $message->sent_at?->format('d.m.Y');
            $subject = trim((string) $message->subject);

            if (filled($date) && filled($subject)) {
                return $this->renderMailPlaceholder(
                    CrmMailPlaceholder::LAST_CONTACT_CONTEXT,
                    CrmMailPlaceholder::LAST_MESSAGE_WITH_SUBJECT,
                    [
                        '{{data_ostatniej_wiadomosci}}' => $date,
                        '{{temat_ostatniej_wiadomosci}}' => $subject,
                        '{{akcja_ostatniej_wiadomosci}}' => app(PotentialMatterWorkflowService::class)->actionLabel($message->action),
                    ],
                    $escapeHtml,
                );
            }

            return $this->renderMailPlaceholder(
                CrmMailPlaceholder::LAST_CONTACT_CONTEXT,
                CrmMailPlaceholder::LAST_MESSAGE_GENERIC,
                [
                    '{{data_ostatniej_wiadomosci}}' => $date ?? '',
                    '{{akcja_ostatniej_wiadomosci}}' => app(PotentialMatterWorkflowService::class)->actionLabel($message->action),
                ],
                $escapeHtml,
            );
        }

        $stage = $matter->currentStage()->first(['label']);

        if ($stage) {
            return $this->renderMailPlaceholder(
                CrmMailPlaceholder::LAST_CONTACT_CONTEXT,
                CrmMailPlaceholder::CURRENT_STAGE,
                ['{{aktualny_etap}}' => $stage->label],
                $escapeHtml,
            );
        }

        return $this->renderMailPlaceholder(
            CrmMailPlaceholder::LAST_CONTACT_CONTEXT,
            CrmMailPlaceholder::GENERIC_CONTEXT,
            [],
            $escapeHtml,
        );
    }

    /**
     * @param  array<string, string>  $replacements
     */
    private function renderMailPlaceholder(string $key, string $variant, array $replacements, bool $escapeHtml): string
    {
        $text = strtr(CrmMailPlaceholder::bodyFor($key, $variant), $replacements);

        return $escapeHtml ? e($text) : $text;
    }

    private function lastSentOfferLabel(Matter $matter): ?string
    {
        $label = trim((string) $matter->crmClientMessages()
            ->whereNotNull('crm_workflow_offer_label')
            ->orderByDesc('sent_at')
            ->orderByDesc('created_at')
            ->value('crm_workflow_offer_label'));

        return $label === '' ? null : $label;
    }

    private function formatMoney(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        return number_format((float) $value, 2, ',', ' ').' zł';
    }

    private function matterLawyer(Matter $matter): ?User
    {
        if ($matter->relationLoaded('lawyer') && $matter->lawyer instanceof User) {
            return $matter->lawyer;
        }

        return $matter->lawyer()
            ->first(['id', 'name', 'signature_title', 'consultation_url', 'is_lawyer', 'is_employee']);
    }

    private function renderRecipientGenderVariants(Matter $matter, string $template): string
    {
        $gender = $this->recipientGender($matter);

        return preg_replace_callback(
            '/(?<!\{)\{([^{}|]*)\|([^{}]*)\}(?!\})/u',
            fn (array $matches): string => match ($gender) {
                'male' => $matches[1],
                'female' => $matches[2],
                default => $matches[1].'/'.$matches[2],
            },
            $template,
        ) ?? $template;
    }

    private function renderLawyerGenderVariants(Matter $matter, string $template): string
    {
        $gender = $this->lawyerGender($matter);

        return preg_replace_callback(
            '/(?<!\[)\[([^\[\]|]*)\|([^\[\]]*)\](?!\])/u',
            fn (array $matches): string => match ($gender) {
                'male' => $matches[1],
                'female' => $matches[2],
                default => $matches[1].'/'.$matches[2],
            },
            $template,
        ) ?? $template;
    }

    private function recipientCasePhrase(Matter $matter): string
    {
        return match ($this->recipientGender($matter)) {
            'female' => 'Pani',
            'male' => 'Pana',
            default => 'Pani/Pana',
        };
    }

    private function recipientGender(Matter $matter): ?string
    {
        $lead = $matter->sourceWebsiteLead()->first(['name', 'email']);

        if ($lead && filled($lead->email)) {
            return $this->genderForName($lead->name);
        }

        $contact = $matter->contacts()
            ->whereNotNull('contacts.email')
            ->where('contacts.email', '!=', '')
            ->first(['contacts.first_name', 'contacts.last_name', 'contacts.label', 'contacts.email', 'contacts.sex']);

        if ($contact) {
            return $this->genderForSex($contact->sex)
                ?? $this->genderForName($this->contactName($contact));
        }

        if ($lead) {
            return $this->genderForName($lead->name);
        }

        return $this->genderForName($this->clientName($matter));
    }

    private function lawyerGender(Matter $matter): ?string
    {
        return $this->genderForName($this->matterLawyer($matter)?->name);
    }

    private function genderForSex(?string $sex): ?string
    {
        return match (strtolower(trim((string) $sex))) {
            'k', 'female', 'kobieta' => 'female',
            'm', 'male', 'mezczyzna', 'mężczyzna' => 'male',
            default => null,
        };
    }

    private function genderForName(?string $name): ?string
    {
        $firstName = $this->firstName($name);

        if (! $firstName) {
            return null;
        }

        if (in_array($firstName, self::FEMALE_FIRST_NAMES, true)) {
            return 'female';
        }

        if (in_array($firstName, self::MALE_A_ENDING_FIRST_NAMES, true)) {
            return 'male';
        }

        return str_ends_with($firstName, 'a') ? 'female' : 'male';
    }

    private function firstName(?string $name): ?string
    {
        $name = Str::of((string) $name)
            ->replaceMatches('/\s+/', ' ')
            ->trim();

        if ($name->isEmpty()) {
            return null;
        }

        $parts = $name
            ->explode(' ')
            ->map(fn (string $part): string => Str::of($part)->lower()->ascii()->toString())
            ->reject(fn (string $part): bool => in_array($part, ['pan', 'pani', 'mecenas', 'adwokat'], true))
            ->values();

        if ($parts->isEmpty()) {
            return null;
        }

        $recognizedName = $parts->first(fn (string $part): bool => in_array($part, self::FEMALE_FIRST_NAMES, true)
            || in_array($part, self::MALE_A_ENDING_FIRST_NAMES, true));

        return is_string($recognizedName) ? $recognizedName : $parts->first();
    }

    private function clientName(Matter $matter): ?string
    {
        $leadName = trim((string) $matter->sourceWebsiteLead()->value('name'));

        if ($leadName !== '') {
            return $leadName;
        }

        $label = trim((string) str($matter->label)->before('/'));

        return $label === '' ? null : $label;
    }

    private function contactName(object $contact): ?string
    {
        $name = trim((string) ($contact->label ?: trim(($contact->first_name ?? '').' '.($contact->last_name ?? ''))));

        return $name === '' ? null : $name;
    }

    private function formatRecipient(?string $name, string $email): string
    {
        $name = trim((string) $name);
        $email = trim($email);

        return $name === '' ? $email : "{$name} <{$email}>";
    }
}
