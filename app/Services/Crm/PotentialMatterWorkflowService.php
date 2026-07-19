<?php

namespace App\Services\Crm;

use App\Models\Matter;
use App\Models\Stage;
use App\Models\TemplateStage;
use App\Support\StageManager;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PotentialMatterWorkflowService
{
    public const CATEGORY = 'Potencjalna';

    public const PARENT = 'Pozyskanie klienta';

    public const NEW_CONTRACT_STAGE = 'new_contract';

    public const QUALIFICATION_CONFIRMED_STAGE = 'qualification_confirmed';

    public const QUALIFICATION_FOLLOW_UP_SENT_STAGE = 'qualification_follow_up_sent';

    public const ADDITIONAL_INFO_REQUESTED_STAGE = 'additional_info_requested';

    public const ADDITIONAL_INFO_FOLLOW_UP_SENT_STAGE = 'additional_info_follow_up_sent';

    public const CERTIFICATE_REQUEST_SENT_STAGE = 'certificate_request_sent';

    public const CERTIFICATE_REQUEST_FOLLOW_UP_SENT_STAGE = 'certificate_request_follow_up_sent';

    public const ANALYSIS_SENT_STAGE = 'analysis_sent';

    public const ANALYSIS_FOLLOW_UP_SENT_STAGE = 'analysis_follow_up_sent';

    public const OFFER_PRESENTED_STAGE = 'offer_presented';

    public const OFFER_FOLLOW_UP_SENT_STAGE = 'offer_follow_up_sent';

    public const MEETING_SCHEDULED_STAGE = 'meeting_scheduled';

    public const MEETING_COMPLETED_STAGE = 'meeting_completed';

    public const MEETING_FOLLOW_UP_SENT_STAGE = 'meeting_follow_up_sent';

    public const POST_MEETING_BENEFITS_ANALYSIS_SENT_STAGE = 'post_meeting_benefits_analysis_sent';

    public const POST_MEETING_BENEFITS_FOLLOW_UP_SENT_STAGE = 'post_meeting_benefits_follow_up_sent';

    public const CLIENT_RETAINED_INTENT_CONFIRMED_STAGE = 'client_retained_intent_confirmed';

    public const FINAL_FOLLOW_UP_SENT_STAGE = 'final_follow_up_sent';

    public const MATTER_RETAINED_STAGE = 'matter_retained';

    public const REVIEW_NEW_POTENTIAL_MATTER = 'review_new_potential_matter';

    public const CONFIRM_QUALIFICATION = 'confirm_qualification';

    public const REQUEST_ADDITIONAL_INFO = 'request_additional_info';

    public const REQUEST_CERTIFICATE = 'request_certificate';

    public const SEND_CONTRACT_ANALYSIS = 'send_contract_analysis';

    public const SEND_OFFER = 'send_offer';

    public const SEND_POST_MEETING_BENEFITS_ANALYSIS = 'send_post_meeting_benefits_analysis';

    public const FOLLOW_UP_AFTER_QUALIFICATION = 'follow_up_after_qualification';

    public const FOLLOW_UP_AFTER_INFO_REQUEST = 'follow_up_after_info_request';

    public const FOLLOW_UP_AFTER_CERTIFICATE_REQUEST = 'follow_up_after_certificate_request';

    public const FOLLOW_UP_AFTER_ANALYSIS = 'follow_up_after_analysis';

    public const FOLLOW_UP_AFTER_OFFER = 'follow_up_after_offer';

    public const FOLLOW_UP_AFTER_MEETING = 'follow_up_after_meeting';

    public const FOLLOW_UP_AFTER_POST_MEETING_BENEFITS_ANALYSIS = 'follow_up_after_post_meeting_benefits_analysis';

    public const FINAL_FOLLOW_UP_BEFORE_CLOSING = 'final_follow_up_before_closing';

    public const ARCHIVE_POTENTIAL_MATTER = 'archive_potential_matter';

    private const STAGES = [
        self::NEW_CONTRACT_STAGE => [
            'label' => 'Nowa umowa',
            'aliases' => ['Nowy lead'],
            'sort' => 1,
            'default' => true,
            'preferred_action_key' => 'send_contract_analysis',
        ],
        self::QUALIFICATION_CONFIRMED_STAGE => [
            'label' => 'Wysłano potwierdzenie kwalifikacji sprawy',
            'aliases' => ['Potwierdzono kwalifikację sprawy'],
            'sort' => 2,
            'preferred_action_key' => 'follow_up_after_qualification',
        ],
        self::QUALIFICATION_FOLLOW_UP_SENT_STAGE => [
            'label' => 'Follow-up (po kwalifikacji)',
            'aliases' => [],
            'sort' => 3,
            'preferred_action_key' => 'final_follow_up_before_closing',
        ],
        self::ADDITIONAL_INFO_REQUESTED_STAGE => [
            'label' => 'Wysłano prośbę o dodatkowe informacje',
            'aliases' => ['Wysłano prośbę o dodatkowe dokumenty'],
            'sort' => 4,
            'preferred_action_key' => 'follow_up_after_info_request',
        ],
        self::ADDITIONAL_INFO_FOLLOW_UP_SENT_STAGE => [
            'label' => 'Follow-up (po prośbie o dodatkowe informacje)',
            'aliases' => ['Follow-up (prośba o informacje)'],
            'sort' => 5,
            'preferred_action_key' => 'final_follow_up_before_closing',
        ],
        self::CERTIFICATE_REQUEST_SENT_STAGE => [
            'label' => 'Wniosek o wydanie zaświadczenia',
            'aliases' => [],
            'sort' => 6,
            'preferred_action_key' => 'follow_up_after_certificate_request',
        ],
        self::CERTIFICATE_REQUEST_FOLLOW_UP_SENT_STAGE => [
            'label' => 'Follow-up (po prośbie o zaświadczenie)',
            'aliases' => [],
            'sort' => 7,
            'preferred_action_key' => 'final_follow_up_before_closing',
        ],
        self::ANALYSIS_SENT_STAGE => [
            'label' => 'Przesłano analizę klientowi',
            'aliases' => ['Wysłano analizę umowy'],
            'sort' => 8,
            'preferred_action_key' => 'follow_up_after_analysis',
        ],
        self::ANALYSIS_FOLLOW_UP_SENT_STAGE => [
            'label' => 'Follow-up (po wysłaniu analizy)',
            'aliases' => [],
            'sort' => 9,
            'preferred_action_key' => 'final_follow_up_before_closing',
        ],
        self::OFFER_PRESENTED_STAGE => [
            'label' => 'Przedstawiono ofertę',
            'aliases' => ['Wysłano ofertę'],
            'sort' => 10,
            'preferred_action_key' => 'follow_up_after_offer',
        ],
        self::OFFER_FOLLOW_UP_SENT_STAGE => [
            'label' => 'Follow-up (po wysłaniu oferty, przed spotkaniem)',
            'aliases' => ['Follow-up (po ofercie)'],
            'sort' => 11,
            'preferred_action_key' => 'final_follow_up_before_closing',
        ],
        self::MEETING_SCHEDULED_STAGE => [
            'label' => 'Umówiono spotkanie',
            'aliases' => [],
            'sort' => 12,
        ],
        self::MEETING_COMPLETED_STAGE => [
            'label' => 'Spotkanie z potencjalnym klientem',
            'aliases' => [],
            'sort' => 13,
            'preferred_action_key' => 'send_post_meeting_benefits_analysis',
        ],
        self::MEETING_FOLLOW_UP_SENT_STAGE => [
            'label' => 'Follow-up (po spotkaniu)',
            'aliases' => [],
            'sort' => 14,
            'preferred_action_key' => 'final_follow_up_before_closing',
        ],
        self::POST_MEETING_BENEFITS_ANALYSIS_SENT_STAGE => [
            'label' => 'Przesłano analizę korzyści po spotkaniu',
            'aliases' => [],
            'sort' => 15,
            'preferred_action_key' => 'follow_up_after_post_meeting_benefits_analysis',
        ],
        self::POST_MEETING_BENEFITS_FOLLOW_UP_SENT_STAGE => [
            'label' => 'Follow-up (po analizie korzyści po spotkaniu)',
            'aliases' => [],
            'sort' => 16,
            'preferred_action_key' => 'final_follow_up_before_closing',
        ],
        self::CLIENT_RETAINED_INTENT_CONFIRMED_STAGE => [
            'label' => 'Potwierdzono chęć zlecenia sprawy',
            'aliases' => [],
            'sort' => 17,
        ],
        self::FINAL_FOLLOW_UP_SENT_STAGE => [
            'label' => 'Ostatni follow-up przed zamknięciem',
            'aliases' => [],
            'sort' => 18,
        ],
        self::MATTER_RETAINED_STAGE => [
            'label' => 'Zlecono prowadzenie sprawy',
            'aliases' => [],
            'sort' => 19,
        ],
    ];

    private const ACTIONS = [
        self::REVIEW_NEW_POTENTIAL_MATTER => [
            'label' => 'Zweryfikuj nową sprawę',
            'stage_key' => null,
            'subject' => '',
            'sort' => 1,
            'available_on_default_stage' => false,
            'available_from_stage_keys' => [],
            'mail_action' => false,
        ],
        self::CONFIRM_QUALIFICATION => [
            'label' => 'Wyślij potwierdzenie kwalifikacji sprawy',
            'stage_key' => self::QUALIFICATION_CONFIRMED_STAGE,
            'subject' => 'Potwierdzenie kwalifikacji sprawy',
            'sort' => 10,
            'available_on_default_stage' => true,
            'available_from_stage_keys' => [
                self::ADDITIONAL_INFO_REQUESTED_STAGE,
                self::ADDITIONAL_INFO_FOLLOW_UP_SENT_STAGE,
            ],
            'blocked_by_completed_stage_keys' => [
                self::QUALIFICATION_CONFIRMED_STAGE,
                self::ANALYSIS_SENT_STAGE,
                self::MEETING_SCHEDULED_STAGE,
                self::CLIENT_RETAINED_INTENT_CONFIRMED_STAGE,
                self::MATTER_RETAINED_STAGE,
            ],
        ],
        self::REQUEST_ADDITIONAL_INFO => [
            'label' => 'Wyślij prośbę o dodatkowe informacje',
            'stage_key' => self::ADDITIONAL_INFO_REQUESTED_STAGE,
            'subject' => 'Prośba o dodatkowe informacje',
            'sort' => 20,
            'available_on_default_stage' => true,
            'available_from_stage_keys' => [
                self::QUALIFICATION_CONFIRMED_STAGE,
            ],
            'blocked_by_completed_stage_keys' => [
                self::ADDITIONAL_INFO_REQUESTED_STAGE,
                self::ANALYSIS_SENT_STAGE,
                self::MEETING_SCHEDULED_STAGE,
                self::CLIENT_RETAINED_INTENT_CONFIRMED_STAGE,
                self::MATTER_RETAINED_STAGE,
            ],
        ],
        self::REQUEST_CERTIFICATE => [
            'label' => 'Wyślij prośbę o zaświadczenie',
            'stage_key' => self::CERTIFICATE_REQUEST_SENT_STAGE,
            'subject' => 'Wniosek o wydanie zaświadczenia',
            'sort' => 30,
            'available_on_default_stage' => false,
            'available_from_stage_keys' => [
                self::MEETING_COMPLETED_STAGE,
            ],
            'blocked_when_certificate' => true,
            'blocked_by_completed_stage_keys' => [
                self::CERTIFICATE_REQUEST_SENT_STAGE,
                self::POST_MEETING_BENEFITS_ANALYSIS_SENT_STAGE,
                self::CLIENT_RETAINED_INTENT_CONFIRMED_STAGE,
                self::MATTER_RETAINED_STAGE,
            ],
        ],
        self::SEND_CONTRACT_ANALYSIS => [
            'label' => 'Wyślij analizę umowy',
            'stage_key' => self::ANALYSIS_SENT_STAGE,
            'subject' => 'Analiza umowy kredytu',
            'sort' => 40,
            'available_on_default_stage' => true,
            'available_from_stage_keys' => [
                self::QUALIFICATION_CONFIRMED_STAGE,
                self::QUALIFICATION_FOLLOW_UP_SENT_STAGE,
                self::ADDITIONAL_INFO_REQUESTED_STAGE,
                self::ADDITIONAL_INFO_FOLLOW_UP_SENT_STAGE,
                self::CERTIFICATE_REQUEST_SENT_STAGE,
                self::CERTIFICATE_REQUEST_FOLLOW_UP_SENT_STAGE,
                self::OFFER_PRESENTED_STAGE,
                self::OFFER_FOLLOW_UP_SENT_STAGE,
                self::MEETING_COMPLETED_STAGE,
            ],
            'blocked_by_completed_stage_keys' => [
                self::ANALYSIS_SENT_STAGE,
                self::CLIENT_RETAINED_INTENT_CONFIRMED_STAGE,
                self::MATTER_RETAINED_STAGE,
            ],
        ],
        self::SEND_OFFER => [
            'label' => 'Wyślij ofertę',
            'stage_key' => self::OFFER_PRESENTED_STAGE,
            'subject' => 'Oferta współpracy',
            'sort' => 50,
            'available_on_default_stage' => false,
            'available_from_stage_keys' => [
                self::QUALIFICATION_CONFIRMED_STAGE,
                self::QUALIFICATION_FOLLOW_UP_SENT_STAGE,
                self::ADDITIONAL_INFO_REQUESTED_STAGE,
                self::ADDITIONAL_INFO_FOLLOW_UP_SENT_STAGE,
                self::ANALYSIS_SENT_STAGE,
                self::ANALYSIS_FOLLOW_UP_SENT_STAGE,
                self::MEETING_COMPLETED_STAGE,
            ],
            'blocked_by_completed_stage_keys' => [
                self::OFFER_PRESENTED_STAGE,
                self::CLIENT_RETAINED_INTENT_CONFIRMED_STAGE,
                self::MATTER_RETAINED_STAGE,
            ],
        ],
        self::SEND_POST_MEETING_BENEFITS_ANALYSIS => [
            'label' => 'Wyślij analizę korzyści po spotkaniu',
            'stage_key' => self::POST_MEETING_BENEFITS_ANALYSIS_SENT_STAGE,
            'subject' => 'Analiza potencjalnych korzyści po spotkaniu',
            'sort' => 60,
            'available_on_default_stage' => false,
            'available_from_stage_keys' => [
                self::MEETING_COMPLETED_STAGE,
                self::CERTIFICATE_REQUEST_SENT_STAGE,
                self::CERTIFICATE_REQUEST_FOLLOW_UP_SENT_STAGE,
            ],
            'required_completed_stage_keys' => [
                self::MEETING_COMPLETED_STAGE,
            ],
            'requires_certificate' => true,
            'blocked_by_completed_stage_keys' => [
                self::POST_MEETING_BENEFITS_ANALYSIS_SENT_STAGE,
                self::CLIENT_RETAINED_INTENT_CONFIRMED_STAGE,
                self::MATTER_RETAINED_STAGE,
            ],
        ],
        self::FOLLOW_UP_AFTER_QUALIFICATION => [
            'label' => 'Wyślij follow-up po kwalifikacji',
            'stage_key' => self::QUALIFICATION_FOLLOW_UP_SENT_STAGE,
            'subject' => 'Follow-up po kwalifikacji sprawy',
            'sort' => 110,
            'available_on_default_stage' => false,
            'available_from_stage_keys' => [
                self::QUALIFICATION_CONFIRMED_STAGE,
            ],
            'blocked_by_completed_stage_keys' => [
                self::QUALIFICATION_FOLLOW_UP_SENT_STAGE,
                self::ADDITIONAL_INFO_REQUESTED_STAGE,
                self::CERTIFICATE_REQUEST_SENT_STAGE,
                self::ANALYSIS_SENT_STAGE,
                self::MEETING_SCHEDULED_STAGE,
                self::OFFER_PRESENTED_STAGE,
                self::CLIENT_RETAINED_INTENT_CONFIRMED_STAGE,
                self::MATTER_RETAINED_STAGE,
            ],
        ],
        self::FOLLOW_UP_AFTER_INFO_REQUEST => [
            'label' => 'Wyślij follow-up po prośbie o informacje',
            'stage_key' => self::ADDITIONAL_INFO_FOLLOW_UP_SENT_STAGE,
            'subject' => 'Follow-up po prośbie o informacje',
            'sort' => 120,
            'available_on_default_stage' => false,
            'available_from_stage_keys' => [
                self::ADDITIONAL_INFO_REQUESTED_STAGE,
            ],
            'blocked_by_completed_stage_keys' => [
                self::ADDITIONAL_INFO_FOLLOW_UP_SENT_STAGE,
                self::QUALIFICATION_CONFIRMED_STAGE,
                self::CERTIFICATE_REQUEST_SENT_STAGE,
                self::ANALYSIS_SENT_STAGE,
                self::MEETING_SCHEDULED_STAGE,
                self::OFFER_PRESENTED_STAGE,
                self::CLIENT_RETAINED_INTENT_CONFIRMED_STAGE,
                self::MATTER_RETAINED_STAGE,
            ],
        ],
        self::FOLLOW_UP_AFTER_CERTIFICATE_REQUEST => [
            'label' => 'Wyślij follow-up po prośbie o zaświadczenie',
            'stage_key' => self::CERTIFICATE_REQUEST_FOLLOW_UP_SENT_STAGE,
            'subject' => 'Czy bank wydał już zaświadczenie?',
            'sort' => 130,
            'available_on_default_stage' => false,
            'available_from_stage_keys' => [
                self::CERTIFICATE_REQUEST_SENT_STAGE,
            ],
            'blocked_by_completed_stage_keys' => [
                self::CERTIFICATE_REQUEST_FOLLOW_UP_SENT_STAGE,
                self::ANALYSIS_SENT_STAGE,
                self::POST_MEETING_BENEFITS_ANALYSIS_SENT_STAGE,
                self::CLIENT_RETAINED_INTENT_CONFIRMED_STAGE,
                self::MATTER_RETAINED_STAGE,
            ],
        ],
        self::FOLLOW_UP_AFTER_ANALYSIS => [
            'label' => 'Wyślij follow-up po wysłaniu analizy',
            'stage_key' => self::ANALYSIS_FOLLOW_UP_SENT_STAGE,
            'subject' => 'Follow-up po wysłaniu analizy',
            'sort' => 140,
            'available_on_default_stage' => false,
            'available_from_stage_keys' => [
                self::ANALYSIS_SENT_STAGE,
            ],
            'blocked_by_completed_stage_keys' => [
                self::ANALYSIS_FOLLOW_UP_SENT_STAGE,
                self::OFFER_PRESENTED_STAGE,
                self::MEETING_SCHEDULED_STAGE,
                self::MEETING_COMPLETED_STAGE,
                self::CLIENT_RETAINED_INTENT_CONFIRMED_STAGE,
                self::MATTER_RETAINED_STAGE,
            ],
        ],
        self::FOLLOW_UP_AFTER_OFFER => [
            'label' => 'Wyślij follow-up po ofercie',
            'stage_key' => self::OFFER_FOLLOW_UP_SENT_STAGE,
            'subject' => 'Czy oferta jest dla Państwa aktualna?',
            'sort' => 150,
            'available_on_default_stage' => false,
            'available_from_stage_keys' => [
                self::OFFER_PRESENTED_STAGE,
                self::ANALYSIS_SENT_STAGE,
                self::ANALYSIS_FOLLOW_UP_SENT_STAGE,
            ],
            'required_completed_stage_keys' => [
                self::OFFER_PRESENTED_STAGE,
            ],
            'blocked_by_completed_stage_keys' => [
                self::OFFER_FOLLOW_UP_SENT_STAGE,
                self::MEETING_SCHEDULED_STAGE,
                self::CLIENT_RETAINED_INTENT_CONFIRMED_STAGE,
                self::MATTER_RETAINED_STAGE,
            ],
        ],
        self::FOLLOW_UP_AFTER_MEETING => [
            'label' => 'Wyślij follow-up po spotkaniu',
            'stage_key' => self::MEETING_FOLLOW_UP_SENT_STAGE,
            'subject' => 'Follow-up po spotkaniu',
            'sort' => 160,
            'available_on_default_stage' => false,
            'available_from_stage_keys' => [
                self::MEETING_COMPLETED_STAGE,
            ],
            'required_completed_stage_keys' => [
                self::MEETING_COMPLETED_STAGE,
            ],
            'blocked_by_completed_stage_keys' => [
                self::MEETING_FOLLOW_UP_SENT_STAGE,
                self::CERTIFICATE_REQUEST_SENT_STAGE,
                self::POST_MEETING_BENEFITS_ANALYSIS_SENT_STAGE,
                self::CLIENT_RETAINED_INTENT_CONFIRMED_STAGE,
                self::MATTER_RETAINED_STAGE,
            ],
        ],
        self::FOLLOW_UP_AFTER_POST_MEETING_BENEFITS_ANALYSIS => [
            'label' => 'Wyślij follow-up po analizie korzyści po spotkaniu',
            'stage_key' => self::POST_MEETING_BENEFITS_FOLLOW_UP_SENT_STAGE,
            'subject' => 'Czy chcą Państwo kontynuować sprawę?',
            'sort' => 170,
            'available_on_default_stage' => false,
            'available_from_stage_keys' => [
                self::POST_MEETING_BENEFITS_ANALYSIS_SENT_STAGE,
            ],
            'blocked_by_completed_stage_keys' => [
                self::POST_MEETING_BENEFITS_FOLLOW_UP_SENT_STAGE,
                self::CLIENT_RETAINED_INTENT_CONFIRMED_STAGE,
                self::MATTER_RETAINED_STAGE,
            ],
        ],
        self::FINAL_FOLLOW_UP_BEFORE_CLOSING => [
            'label' => 'Wyślij ostatni follow-up',
            'stage_key' => self::FINAL_FOLLOW_UP_SENT_STAGE,
            'subject' => 'Czy temat sprawy jest nadal aktualny?',
            'sort' => 900,
            'available_on_default_stage' => false,
            'available_from_stage_keys' => [
                self::QUALIFICATION_FOLLOW_UP_SENT_STAGE,
                self::ADDITIONAL_INFO_FOLLOW_UP_SENT_STAGE,
                self::CERTIFICATE_REQUEST_FOLLOW_UP_SENT_STAGE,
                self::ANALYSIS_FOLLOW_UP_SENT_STAGE,
                self::OFFER_FOLLOW_UP_SENT_STAGE,
                self::MEETING_FOLLOW_UP_SENT_STAGE,
                self::POST_MEETING_BENEFITS_FOLLOW_UP_SENT_STAGE,
            ],
            'blocked_by_completed_stage_keys' => [
                self::FINAL_FOLLOW_UP_SENT_STAGE,
                self::ANALYSIS_SENT_STAGE,
                self::MEETING_SCHEDULED_STAGE,
                self::CLIENT_RETAINED_INTENT_CONFIRMED_STAGE,
                self::MATTER_RETAINED_STAGE,
            ],
            'only_when_due' => true,
        ],
        self::ARCHIVE_POTENTIAL_MATTER => [
            'label' => 'Zamknij potencjalną sprawę',
            'stage_key' => null,
            'subject' => '',
            'sort' => 1000,
            'available_on_default_stage' => false,
            'available_from_stage_keys' => [],
            'mail_action' => false,
        ],
    ];

    /**
     * @return array<string, array<string, mixed>>
     */
    public function definitions(): array
    {
        return self::ACTIONS;
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(string $action): array
    {
        if (! array_key_exists($action, self::ACTIONS)) {
            throw new InvalidArgumentException('Nieznana akcja CRM.');
        }

        return self::ACTIONS[$action];
    }

    /**
     * @return array<string, string>
     */
    public function options(): array
    {
        return collect(self::ACTIONS)
            ->filter(fn (array $action, string $key): bool => $this->isMailAction($key))
            ->sortBy('sort')
            ->mapWithKeys(fn (array $action, string $key): array => [$key => $action['label']])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function availableOptions(Matter $matter): array
    {
        $availableActions = collect(self::ACTIONS)
            ->filter(fn (array $definition, string $key): bool => $this->canPerform($matter, $key))
            ->sortBy('sort');

        $preferredActionKey = $this->preferredActionKey($matter);

        if ($preferredActionKey && $availableActions->has($preferredActionKey)) {
            $availableActions = $availableActions->sortBy(
                fn (array $definition, string $key): int => $key === $preferredActionKey
                    ? -1
                    : (int) ($definition['sort'] ?? 0),
            );
        }

        return $availableActions
            ->mapWithKeys(fn (array $definition, string $key): array => [$key => $definition['label']])
            ->all();
    }

    public function shouldDisplay(Matter $matter): bool
    {
        return $this->availableOptions($matter) !== [];
    }

    public function canPerform(Matter $matter, string $action): bool
    {
        return $this->canPerformAction($matter, $action, enforceDueDate: true);
    }

    public function canSuggest(Matter $matter, string $action): bool
    {
        if ($action === self::REVIEW_NEW_POTENTIAL_MATTER) {
            return $this->isPotentialMatter($matter)
                && $this->currentStage($matter)
                && $this->isDefaultStage($matter, $this->currentStage($matter));
        }

        if ($action === self::ARCHIVE_POTENTIAL_MATTER) {
            return $this->isPotentialMatter($matter)
                && ! $matter->is_archived
                && ! $this->matterHasAnyStageKey($matter, [
                    self::CLIENT_RETAINED_INTENT_CONFIRMED_STAGE,
                    self::MATTER_RETAINED_STAGE,
                ]);
        }

        return $this->canPerformAction($matter, $action, enforceDueDate: false);
    }

    private function canPerformAction(Matter $matter, string $action, bool $enforceDueDate): bool
    {
        if (! $this->isMailAction($action)) {
            return false;
        }

        if (! $this->isPotentialMatter($matter)) {
            return false;
        }

        $definition = $this->definition($action);
        $currentStage = $this->currentStage($matter);

        if (! $currentStage || ! $currentStage->is_active) {
            return false;
        }

        if (($definition['requires_certificate'] ?? false) && ! $matter->has_certificate) {
            return false;
        }

        if (($definition['blocked_when_certificate'] ?? false) && $matter->has_certificate) {
            return false;
        }

        if ($enforceDueDate && ($definition['only_when_due'] ?? false) && ! $this->isDueNextAction($matter, $action)) {
            return false;
        }

        if ($this->matterHasAnyStageKey($matter, $definition['blocked_by_completed_stage_keys'] ?? [])) {
            return false;
        }

        $requiredCompletedStageKeys = $definition['required_completed_stage_keys'] ?? [];

        if ($requiredCompletedStageKeys !== [] && ! $this->matterHasAllStageKeys($matter, $requiredCompletedStageKeys)) {
            return false;
        }

        if (($definition['available_on_default_stage'] ?? false) && $this->isDefaultStage($matter, $currentStage)) {
            return true;
        }

        return $this->stageMatchesAnyKey($currentStage, $definition['available_from_stage_keys'] ?? []);
    }

    public function isDueNextAction(Matter $matter, string $action): bool
    {
        return $matter->next_action_key === $action
            && $matter->next_action_due_at
            && $matter->next_action_due_at->lte(now()->toDateString());
    }

    public function isMailAction(string $action): bool
    {
        if (! array_key_exists($action, self::ACTIONS)) {
            return false;
        }

        return self::ACTIONS[$action]['mail_action'] ?? true;
    }

    public function actionLabel(?string $action): string
    {
        if (! $action || ! array_key_exists($action, self::ACTIONS)) {
            return '-';
        }

        return self::ACTIONS[$action]['label'];
    }

    public function stageLabel(?string $stageKey): string
    {
        if (! $stageKey || ! array_key_exists($stageKey, self::STAGES)) {
            return '-';
        }

        return self::STAGES[$stageKey]['label'];
    }

    /**
     * @return array<string, string>
     */
    public function stageOptions(): array
    {
        return collect(self::STAGES)
            ->mapWithKeys(fn (array $stage, string $key): array => [$key => $stage['label']])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function actionOptions(bool $includeNonMailActions = true): array
    {
        return collect(self::ACTIONS)
            ->when(! $includeNonMailActions, fn (Collection $actions): Collection => $actions
                ->filter(fn (array $action, string $key): bool => $this->isMailAction($key)))
            ->sortBy('sort')
            ->mapWithKeys(fn (array $action, string $key): array => [$key => $action['label']])
            ->all();
    }

    public function targetStageForAction(string $action): ?TemplateStage
    {
        $definition = $this->definition($action);
        $stageKey = $definition['stage_key'] ?? null;

        return filled($stageKey) ? $this->stageForKey($stageKey) : null;
    }

    public function stageForKey(string $stageKey): TemplateStage
    {
        if (! array_key_exists($stageKey, self::STAGES)) {
            throw new InvalidArgumentException('Nieznany etap potencjalnej sprawy.');
        }

        $definition = self::STAGES[$stageKey];
        $labels = $this->stageLabels($stageKey);

        $stage = TemplateStage::query()
            ->where('category', self::CATEGORY)
            ->where('key', $stageKey)
            ->first()
            ?? TemplateStage::query()
                ->where('category', self::CATEGORY)
                ->whereIn('label', $labels)
                ->orderBy('sort')
                ->first();

        $updates = [
            'key' => $stageKey,
            'parent' => self::PARENT,
            'sort' => $definition['sort'],
            'is_lead_default' => false,
            'is_chf_default' => (bool) ($definition['default'] ?? false),
            'is_active' => true,
        ];

        if ($stage) {
            $stage->forceFill($updates)->save();

            return $stage->refresh();
        }

        $createData = [
            'id' => (string) Str::uuid(),
            'category' => self::CATEGORY,
            'label' => $definition['label'],
            ...$updates,
        ];

        if (Schema::hasColumn('template_stages', 'preferred_action_key')) {
            $createData['preferred_action_key'] = $definition['preferred_action_key'] ?? null;
        }

        return TemplateStage::create($createData);
    }

    public function matterHasStageKey(Matter $matter, string $stageKey): bool
    {
        if (! array_key_exists($stageKey, self::STAGES)) {
            return false;
        }

        $labels = $this->stageLabels($stageKey);

        return $matter->stages()
            ->where(function ($query) use ($stageKey, $labels): void {
                $query->whereIn('label', $labels)
                    ->orWhereHas('templateStage', fn ($query) => $query
                        ->where('key', $stageKey)
                        ->orWhereIn('label', $labels));
            })
            ->exists();
    }

    /**
     * @param  array<int, string>  $stageKeys
     */
    public function matterHasAnyStageKey(Matter $matter, array $stageKeys): bool
    {
        foreach ($stageKeys as $stageKey) {
            if ($this->matterHasStageKey($matter, $stageKey)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, string>  $stageKeys
     */
    public function matterHasAllStageKeys(Matter $matter, array $stageKeys): bool
    {
        foreach ($stageKeys as $stageKey) {
            if (! $this->matterHasStageKey($matter, $stageKey)) {
                return false;
            }
        }

        return true;
    }

    public function stageDateForKey(Matter $matter, string $stageKey): ?Carbon
    {
        if (! array_key_exists($stageKey, self::STAGES)) {
            return null;
        }

        $labels = $this->stageLabels($stageKey);

        $stage = $matter->stages()
            ->where(function ($query) use ($stageKey, $labels): void {
                $query->whereIn('label', $labels)
                    ->orWhereHas('templateStage', fn ($query) => $query
                        ->where('key', $stageKey)
                        ->orWhereIn('label', $labels));
            })
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->first();

        return $stage?->date
            ?? $stage?->created_at
            ?? $matter->updated_at
            ?? $matter->created_at;
    }

    public function isPotentialMatter(Matter $matter): bool
    {
        return $matter->category === 'CHF' && ! $matter->is_matter;
    }

    private function currentStage(Matter $matter): ?TemplateStage
    {
        if (! $matter->current_template_stage_id) {
            return null;
        }

        if (
            $matter->relationLoaded('currentStage')
            && $matter->currentStage instanceof TemplateStage
            && $matter->currentStage->getKey() === $matter->current_template_stage_id
        ) {
            return $matter->currentStage;
        }

        return TemplateStage::query()->find($matter->current_template_stage_id);
    }

    private function isDefaultStage(Matter $matter, TemplateStage $currentStage): bool
    {
        if ($this->stageMatchesAnyKey($currentStage, [self::NEW_CONTRACT_STAGE])) {
            return true;
        }

        $defaultStage = StageManager::defaultTemplateStageForMatter($matter);

        return $defaultStage && $currentStage->getKey() === $defaultStage->getKey();
    }

    private function preferredActionKey(Matter $matter): ?string
    {
        $preferredActionKey = $this->currentStage($matter)?->preferred_action_key;

        if (! is_string($preferredActionKey) || blank($preferredActionKey)) {
            return null;
        }

        return $this->isMailAction($preferredActionKey) ? $preferredActionKey : null;
    }

    /**
     * @param  array<int, string>  $stageKeys
     */
    private function stageMatchesAnyKey(TemplateStage|Stage $stage, array $stageKeys): bool
    {
        foreach ($stageKeys as $stageKey) {
            if ($this->stageMatchesKey($stage, $stageKey)) {
                return true;
            }
        }

        return false;
    }

    private function stageMatchesKey(TemplateStage|Stage $stage, string $stageKey): bool
    {
        if (! array_key_exists($stageKey, self::STAGES)) {
            return false;
        }

        if ($stage instanceof TemplateStage) {
            return $stage->key === $stageKey
                || in_array($stage->label, $this->stageLabels($stageKey), true);
        }

        return $stage->templateStage?->key === $stageKey
            || in_array($stage->label, $this->stageLabels($stageKey), true)
            || in_array((string) $stage->templateStage?->label, $this->stageLabels($stageKey), true);
    }

    /**
     * @return array<int, string>
     */
    private function stageLabels(string $stageKey): array
    {
        if (! array_key_exists($stageKey, self::STAGES)) {
            return [];
        }

        return array_values(array_unique([
            self::STAGES[$stageKey]['label'],
            ...(self::STAGES[$stageKey]['aliases'] ?? []),
        ]));
    }
}
