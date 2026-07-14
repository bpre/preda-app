<?php

namespace App\Services\Crm;

use App\Models\Matter;
use App\Models\TemplateStage;
use App\Support\StageManager;
use InvalidArgumentException;

class PotentialMatterWorkflowService
{
    public const CONFIRM_QUALIFICATION = 'confirm_qualification';

    public const REQUEST_ADDITIONAL_INFO = 'request_additional_info';

    public const SEND_CONTRACT_ANALYSIS = 'send_contract_analysis';

    public const FOLLOW_UP_AFTER_QUALIFICATION = 'follow_up_after_qualification';

    public const FOLLOW_UP_AFTER_INFO_REQUEST = 'follow_up_after_info_request';

    public const FOLLOW_UP_AFTER_ANALYSIS = 'follow_up_after_analysis';

    public const SEND_OFFER = 'send_offer';

    public const FOLLOW_UP_AFTER_MEETING = 'follow_up_after_meeting';

    private const ACTIONS = [
        self::CONFIRM_QUALIFICATION => [
            'label' => 'Wyślij potwierdzenie kwalifikacji sprawy',
            'stage_label' => 'Wysłano potwierdzenie kwalifikacji sprawy',
            'subject' => 'Potwierdzenie kwalifikacji sprawy',
            'sort' => 2,
            'available_on_default_stage' => true,
            'available_from_stage_labels' => [
                'Wysłano prośbę o dodatkowe informacje',
                'Follow-up (prośba o informacje)',
            ],
        ],
        self::REQUEST_ADDITIONAL_INFO => [
            'label' => 'Wyślij prośbę o dodatkowe informacje',
            'stage_label' => 'Wysłano prośbę o dodatkowe informacje',
            'subject' => 'Prośba o dodatkowe informacje',
            'sort' => 3,
            'available_on_default_stage' => true,
            'available_from_stage_labels' => [],
        ],
        self::SEND_CONTRACT_ANALYSIS => [
            'label' => 'Wyślij analizę umowy',
            'stage_label' => 'Wysłano analizę umowy',
            'subject' => 'Analiza umowy kredytu',
            'sort' => 4,
            'available_on_default_stage' => true,
            'available_from_stage_labels' => [
                'Wysłano potwierdzenie kwalifikacji sprawy',
                'Wysłano prośbę o dodatkowe informacje',
                'Follow-up (po kwalifikacji)',
                'Follow-up (prośba o informacje)',
            ],
        ],
        self::FOLLOW_UP_AFTER_QUALIFICATION => [
            'label' => 'Wyślij follow-up po kwalifikacji',
            'stage_label' => 'Follow-up (po kwalifikacji)',
            'subject' => 'Follow-up po kwalifikacji sprawy',
            'sort' => 5,
            'available_on_default_stage' => false,
            'available_from_stage_labels' => [
                'Wysłano potwierdzenie kwalifikacji sprawy',
            ],
        ],
        self::FOLLOW_UP_AFTER_INFO_REQUEST => [
            'label' => 'Wyślij follow-up po prośbie o informacje',
            'stage_label' => 'Follow-up (prośba o informacje)',
            'subject' => 'Follow-up po prośbie o informacje',
            'sort' => 6,
            'available_on_default_stage' => false,
            'available_from_stage_labels' => [
                'Wysłano prośbę o dodatkowe informacje',
            ],
        ],
        self::FOLLOW_UP_AFTER_ANALYSIS => [
            'label' => 'Wyślij follow-up po wysłaniu analizy',
            'stage_label' => 'Follow-up (po wysłaniu analizy)',
            'subject' => 'Follow-up po wysłaniu analizy',
            'sort' => 7,
            'available_on_default_stage' => false,
            'available_from_stage_labels' => [
                'Wysłano analizę umowy',
            ],
        ],
        self::SEND_OFFER => [
            'label' => 'Wyślij ofertę',
            'stage_label' => 'Wysłano ofertę',
            'subject' => 'Oferta współpracy',
            'sort' => 8,
            'available_on_default_stage' => false,
            'available_from_stage_labels' => [
                'Wysłano analizę umowy',
                'Follow-up (po wysłaniu analizy)',
                'Wysłano potwierdzenie kwalifikacji sprawy',
                'Follow-up (po kwalifikacji)',
            ],
        ],
        self::FOLLOW_UP_AFTER_MEETING => [
            'label' => 'Wyślij follow-up po spotkaniu',
            'stage_label' => 'Follow-up (po spotkaniu)',
            'subject' => 'Follow-up po spotkaniu',
            'sort' => 10,
            'available_on_default_stage' => false,
            'available_from_stage_labels' => [],
            'required_completed_stage_labels' => [
                'Spotkanie z potencjalnym klientem',
            ],
            'blocked_by_completed_stage_labels' => [
                'Follow-up (po spotkaniu)',
            ],
        ],
    ];

    /**
     * @return array<string, array{label: string, stage_label: string, subject: string, sort: int, available_on_default_stage: bool, available_from_stage_labels: array<int, string>, required_completed_stage_labels?: array<int, string>, blocked_by_completed_stage_labels?: array<int, string>}>
     */
    public function definitions(): array
    {
        return self::ACTIONS;
    }

    /**
     * @return array{label: string, stage_label: string, subject: string, sort: int, available_on_default_stage: bool, available_from_stage_labels: array<int, string>, required_completed_stage_labels?: array<int, string>, blocked_by_completed_stage_labels?: array<int, string>}
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
            ->mapWithKeys(fn (array $action, string $key): array => [$key => $action['label']])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function availableOptions(Matter $matter): array
    {
        return collect(self::ACTIONS)
            ->filter(fn (array $definition, string $key): bool => $this->canPerform($matter, $key))
            ->mapWithKeys(fn (array $definition, string $key): array => [$key => $definition['label']])
            ->all();
    }

    public function shouldDisplay(Matter $matter): bool
    {
        return $this->availableOptions($matter) !== [];
    }

    public function canPerform(Matter $matter, string $action): bool
    {
        $definition = $this->definition($action);
        $currentStage = $this->currentStage($matter);

        if (! $currentStage || ! $currentStage->is_active) {
            return false;
        }

        if ($this->matterHasAnyStageLabel($matter, $definition['blocked_by_completed_stage_labels'] ?? [])) {
            return false;
        }

        if (($definition['available_on_default_stage'] ?? false) && $this->isDefaultStage($matter, $currentStage)) {
            return true;
        }

        if (in_array($currentStage->label, $definition['available_from_stage_labels'], true)) {
            return true;
        }

        $requiredCompletedStageLabels = $definition['required_completed_stage_labels'] ?? [];

        return $requiredCompletedStageLabels !== []
            && $this->matterHasAllStageLabels($matter, $requiredCompletedStageLabels);
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
        $defaultStage = StageManager::defaultTemplateStageForMatter($matter);

        return $defaultStage && $currentStage->getKey() === $defaultStage->getKey();
    }

    /**
     * @param  array<int, string>  $labels
     */
    private function matterHasAllStageLabels(Matter $matter, array $labels): bool
    {
        foreach ($labels as $label) {
            if (! $this->matterHasStageLabel($matter, $label)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<int, string>  $labels
     */
    private function matterHasAnyStageLabel(Matter $matter, array $labels): bool
    {
        foreach ($labels as $label) {
            if ($this->matterHasStageLabel($matter, $label)) {
                return true;
            }
        }

        return false;
    }

    private function matterHasStageLabel(Matter $matter, string $label): bool
    {
        return $matter->stages()
            ->where(function ($query) use ($label): void {
                $query->where('label', $label)
                    ->orWhereHas('templateStage', fn ($query) => $query->where('label', $label));
            })
            ->exists();
    }
}
