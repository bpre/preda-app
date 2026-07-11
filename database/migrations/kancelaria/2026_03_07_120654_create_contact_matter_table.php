<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_matter', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('matter_id');
            $table->uuid('contact_id');
            $table->boolean('receives_notifications')->default(false);
            $table->timestamps();

            $table->unique(['matter_id', 'contact_id']);

            $table->foreign('matter_id')
                ->references('id')
                ->on('matters')
                ->cascadeOnDelete();

            $table->foreign('contact_id')
                ->references('id')
                ->on('contacts')
                ->cascadeOnDelete();

            $table->index('receives_notifications');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_matter');
    }
};
