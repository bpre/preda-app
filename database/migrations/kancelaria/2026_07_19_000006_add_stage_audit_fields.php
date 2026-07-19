<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matters', function (Blueprint $table): void {
            if (! Schema::hasColumn('matters', 'current_stage_set_by')) {
                $table->foreignId('current_stage_set_by')
                    ->nullable()
                    ->after('current_template_stage_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('matters', 'current_stage_set_at')) {
                $table->timestamp('current_stage_set_at')
                    ->nullable()
                    ->after('current_stage_set_by');
            }
        });

        Schema::table('stages', function (Blueprint $table): void {
            if (! Schema::hasColumn('stages', 'current_stage_set_by')) {
                $table->foreignId('current_stage_set_by')
                    ->nullable()
                    ->after('is_current')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('stages', 'current_stage_set_at')) {
                $table->timestamp('current_stage_set_at')
                    ->nullable()
                    ->after('current_stage_set_by');
            }

            if (! Schema::hasColumn('stages', 'last_edited_by')) {
                $table->foreignId('last_edited_by')
                    ->nullable()
                    ->after('current_stage_set_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('stages', 'last_edited_at')) {
                $table->timestamp('last_edited_at')
                    ->nullable()
                    ->after('last_edited_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stages', function (Blueprint $table): void {
            foreach (['last_edited_at', 'current_stage_set_at'] as $column) {
                if (Schema::hasColumn('stages', $column)) {
                    $table->dropColumn($column);
                }
            }

            foreach (['last_edited_by', 'current_stage_set_by'] as $column) {
                if (Schema::hasColumn('stages', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }
        });

        Schema::table('matters', function (Blueprint $table): void {
            if (Schema::hasColumn('matters', 'current_stage_set_at')) {
                $table->dropColumn('current_stage_set_at');
            }

            if (Schema::hasColumn('matters', 'current_stage_set_by')) {
                $table->dropConstrainedForeignId('current_stage_set_by');
            }
        });
    }
};
