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
        Schema::create('credits', function (Blueprint $table) {

            $table->uuid('id')->primary();
            $table->string('old_id')->nullable();
            $table->foreignUuid('former_bank')->nullable()->references('id')->on('contacts');
            $table->foreignUuid('current_bank')->nullable()->references('id')->on('contacts');
            $table->string('number')->nullable();
            $table->date('date')->nullable();
            $table->json('details')->nullable();
            $table->foreignUuid('matter_id')->nullable()->references('id')->on('matters');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credits');
    }
};
