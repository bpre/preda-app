<?php

namespace App\Support\Website;

use App\Models\Website\Lead;
use Illuminate\Support\Arr;

class GoogleAdsAttribution
{
    public static function campaignIdFromLead(?Lead $lead): ?string
    {
        if (! $lead) {
            return null;
        }

        return self::cleanCampaignId($lead->google_ads_campaign_id)
            ?? self::campaignIdFromParams(self::paramsFromPayload($lead->attribution_data))
            ?? self::campaignIdFromUrl($lead->attribution_landing_page)
            ?? self::campaignIdFromUrl($lead->attribution_conversion_page)
            ?? self::cleanCampaignId($lead->attribution_campaign);
    }

    public static function campaignIdFromPayload(mixed $payload): ?string
    {
        return self::campaignIdFromParams(self::paramsFromPayload($payload))
            ?? self::campaignIdFromPayloadUrls($payload);
    }

    public static function campaignIdFromParams(array $params): ?string
    {
        foreach (['gad_campaignid', 'campaignid'] as $key) {
            $campaignId = self::cleanCampaignId($params[$key] ?? null);

            if ($campaignId !== null) {
                return $campaignId;
            }
        }

        return null;
    }

    public static function campaignIdFromUrl(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        $query = parse_url((string) $url, PHP_URL_QUERY);

        if (! is_string($query) || $query === '') {
            $query = parse_url('https://example.test'.(string) $url, PHP_URL_QUERY);
        }

        if (! is_string($query) || $query === '') {
            return null;
        }

        parse_str($query, $params);

        return self::campaignIdFromParams($params);
    }

    public static function cleanCampaignId(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return preg_match('/^\d{4,32}$/', $value) ? $value : null;
    }

    private static function campaignIdFromPayloadUrls(mixed $payload): ?string
    {
        $payload = is_array($payload) ? $payload : [];

        foreach (['first_touch', 'last_touch', 'current_page'] as $touchKey) {
            foreach (['url', 'path'] as $field) {
                $campaignId = self::campaignIdFromUrl(Arr::get($payload, "{$touchKey}.{$field}"));

                if ($campaignId !== null) {
                    return $campaignId;
                }
            }
        }

        return null;
    }

    private static function paramsFromPayload(mixed $payload): array
    {
        $payload = is_array($payload) ? $payload : [];
        $params = [];

        foreach (['first_touch', 'last_touch'] as $touchKey) {
            foreach ((array) Arr::get($payload, "{$touchKey}.params", []) as $key => $value) {
                if (! is_scalar($value)) {
                    continue;
                }

                $key = strtolower((string) $key);
                $value = trim((string) $value);

                if ($key !== '' && $value !== '') {
                    $params[$key] = $value;
                }
            }
        }

        return $params;
    }
}
