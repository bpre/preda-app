<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        if (Schema::hasTable('posts')) {
            DB::table('posts')
                ->whereNotNull('author_id')
                ->whereNotIn('author_id', DB::table('users')->select('id'))
                ->update(['author_id' => null]);

            Schema::table('posts', function (Blueprint $table) {
                $table->foreign('author_id')->references('id')->on('users')->nullOnDelete();
            });
        }

        if (Schema::hasTable('offices')) {
            DB::table('offices')
                ->whereNotNull('director_id')
                ->whereNotIn('director_id', DB::table('users')->select('id'))
                ->update(['director_id' => null]);

            Schema::table('offices', function (Blueprint $table) {
                $table->foreign('director_id')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('posts')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->dropForeign(['author_id']);
            });
        }

        if (Schema::hasTable('offices')) {
            Schema::table('offices', function (Blueprint $table) {
                $table->dropForeign(['director_id']);
            });
        }
    }
};
