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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('excerpt');
            $table->text('content');
            $table->date('date');
            $table->string('slug');
            $table->string('metatitle');
            $table->text('metadescription');
            $table->boolean('is_published')->default(false);
            $table->date('reviewed_at')->nullable();
            $table->date('modified_at')->nullable();
            $table->string('category')->default('blog');
            $table->unsignedBigInteger('author_id')->nullable();
            $table->text('alternative_slugs')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
