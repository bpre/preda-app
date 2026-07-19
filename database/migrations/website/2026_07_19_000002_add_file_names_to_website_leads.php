<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('website_leads', function (Blueprint $table): void {
            $table->json('files_names')->nullable()->after('files');
        });
    }

    public function down(): void
    {
        Schema::table('website_leads', function (Blueprint $table): void {
            $table->dropColumn('files_names');
        });
    }
};
