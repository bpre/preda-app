<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_google_ads_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('customer_id');
            $table->string('campaign_id');
            $table->string('name');
            $table->string('status')->nullable();
            $table->string('advertising_channel_type')->nullable();
            $table->string('bidding_strategy_type')->nullable();
            $table->decimal('optimization_score', 8, 4)->nullable();
            $table->string('budget_name')->nullable();
            $table->unsignedBigInteger('budget_amount_micros')->nullable();
            $table->string('budget_type')->nullable();
            $table->string('currency_code', 3)->nullable();
            $table->unsignedBigInteger('clicks')->default(0);
            $table->unsignedBigInteger('impressions')->default(0);
            $table->decimal('ctr', 12, 6)->nullable();
            $table->unsignedBigInteger('average_cpc_micros')->nullable();
            $table->unsignedBigInteger('cost_micros')->default(0);
            $table->decimal('conversions', 14, 4)->default(0);
            $table->decimal('conversion_value', 14, 4)->default(0);
            $table->date('metrics_from')->nullable();
            $table->date('metrics_to')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->unique(['customer_id', 'campaign_id'], 'google_ads_campaigns_customer_campaign_unique');
            $table->index('campaign_id');
            $table->index('status');
        });

        Schema::table('website_leads', function (Blueprint $table) {
            $table->string('google_ads_campaign_id')->nullable()->after('attribution_campaign');
            $table->index('google_ads_campaign_id');
        });

        $this->backfillLeadCampaignIds();
    }

    public function down(): void
    {
        Schema::table('website_leads', function (Blueprint $table) {
            $table->dropIndex(['google_ads_campaign_id']);
            $table->dropColumn('google_ads_campaign_id');
        });

        Schema::dropIfExists('website_google_ads_campaigns');
    }

    private function backfillLeadCampaignIds(): void
    {
        DB::table('website_leads')
            ->select(['id', 'attribution_campaign', 'attribution_landing_page', 'attribution_data'])
            ->whereNull('google_ads_campaign_id')
            ->where(function ($query): void {
                $query
                    ->whereNotNull('attribution_campaign')
                    ->orWhereNotNull('attribution_landing_page')
                    ->orWhereNotNull('attribution_data');
            })
            ->orderBy('id')
            ->chunkById(100, function ($leads): void {
                foreach ($leads as $lead) {
                    $campaignId = $this->extractCampaignId($lead);

                    if ($campaignId === null) {
                        continue;
                    }

                    DB::table('website_leads')
                        ->where('id', $lead->id)
                        ->update(['google_ads_campaign_id' => $campaignId]);
                }
            });
    }

    private function extractCampaignId(object $lead): ?string
    {
        $direct = $this->cleanCampaignId($lead->attribution_campaign ?? null);

        if ($direct !== null) {
            return $direct;
        }

        foreach ($this->attributionUrls($lead) as $url) {
            $fromUrl = $this->extractCampaignIdFromUrl($url);

            if ($fromUrl !== null) {
                return $fromUrl;
            }
        }

        return null;
    }

    private function attributionUrls(object $lead): array
    {
        $urls = array_filter([(string) ($lead->attribution_landing_page ?? '')]);
        $data = json_decode((string) ($lead->attribution_data ?? ''), true);

        if (! is_array($data)) {
            return $urls;
        }

        foreach (['first_touch', 'last_touch', 'current_page'] as $touchKey) {
            foreach (['url', 'path'] as $field) {
                $value = $data[$touchKey][$field] ?? null;

                if (is_string($value) && trim($value) !== '') {
                    $urls[] = trim($value);
                }
            }
        }

        return $urls;
    }

    private function extractCampaignIdFromUrl(string $url): ?string
    {
        $query = parse_url($url, PHP_URL_QUERY);

        if (! is_string($query) || $query === '') {
            $query = parse_url('https://example.test'.$url, PHP_URL_QUERY);
        }

        if (! is_string($query) || $query === '') {
            return null;
        }

        parse_str($query, $params);

        foreach (['gad_campaignid', 'campaignid'] as $key) {
            $campaignId = $this->cleanCampaignId($params[$key] ?? null);

            if ($campaignId !== null) {
                return $campaignId;
            }
        }

        return null;
    }

    private function cleanCampaignId(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return preg_match('/^\d{4,32}$/', $value) ? $value : null;
    }
};
