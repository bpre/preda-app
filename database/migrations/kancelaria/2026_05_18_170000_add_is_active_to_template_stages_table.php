<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('template_stages', function (Blueprint $table): void {
            if (! Schema::hasColumn('template_stages', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_chf_default');
            }
        });
    }

    public function down(): void
    {
        Schema::table('template_stages', function (Blueprint $table): void {
            if (Schema::hasColumn('template_stages', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
