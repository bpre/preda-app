<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('letter_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('letter_id');
            $table->uuid('contact_id');

            $table->string('status')->default('pending');

            $table->string('recipient_email')->nullable();
            $table->string('subject')->nullable();
            $table->longText('message')->nullable();

            $table->boolean('with_attachments')->default(false);

            $table->timestamp('ignored_at')->nullable();
            $table->unsignedBigInteger('ignored_by')->nullable();

            $table->timestamp('sent_at')->nullable();
            $table->unsignedBigInteger('sent_by')->nullable();

            $table->text('error_message')->nullable();

            $table->timestamps();

            $table->unique(['letter_id', 'contact_id']);

            $table->foreign('letter_id')
                ->references('id')
                ->on('letters')
                ->cascadeOnDelete();

            $table->foreign('contact_id')
                ->references('id')
                ->on('contacts')
                ->cascadeOnDelete();

            $table->foreign('ignored_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('sent_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index('status');
            $table->index('recipient_email');
            $table->index('with_attachments');
            $table->index('ignored_at');
            $table->index('sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letter_notifications');
    }
};
