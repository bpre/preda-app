<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('crm_mail_templates')) {
            return;
        }

        DB::table('crm_mail_templates')
            ->where('body', 'like', '%{{powitanie}}%')
            ->update([
                'body' => DB::raw("REPLACE(body, '{{powitanie}}', 'Dzień dobry,')"),
                'updated_at' => now(),
            ]);

        DB::table('crm_mail_templates')
            ->where('subject', 'like', '%{{powitanie}}%')
            ->update([
                'subject' => DB::raw("REPLACE(subject, '{{powitanie}}', 'Dzień dobry,')"),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        //
    }
};
