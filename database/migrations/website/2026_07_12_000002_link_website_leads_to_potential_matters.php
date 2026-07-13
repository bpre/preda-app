<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('website_leads', function (Blueprint $table): void {
            $table->foreignUuid('potential_matter_id')
                ->nullable()
                ->after('id')
                ->constrained('matters')
                ->nullOnDelete();

            $table->timestamp('potential_matter_created_at')
                ->nullable()
                ->after('potential_matter_id');

            $table->foreignId('potential_matter_created_by')
                ->nullable()
                ->after('potential_matter_created_at')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('website_leads', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('potential_matter_created_by');
            $table->dropForeign(['potential_matter_id']);
            $table->dropColumn('potential_matter_id');
            $table->dropColumn('potential_matter_created_at');
        });
    }
};
