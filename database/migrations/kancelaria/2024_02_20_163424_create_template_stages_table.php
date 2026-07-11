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
        Schema::create('template_stages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('label');
            $table->string('parent');
            $table->integer('sort')->default('999');
            $table->boolean('is_lead_default')->default(false);
            $table->boolean('is_chf_default')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_stages');
    }
};
