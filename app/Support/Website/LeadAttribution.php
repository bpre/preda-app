<?php

namespace App\Support\Website;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class LeadAttribution
{
    private const CLICK_ID_PARAMS = [
        'gclid',
        'gbraid',
        'wbraid',
        'fbclid',
        'msclkid',
        'dclid',
        'ttclid',
        'li_fat_id',
    ];

    private const GOOGLE_AD_SIGNAL_PARAMS = [
        'gclid',
        'gbraid',
        'wbraid',
        'dclid',
        'gad',
        'gad_source',
        'gad_campaignid',
    ];

    private const STRING_LIMIT = 1000;

    public static function fromPayload(mixed $payload, ?Request $request = null): array
    {
        $payload = self::arrayValue($payload);

        $firstTouch = self::arrayValue($payload['first_touch'] ?? []);
        $lastTouch = self::arrayValue($payload['last_touch'] ?? []);
        $currentPage = self::arrayValue($payload['current_page'] ?? []);

        if ($lastTouch === []) {
            $lastTouch = $firstTouch;
        }

        $firstParams = self::params($firstTouch);
        $lastParams = self::params($lastTouch);
        $params = array_merge($firstParams, $lastParams);

        $referrer = self::cleanString($lastTouch['referrer'] ?? null)
            ?: self::cleanString($firstTouch['referrer'] ?? null)
            ?: self::cleanString($request?->headers->get('referer'));

        $source = self::resolveSource($params, $referrer);
        $campaign = self::firstParam($params, ['utm_campaign', 'campaign', 'campaignid', 'gad_campaignid', 'utm_id']);
        $term = self::firstParam($params, ['utm_term', 'keyword', 'term']);
        $content = self::firstParam($params, ['utm_content', 'content', 'creative', 'ad_id', 'ad']);
        $channel = self::resolveChannel($source['source'], $source['medium'], $campaign, $params);
        $googleAdsCampaignId = GoogleAdsAttribution::campaignIdFromPayload($payload);

        $attribution = [
            'attribution_channel' => $channel,
            'attribution_source' => $source['source'],
            'attribution_medium' => $source['medium'],
            'attribution_campaign' => $campaign,
            'attribution_term' => $term,
            'attribution_content' => $content,
            'attribution_landing_page' => self::cleanString($firstTouch['url'] ?? $firstTouch['path'] ?? null),
            'attribution_conversion_page' => self::cleanString($currentPage['url'] ?? $currentPage['path'] ?? null),
            'attribution_referrer' => $referrer,
            'attribution_first_touch_at' => self::dateTime($firstTouch['captured_at'] ?? null),
            'attribution_last_touch_at' => self::dateTime($lastTouch['captured_at'] ?? null),
            'attribution_click_ids' => self::clickIds($params),
            'attribution_data' => self::cleanArray($payload),
        ];

        if (Schema::hasColumn('website_leads', 'google_ads_campaign_id')) {
            $attribution['google_ads_campaign_id'] = $googleAdsCampaignId;
        }

        return $attribution;
    }

    private static function resolveSource(array $params, ?string $referrer): array
    {
        $utmSource = self::firstParam($params, ['utm_source', 'source']);
        $utmMedium = self::firstParam($params, ['utm_medium', 'medium']);

        if ($utmSource || $utmMedium) {
            return [
                'source' => self::normalizeSource($utmSource ?: self::sourceFromClickIds($params) ?: self::sourceFromReferrer($referrer) ?: 'unknown'),
                'medium' => self::normalizeMedium($utmMedium ?: self::mediumFromClickIds($params) ?: 'unknown'),
            ];
        }

        $clickSource = self::sourceFromClickIds($params);

        if ($clickSource) {
            return [
                'source' => $clickSource,
                'medium' => self::mediumFromClickIds($params) ?: 'paid',
            ];
        }

        $referrerSource = self::sourceFromReferrer($referrer);

        if ($referrerSource) {
            return [
                'source' => $referrerSource,
                'medium' => self::organicSearchSource($referrerSource) ? 'organic' : 'referral',
            ];
        }

        return [
            'source' => 'direct',
            'medium' => 'none',
        ];
    }

    private static function resolveChannel(?string $source, ?string $medium, ?string $campaign, array $params): string
    {
        $haystack = Str::lower(implode(' ', array_filter([
            $source,
            $medium,
            $campaign,
            self::firstParam($params, ['utm_content', 'content', 'targetid']),
        ])));

        if (Str::contains($haystack, ['remarketing', 'retargeting', 'retarget', 'aud-'])) {
            return 'remarketing';
        }

        if (in_array($source, ['google'], true) && Str::contains($medium ?? '', ['cpc', 'ppc', 'paid', 'ads'])) {
            return 'google_ads';
        }

        if (in_array($source, ['meta', 'facebook', 'instagram'], true) && Str::contains($medium ?? '', ['paid', 'cpc', 'ppc', 'ads', 'social'])) {
            return Str::contains($medium ?? '', ['paid', 'cpc', 'ppc', 'ads']) ? 'meta_ads' : 'social';
        }

        if ($medium === 'organic') {
            return 'organic_search';
        }

        if (in_array($medium, ['referral', 'social'], true)) {
            return $medium;
        }

        if ($source === 'direct' && $medium === 'none') {
            return 'direct';
        }

        return 'other';
    }

    private static function params(array $touch): array
    {
        $params = [];

        foreach (self::arrayValue($touch['params'] ?? []) as $key => $value) {
            $key = Str::lower((string) $key);
            $value = self::cleanString($value);

            if ($key !== '' && $value !== null) {
                $params[$key] = $value;
            }
        }

        return $params;
    }

    private static function firstParam(array $params, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = self::cleanString($params[Str::lower($key)] ?? null);

            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }

    private static function clickIds(array $params): array
    {
        $clickIds = [];

        foreach (self::CLICK_ID_PARAMS as $key) {
            $value = self::cleanString($params[$key] ?? null);

            if ($value !== null) {
                $clickIds[$key] = $value;
            }
        }

        return $clickIds;
    }

    private static function sourceFromClickIds(array $params): ?string
    {
        return match (true) {
            self::hasAny($params, self::GOOGLE_AD_SIGNAL_PARAMS) => 'google',
            self::hasAny($params, ['fbclid']) => 'meta',
            self::hasAny($params, ['msclkid']) => 'microsoft',
            self::hasAny($params, ['ttclid']) => 'tiktok',
            self::hasAny($params, ['li_fat_id']) => 'linkedin',
            default => null,
        };
    }

    private static function mediumFromClickIds(array $params): ?string
    {
        return match (true) {
            self::hasAny($params, [...self::GOOGLE_AD_SIGNAL_PARAMS, 'msclkid']) => 'cpc',
            self::hasAny($params, ['fbclid', 'ttclid', 'li_fat_id']) => 'social',
            default => null,
        };
    }

    private static function hasAny(array $params, array $keys): bool
    {
        foreach ($keys as $key) {
            if (filled($params[$key] ?? null)) {
                return true;
            }
        }

        return false;
    }

    private static function sourceFromReferrer(?string $referrer): ?string
    {
        if (! $referrer) {
            return null;
        }

        $host = parse_url($referrer, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return null;
        }

        $host = Str::lower(preg_replace('/^www\./', '', $host));

        return match (true) {
            Str::contains($host, ['google.']) => 'google',
            Str::contains($host, ['bing.com']) => 'bing',
            Str::contains($host, ['yahoo.']) => 'yahoo',
            Str::contains($host, ['duckduckgo.com']) => 'duckduckgo',
            Str::contains($host, ['facebook.com', 'l.facebook.com', 'instagram.com', 'l.instagram.com']) => 'meta',
            default => $host,
        };
    }

    private static function organicSearchSource(string $source): bool
    {
        return in_array($source, ['google', 'bing', 'yahoo', 'duckduckgo'], true);
    }

    private static function normalizeSource(string $source): string
    {
        $source = Str::lower(trim($source));

        return match ($source) {
            'fb', 'facebook', 'instagram', 'ig' => $source === 'instagram' || $source === 'ig' ? 'instagram' : 'facebook',
            'meta' => 'meta',
            'adwords' => 'google',
            default => $source,
        };
    }

    private static function normalizeMedium(string $medium): string
    {
        $medium = Str::lower(trim($medium));

        return match ($medium) {
            'ppc', 'paidsearch', 'paid_search' => 'cpc',
            'paid-social', 'paid_social' => 'paid_social',
            default => $medium,
        };
    }

    private static function dateTime(mixed $value): ?Carbon
    {
        if (blank($value)) {
            return null;
        }

        try {
            return Carbon::parse((string) $value);
        } catch (\Throwable) {
            return null;
        }
    }

    private static function cleanString(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return Str::limit($value, self::STRING_LIMIT, '');
    }

    private static function arrayValue(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    private static function cleanArray(array $value): array
    {
        $clean = [];

        foreach ($value as $key => $item) {
            $key = (string) $key;

            if (is_array($item)) {
                $clean[$key] = self::cleanArray($item);
                continue;
            }

            $item = self::cleanString($item);

            if ($item !== null) {
                $clean[$key] = $item;
            }
        }

        return $clean;
    }
}
