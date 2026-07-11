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
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('old_id')->nullable();
            $table->string('label');
            $table->date('deadline')->nullable();
            $table->date('date')->nullable();
            $table->decimal('amount', 8,2);
            $table->boolean('is_paid')->default(false);
            $table->foreignUuid('matter_id')->references('id')->on('matters');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
