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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();

            $table->foreignUuid('matter_id')->references('id')->on('matters');

            $table->string('name');
            $table->string('bank');
            $table->string('year');
            $table->integer('amount');
            $table->string('amount_orig');
            $table->string('currency');
            $table->decimal('rate', 12, 4);

            $table->string('sex', 6)->nullable();

            $table->integer('start_wstepna')->nullable();
            $table->integer('start_premia')->nullable();
            $table->integer('start_procent_limit')->nullable();
            $table->integer('start_rozprawa')->nullable();
            $table->integer('start_razem_max')->nullable();

            $table->integer('max_wstepna')->nullable();
            $table->integer('max_druga_instancja')->nullable();
            $table->integer('max_rozprawa')->nullable();
            $table->integer('max_rozprawy_limit')->nullable();
            $table->integer('max_razem_max')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_offers');
    }
};
