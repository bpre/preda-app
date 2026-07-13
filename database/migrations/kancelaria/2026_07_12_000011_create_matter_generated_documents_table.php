<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matter_generated_documents', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('matter_id')->constrained('matters')->cascadeOnDelete();
            $table->foreignUuid('credit_id')->nullable()->constrained('credits')->nullOnDelete();
            $table->string('type');
            $table->string('filename');
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('mime_type')->default('application/pdf');
            $table->unsignedBigInteger('size')->nullable();
            $table->boolean('attach_to_client_mail')->default(false);
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->index(['matter_id', 'generated_at']);
            $table->index(['credit_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matter_generated_documents');
    }
};
