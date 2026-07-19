<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('website_leads', function (Blueprint $table): void {
            if (! Schema::hasColumn('website_leads', 'lead_type')) {
                $table->string('lead_type')->default('form')->after('status');
                $table->index('lead_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('website_leads', function (Blueprint $table): void {
            if (Schema::hasColumn('website_leads', 'lead_type')) {
                $table->dropIndex(['lead_type']);
                $table->dropColumn('lead_type');
            }
        });
    }
};
