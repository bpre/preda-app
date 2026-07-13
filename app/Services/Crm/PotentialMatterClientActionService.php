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

    private const ACTIONS = [
        self::CONFIRM_QUALIFICATION => [
            'label' => 'Wyślij potwierdzenie kwalifikacji sprawy',
            'stage_label' => 'Wysłano potwierdzenie kwalifikacji sprawy',
            'subject' => 'Potwierdzenie kwalifikacji sprawy',
            'sort' => 2,
        ],
        self::REQUEST_ADDITIONAL_INFO => [
            'label' => 'Wyślij prośbę o dodatkowe informacje',
            'stage_label' => 'Wysłano prośbę o dodatkowe informacje',
            'subject' => 'Prośba o dodatkowe informacje',
            'sort' => 3,
        ],
        self::SEND_CONTRACT_ANALYSIS => [
            'label' => 'Wyślij analizę umowy',
            'stage_label' => 'Wysłano analizę umowy',
            'subject' => 'Analiza umowy kredytu',
            'sort' => 4,
        ],
    ];

    /**
     * @return array<string, string>
     */
    public function options(): array
    {
        return collect(self::ACTIONS)
            ->mapWithKeys(fn (array $action, string $key): array => [$key => $action['label']])
            ->all();
    }

    /**
     * @return array{subject: string, body: string}
     */
    public function defaultPayload(Matter $matter, string $action): array
    {
        $definition = $this->definition($action);
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
            'body' => $this->defaultBody($matter, $action),
        ];
    }

    public function label(string $action): string
    {
        return $this->definition($action)['label'];
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
    ): TemplateStage {
        $definition = $this->definition($action);
        $email = $this->recipientEmail($matter);
        $subject = trim((string) $subject);
        $body = trim((string) $body);

        if (blank($email)) {
            throw new RuntimeException('Nie znaleziono adresu e-mail klienta.');
        }

        if (blank($subject) || blank(strip_tags($body))) {
            throw new RuntimeException('Temat i treść wiadomości nie mogą być puste.');
        }

        Notification::route('mail', $email)
            ->notify(new LeadGeneratedMessage($subject, $body, $this->selectedAttachments($matter, $generatedDocumentIds)));

        $stage = $this->targetStage($definition);

        StageManager::setCurrentStage($matter->refresh(), $stage, now());

        return $stage;
    }

    /**
     * @return array{label: string, stage_label: string, subject: string, sort: int}
     */
    private function definition(string $action): array
    {
        if (! array_key_exists($action, self::ACTIONS)) {
            throw new InvalidArgumentException('Nieznana akcja CRM.');
        }

        return self::ACTIONS[$action];
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
            default => throw new InvalidArgumentException('Nieznana akcja CRM.'),
        };
    }

    private function renderTemplate(Matter $matter, string $template, bool $escapeHtml = false): string
    {
        $template = strtr($template, $this->templateReplacements($matter, $escapeHtml));

        return $this->renderGenderVariants($matter, $template);
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

    private function renderGenderVariants(Matter $matter, string $template): string
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
