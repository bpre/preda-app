<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const CATEGORY = 'Potencjalna';

    private const PARENT = 'Pozyskanie klienta';

    private const STAGES = [
        'new_contract' => [
            'label' => 'Nowa umowa',
            'aliases' => ['Nowy lead'],
            'sort' => 1,
            'default' => true,
        ],
        'qualification_confirmed' => [
            'label' => 'Wysłano potwierdzenie kwalifikacji sprawy',
            'aliases' => ['Potwierdzono kwalifikację sprawy'],
            'sort' => 2,
        ],
        'qualification_follow_up_sent' => [
            'label' => 'Follow-up (po kwalifikacji)',
            'aliases' => [],
            'sort' => 3,
        ],
        'additional_info_requested' => [
            'label' => 'Wysłano prośbę o dodatkowe informacje',
            'aliases' => ['Wysłano prośbę o dodatkowe dokumenty'],
            'sort' => 4,
        ],
        'additional_info_follow_up_sent' => [
            'label' => 'Follow-up (po prośbie o dodatkowe informacje)',
            'aliases' => ['Follow-up (prośba o informacje)'],
            'sort' => 5,
        ],
        'certificate_request_sent' => [
            'label' => 'Wniosek o wydanie zaświadczenia',
            'aliases' => [],
            'sort' => 6,
        ],
        'certificate_request_follow_up_sent' => [
            'label' => 'Follow-up (po prośbie o zaświadczenie)',
            'aliases' => [],
            'sort' => 7,
        ],
        'analysis_sent' => [
            'label' => 'Przesłano analizę klientowi',
            'aliases' => ['Wysłano analizę umowy'],
            'sort' => 8,
        ],
        'analysis_follow_up_sent' => [
            'label' => 'Follow-up (po wysłaniu analizy)',
            'aliases' => [],
            'sort' => 9,
        ],
        'offer_presented' => [
            'label' => 'Przedstawiono ofertę',
            'aliases' => ['Wysłano ofertę'],
            'sort' => 10,
        ],
        'offer_follow_up_sent' => [
            'label' => 'Follow-up (po wysłaniu oferty, przed spotkaniem)',
            'aliases' => ['Follow-up (po ofercie)'],
            'sort' => 11,
        ],
        'meeting_scheduled' => [
            'label' => 'Umówiono spotkanie',
            'aliases' => [],
            'sort' => 12,
        ],
        'meeting_completed' => [
            'label' => 'Spotkanie z potencjalnym klientem',
            'aliases' => [],
            'sort' => 13,
        ],
        'meeting_follow_up_sent' => [
            'label' => 'Follow-up (po spotkaniu)',
            'aliases' => [],
            'sort' => 14,
        ],
        'post_meeting_benefits_analysis_sent' => [
            'label' => 'Przesłano analizę korzyści po spotkaniu',
            'aliases' => [],
            'sort' => 15,
        ],
        'post_meeting_benefits_follow_up_sent' => [
            'label' => 'Follow-up (po analizie korzyści po spotkaniu)',
            'aliases' => [],
            'sort' => 16,
        ],
        'client_retained_intent_confirmed' => [
            'label' => 'Potwierdzono chęć zlecenia sprawy',
            'aliases' => [],
            'sort' => 17,
        ],
        'final_follow_up_sent' => [
            'label' => 'Ostatni follow-up przed zamknięciem',
            'aliases' => [],
            'sort' => 18,
        ],
        'matter_retained' => [
            'label' => 'Zlecono prowadzenie sprawy',
            'aliases' => [],
            'sort' => 19,
        ],
    ];

    private const MAIL_TEMPLATES = [
        [
            'action' => 'request_certificate',
            'name' => 'Prośba o zaświadczenie',
            'subject' => 'Wniosek o wydanie zaświadczenia',
            'body' => <<<'HTML'
<p>Dzień dobry,</p>
<p>do precyzyjnego określenia możliwych korzyści potrzebne będzie zaświadczenie z banku dotyczące wykonywania umowy kredytu.</p>
<p>W załączeniu przesyłamy wniosek, który można złożyć w banku.</p>
<p>Z poważaniem,<br>Kancelaria Pręda</p>
HTML,
            'sort' => 4,
        ],
        [
            'action' => 'follow_up_after_certificate_request',
            'name' => 'Follow-up (po prośbie o zaświadczenie)',
            'subject' => 'Czy bank wydał już zaświadczenie?',
            'body' => <<<'HTML'
<p>Dzień dobry,</p>
<p>wracam do tematu zaświadczenia z banku. Banki zwykle mają 30 dni na wydanie takiego dokumentu, dlatego po tym czasie warto sprawdzić, czy zaświadczenie jest już gotowe.</p>
<p>Po jego otrzymaniu będziemy mogli dokładniej ocenić potencjalne korzyści z prowadzenia sprawy.</p>
<p>Z poważaniem,<br>Kancelaria Pręda</p>
HTML,
            'sort' => 6,
        ],
        [
            'action' => 'follow_up_after_offer',
            'name' => 'Follow-up (po ofercie)',
            'subject' => 'Czy oferta jest dla Państwa aktualna?',
            'body' => <<<'HTML'
<p>Dzień dobry,</p>
<p>wracam do przesłanej propozycji współpracy.</p>
<p>{{akapit_o_korzysciach}}</p>
<p>Jeżeli chcą Państwo kontynuować temat, proszę o krótką odpowiedź. Wskażemy wtedy dalsze kroki.</p>
<p>Z poważaniem,<br>Kancelaria Pręda</p>
HTML,
            'sort' => 9,
        ],
        [
            'action' => 'send_post_meeting_benefits_analysis',
            'name' => 'Analiza korzyści po spotkaniu',
            'subject' => 'Analiza potencjalnych korzyści po spotkaniu',
            'body' => <<<'HTML'
<p>Dzień dobry,</p>
<p>zgodnie z rozmową przesyłam podsumowanie potencjalnych korzyści związanych z prowadzeniem sprawy.</p>
<p>{{akapit_o_korzysciach}}</p>
<p>Jeżeli po zapoznaniu się z tym podsumowaniem chcą Państwo zlecić prowadzenie sprawy, proszę o krótką odpowiedź.</p>
<p>Z poważaniem,<br>Kancelaria Pręda</p>
HTML,
            'sort' => 10,
        ],
        [
            'action' => 'follow_up_after_post_meeting_benefits_analysis',
            'name' => 'Follow-up (po analizie korzyści po spotkaniu)',
            'subject' => 'Czy chcą Państwo kontynuować sprawę?',
            'body' => <<<'HTML'
<p>Dzień dobry,</p>
<p>wracam do przesłanego po spotkaniu podsumowania potencjalnych korzyści.</p>
<p>{{akapit_o_korzysciach}}</p>
<p>Jeżeli temat jest aktualny, proszę o informację, czy chcą Państwo zlecić prowadzenie sprawy.</p>
<p>Z poważaniem,<br>Kancelaria Pręda</p>
HTML,
            'sort' => 11,
        ],
        [
            'action' => 'final_follow_up_before_closing',
            'name' => 'Ostatni follow-up przed zamknięciem',
            'subject' => 'Czy temat sprawy jest nadal aktualny?',
            'body' => <<<'HTML'
<p>Dzień dobry,</p>
<p>{{kontekst_ostatniego_kontaktu}}</p>
<p>Ponieważ nie otrzymaliśmy odpowiedzi, na ten moment nie będziemy już ponawiać kontaktu w tej sprawie.</p>
<p>Jeżeli temat jest nadal aktualny, proszę po prostu odpisać na tę wiadomość - wrócimy do sprawy.</p>
<p>Z poważaniem,<br>Kancelaria Pręda</p>
HTML,
            'sort' => 12,
        ],
    ];

    private const RULES = [
        [
            'key' => 'review_new_potential_matter',
            'name' => 'Weryfikacja nowej potencjalnej sprawy',
            'trigger_stage_key' => 'new_contract',
            'suggested_action_key' => 'review_new_potential_matter',
            'delay_days' => 0,
            'sort' => 1,
            'blocking_stage_keys' => [
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
            ],
            'reason' => 'Nowa potencjalna sprawa wymaga weryfikacji i wyboru pierwszego działania.',
        ],
        [
            'key' => 'follow_up_after_info_request',
            'name' => 'Follow-up po prośbie o dodatkowe informacje',
            'trigger_stage_key' => 'additional_info_requested',
            'suggested_action_key' => 'follow_up_after_info_request',
            'delay_days' => 7,
            'sort' => 10,
            'blocking_stage_keys' => [
                'additional_info_follow_up_sent',
                'qualification_confirmed',
                'certificate_request_sent',
                'analysis_sent',
                'meeting_scheduled',
                'offer_presented',
                'client_retained_intent_confirmed',
                'matter_retained',
                'final_follow_up_sent',
            ],
            'reason' => 'Klient nie dosłał jeszcze informacji potrzebnych do dalszej oceny.',
        ],
        [
            'key' => 'follow_up_after_certificate_request',
            'name' => 'Follow-up po prośbie o zaświadczenie',
            'trigger_stage_key' => 'certificate_request_sent',
            'suggested_action_key' => 'follow_up_after_certificate_request',
            'delay_days' => 35,
            'sort' => 20,
            'blocking_stage_keys' => [
                'certificate_request_follow_up_sent',
                'analysis_sent',
                'post_meeting_benefits_analysis_sent',
                'client_retained_intent_confirmed',
                'matter_retained',
                'final_follow_up_sent',
            ],
            'reason' => 'Minął czas, w którym bank zwykle powinien wydać zaświadczenie.',
        ],
        [
            'key' => 'follow_up_after_qualification',
            'name' => 'Follow-up po pozytywnej kwalifikacji',
            'trigger_stage_key' => 'qualification_confirmed',
            'suggested_action_key' => 'follow_up_after_qualification',
            'delay_days' => 5,
            'sort' => 30,
            'blocking_stage_keys' => [
                'qualification_follow_up_sent',
                'additional_info_requested',
                'certificate_request_sent',
                'analysis_sent',
                'meeting_scheduled',
                'offer_presented',
                'client_retained_intent_confirmed',
                'matter_retained',
                'final_follow_up_sent',
            ],
            'reason' => 'Po kwalifikacji nie odnotowano kolejnego kroku klienta.',
        ],
        [
            'key' => 'follow_up_after_analysis',
            'name' => 'Follow-up po przesłaniu analizy',
            'trigger_stage_key' => 'analysis_sent',
            'suggested_action_key' => 'follow_up_after_analysis',
            'delay_days' => 5,
            'sort' => 40,
            'blocking_stage_keys' => [
                'analysis_follow_up_sent',
                'offer_presented',
                'meeting_scheduled',
                'meeting_completed',
                'client_retained_intent_confirmed',
                'matter_retained',
                'final_follow_up_sent',
            ],
            'reason' => 'Klient otrzymał analizę, ale nie ma dalszego kroku w sprawie.',
        ],
        [
            'key' => 'follow_up_after_offer',
            'name' => 'Follow-up po przesłaniu oferty',
            'trigger_stage_key' => 'offer_presented',
            'suggested_action_key' => 'follow_up_after_offer',
            'delay_days' => 7,
            'sort' => 50,
            'blocking_stage_keys' => [
                'offer_follow_up_sent',
                'meeting_scheduled',
                'client_retained_intent_confirmed',
                'matter_retained',
                'final_follow_up_sent',
            ],
            'reason' => 'Oferta została przedstawiona, ale nie odnotowano decyzji klienta.',
        ],
        [
            'key' => 'follow_up_after_meeting',
            'name' => 'Follow-up po spotkaniu',
            'trigger_stage_key' => 'meeting_completed',
            'suggested_action_key' => 'follow_up_after_meeting',
            'delay_days' => 3,
            'sort' => 60,
            'blocking_stage_keys' => [
                'meeting_follow_up_sent',
                'certificate_request_sent',
                'post_meeting_benefits_analysis_sent',
                'client_retained_intent_confirmed',
                'matter_retained',
                'final_follow_up_sent',
            ],
            'reason' => 'Po spotkaniu nie odnotowano kolejnej decyzji klienta.',
        ],
        [
            'key' => 'follow_up_after_post_meeting_benefits_analysis',
            'name' => 'Follow-up po analizie korzyści po spotkaniu',
            'trigger_stage_key' => 'post_meeting_benefits_analysis_sent',
            'suggested_action_key' => 'follow_up_after_post_meeting_benefits_analysis',
            'delay_days' => 5,
            'sort' => 70,
            'blocking_stage_keys' => [
                'post_meeting_benefits_follow_up_sent',
                'client_retained_intent_confirmed',
                'matter_retained',
                'final_follow_up_sent',
            ],
            'reason' => 'Klient otrzymał podsumowanie korzyści po spotkaniu, ale nie odnotowano decyzji.',
        ],
        [
            'key' => 'final_after_info_follow_up',
            'name' => 'Ostatni follow-up po przypomnieniu o informacje',
            'trigger_stage_key' => 'additional_info_follow_up_sent',
            'suggested_action_key' => 'final_follow_up_before_closing',
            'delay_days' => 10,
            'sort' => 110,
            'blocking_stage_keys' => ['qualification_confirmed', 'analysis_sent', 'meeting_scheduled', 'offer_presented', 'client_retained_intent_confirmed', 'matter_retained', 'final_follow_up_sent'],
            'reason' => 'Po follow-upie nie odnotowano reakcji klienta.',
        ],
        [
            'key' => 'final_after_certificate_follow_up',
            'name' => 'Ostatni follow-up po przypomnieniu o zaświadczenie',
            'trigger_stage_key' => 'certificate_request_follow_up_sent',
            'suggested_action_key' => 'final_follow_up_before_closing',
            'delay_days' => 14,
            'sort' => 120,
            'blocking_stage_keys' => ['analysis_sent', 'post_meeting_benefits_analysis_sent', 'client_retained_intent_confirmed', 'matter_retained', 'final_follow_up_sent'],
            'reason' => 'Po przypomnieniu o zaświadczenie nie odnotowano dalszej reakcji.',
        ],
        [
            'key' => 'final_after_qualification_follow_up',
            'name' => 'Ostatni follow-up po kwalifikacji',
            'trigger_stage_key' => 'qualification_follow_up_sent',
            'suggested_action_key' => 'final_follow_up_before_closing',
            'delay_days' => 10,
            'sort' => 130,
            'blocking_stage_keys' => ['analysis_sent', 'meeting_scheduled', 'offer_presented', 'client_retained_intent_confirmed', 'matter_retained', 'final_follow_up_sent'],
            'reason' => 'Po follow-upie po kwalifikacji nie odnotowano kolejnego kroku.',
        ],
        [
            'key' => 'final_after_analysis_follow_up',
            'name' => 'Ostatni follow-up po analizie',
            'trigger_stage_key' => 'analysis_follow_up_sent',
            'suggested_action_key' => 'final_follow_up_before_closing',
            'delay_days' => 10,
            'sort' => 140,
            'blocking_stage_keys' => ['offer_presented', 'meeting_scheduled', 'client_retained_intent_confirmed', 'matter_retained', 'final_follow_up_sent'],
            'reason' => 'Po follow-upie po analizie nie odnotowano dalszego kroku.',
        ],
        [
            'key' => 'final_after_offer_follow_up',
            'name' => 'Ostatni follow-up po ofercie',
            'trigger_stage_key' => 'offer_follow_up_sent',
            'suggested_action_key' => 'final_follow_up_before_closing',
            'delay_days' => 10,
            'sort' => 150,
            'blocking_stage_keys' => ['meeting_scheduled', 'client_retained_intent_confirmed', 'matter_retained', 'final_follow_up_sent'],
            'reason' => 'Po follow-upie po ofercie nie odnotowano decyzji klienta.',
        ],
        [
            'key' => 'final_after_meeting_follow_up',
            'name' => 'Ostatni follow-up po spotkaniu',
            'trigger_stage_key' => 'meeting_follow_up_sent',
            'suggested_action_key' => 'final_follow_up_before_closing',
            'delay_days' => 10,
            'sort' => 160,
            'blocking_stage_keys' => ['certificate_request_sent', 'post_meeting_benefits_analysis_sent', 'client_retained_intent_confirmed', 'matter_retained', 'final_follow_up_sent'],
            'reason' => 'Po follow-upie po spotkaniu nie odnotowano decyzji klienta.',
        ],
        [
            'key' => 'final_after_post_meeting_benefits_follow_up',
            'name' => 'Ostatni follow-up po analizie korzyści',
            'trigger_stage_key' => 'post_meeting_benefits_follow_up_sent',
            'suggested_action_key' => 'final_follow_up_before_closing',
            'delay_days' => 10,
            'sort' => 170,
            'blocking_stage_keys' => ['client_retained_intent_confirmed', 'matter_retained', 'final_follow_up_sent'],
            'reason' => 'Po follow-upie po analizie korzyści nie odnotowano decyzji klienta.',
        ],
        [
            'key' => 'archive_after_final_follow_up',
            'name' => 'Sugestia archiwizacji po ostatnim follow-upie',
            'trigger_stage_key' => 'final_follow_up_sent',
            'suggested_action_key' => 'archive_potential_matter',
            'delay_days' => 14,
            'sort' => 200,
            'blocking_stage_keys' => ['client_retained_intent_confirmed', 'matter_retained'],
            'reason' => 'Po ostatnim follow-upie minął skonfigurowany czas bez dalszego kroku.',
        ],
    ];

    private const PERMISSIONS = [
        'view_any_crm_workflow_rule',
        'view_crm_workflow_rule',
        'update_crm_workflow_rule',
        'view_any_crm_workflow_setting',
        'view_crm_workflow_setting',
        'update_crm_workflow_setting',
    ];

    public function up(): void
    {
        $this->addColumns();
        $this->createTables();
        $this->ensureStages();
        $this->ensureMailTemplates();
        $this->ensureRules();
        $this->ensureSettings();
        $this->createPermissions();
    }

    public function down(): void
    {
        $this->deletePermissions();

        Schema::dropIfExists('crm_workflow_settings');
        Schema::dropIfExists('crm_workflow_rules');
        Schema::dropIfExists('crm_client_messages');

        Schema::table('matters', function (Blueprint $table): void {
            $columns = [
                'next_action_generated_at',
                'next_action_reason',
                'next_action_due_at',
                'next_action_key',
                'offer_sent_conditionally',
                'offer_sent_by',
                'offer_sent_at',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('matters', $column)) {
                    if ($column === 'offer_sent_by') {
                        $table->dropConstrainedForeignId($column);
                    } else {
                        $table->dropColumn($column);
                    }
                }
            }
        });

        Schema::table('template_stages', function (Blueprint $table): void {
            if (Schema::hasColumn('template_stages', 'key')) {
                $table->dropIndex('template_stages_category_key_index');
                $table->dropColumn('key');
            }
        });
    }

    private function addColumns(): void
    {
        Schema::table('template_stages', function (Blueprint $table): void {
            if (! Schema::hasColumn('template_stages', 'key')) {
                $table->string('key')->nullable()->after('id');
                $table->index(['category', 'key'], 'template_stages_category_key_index');
            }
        });

        Schema::table('matters', function (Blueprint $table): void {
            if (! Schema::hasColumn('matters', 'offer_sent_at')) {
                $table->timestamp('offer_sent_at')->nullable()->after('overpayment_refund_amount');
            }

            if (! Schema::hasColumn('matters', 'offer_sent_by')) {
                $table->foreignId('offer_sent_by')
                    ->nullable()
                    ->after('offer_sent_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('matters', 'offer_sent_conditionally')) {
                $table->boolean('offer_sent_conditionally')
                    ->default(false)
                    ->after('offer_sent_by');
            }

            if (! Schema::hasColumn('matters', 'next_action_key')) {
                $table->string('next_action_key')->nullable()->after('offer_sent_conditionally');
            }

            if (! Schema::hasColumn('matters', 'next_action_due_at')) {
                $table->date('next_action_due_at')->nullable()->after('next_action_key');
                $table->index('next_action_due_at');
            }

            if (! Schema::hasColumn('matters', 'next_action_reason')) {
                $table->string('next_action_reason')->nullable()->after('next_action_due_at');
            }

            if (! Schema::hasColumn('matters', 'next_action_generated_at')) {
                $table->timestamp('next_action_generated_at')->nullable()->after('next_action_reason');
            }
        });
    }

    private function createTables(): void
    {
        if (! Schema::hasTable('crm_client_messages')) {
            Schema::create('crm_client_messages', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('matter_id')->constrained('matters')->cascadeOnDelete();
                $table->foreignUuid('crm_mail_template_id')->nullable()->constrained('crm_mail_templates')->nullOnDelete();
                $table->string('action');
                $table->string('recipient_name')->nullable();
                $table->string('recipient_email')->nullable();
                $table->string('subject');
                $table->longText('body');
                $table->foreignUuid('target_stage_id')->nullable()->constrained('template_stages')->nullOnDelete();
                $table->boolean('default_offer_attached')->default(false);
                $table->string('default_offer_disk')->nullable();
                $table->string('default_offer_path')->nullable();
                $table->string('default_offer_filename')->nullable();
                $table->json('attachments')->nullable();
                $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();

                $table->index(['matter_id', 'sent_at']);
                $table->index('action');
            });
        }

        if (! Schema::hasTable('crm_workflow_rules')) {
            Schema::create('crm_workflow_rules', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('key')->unique();
                $table->string('name');
                $table->string('trigger_stage_key');
                $table->string('suggested_action_key');
                $table->unsignedSmallInteger('delay_days')->default(7);
                $table->json('blocking_stage_keys')->nullable();
                $table->string('reason')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort')->default(0);
                $table->timestamps();

                $table->index(['is_active', 'sort']);
                $table->index('trigger_stage_key');
                $table->index('suggested_action_key');
            });
        }

        if (! Schema::hasTable('crm_workflow_settings')) {
            Schema::create('crm_workflow_settings', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->default('Ustawienia workflow CRM');
                $table->string('default_offer_disk')->default('local');
                $table->string('default_offer_path')->nullable();
                $table->string('default_offer_original_name')->nullable();
                $table->timestamps();
            });
        }
    }

    private function ensureStages(): void
    {
        foreach (self::STAGES as $key => $definition) {
            $this->ensureStage($key, $definition);
        }

        DB::table('template_stages')
            ->where('category', self::CATEGORY)
            ->where(fn ($query) => $query
                ->where('key', '!=', 'new_contract')
                ->orWhereNull('key'))
            ->update(['is_chf_default' => false]);
    }

    /**
     * @param  array{label: string, aliases: array<int, string>, sort: int, default?: bool}  $definition
     */
    private function ensureStage(string $key, array $definition): void
    {
        $existing = DB::table('template_stages')
            ->where('category', self::CATEGORY)
            ->where('key', $key)
            ->first();

        if (! $existing) {
            $labels = array_merge([$definition['label']], $definition['aliases']);

            $existing = DB::table('template_stages')
                ->where('category', self::CATEGORY)
                ->whereIn('label', $labels)
                ->orderBy('sort')
                ->first();
        }

        $updates = [
            'key' => $key,
            'parent' => self::PARENT,
            'sort' => $definition['sort'],
        ];

        if (Schema::hasColumn('template_stages', 'is_active')) {
            $updates['is_active'] = true;
        }

        if (Schema::hasColumn('template_stages', 'is_lead_default')) {
            $updates['is_lead_default'] = false;
        }

        if (Schema::hasColumn('template_stages', 'is_chf_default')) {
            $updates['is_chf_default'] = (bool) ($definition['default'] ?? false);
        }

        if ($existing) {
            DB::table('template_stages')
                ->where('id', $existing->id)
                ->update($updates);

            return;
        }

        DB::table('template_stages')->insert([
            'id' => (string) Str::uuid(),
            'key' => $key,
            'category' => self::CATEGORY,
            'label' => $definition['label'],
            ...$updates,
        ]);
    }

    private function ensureMailTemplates(): void
    {
        if (! Schema::hasTable('crm_mail_templates')) {
            return;
        }

        foreach (self::MAIL_TEMPLATES as $template) {
            if (DB::table('crm_mail_templates')->where('action', $template['action'])->exists()) {
                continue;
            }

            DB::table('crm_mail_templates')->insert([
                'id' => (string) Str::uuid(),
                'action' => $template['action'],
                'name' => $template['name'],
                'subject' => $template['subject'],
                'body' => $template['body'],
                'is_active' => true,
                'sort' => $template['sort'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function ensureRules(): void
    {
        $now = now();

        foreach (self::RULES as $rule) {
            $existingId = DB::table('crm_workflow_rules')
                ->where('key', $rule['key'])
                ->value('id');

            DB::table('crm_workflow_rules')->updateOrInsert(
                ['key' => $rule['key']],
                [
                    'id' => $existingId ?: (string) Str::uuid(),
                    'name' => $rule['name'],
                    'trigger_stage_key' => $rule['trigger_stage_key'],
                    'suggested_action_key' => $rule['suggested_action_key'],
                    'delay_days' => $rule['delay_days'],
                    'blocking_stage_keys' => json_encode($rule['blocking_stage_keys']),
                    'reason' => $rule['reason'],
                    'is_active' => true,
                    'sort' => $rule['sort'],
                    'created_at' => $existingId
                        ? DB::table('crm_workflow_rules')->where('id', $existingId)->value('created_at')
                        : $now,
                    'updated_at' => $now,
                ],
            );
        }
    }

    private function ensureSettings(): void
    {
        if (! DB::table('crm_workflow_settings')->where('id', 1)->exists()) {
            DB::table('crm_workflow_settings')->insert([
                'id' => 1,
                'name' => 'Ustawienia workflow CRM',
                'default_offer_disk' => 'local',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function createPermissions(): void
    {
        $permissionsTable = config('permission.table_names.permissions', 'permissions');
        $rolesTable = config('permission.table_names.roles', 'roles');
        $roleHasPermissionsTable = config('permission.table_names.role_has_permissions', 'role_has_permissions');

        if (! Schema::hasTable($permissionsTable) || ! Schema::hasTable($rolesTable) || ! Schema::hasTable($roleHasPermissionsTable)) {
            return;
        }

        $now = now();

        DB::table($permissionsTable)->insertOrIgnore(array_map(
            fn (string $permissionName): array => [
                'name' => $permissionName,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            self::PERMISSIONS,
        ));

        $superAdminRoleId = DB::table($rolesTable)
            ->where('name', config('filament-shield.super_admin.name', 'super_admin'))
            ->where('guard_name', 'web')
            ->value('id');

        if (! $superAdminRoleId) {
            return;
        }

        $permissionIds = DB::table($permissionsTable)
            ->whereIn('name', self::PERMISSIONS)
            ->where('guard_name', 'web')
            ->pluck('id')
            ->all();

        DB::table($roleHasPermissionsTable)->insertOrIgnore(array_map(
            fn (int $permissionId): array => [
                'permission_id' => $permissionId,
                'role_id' => $superAdminRoleId,
            ],
            $permissionIds,
        ));

        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

    private function deletePermissions(): void
    {
        $permissionsTable = config('permission.table_names.permissions', 'permissions');
        $roleHasPermissionsTable = config('permission.table_names.role_has_permissions', 'role_has_permissions');

        if (! Schema::hasTable($permissionsTable) || ! Schema::hasTable($roleHasPermissionsTable)) {
            return;
        }

        $permissionIds = DB::table($permissionsTable)
            ->whereIn('name', self::PERMISSIONS)
            ->where('guard_name', 'web')
            ->pluck('id')
            ->all();

        DB::table($roleHasPermissionsTable)
            ->whereIn('permission_id', $permissionIds)
            ->delete();

        DB::table($permissionsTable)
            ->whereIn('id', $permissionIds)
            ->delete();

        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }
};
