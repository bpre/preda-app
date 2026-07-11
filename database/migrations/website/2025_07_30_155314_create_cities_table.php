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
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('city');
            $table->string('form_a');
            $table->string('form_e');
            $table->string('form_w');
            $table->string('form_z');
            $table->string('km');
            $table->string('so');
            $table->string('sa');
            $table->string('province');
            $table->string('slug');
            $table->integer('sort')->default(0);
            $table->boolean('is_published')->default(false);
            $table->boolean('show_in_footer')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
