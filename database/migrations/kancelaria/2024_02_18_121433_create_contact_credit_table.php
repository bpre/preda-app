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
        Schema::create('contact_credit', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('contact_id')->references('id')->on('contacts');
            $table->foreignUuid('credit_id')->references('id')->on('credits');
            $table->integer('sort')->default('999');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_credit');
    }
};
