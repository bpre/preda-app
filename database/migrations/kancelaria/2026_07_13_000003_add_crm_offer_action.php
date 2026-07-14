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
            'label' => 'Follow-up (po wysłaniu analizy)',
            'sort' => 7,
        ],
        [
            'label' => 'Wysłano ofertę',
            'sort' => 8,
        ],
    ];

    private const TEMPLATES = [
        [
            'action' => 'send_offer',
            'name' => 'Oferta',
            'subject' => 'Oferta współpracy',
            'body' => <<<'HTML'
<p>Dzień dobry,</p>
<p>przesyłam propozycję dalszej współpracy w sprawie dotyczącej umowy kredytu.</p>
<p>Przed przyjęciem sprawy do prowadzenia musimy zapoznać się z dokumentami oraz porozmawiać o szczegółach sytuacji.</p>
<p>Jeżeli oferta jest dla Państwa interesująca, proszę o krótką informację zwrotną. Ustalimy wtedy dogodny termin rozmowy i zakres dokumentów potrzebnych do dalszej oceny.</p>
<p>Z poważaniem,<br>Kancelaria Pręda</p>
HTML,
            'sort' => 7,
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
