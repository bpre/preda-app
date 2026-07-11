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
        Schema::create('website_pipedrives', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('matter_id')->nullable();
            $table->string('gdrive')->nullable();
            $table->string('name')->nullable();
            $table->string('sex')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('city')->nullable();
            $table->string('bank')->nullable();
            $table->string('banku')->nullable();
            $table->string('year')->nullable();
            $table->string('amount')->nullable();
            $table->string('currency')->nullable();
            $table->string('stage')->nullable();
            $table->date('first')->nullable();
            $table->datetime('reviewed')->nullable();
            $table->string('review_status')->nullable();
            $table->datetime('remove_request')->nullable();
            $table->datetime('offer_request')->nullable();
            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('website_pipedrives');
    }
};
