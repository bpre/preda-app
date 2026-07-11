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
        Schema::create('neostamps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('label');
            $table->string('type');
            $table->foreignId('contact_letter_id')->nullable()->references('id')->on('contact_letter');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('neostamps');
    }
};
