<?php

namespace App\Services\Crm;

use App\Models\CrmWorkflowRule;
use App\Models\Matter;
use Illuminate\Support\Carbon;

class PotentialMatterNextActionService
{
    public function __construct(
        private readonly PotentialMatterWorkflowService $workflow,
    ) {}

    public function refresh(Matter $matter): void
    {
        if (! $this->workflow->isPotentialMatter($matter)) {
            if (filled($matter->next_action_key) || filled($matter->next_action_due_at) || filled($matter->next_action_reason)) {
                $matter->forceFill([
                    'next_action_key' => null,
                    'next_action_due_at' => null,
                    'next_action_reason' => null,
                    'next_action_generated_at' => now(),
                ])->save();
            }

            return;
        }

        $suggestion = $this->suggest($matter->refresh());

        $matter->forceFill([
            'next_action_key' => $suggestion['action'] ?? null,
            'next_action_due_at' => $suggestion['due_at'] ?? null,
            'next_action_reason' => $suggestion['reason'] ?? null,
            'next_action_generated_at' => now(),
        ])->save();
    }

    /**
     * @return array{action: string, due_at: Carbon, reason: string|null}|null
     */
    public function suggest(Matter $matter): ?array
    {
        if (! $this->workflow->isPotentialMatter($matter) || $matter->is_archived || filled($matter->end)) {
            return null;
        }

        if ($this->workflow->matterHasAnyStageKey($matter, [
            PotentialMatterWorkflowService::CLIENT_RETAINED_INTENT_CONFIRMED_STAGE,
            PotentialMatterWorkflowService::MATTER_RETAINED_STAGE,
        ])) {
            return null;
        }

        $rule = CrmWorkflowRule::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->first(fn (CrmWorkflowRule $rule): bool => $this->ruleApplies($matter, $rule));

        return $rule ? $this->suggestionForRule($matter, $rule) : null;
    }

    private function ruleApplies(Matter $matter, CrmWorkflowRule $rule): bool
    {
        if (! $this->workflow->matterHasStageKey($matter, $rule->trigger_stage_key)) {
            return false;
        }

        if ($this->workflow->matterHasAnyStageKey($matter, $rule->blocking_stage_keys ?? [])) {
            return false;
        }

        return $this->workflow->canSuggest($matter, $rule->suggested_action_key);
    }

    /**
     * @return array{action: string, due_at: Carbon, reason: string|null}
     */
    private function suggestionForRule(Matter $matter, CrmWorkflowRule $rule): array
    {
        $triggerDate = $this->workflow->stageDateForKey($matter, $rule->trigger_stage_key)
            ?? $matter->updated_at
            ?? $matter->created_at
            ?? now();

        return [
            'action' => $rule->suggested_action_key,
            'due_at' => $triggerDate->copy()->startOfDay()->addDays($rule->delay_days),
            'reason' => $rule->reason,
        ];
    }
}
