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
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('bank');
            $table->string('label');
            $table->string('form_a');
            $table->string('form_e');
            $table->string('form_w');
            $table->string('form_z');
            $table->foreignId('successor_id')->nullable();
            $table->string('slug');
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            $table->foreign('successor_id')->references('id')->on('banks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};
