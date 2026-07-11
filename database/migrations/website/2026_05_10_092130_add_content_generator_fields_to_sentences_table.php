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
        Schema::table('website_sentences', function (Blueprint $table) {
            if (! Schema::hasColumn('website_sentences', 'ruling_points')) {
                $table->json('ruling_points')->nullable()->after('paid_off_year');
            }

            if (! Schema::hasColumn('website_sentences', 'judgment_publication_mode')) {
                $table->string('judgment_publication_mode')->nullable()->after('ruling_points');
            }

            if (! Schema::hasColumn('website_sentences', 'reasoning_source')) {
                $table->string('reasoning_source')->nullable()->after('judgment_publication_mode');
            }

            if (! Schema::hasColumn('website_sentences', 'court_reasoning_summary')) {
                $table->text('court_reasoning_summary')->nullable()->after('reasoning_source');
            }

            if (! Schema::hasColumn('website_sentences', 'evidence_scope')) {
                $table->json('evidence_scope')->nullable()->after('court_reasoning_summary');
            }

            if (! Schema::hasColumn('website_sentences', 'security_granted')) {
                $table->boolean('security_granted')->default(false)->after('evidence_scope');
            }

            if (! Schema::hasColumn('website_sentences', 'security_note')) {
                $table->text('security_note')->nullable()->after('security_granted');
            }

            if (! Schema::hasColumn('website_sentences', 'setoff_or_retention_note')) {
                $table->text('setoff_or_retention_note')->nullable()->after('security_note');
            }

            if (! Schema::hasColumn('website_sentences', 'counterclaim_note')) {
                $table->text('counterclaim_note')->nullable()->after('setoff_or_retention_note');
            }

            if (! Schema::hasColumn('website_sentences', 'content_note')) {
                $table->text('content_note')->nullable()->after('counterclaim_note');
            }

            if (! Schema::hasColumn('website_sentences', 'content_generated_at')) {
                $table->timestamp('content_generated_at')->nullable()->after('content_note');
            }
        });

        Schema::table('website_sentences', function (Blueprint $table) {
            $table->string('label')->nullable()->change();
            $table->text('excerpt')->nullable()->change();
            $table->text('content')->nullable()->change();
            $table->string('metatitle')->nullable()->change();
            $table->text('metadescription')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('website_sentences', function (Blueprint $table) {
            $table->string('label')->nullable(false)->change();
            $table->text('excerpt')->nullable(false)->change();
            $table->text('content')->nullable(false)->change();
            $table->string('metatitle')->nullable(false)->change();
            $table->text('metadescription')->nullable(false)->change();
        });

        Schema::table('website_sentences', function (Blueprint $table) {
            foreach ([
                'content_generated_at',
                'content_note',
                'counterclaim_note',
                'setoff_or_retention_note',
                'security_note',
                'security_granted',
                'evidence_scope',
                'court_reasoning_summary',
                'reasoning_source',
                'judgment_publication_mode',
                'ruling_points',
            ] as $column) {
                if (Schema::hasColumn('website_sentences', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
