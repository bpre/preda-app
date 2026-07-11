<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'name_genitive')) {
                $table->string('name_genitive')->nullable()->after('name');
            }

            if (! Schema::hasColumn('users', 'signature_title')) {
                $table->string('signature_title')->nullable()->after('name_genitive');
            }

            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }

            if (! Schema::hasColumn('users', 'filament_layout_preferences')) {
                $table->json('filament_layout_preferences')->nullable()->after('remember_token');
            }
        });

        DB::table('users')
            ->whereNull('signature_title')
            ->where('is_lawyer', true)
            ->update([
                'signature_title' => 'Adwokat',
            ]);

        DB::table('users')
            ->whereNull('signature_title')
            ->where('is_employee', true)
            ->update([
                'signature_title' => 'Pracownik kancelarii',
            ]);

        if (Schema::hasTable('letter_notifications') && ! Schema::hasColumn('letter_notifications', 'prepared_by')) {
            Schema::table('letter_notifications', function (Blueprint $table): void {
                $table->foreignId('prepared_by')
                    ->nullable()
                    ->after('message')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('letter_notifications') && Schema::hasColumn('letter_notifications', 'prepared_by')) {
            Schema::table('letter_notifications', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('prepared_by');
            });
        }

        Schema::table('users', function (Blueprint $table): void {
            foreach (['filament_layout_preferences', 'phone', 'signature_title', 'name_genitive'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
