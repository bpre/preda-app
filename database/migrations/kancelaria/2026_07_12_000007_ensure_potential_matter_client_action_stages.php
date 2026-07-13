<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const CATEGORY = 'Potencjalna';

    private const PARENT = 'Pozyskanie klienta';

    private const OLD_REQUEST_INFO_LABEL = 'Wysłano prośbę o dodatkowe dokumenty';

    private const STAGES = [
        [
            'label' => 'Wysłano potwierdzenie kwalifikacji sprawy',
            'sort' => 2,
        ],
        [
            'label' => 'Wysłano prośbę o dodatkowe informacje',
            'sort' => 3,
        ],
        [
            'label' => 'Wysłano analizę umowy',
            'sort' => 4,
        ],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('template_stages')) {
            return;
        }

        foreach (self::STAGES as $stage) {
            $this->ensureStage($stage);
        }
    }

    public function down(): void
    {
        //
    }

    /**
     * @param  array{label: string, sort: int}  $stage
     */
    private function ensureStage(array $stage): void
    {
        $existing = DB::table('template_stages')
            ->where('category', self::CATEGORY)
            ->where('label', $stage['label'])
            ->first();

        if ($existing) {
            DB::table('template_stages')
                ->where('id', $existing->id)
                ->update($this->stageUpdates($stage));

            return;
        }

        if ($stage['label'] === 'Wysłano prośbę o dodatkowe informacje') {
            $oldStage = DB::table('template_stages')
                ->where('category', self::CATEGORY)
                ->where('label', self::OLD_REQUEST_INFO_LABEL)
                ->first();

            if ($oldStage && ! $this->hasStageHistory($oldStage->id)) {
                DB::table('template_stages')
                    ->where('id', $oldStage->id)
                    ->update([
                        ...$this->stageUpdates($stage),
                        'label' => $stage['label'],
                    ]);

                return;
            }
        }

        DB::table('template_stages')->insert([
            'id' => (string) Str::uuid(),
            'category' => self::CATEGORY,
            'label' => $stage['label'],
            ...$this->stageUpdates($stage),
        ]);
    }

    /**
     * @param  array{label: string, sort: int}  $stage
     * @return array<string, mixed>
     */
    private function stageUpdates(array $stage): array
    {
        $updates = [
            'parent' => self::PARENT,
            'sort' => $stage['sort'],
        ];

        if (Schema::hasColumn('template_stages', 'is_active')) {
            $updates['is_active'] = true;
        }

        if (Schema::hasColumn('template_stages', 'is_lead_default')) {
            $updates['is_lead_default'] = false;
        }

        if (Schema::hasColumn('template_stages', 'is_chf_default')) {
            $updates['is_chf_default'] = false;
        }

        return $updates;
    }

    private function hasStageHistory(string $templateStageId): bool
    {
        return Schema::hasTable('stages')
            && DB::table('stages')
                ->where('stage_id', $templateStageId)
                ->exists();
    }
};
