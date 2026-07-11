<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('letter_notification_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('name');
            $table->string('letter_type', 10); // in / out

            $table->string('subject');
            $table->longText('message');

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);

            $table->timestamps();

            $table->index('letter_type');
            $table->index('is_active');
            $table->index('sort');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letter_notification_templates');
    }
};
