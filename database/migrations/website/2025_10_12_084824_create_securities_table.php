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
        Schema::create('website_securities', function (Blueprint $table) {
            $table->id();
            $table->string('sign')->nullable();
            $table->date('sentence_date')->nullable();
            $table->foreignId('court_id')->nullable();
            $table->foreignId('judge_id')->nullable();
            $table->foreignId('bank_id')->nullable();
            $table->foreignId('bank_previously_id')->nullable();
            $table->string('credit_year')->nullable();
            $table->string('credit_name')->nullable();
            $table->boolean('is_published')->default(false);
            $table->json('files')->nullable();
            $table->timestamps();

            $table->foreign('bank_id')->references('id')->on('website_banks');
            $table->foreign('bank_previously_id')->references('id')->on('website_banks');
            $table->foreign('court_id')->references('id')->on('website_contacts');
            $table->foreign('judge_id')->references('id')->on('website_contacts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('website_securities');
    }
};
