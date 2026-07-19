<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const RULE_KEY = 'review_new_potential_matter';

    private const ACTION_KEY = 'review_new_potential_matter';

    private const STAGE_KEY = 'new_contract';

    private const REASON = 'Nowa potencjalna sprawa wymaga weryfikacji i wyboru pierwszego działania.';

    public function up(): void
    {
        if (! Schema::hasTable('crm_workflow_rules')) {
            return;
        }

        $this->upsertRule();
        $this->markCurrentNewPotentialMattersAsDue();
    }

    public function down(): void
    {
        if (! Schema::hasTable('crm_workflow_rules')) {
            return;
        }

        DB::table('crm_workflow_rules')
            ->where('key', self::RULE_KEY)
            ->delete();

        if (! Schema::hasTable('matters')) {
            return;
        }

        DB::table('matters')
            ->where('next_action_key', self::ACTION_KEY)
            ->update([
                'next_action_key' => null,
                'next_action_due_at' => null,
                'next_action_reason' => null,
                'next_action_generated_at' => now(),
                'updated_at' => now(),
            ]);
    }

    private function upsertRule(): void
    {
        $existingId = DB::table('crm_workflow_rules')
            ->where('key', self::RULE_KEY)
            ->value('id');

        DB::table('crm_workflow_rules')->updateOrInsert(
            ['key' => self::RULE_KEY],
            [
                'id' => $existingId ?: (string) Str::uuid(),
                'name' => 'Weryfikacja nowej potencjalnej sprawy',
                'trigger_stage_key' => self::STAGE_KEY,
                'suggested_action_key' => self::ACTION_KEY,
                'delay_days' => 0,
                'blocking_stage_keys' => json_encode([
                    'additional_info_requested',
                    'additional_info_follow_up_sent',
                    'certificate_request_sent',
                    'certificate_request_follow_up_sent',
                    'qualification_confirmed',
                    'qualification_follow_up_sent',
                    'analysis_sent',
                    'analysis_follow_up_sent',
                    'offer_presented',
                    'offer_follow_up_sent',
                    'meeting_scheduled',
                    'meeting_completed',
                    'meeting_follow_up_sent',
                    'post_meeting_benefits_analysis_sent',
                    'post_meeting_benefits_follow_up_sent',
                    'client_retained_intent_confirmed',
                    'matter_retained',
                    'final_follow_up_sent',
                ]),
                'reason' => self::REASON,
                'is_active' => true,
                'sort' => 1,
                'created_at' => $existingId
                    ? DB::table('crm_workflow_rules')->where('id', $existingId)->value('created_at')
                    : now(),
                'updated_at' => now(),
            ],
        );
    }

    private function markCurrentNewPotentialMattersAsDue(): void
    {
        if (
            ! Schema::hasTable('matters')
            || ! Schema::hasTable('template_stages')
            || ! Schema::hasColumn('matters', 'current_template_stage_id')
            || ! Schema::hasColumn('matters', 'next_action_key')
        ) {
            return;
        }

        $stageIds = DB::table('template_stages')
            ->where('category', 'Potencjalna')
            ->where('key', self::STAGE_KEY)
            ->pluck('id');

        if ($stageIds->isEmpty()) {
            return;
        }

        $matters = DB::table('matters')
            ->where('category', 'CHF')
            ->where('is_matter', false)
            ->where('is_archived', false)
            ->whereNull('end')
            ->whereIn('current_template_stage_id', $stageIds)
            ->where(function ($query): void {
                $query
                    ->whereNull('next_action_key')
                    ->orWhereNull('next_action_due_at');
            })
            ->select(['id', 'created_at'])
            ->get();

        if ($matters->isEmpty()) {
            return;
        }

        $stageRows = $this->stageRowsForMatters($matters->pluck('id')->all(), $stageIds->all());

        foreach ($matters as $matter) {
            DB::table('matters')
                ->where('id', $matter->id)
                ->update([
                    'next_action_key' => self::ACTION_KEY,
                    'next_action_due_at' => $this->historicalDueDate($matter, $stageRows->get($matter->id)),
                    'next_action_reason' => self::REASON,
                    'next_action_generated_at' => now(),
                    'updated_at' => now(),
                ]);
        }
    }

    private function stageRowsForMatters(array $matterIds, array $stageIds): \Illuminate\Support\Collection
    {
        if (
            $matterIds === []
            || $stageIds === []
            || ! Schema::hasTable('stages')
            || ! Schema::hasColumn('stages', 'stage_id')
            || ! Schema::hasColumn('stages', 'matter_id')
        ) {
            return collect();
        }

        return DB::table('stages')
            ->whereIn('matter_id', $matterIds)
            ->whereIn('stage_id', $stageIds)
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->get(['matter_id', 'date', 'created_at'])
            ->groupBy('matter_id')
            ->map(fn ($rows) => $rows->first());
    }

    private function historicalDueDate(object $matter, ?object $stage): string
    {
        return Carbon::parse($stage?->date ?? $stage?->created_at ?? $matter->created_at ?? now())->toDateString();
    }
};
