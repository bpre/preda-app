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
        Schema::create('lawsuits', function (Blueprint $table) {
            $table->string('old_id')->nullable();
            $table->uuid('id')->primary();
            $table->string('instance');
            $table->string('signature');
            $table->foreignUuid('matter_id')->references('id')->on('matters');
            $table->foreignUuid('court_id')->references('id')->on('contacts');
            $table->foreignUuid('judge_id')->nullable()->references('id')->on('contacts');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lawsuits');
    }
};
