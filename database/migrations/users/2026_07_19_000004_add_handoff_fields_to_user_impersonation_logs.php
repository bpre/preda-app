<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_impersonation_logs')) {
            return;
        }

        Schema::table('user_impersonation_logs', function (Blueprint $table): void {
            if (! Schema::hasColumn('user_impersonation_logs', 'return_url')) {
                $table->text('return_url')->nullable();
            }

            if (! Schema::hasColumn('user_impersonation_logs', 'handoff_token_hash')) {
                $table->string('handoff_token_hash', 64)->nullable()->unique();
            }

            if (! Schema::hasColumn('user_impersonation_logs', 'handoff_expires_at')) {
                $table->timestamp('handoff_expires_at')->nullable();
            }

            if (! Schema::hasColumn('user_impersonation_logs', 'handoff_consumed_at')) {
                $table->timestamp('handoff_consumed_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('user_impersonation_logs')) {
            return;
        }

        Schema::table('user_impersonation_logs', function (Blueprint $table): void {
            foreach (['return_url', 'handoff_token_hash', 'handoff_expires_at', 'handoff_consumed_at'] as $column) {
                if (Schema::hasColumn('user_impersonation_logs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
