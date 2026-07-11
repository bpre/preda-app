<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('website_sentences', 'content_generator_flags')) {
            return;
        }

        Schema::table('website_sentences', function (Blueprint $table) {
            $table->json('content_generator_flags')->nullable()->after('security_note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('website_sentences', 'content_generator_flags')) {
            return;
        }

        Schema::table('website_sentences', function (Blueprint $table) {
            $table->dropColumn('content_generator_flags');
        });
    }
};
