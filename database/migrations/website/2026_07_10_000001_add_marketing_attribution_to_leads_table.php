<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('website_leads', function (Blueprint $table) {
            $table->string('attribution_channel')->nullable()->after('documents_skipped_at');
            $table->string('attribution_source')->nullable()->after('attribution_channel');
            $table->string('attribution_medium')->nullable()->after('attribution_source');
            $table->string('attribution_campaign')->nullable()->after('attribution_medium');
            $table->string('attribution_term')->nullable()->after('attribution_campaign');
            $table->string('attribution_content')->nullable()->after('attribution_term');
            $table->text('attribution_landing_page')->nullable()->after('attribution_content');
            $table->text('attribution_conversion_page')->nullable()->after('attribution_landing_page');
            $table->text('attribution_referrer')->nullable()->after('attribution_conversion_page');
            $table->timestamp('attribution_first_touch_at')->nullable()->after('attribution_referrer');
            $table->timestamp('attribution_last_touch_at')->nullable()->after('attribution_first_touch_at');
            $table->json('attribution_click_ids')->nullable()->after('attribution_last_touch_at');
            $table->json('attribution_data')->nullable()->after('attribution_click_ids');

            $table->index('attribution_channel');
            $table->index('attribution_source');
            $table->index('attribution_campaign');
        });
    }

    public function down(): void
    {
        Schema::table('website_leads', function (Blueprint $table) {
            $table->dropIndex(['attribution_channel']);
            $table->dropIndex(['attribution_source']);
            $table->dropIndex(['attribution_campaign']);

            $table->dropColumn([
                'attribution_channel',
                'attribution_source',
                'attribution_medium',
                'attribution_campaign',
                'attribution_term',
                'attribution_content',
                'attribution_landing_page',
                'attribution_conversion_page',
                'attribution_referrer',
                'attribution_first_touch_at',
                'attribution_last_touch_at',
                'attribution_click_ids',
                'attribution_data',
            ]);
        });
    }
};
