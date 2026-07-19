<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const ACTION_KEY = 'review_new_potential_matter';

    private const STAGE_KEY = 'new_contract';

    public function up(): void
    {
        if (
            ! Schema::hasTable('matters')
            || ! Schema::hasTable('template_stages')
            || ! Schema::hasColumn('matters', 'current_template_stage_id')
            || ! Schema::hasColumn('matters', 'next_action_key')
            || ! Schema::hasColumn('matters', 'next_action_due_at')
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
            ->where('next_action_key', self::ACTION_KEY)
            ->whereIn('current_template_stage_id', $stageIds)
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
                    'next_action_due_at' => $this->historicalDueDate($matter, $stageRows->get($matter->id)),
                    'next_action_generated_at' => now(),
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        //
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
