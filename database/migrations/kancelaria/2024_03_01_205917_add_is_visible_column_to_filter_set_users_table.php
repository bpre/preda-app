<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('filament_filter_set_user', function (Blueprint $table) {
            $table->boolean('is_visible')->default(true);
        });

    }

    public function down(): void
    {
        Schema::table('filament_filter_set_user', function (Blueprint $table) {
            $table->dropColumn('is_visible');
        });
    }
};
