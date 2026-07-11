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
        Schema::create('matters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('old_id')->nullable();
            $table->string('label');
            $table->string('status')->default('otwarta');
            $table->string('category')->nullable();
            $table->string('gdrive')->nullable();
            $table->foreignId('lawyer_id')->references('id')->on('users');
            $table->foreignUuid('opponent_lawyer_id')->nullable()->references('id')->on('contacts');
            $table->json('userinfo')->nullable();
            $table->boolean('is_matter')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->boolean('is_chf')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matters');
    }
};
