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
        Schema::table('contact_letter', function (Blueprint $table) {
            $table->string('delivery_type')->nullable();
            $table->foreignUuid('neostamp_id')->nullable()->references('id')->on('neostamps');
            // $table->foreignUuid('letter_id')->references('id')->on('letters');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contact_letter', function (Blueprint $table) {
            //
        });
    }
};
