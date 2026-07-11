<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('letters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('old_id')->nullable();
            $table->string('label');
            $table->string('description')->nullable();
            $table->date('date');
            $table->string('type')->default('in');
            $table->text('files')->nullable();
            $table->text('files_names')->nullable();
            $table->foreignUuid('matter_id')->nullable()->references('id')->on('matters');
            $table->foreignUuid('sender_id')->nullable()->references('id')->on('contacts');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('letters');
    }
};
