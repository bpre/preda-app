<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('page_snapshots', function (Blueprint $table) {
            $table->string('h2')->nullable()->after('h1');

            $table->unsignedInteger('title_length')->nullable()->after('title');
            $table->unsignedInteger('meta_description_length')->nullable()->after('meta_description');
            $table->unsignedInteger('h1_length')->nullable()->after('h1');
            $table->unsignedInteger('h2_length')->nullable()->after('h2');

            $table->boolean('is_title_unique')->nullable()->after('title_length');
            $table->boolean('is_h1_unique')->nullable()->after('h1_length');

            $table->string('category')->nullable()->after('url')->index();
        });
    }

    public function down(): void {
        Schema::table('page_snapshots', function (Blueprint $table) {
            $table->dropColumn([
                'h2',
                'title_length','meta_description_length','h1_length','h2_length',
                'is_title_unique','is_h1_unique',
                'category',
            ]);
        });
    }
};
