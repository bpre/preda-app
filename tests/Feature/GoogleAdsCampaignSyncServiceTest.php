<?php

namespace Tests\Feature;

use App\Models\Website\GoogleAdsCampaign;
use App\Models\Website\GoogleAdsCampaignMonthlyMetric;
use App\Models\Website\Lead;
use App\Services\Integrations\GoogleAdsCampaignSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleAdsCampaignSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_campaigns_imports_campaigns_and_links_existing_google_ads_leads(): void
    {
        config([
            'services.google_ads.developer_token' => 'developer-token',
            'services.google_ads.client_id' => 'client-id',
            'services.google_ads.client_secret' => 'client-secret',
            'services.google_ads.refresh_token' => 'refresh-token',
            'services.google_ads.customer_id' => '229-939-4554',
            'services.google_ads.login_customer_id' => '999-121-6572',
        ]);

        $lead = Lead::query()->create([
            'name' => 'Lead Google Ads',
            'email' => 'google-ads@example.test',
            'phone' => '500 100 100',
            'attribution_channel' => 'google_ads',
            'attribution_source' => 'google',
            'attribution_medium' => 'cpc',
            'attribution_landing_page' => 'https://preda.info/kredyt-euro-kancelaria-glogow?gad_source=1&gad_campaignid=23332662670&gclid=test-gclid',
        ]);

        DB::table('website_leads')
            ->where('id', $lead->getKey())
            ->update(['google_ads_campaign_id' => null]);

        Http::fake(function ($request) {
            if (str_contains($request->url(), 'oauth2.googleapis.com/token')) {
                return Http::response([
                    'access_token' => 'access-token',
                    'expires_in' => 3600,
                ]);
            }

            $payload = json_decode($request->body(), true);
            $query = (string) ($payload['query'] ?? '');

            if (str_contains($query, 'segments.month')) {
                return Http::response([
                    [
                        'results' => [
                            [
                                'customer' => [
                                    'currencyCode' => 'PLN',
                                ],
                                'campaign' => [
                                    'id' => '23332662670',
                                    'name' => '[TXT] Głogów - EUR',
                                    'status' => 'ENABLED',
                                    'advertisingChannelType' => 'SEARCH',
                                    'biddingStrategyType' => 'MAXIMIZE_CONVERSIONS',
                                ],
                                'segments' => [
                                    'month' => '2026-06-01',
                                ],
                                'metrics' => [
                                    'clicks' => '50',
                                    'impressions' => '2000',
                                    'ctr' => 0.025,
                                    'averageCpc' => '4000000',
                                    'costMicros' => '200000000',
                                    'conversions' => 4,
                                    'conversionsValue' => 0,
                                ],
                            ],
                            [
                                'customer' => [
                                    'currencyCode' => 'PLN',
                                ],
                                'campaign' => [
                                    'id' => '23332662670',
                                    'name' => '[TXT] Głogów - EUR',
                                    'status' => 'ENABLED',
                                    'advertisingChannelType' => 'SEARCH',
                                    'biddingStrategyType' => 'MAXIMIZE_CONVERSIONS',
                                ],
                                'segments' => [
                                    'month' => '2026-07-01',
                                ],
                                'metrics' => [
                                    'clicks' => '73',
                                    'impressions' => '2567',
                                    'ctr' => 0.0284,
                                    'averageCpc' => '4336986',
                                    'costMicros' => '316600000',
                                    'conversions' => 4.5,
                                    'conversionsValue' => 0,
                                ],
                            ],
                        ],
                    ],
                ]);
            }

            return Http::response([
                [
                    'results' => [
                        [
                            'customer' => [
                                'currencyCode' => 'PLN',
                            ],
                            'campaign' => [
                                'id' => '23332662670',
                                'name' => '[TXT] Głogów - EUR',
                                'status' => 'ENABLED',
                                'advertisingChannelType' => 'SEARCH',
                                'biddingStrategyType' => 'MAXIMIZE_CONVERSIONS',
                                'optimizationScore' => 0.91,
                            ],
                            'campaignBudget' => [
                                'name' => 'Budżet dzienny',
                                'amountMicros' => '150000000',
                                'type' => 'STANDARD',
                            ],
                            'metrics' => [
                                'clicks' => '123',
                                'impressions' => '4567',
                                'ctr' => 0.0269,
                                'averageCpc' => '4200000',
                                'costMicros' => '516600000',
                                'conversions' => 8.5,
                                'conversionsValue' => 0,
                            ],
                        ],
                    ],
                ],
            ]);
        });

        $result = app(GoogleAdsCampaignSyncService::class)->syncCampaigns(monthlyMonths: 2);

        $this->assertSame(1, $result['created']);
        $this->assertSame(0, $result['updated']);
        $this->assertSame(0, $result['skipped']);
        $this->assertSame(1, $result['linked_leads']);
        $this->assertSame(2, $result['monthly_metrics']['created']);
        $this->assertSame(0, $result['monthly_metrics']['updated']);
        $this->assertSame(0, $result['monthly_metrics']['skipped']);

        $this->assertDatabaseHas(GoogleAdsCampaign::class, [
            'customer_id' => '2299394554',
            'campaign_id' => '23332662670',
            'name' => '[TXT] Głogów - EUR',
            'status' => 'ENABLED',
            'clicks' => 123,
            'cost_micros' => 516600000,
        ]);

        $campaign = GoogleAdsCampaign::query()->where('campaign_id', '23332662670')->firstOrFail();

        $this->assertDatabaseHas(GoogleAdsCampaignMonthlyMetric::class, [
            'google_ads_campaign_id' => $campaign->getKey(),
            'month' => '2026-06-01 00:00:00',
            'clicks' => 50,
            'cost_micros' => 200000000,
        ]);
        $this->assertDatabaseHas(GoogleAdsCampaignMonthlyMetric::class, [
            'google_ads_campaign_id' => $campaign->getKey(),
            'month' => '2026-07-01 00:00:00',
            'clicks' => 73,
            'cost_micros' => 316600000,
        ]);

        $this->assertSame(
            '23332662670',
            $lead->refresh()->google_ads_campaign_id,
        );

        Http::assertSent(function ($request): bool {
            if (! str_contains($request->url(), '/customers/2299394554/googleAds:searchStream')) {
                return false;
            }

            $payload = json_decode($request->body(), true);

            return ($request->header('developer-token')[0] ?? null) === 'developer-token'
                && ($request->header('login-customer-id')[0] ?? null) === '9991216572'
                && ($request->header('Authorization')[0] ?? null) === 'Bearer access-token'
                && str_contains((string) ($payload['query'] ?? ''), 'FROM campaign');
        });

        Http::assertSent(fn ($request): bool => str_contains(
            (string) (json_decode($request->body(), true)['query'] ?? ''),
            'segments.month',
        ));
    }

    public function test_sync_monthly_metrics_can_create_historical_campaigns(): void
    {
        config([
            'services.google_ads.developer_token' => 'developer-token',
            'services.google_ads.client_id' => 'client-id',
            'services.google_ads.client_secret' => 'client-secret',
            'services.google_ads.refresh_token' => 'refresh-token',
            'services.google_ads.customer_id' => '229-939-4554',
            'services.google_ads.login_customer_id' => '999-121-6572',
        ]);

        Http::fake([
            'oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'access-token',
                'expires_in' => 3600,
            ]),
            'googleads.googleapis.com/*' => Http::response([
                [
                    'results' => [
                        [
                            'customer' => [
                                'currencyCode' => 'PLN',
                            ],
                            'campaign' => [
                                'id' => '11122233344',
                                'name' => '[HIST] Dawna kampania',
                                'status' => 'REMOVED',
                                'advertisingChannelType' => 'SEARCH',
                                'biddingStrategyType' => 'MANUAL_CPC',
                            ],
                            'segments' => [
                                'month' => '2020-01-01',
                            ],
                            'metrics' => [
                                'clicks' => '10',
                                'impressions' => '100',
                                'ctr' => 0.1,
                                'averageCpc' => '5000000',
                                'costMicros' => '50000000',
                                'conversions' => 1,
                                'conversionsValue' => 0,
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $result = app(GoogleAdsCampaignSyncService::class)->syncMonthlyMetrics(months: 132);

        $this->assertSame(1, $result['created']);
        $this->assertSame(0, $result['updated']);
        $this->assertSame(0, $result['skipped']);

        $this->assertDatabaseHas(GoogleAdsCampaign::class, [
            'customer_id' => '2299394554',
            'campaign_id' => '11122233344',
            'name' => '[HIST] Dawna kampania',
            'status' => 'REMOVED',
        ]);

        $campaign = GoogleAdsCampaign::query()->where('campaign_id', '11122233344')->firstOrFail();

        $this->assertDatabaseHas(GoogleAdsCampaignMonthlyMetric::class, [
            'google_ads_campaign_id' => $campaign->getKey(),
            'month' => '2020-01-01 00:00:00',
            'cost_micros' => 50000000,
        ]);

        Http::assertSent(function ($request): bool {
            if (! str_contains($request->url(), '/customers/2299394554/googleAds:searchStream')) {
                return false;
            }

            $payload = json_decode($request->body(), true);

            return ($request->header('developer-token')[0] ?? null) === 'developer-token'
                && ($request->header('login-customer-id')[0] ?? null) === '9991216572'
                && ($request->header('Authorization')[0] ?? null) === 'Bearer access-token'
                && str_contains((string) ($payload['query'] ?? ''), 'segments.month');
        });
    }
}
