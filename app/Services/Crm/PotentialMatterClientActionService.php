<?php

namespace App\Services\Crm;

use App\Models\CrmMailTemplate;
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

    public const SEND_CONTRACT_ANALYSIS = 'send_contract_analysis';

    public const FOLLOW_UP_AFTER_QUALIFICATION = 'follow_up_after_qualification';

    public const FOLLOW_UP_AFTER_INFO_REQUEST = 'follow_up_after_info_request';

    public const FOLLOW_UP_AFTER_ANALYSIS = 'follow_up_after_analysis';

    public const SEND_OFFER = 'send_offer';

    public const FOLLOW_UP_AFTER_MEETING = 'follow_up_after_meeting';

    private const CATEGORY = 'Potencjalna';

    private const PARENT = 'Pozyskanie klienta';

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
        $template = CrmMailTemplate::query()
            ->where('action', $action)
            ->where('is_active', true)
            ->first();

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
    ): TemplateStage {
        $workflow = app(PotentialMatterWorkflowService::class);
        $definition = $workflow->definition($action);
        $email = $this->recipientEmail($matter);
        $subject = trim((string) $subject);
        $body = trim((string) $body);

        if (! $workflow->canPerform($matter, $action)) {
            throw new RuntimeException('To działanie nie jest dostępne na aktualnym etapie sprawy.');
        }

        if (blank($email)) {
            throw new RuntimeException('Nie znaleziono adresu e-mail klienta.');
        }

        if (blank($subject) || blank(strip_tags($body))) {
            throw new RuntimeException('Temat i treść wiadomości nie mogą być puste.');
        }

        Notification::route('mail', $email)
            ->notify(new LeadGeneratedMessage(
                subject: $subject,
                body: $body,
                attachments: $this->selectedAttachments($matter, $generatedDocumentIds),
                replyToEmail: $sender?->email,
                replyToName: $sender?->name,
            ));

        $stage = $this->targetStage($definition);

        StageManager::setCurrentStage($matter->refresh(), $stage, now());

        return $stage;
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
     * @param  array{stage_label: string, sort: int}  $definition
     */
    private function targetStage(array $definition): TemplateStage
    {
        $stage = TemplateStage::query()
            ->where('category', self::CATEGORY)
            ->where('label', $definition['stage_label'])
            ->first();

        if (! $stage) {
            $stage = TemplateStage::create([
                'id' => (string) Str::uuid(),
                'category' => self::CATEGORY,
                'label' => $definition['stage_label'],
                'parent' => self::PARENT,
                'sort' => $definition['sort'],
                'is_lead_default' => false,
                'is_chf_default' => false,
                'is_active' => true,
            ]);
        }

        if (! $stage->is_active) {
            $stage->forceFill(['is_active' => true])->save();
        }

        return $stage;
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
            self::SEND_CONTRACT_ANALYSIS => <<<'HTML'
<p>Dzień dobry,</p>
<p>przesyłamy analizę przesłanej umowy kredytu.</p>
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
            self::FOLLOW_UP_AFTER_ANALYSIS => <<<'HTML'
<p>Dzień dobry,</p>
<p>wracam do przesłanej analizy umowy kredytu.</p>
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
            self::FOLLOW_UP_AFTER_MEETING => <<<'HTML'
<p>Dzień dobry,</p>
<p>wracam po naszym spotkaniu dotyczącym sprawy kredytu.</p>
<p>Jeżeli są Państwo zainteresowani dalszą współpracą, proszę o krótką informację zwrotną. W kolejnym kroku wskażemy dokumenty potrzebne do przyjęcia sprawy i ustalimy dalszy plan działania.</p>
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
        ];

        if (! $escapeHtml) {
            return $replacements;
        }

        return array_map(fn (string $value): string => e($value), $replacements);
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
