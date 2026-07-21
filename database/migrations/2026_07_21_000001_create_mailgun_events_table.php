<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mailgun_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('mailgun_event_id')->nullable()->unique();
            $table->char('payload_hash', 64)->unique();
            $table->string('event')->index();
            $table->string('domain')->nullable()->index();
            $table->string('recipient_email')->nullable()->index();
            $table->string('sender_email')->nullable()->index();
            $table->string('subject')->nullable();
            $table->string('message_id')->nullable()->index();
            $table->string('mailgun_message_id')->nullable()->index();
            $table->string('url')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('client_info')->nullable();
            $table->json('tags')->nullable();
            $table->json('user_variables')->nullable();
            $table->uuid('crm_client_message_id')->nullable()->index();
            $table->uuid('matter_id')->nullable()->index();
            $table->unsignedBigInteger('website_lead_id')->nullable()->index();
            $table->json('payload');
            $table->timestamp('occurred_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mailgun_events');
    }
};
