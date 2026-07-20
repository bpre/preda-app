<?php

namespace App\Services\Integrations;

use App\Models\Website\GoogleAdsCampaign;
use App\Models\Website\GoogleAdsCampaignMonthlyMetric;
use App\Models\Website\Lead;
use App\Support\Website\GoogleAdsAttribution;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class GoogleAdsCampaignSyncService
{
    public const DEFAULT_MONTHLY_LOOKBACK_MONTHS = 36;

    public const HISTORICAL_MONTHLY_LOOKBACK_MONTHS = 132;

    private const API_VERSION = 'v24';

    private const OAUTH_TOKEN_URL = 'https://oauth2.googleapis.com/token';

    private const GOOGLE_ADS_URL = 'https://googleads.googleapis.com';

    public function isConfigured(): bool
    {
        return filled($this->developerToken())
            && filled($this->clientId())
            && filled($this->clientSecret())
            && filled($this->refreshToken())
            && $this->customerId() !== '';
    }

    public function syncCampaigns(?CarbonInterface $from = null, ?CarbonInterface $to = null, int $monthlyMonths = self::DEFAULT_MONTHLY_LOOKBACK_MONTHS): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Brakuje konfiguracji Google Ads API. Uzupełnij GOOGLE_ADS_DEVELOPER_TOKEN, GOOGLE_ADS_CLIENT_ID, GOOGLE_ADS_CLIENT_SECRET, GOOGLE_ADS_REFRESH_TOKEN i GOOGLE_ADS_CUSTOMER_ID.');
        }

        $accessToken = $this->fetchAccessToken();
        $rows = $this->searchStream($this->campaignQuery($from, $to), $accessToken);

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $syncedAt = now();
        $customerId = $this->customerId();

        DB::transaction(function () use ($rows, $from, $to, $syncedAt, $customerId, &$created, &$updated, &$skipped): void {
            foreach ($rows as $row) {
                $mapped = $this->mapCampaignRow($row, $customerId, $from, $to, $syncedAt);

                if ($mapped === null) {
                    $skipped++;

                    continue;
                }

                $campaign = GoogleAdsCampaign::query()->firstOrNew([
                    'customer_id' => $mapped['customer_id'],
                    'campaign_id' => $mapped['campaign_id'],
                ]);

                $campaign->fill($mapped);
                $campaign->save();

                $campaign->wasRecentlyCreated ? $created++ : $updated++;
            }
        });

        $linkedLeads = $this->backfillLeadCampaignIds();
        $monthlyMetrics = $monthlyMonths > 0
            ? $this->syncMonthlyMetricsWithAccessToken($accessToken, $monthlyMonths)
            : $this->emptyMonthlyMetricsResult();

        return [
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'linked_leads' => $linkedLeads,
            'monthly_metrics' => $monthlyMetrics,
        ];
    }

    public function syncMonthlyMetrics(int $months = self::HISTORICAL_MONTHLY_LOOKBACK_MONTHS): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Brakuje konfiguracji Google Ads API. Uzupełnij GOOGLE_ADS_DEVELOPER_TOKEN, GOOGLE_ADS_CLIENT_ID, GOOGLE_ADS_CLIENT_SECRET, GOOGLE_ADS_REFRESH_TOKEN i GOOGLE_ADS_CUSTOMER_ID.');
        }

        return $this->syncMonthlyMetricsWithAccessToken($this->fetchAccessToken(), $months);
    }

    public function backfillLeadCampaignIds(): int
    {
        $linked = 0;

        Lead::query()
            ->whereNull('google_ads_campaign_id')
            ->where(function ($query): void {
                $query
                    ->whereNotNull('attribution_campaign')
                    ->orWhereNotNull('attribution_landing_page')
                    ->orWhereNotNull('attribution_data');
            })
            ->orderBy('id')
            ->chunkById(100, function ($leads) use (&$linked): void {
                foreach ($leads as $lead) {
                    $campaignId = GoogleAdsAttribution::campaignIdFromLead($lead);

                    if ($campaignId === null) {
                        continue;
                    }

                    $lead->forceFill(['google_ads_campaign_id' => $campaignId])->save();
                    $linked++;
                }
            });

        return $linked;
    }

    private function fetchAccessToken(): string
    {
        $response = Http::asForm()->post(self::OAUTH_TOKEN_URL, [
            'client_id' => $this->clientId(),
            'client_secret' => $this->clientSecret(),
            'refresh_token' => $this->refreshToken(),
            'grant_type' => 'refresh_token',
        ]);

        $payload = $this->decodeResponse($response, 'Nie udało się odświeżyć access tokena Google Ads API.');
        $accessToken = trim((string) ($payload['access_token'] ?? ''));

        if ($accessToken === '') {
            throw new RuntimeException('Google OAuth nie zwrócił access tokena.');
        }

        return $accessToken;
    }

    private function searchStream(string $query, string $accessToken): array
    {
        $headers = [
            'developer-token' => $this->developerToken(),
        ];

        if (filled($this->loginCustomerId())) {
            $headers['login-customer-id'] = $this->loginCustomerId();
        }

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->withHeaders($headers)
            ->post(self::GOOGLE_ADS_URL.'/'.self::API_VERSION.'/customers/'.$this->customerId().'/googleAds:searchStream', [
                'query' => $query,
            ]);

        $payload = $this->decodeResponse($response, 'Google Ads API zwróciło błąd.', [
            'customer_id' => $this->customerId(),
            'login_customer_id' => $this->loginCustomerId(),
        ]);

        if (isset($payload['results']) && is_array($payload['results'])) {
            return $payload['results'];
        }

        return collect($payload)
            ->flatMap(fn (mixed $chunk): array => is_array($chunk) ? (array) ($chunk['results'] ?? []) : [])
            ->values()
            ->all();
    }

    private function campaignQuery(?CarbonInterface $from, ?CarbonInterface $to): string
    {
        $dateClause = $from && $to
            ? "segments.date BETWEEN '{$from->toDateString()}' AND '{$to->toDateString()}'"
            : 'segments.date DURING LAST_30_DAYS';

        return <<<GAQL
            SELECT
              customer.currency_code,
              campaign.id,
              campaign.name,
              campaign.status,
              campaign.advertising_channel_type,
              campaign.bidding_strategy_type,
              campaign.optimization_score,
              campaign_budget.name,
              campaign_budget.amount_micros,
              campaign_budget.type,
              metrics.clicks,
              metrics.impressions,
              metrics.ctr,
              metrics.average_cpc,
              metrics.cost_micros,
              metrics.conversions,
              metrics.conversions_value
            FROM campaign
            WHERE {$dateClause}
              AND campaign.status != 'REMOVED'
            GAQL;
    }

    private function monthlyCampaignMetricsQuery(CarbonInterface $from, CarbonInterface $to): string
    {
        $fromMonth = CarbonImmutable::parse($from)->startOfMonth()->toDateString();
        $toMonth = CarbonImmutable::parse($to)->startOfMonth()->toDateString();

        return <<<GAQL
            SELECT
              customer.currency_code,
              campaign.id,
              campaign.name,
              campaign.status,
              campaign.advertising_channel_type,
              campaign.bidding_strategy_type,
              segments.month,
              metrics.clicks,
              metrics.impressions,
              metrics.ctr,
              metrics.average_cpc,
              metrics.cost_micros,
              metrics.conversions,
              metrics.conversions_value
            FROM campaign
            WHERE segments.month >= '{$fromMonth}'
              AND segments.month <= '{$toMonth}'
            GAQL;
    }

    private function syncMonthlyMetricsWithAccessToken(string $accessToken, int $months): array
    {
        $months = max(1, min(self::HISTORICAL_MONTHLY_LOOKBACK_MONTHS, $months));
        $to = CarbonImmutable::now()->startOfMonth();
        $from = $to->subMonths($months - 1);
        $rows = $this->searchStream($this->monthlyCampaignMetricsQuery($from, $to), $accessToken);

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $syncedAt = now();
        $customerId = $this->customerId();

        DB::transaction(function () use ($rows, $syncedAt, $customerId, &$created, &$updated, &$skipped): void {
            foreach ($rows as $row) {
                $campaign = $this->upsertCampaignFromMonthlyRow($row, $customerId, $syncedAt);
                $mapped = $campaign
                    ? $this->mapMonthlyMetricRow($row, $campaign->getKey(), $syncedAt)
                    : null;

                if ($mapped === null) {
                    $skipped++;

                    continue;
                }

                $metric = GoogleAdsCampaignMonthlyMetric::query()->firstOrNew([
                    'google_ads_campaign_id' => $mapped['google_ads_campaign_id'],
                    'month' => $mapped['month'],
                ]);

                $metric->fill($mapped);
                $metric->save();

                $metric->wasRecentlyCreated ? $created++ : $updated++;
            }
        });

        return [
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'months' => $months,
        ];
    }

    private function emptyMonthlyMetricsResult(): array
    {
        return [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'from' => null,
            'to' => null,
            'months' => 0,
        ];
    }

    private function mapCampaignRow(array $row, string $customerId, ?CarbonInterface $from, ?CarbonInterface $to, mixed $syncedAt): ?array
    {
        $campaignId = $this->stringValue(Arr::get($row, 'campaign.id'));
        $name = $this->stringValue(Arr::get($row, 'campaign.name'));

        if ($campaignId === null || $name === null) {
            return null;
        }

        return [
            'customer_id' => $customerId,
            'campaign_id' => $campaignId,
            'name' => $name,
            'status' => $this->stringValue(Arr::get($row, 'campaign.status')),
            'advertising_channel_type' => $this->stringValue(Arr::get($row, 'campaign.advertisingChannelType')),
            'bidding_strategy_type' => $this->stringValue(Arr::get($row, 'campaign.biddingStrategyType')),
            'optimization_score' => $this->floatValue(Arr::get($row, 'campaign.optimizationScore')),
            'budget_name' => $this->stringValue(Arr::get($row, 'campaignBudget.name')),
            'budget_amount_micros' => $this->intValue(Arr::get($row, 'campaignBudget.amountMicros')),
            'budget_type' => $this->stringValue(Arr::get($row, 'campaignBudget.type')),
            'currency_code' => $this->stringValue(Arr::get($row, 'customer.currencyCode')),
            'clicks' => $this->intValue(Arr::get($row, 'metrics.clicks')) ?? 0,
            'impressions' => $this->intValue(Arr::get($row, 'metrics.impressions')) ?? 0,
            'ctr' => $this->floatValue(Arr::get($row, 'metrics.ctr')),
            'average_cpc_micros' => $this->intValue(Arr::get($row, 'metrics.averageCpc')),
            'cost_micros' => $this->intValue(Arr::get($row, 'metrics.costMicros')) ?? 0,
            'conversions' => $this->floatValue(Arr::get($row, 'metrics.conversions')) ?? 0,
            'conversion_value' => $this->floatValue(Arr::get($row, 'metrics.conversionsValue')) ?? 0,
            'metrics_from' => $from?->toDateString() ?? now()->subDays(29)->toDateString(),
            'metrics_to' => $to?->toDateString() ?? now()->toDateString(),
            'last_synced_at' => $syncedAt,
            'raw_data' => $row,
        ];
    }

    private function upsertCampaignFromMonthlyRow(array $row, string $customerId, mixed $syncedAt): ?GoogleAdsCampaign
    {
        $campaignId = $this->stringValue(Arr::get($row, 'campaign.id'));
        $name = $this->stringValue(Arr::get($row, 'campaign.name'));

        if ($campaignId === null || $name === null) {
            return null;
        }

        $campaign = GoogleAdsCampaign::query()->firstOrNew([
            'customer_id' => $customerId,
            'campaign_id' => $campaignId,
        ]);

        $campaign->fill(array_filter([
            'customer_id' => $customerId,
            'campaign_id' => $campaignId,
            'name' => $name,
            'status' => $this->stringValue(Arr::get($row, 'campaign.status')),
            'advertising_channel_type' => $this->stringValue(Arr::get($row, 'campaign.advertisingChannelType')),
            'bidding_strategy_type' => $this->stringValue(Arr::get($row, 'campaign.biddingStrategyType')),
            'currency_code' => $this->stringValue(Arr::get($row, 'customer.currencyCode')),
            'last_synced_at' => $syncedAt,
        ], fn (mixed $value): bool => $value !== null));
        $campaign->save();

        return $campaign;
    }

    private function mapMonthlyMetricRow(array $row, int $campaignKey, mixed $syncedAt): ?array
    {
        $month = $this->monthValue(Arr::get($row, 'segments.month'));

        if ($month === null) {
            return null;
        }

        return [
            'google_ads_campaign_id' => $campaignKey,
            'month' => $month,
            'currency_code' => $this->stringValue(Arr::get($row, 'customer.currencyCode')),
            'clicks' => $this->intValue(Arr::get($row, 'metrics.clicks')) ?? 0,
            'impressions' => $this->intValue(Arr::get($row, 'metrics.impressions')) ?? 0,
            'ctr' => $this->floatValue(Arr::get($row, 'metrics.ctr')),
            'average_cpc_micros' => $this->intValue(Arr::get($row, 'metrics.averageCpc')),
            'cost_micros' => $this->intValue(Arr::get($row, 'metrics.costMicros')) ?? 0,
            'conversions' => $this->floatValue(Arr::get($row, 'metrics.conversions')) ?? 0,
            'conversion_value' => $this->floatValue(Arr::get($row, 'metrics.conversionsValue')) ?? 0,
            'last_synced_at' => $syncedAt,
            'raw_data' => $row,
        ];
    }

    private function stringValue(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function intValue(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    private function floatValue(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private function monthValue(mixed $value): ?string
    {
        $value = $this->stringValue($value);

        if ($value === null) {
            return null;
        }

        try {
            return CarbonImmutable::parse($value)->startOfMonth()->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function developerToken(): ?string
    {
        return config('services.google_ads.developer_token');
    }

    private function clientId(): ?string
    {
        return config('services.google_ads.client_id');
    }

    private function clientSecret(): ?string
    {
        return config('services.google_ads.client_secret');
    }

    private function refreshToken(): ?string
    {
        return config('services.google_ads.refresh_token');
    }

    private function customerId(): string
    {
        return preg_replace('/\D+/', '', (string) config('services.google_ads.customer_id')) ?: '';
    }

    private function loginCustomerId(): string
    {
        return preg_replace('/\D+/', '', (string) config('services.google_ads.login_customer_id')) ?: '';
    }

    private function decodeResponse(Response $response, string $fallbackMessage, array $context = []): array
    {
        if ($response->successful()) {
            return $response->json() ?? [];
        }

        $payload = $response->json();
        $googleError = is_array($payload) ? Arr::get($payload, 'error', []) : [];
        $message = trim((string) Arr::get($googleError, 'message', '')) ?: $fallbackMessage;
        $status = trim((string) Arr::get($googleError, 'status', ''));
        $code = Arr::get($googleError, 'code', $response->status());
        $bodySnippet = Str::limit(trim(preg_replace('/\s+/', ' ', $response->body()) ?: ''), 500);

        Log::warning('Google Ads API request failed.', array_filter([
            ...$context,
            'status' => $response->status(),
            'google_error' => $googleError,
            'response_snippet' => $bodySnippet,
        ]));

        $details = array_filter([
            'HTTP '.$response->status(),
            $status !== '' ? "status: {$status}" : null,
            filled($code) ? "code: {$code}" : null,
            ! is_array($payload) && $bodySnippet !== '' ? "response: {$bodySnippet}" : null,
        ]);

        throw new RuntimeException($message.' ['.implode('; ', $details).']');
    }
}
