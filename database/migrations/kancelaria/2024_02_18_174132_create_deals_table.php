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
        Schema::create('deals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('old_id')->nullable();
            $table->string('label');
            $table->date('date');
            $table->decimal('entry_fee', 8,2)->nullable();
            $table->decimal('stage_one_fee', 8,2)->nullable();
            $table->decimal('stage_two_fee', 8,2)->nullable();
            $table->decimal('re_recogniction_fee', 8,2)->nullable();
            $table->decimal('supreme_court_fee', 8,2)->nullable();
            $table->decimal('bank_lawsuit_fee', 8,2)->nullable();
            $table->decimal('hearing_fee', 8,2)->nullable();
            $table->decimal('hearing_online_fee', 8,2)->nullable();
            $table->boolean('is_bonus')->default(false);
            $table->integer('bonus_percent')->nullable();
            $table->decimal('bonus_minimum', 8,2)->nullable();
            $table->decimal('bonus_fee', 8,2)->nullable();
            $table->integer('installments')->default(4);
            $table->date('first_installment_date')->nullable();
            $table->foreignUuid('matter_id')->nullable()->references('id')->on('matters');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
