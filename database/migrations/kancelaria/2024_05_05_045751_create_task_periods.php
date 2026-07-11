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
        // Schema::create('task_periods', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignUuid('task_id')->references('id')->on('tasks');
        //     $table->datetime('start');
        //     $table->datetime('end')->nullable();
        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_periods');
    }
};
