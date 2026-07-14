<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const TEMPLATES = [
        [
            'action' => 'follow_up_after_qualification',
            'name' => 'Follow-up (po kwalifikacji)',
            'subject' => 'Czy chce Pan/Pani kontynuować sprawę?',
            'body' => <<<'HTML'
<p>Dzień dobry,</p>
<p>wracam do wiadomości dotyczącej pozytywnej kwalifikacji sprawy.</p>
<p>Jeżeli {{pani_pana}} decyzja jest aktualna, proszę o krótką informację zwrotną, a wskażemy dalsze kroki.</p>
<p>Z poważaniem,<br>Kancelaria Pręda</p>
HTML,
            'sort' => 4,
        ],
        [
            'action' => 'follow_up_after_info_request',
            'name' => 'Follow-up (prośba o informacje)',
            'subject' => 'Przypomnienie o prośbie o dodatkowe informacje',
            'body' => <<<'HTML'
<p>Dzień dobry,</p>
<p>wracam do naszej prośby o dodatkowe informacje potrzebne do oceny sprawy.</p>
<p>Po ich otrzymaniu będziemy mogli dokończyć analizę i wskazać możliwe dalsze działania.</p>
<p>Z poważaniem,<br>Kancelaria Pręda</p>
HTML,
            'sort' => 5,
        ],
        [
            'action' => 'follow_up_after_analysis',
            'name' => 'Follow-up (po wysłaniu analizy)',
            'subject' => 'Czy udało się zapoznać z analizą?',
            'body' => <<<'HTML'
<p>Dzień dobry,</p>
<p>wracam do przesłanej analizy umowy kredytu.</p>
<p>Jeżeli pojawiły się pytania albo chcą Państwo omówić możliwe dalsze kroki, pozostaję do dyspozycji.</p>
<p>Z poważaniem,<br>Kancelaria Pręda</p>
HTML,
            'sort' => 6,
        ],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('crm_mail_templates')) {
            return;
        }

        foreach (self::TEMPLATES as $template) {
            $this->upsertTemplate($template);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('crm_mail_templates')) {
            return;
        }

        DB::table('crm_mail_templates')
            ->whereIn('action', array_column(self::TEMPLATES, 'action'))
            ->delete();
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
