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
            'label' => 'Spotkanie z potencjalnym klientem',
            'sort' => 9,
        ],
        [
            'label' => 'Follow-up (po spotkaniu)',
            'sort' => 10,
        ],
    ];

    private const TEMPLATES = [
        [
            'action' => 'follow_up_after_meeting',
            'name' => 'Follow-up (po spotkaniu)',
            'subject' => 'Follow-up po spotkaniu',
            'body' => <<<'HTML'
<p>Dzień dobry,</p>
<p>wracam po naszym spotkaniu dotyczącym sprawy kredytu.</p>
<p>Jeżeli są Państwo zainteresowani dalszą współpracą, proszę o krótką informację zwrotną. W kolejnym kroku wskażemy dokumenty potrzebne do przyjęcia sprawy i ustalimy dalszy plan działania.</p>
<p>Z poważaniem,<br>Kancelaria Pręda</p>
HTML,
            'sort' => 8,
        ],
    ];

    public function up(): void
    {
        if (Schema::hasTable('template_stages')) {
            foreach (self::STAGES as $stage) {
                $this->ensureStage($stage);
            }
        }

        if (Schema::hasTable('crm_mail_templates')) {
            foreach (self::TEMPLATES as $template) {
                $this->upsertTemplate($template);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('crm_mail_templates')) {
            DB::table('crm_mail_templates')
                ->whereIn('action', array_column(self::TEMPLATES, 'action'))
                ->delete();
        }
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

    /**
     * @param  array{action: string, name: string, subject: string, body: string, sort: int}  $template
     */
    private function upsertTemplate(array $template): void
    {
        $now = now();
        $existingId = DB::table('crm_mail_templates')
            ->where('action', $template['action'])
            ->value('id');

        DB::table('crm_mail_templates')->updateOrInsert(
            ['action' => $template['action']],
            [
                'id' => $existingId ?: (string) Str::uuid(),
                'name' => $template['name'],
                'subject' => $template['subject'],
                'body' => $template['body'],
                'is_active' => true,
                'sort' => $template['sort'],
                'created_at' => $existingId
                    ? DB::table('crm_mail_templates')->where('id', $existingId)->value('created_at')
                    : $now,
                'updated_at' => $now,
            ],
        );
    }
};
