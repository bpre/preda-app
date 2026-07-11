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
       Schema::create('website_page_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('url')->index();
            $table->string('title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('h1')->nullable();
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('website_page_snapshots');
    }
};
