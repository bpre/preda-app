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
        Schema::dropIfExists('stage_placeholders_archive');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('stage_placeholders_archive', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('original_stage_id')->unique();
            $table->string('label');
            $table->text('description')->nullable();
            $table->text('files')->nullable();
            $table->text('files_names')->nullable();
            $table->integer('sort')->default(999);
            $table->string('parent');
            $table->date('date')->nullable();
            $table->uuid('matter_id');
            $table->boolean('is_current')->default(false);
            $table->uuid('stage_id')->nullable();
            $table->timestamp('original_created_at')->nullable();
            $table->timestamp('original_updated_at')->nullable();
            $table->timestamp('archived_at')->useCurrent();
        });
    }
};
