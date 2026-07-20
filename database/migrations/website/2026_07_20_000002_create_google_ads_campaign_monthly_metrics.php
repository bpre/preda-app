<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_google_ads_campaign_monthly_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('google_ads_campaign_id');
            $table->date('month');
            $table->string('currency_code', 3)->nullable();
            $table->unsignedBigInteger('clicks')->default(0);
            $table->unsignedBigInteger('impressions')->default(0);
            $table->decimal('ctr', 12, 6)->nullable();
            $table->unsignedBigInteger('average_cpc_micros')->nullable();
            $table->unsignedBigInteger('cost_micros')->default(0);
            $table->decimal('conversions', 14, 4)->default(0);
            $table->decimal('conversion_value', 14, 4)->default(0);
            $table->timestamp('last_synced_at')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->unique(['google_ads_campaign_id', 'month'], 'google_ads_campaign_month_unique');
            $table->index('month');
            $table
                ->foreign('google_ads_campaign_id', 'google_ads_month_campaign_fk')
                ->references('id')
                ->on('website_google_ads_campaigns')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_google_ads_campaign_monthly_metrics');
    }
};
