<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mailgun_events', function (Blueprint $table): void {
            if (! Schema::hasColumn('mailgun_events', 'letter_notification_id')) {
                $table->uuid('letter_notification_id')->nullable()->after('website_lead_id')->index();
            }

            if (! Schema::hasColumn('mailgun_events', 'letter_id')) {
                $table->uuid('letter_id')->nullable()->after('letter_notification_id')->index();
            }

            if (! Schema::hasColumn('mailgun_events', 'contact_id')) {
                $table->uuid('contact_id')->nullable()->after('letter_id')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('mailgun_events', function (Blueprint $table): void {
            if (Schema::hasColumn('mailgun_events', 'contact_id')) {
                $table->dropColumn('contact_id');
            }

            if (Schema::hasColumn('mailgun_events', 'letter_id')) {
                $table->dropColumn('letter_id');
            }

            if (Schema::hasColumn('mailgun_events', 'letter_notification_id')) {
                $table->dropColumn('letter_notification_id');
            }
        });
    }
};
