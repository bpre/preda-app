<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CATEGORY = 'Potencjalna';

    private const DEFAULT_PREFERRED_ACTIONS = [
        'new_contract' => 'send_contract_analysis',
        'qualification_confirmed' => 'follow_up_after_qualification',
        'qualification_follow_up_sent' => 'final_follow_up_before_closing',
        'additional_info_requested' => 'follow_up_after_info_request',
        'additional_info_follow_up_sent' => 'final_follow_up_before_closing',
        'certificate_request_sent' => 'follow_up_after_certificate_request',
        'certificate_request_follow_up_sent' => 'final_follow_up_before_closing',
        'analysis_sent' => 'follow_up_after_analysis',
        'analysis_follow_up_sent' => 'final_follow_up_before_closing',
        'offer_presented' => 'follow_up_after_offer',
        'offer_follow_up_sent' => 'final_follow_up_before_closing',
        'meeting_completed' => 'send_post_meeting_benefits_analysis',
        'meeting_follow_up_sent' => 'final_follow_up_before_closing',
        'post_meeting_benefits_analysis_sent' => 'follow_up_after_post_meeting_benefits_analysis',
        'post_meeting_benefits_follow_up_sent' => 'final_follow_up_before_closing',
    ];

    public function up(): void
    {
        Schema::table('template_stages', function (Blueprint $table): void {
            if (! Schema::hasColumn('template_stages', 'preferred_action_key')) {
                $table->string('preferred_action_key')
                    ->nullable()
                    ->after('key')
                    ->index();
            }
        });

        foreach (self::DEFAULT_PREFERRED_ACTIONS as $stageKey => $preferredActionKey) {
            DB::table('template_stages')
                ->where('category', self::CATEGORY)
                ->where('key', $stageKey)
                ->update(['preferred_action_key' => $preferredActionKey]);
        }
    }

    public function down(): void
    {
        Schema::table('template_stages', function (Blueprint $table): void {
            if (Schema::hasColumn('template_stages', 'preferred_action_key')) {
                $table->dropIndex(['preferred_action_key']);
                $table->dropColumn('preferred_action_key');
            }
        });
    }
};
