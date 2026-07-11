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
        Schema::table('website_offers', function (Blueprint $table) {

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

            $table->timestamp('offer_confirmed_at')->nullable();
            $table->timestamp('offer_sent_at')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
