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
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('label');
            $table->boolean('is_private')->default(false);
            $table->foreignId('created_by')->references('id')->on('users');
            $table->foreignId('assigned_to')->nullable()->references('id')->on('users');
            $table->foreignUuid('matter_id')->nullable()->references('id')->on('matters');
            // $table->integer('status')->default(1);
            $table->integer('priority')->default(1);
            // $table->datetime('start')->nullable();
            // $table->datetime('end')->nullable();
            $table->date('not_show_before')->nullable();
            $table->datetime('done_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
