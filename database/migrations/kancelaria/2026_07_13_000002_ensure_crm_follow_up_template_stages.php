<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const CATEGORY = 'Potencjalna';

    private const PARENT = 'Pozyskanie klienta';

    private const STAGES = [
        [
            'label' => 'Follow-up (po kwalifikacji)',
            'sort' => 5,
        ],
        [
            'label' => 'Follow-up (prośba o informacje)',
            'sort' => 6,
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
};
