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
        Schema::create('website_sentences', function (Blueprint $table) {
            $table->id();
            $table->string('sign')->nullable();
            $table->date('lawsuit_date')->nullable();
            $table->date('appeal_date')->nullable();
            $table->date('sentence_date')->nullable();
            $table->string('instance')->nullable();
            $table->foreignId('parent_id')->nullable();
            $table->foreignId('court_id')->nullable();
            $table->foreignId('judge_id')->nullable();
            $table->foreignId('bank_id')->nullable();
            $table->foreignId('bank_previously_id')->nullable();
            $table->string('credit_year');
            $table->string('credit_name');
            $table->string('wps');
            $table->string('hearings');
            $table->string('result');
            $table->string('claim');
            $table->string('lawyer');
            $table->string('label');
            $table->text('excerpt');
            $table->text('content');
            $table->string('slug');
            $table->string('metatitle');
            $table->text('metadescription');
            $table->boolean('is_published')->default(false);
            $table->json('files')->nullable();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('website_sentences');
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
        Schema::dropIfExists('website_sentences');
    }
};
