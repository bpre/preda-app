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
        Schema::create('docs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('label');
            $table->foreignId('author_id')->references('id')->on('users');
            $table->foreignUuid('recipient_id')->nullable()->references('id')->on('contacts');
            $table->date('date')->nullable();
            $table->foreignUuid('matter_id')->nullable()->references('id')->on('matters');
            $table->foreignUuid('credit_id')->nullable()->references('id')->on('credits');
            $table->json('body')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('docs');
    }
};
