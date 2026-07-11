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
        Schema::create('contacts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('old_id')->nullable();
            $table->string('type');
            $table->string('category')->default('inny');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('label')->nullable();
            $table->string('sort_name')->nullable();
            $table->string('organization')->nullable();
            $table->string('organization_short')->nullable();
            $table->string('pesel',11)->nullable();
            $table->string('status', 8)->default('aktywny');
            $table->string('sex',1)->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('city')->nullable();
            $table->string('krs')->nullable();
            $table->string('profession')->nullable();
            $table->foreignUuid('lawfirm_id')->nullable()->references('id')->on('contacts');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
