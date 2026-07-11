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
        Schema::create('website_credits', function (Blueprint $table) {
            $table->id();
            $table->string('credit_name');
            $table->string('credit_year');
            $table->string('credit_type');
            $table->string('credit_currency')->default('CHF');
            $table->foreignId('bank_id')->references('id')->on('website_banks');
            $table->boolean('is_published')->default(false);
            $table->json('clauses')->nullable();
            $table->integer('sort')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('website_credits');
    }
};
