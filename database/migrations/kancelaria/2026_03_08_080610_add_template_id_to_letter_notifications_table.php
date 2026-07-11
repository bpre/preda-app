<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('letter_notifications', function (Blueprint $table) {
            $table->uuid('template_id')->nullable()->after('contact_id');

            $table->foreign('template_id')
                ->references('id')
                ->on('letter_notification_templates')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('letter_notifications', function (Blueprint $table) {
            $table->dropForeign(['template_id']);
            $table->dropColumn('template_id');
        });
    }
};
