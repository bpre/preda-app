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
        Schema::table('website_sentences', function (Blueprint $table) {
            $table->integer('credit_payoff')->nullable();
            $table->integer('credit_profit')->nullable();
            $table->string('currency')->default('CHF');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
