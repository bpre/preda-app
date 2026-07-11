<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('website_reviews', function (Blueprint $table) {
            $table->string('source')->nullable()->after('name');
            $table->string('source_review_id')->nullable()->after('source');
            $table->text('avatar_url')->nullable()->after('review');

            $table->index(['source', 'date']);
            $table->unique(['source', 'source_review_id']);
        });
    }

    public function down(): void
    {
        Schema::table('website_reviews', function (Blueprint $table) {
            $table->dropUnique(['source', 'source_review_id']);
            $table->dropIndex(['source', 'date']);
            $table->dropColumn([
                'source',
                'source_review_id',
                'avatar_url',
            ]);
        });
    }
};
