<?php

namespace App\Services\Integrations;

use App\Models\Website\GoogleBusinessProfileConnection;
use App\Models\Website\Review;
use Carbon\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class GoogleBusinessProfileService
{
    private const OAUTH_SCOPE = 'https://www.googleapis.com/auth/business.manage';

    private const OAUTH_AUTHORIZE_URL = 'https://accounts.google.com/o/oauth2/v2/auth';

    private const OAUTH_TOKEN_URL = 'https://oauth2.googleapis.com/token';

    private const OAUTH_STATE_TTL_MINUTES = 15;

    private const ACCOUNTS_URL = 'https://mybusinessaccountmanagement.googleapis.com/v1/accounts';

    private const LOCATION_URL = 'https://mybusinessbusinessinformation.googleapis.com/v1';

    private const REVIEWS_URL = 'https://mybusiness.googleapis.com/v4';

    public function isConfigured(): bool
    {
        return filled(config('services.google_business_profile.client_id'))
            && filled(config('services.google_business_profile.client_secret'));
    }

    public function getRedirectUri(): string
    {
        $configuredRedirectUri = trim((string) config('services.google_business_profile.redirect_uri'));

        if ($configuredRedirectUri !== '') {
            return $configuredRedirectUri;
        }

        return route('website.integrations.google-business-profile.callback');
    }

    public function getAuthorizationUrl(string $state): string
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Brakuje konfiguracji GOOGLE_BUSINESS_PROFILE_CLIENT_ID lub GOOGLE_BUSINESS_PROFILE_CLIENT_SECRET.');
        }

        return self::OAUTH_AUTHORIZE_URL.'?'.http_build_query([
            'client_id' => config('services.google_business_profile.client_id'),
            'redirect_uri' => $this->getRedirectUri(),
            'response_type' => 'code',
            'scope' => self::OAUTH_SCOPE,
            'access_type' => 'offline',
            'prompt' => 'consent',
            'include_granted_scopes' => 'true',
            'state' => $state,
        ]);
    }

    public function makeAuthorizationState(): string
    {
        $payload = $this->base64UrlEncode((string) json_encode([
            'nonce' => Str::random(32),
            'issued_at' => now()->timestamp,
        ], JSON_THROW_ON_ERROR));

        return $payload.'.'.$this->signAuthorizationStatePayload($payload);
    }

    public function isAuthorizationStateValid(string $state): bool
    {
        if (! str_contains($state, '.')) {
            return false;
        }

        [$payload, $signature] = explode('.', $state, 2);

        if (! hash_equals($this->signAuthorizationStatePayload($payload), $signature)) {
            return false;
        }

        $decodedPayload = json_decode($this->base64UrlDecode($payload), true);

        if (! is_array($decodedPayload)) {
            return false;
        }

        $issuedAt = (int) ($decodedPayload['issued_at'] ?? 0);

        if ($issuedAt <= 0) {
            return false;
        }

        return $issuedAt <= now()->addMinute()->timestamp
            && $issuedAt >= now()->subMinutes(self::OAUTH_STATE_TTL_MINUTES)->timestamp;
    }

    /**
     * @return array<string, mixed>
     */
    public function exchangeAuthorizationCode(string $code): array
    {
        $response = Http::asForm()->post(self::OAUTH_TOKEN_URL, [
            'code' => $code,
            'client_id' => config('services.google_business_profile.client_id'),
            'client_secret' => config('services.google_business_profile.client_secret'),
            'redirect_uri' => $this->getRedirectUri(),
            'grant_type' => 'authorization_code',
        ]);

        return $this->decodeResponse($response, 'Nie udało się pobrać tokenów Google Business Profile.');
    }

    public function refreshAccessToken(GoogleBusinessProfileConnection $connection): GoogleBusinessProfileConnection
    {
        if (! $connection->hasRefreshToken()) {
            throw new RuntimeException('Brakuje refresh tokena Google Business Profile.');
        }

        if ($connection->token_expires_at?->isFuture() && filled($connection->accessTokenValue())) {
            return $connection;
        }

        $response = Http::asForm()->post(self::OAUTH_TOKEN_URL, [
            'client_id' => config('services.google_business_profile.client_id'),
            'client_secret' => config('services.google_business_profile.client_secret'),
            'refresh_token' => $connection->refreshTokenValue(),
            'grant_type' => 'refresh_token',
        ]);

        $payload = $this->decodeResponse($response, 'Nie udało się odświeżyć tokena Google Business Profile.');

        $connection->fill([
            'access_token' => $payload['access_token'] ?? $connection->accessTokenValue(),
            'token_expires_at' => isset($payload['expires_in']) ? now()->addSeconds(max(((int) $payload['expires_in']) - 60, 60)) : $connection->token_expires_at,
            'scopes' => isset($payload['scope']) ? (string) $payload['scope'] : $connection->scopes,
        ])->save();

        return $connection->fresh();
    }

    public function syncAccountAndLocationOptions(GoogleBusinessProfileConnection $connection): GoogleBusinessProfileConnection
    {
        $accounts = $this->listAccounts($connection);
        $locations = collect($accounts)
            ->flatMap(fn (array $account): array => $this->listLocations($connection, $account['name'], $account['label']))
            ->values()
            ->all();

        $connection->fill([
            'available_accounts' => $accounts,
            'available_locations' => $locations,
        ]);

        if (! $connection->hasSelectedLocation() && count($locations) === 1) {
            $location = $locations[0];

            $connection->fill([
                'google_account_name' => $location['account_name'],
                'google_account_label' => $location['account_label'],
                'google_location_name' => $location['name'],
                'google_location_title' => $location['label'],
            ]);
        }

        $connection->save();

        return $connection->fresh();
    }

    /**
     * @return array<int, array{name:string,label:string}>
     */
    public function listAccounts(GoogleBusinessProfileConnection $connection): array
    {
        $payload = $this->authorizedRequest($connection, 'get', self::ACCOUNTS_URL);

        return collect($payload['accounts'] ?? [])
            ->map(fn (array $account): array => [
                'name' => (string) ($account['name'] ?? ''),
                'label' => (string) ($account['accountName'] ?? $account['name'] ?? 'Konto Google'),
            ])
            ->filter(fn (array $account): bool => filled($account['name']))
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{name:string,label:string,account_name:string,account_label:string}>
     */
    public function listLocations(GoogleBusinessProfileConnection $connection, string $accountName, string $accountLabel): array
    {
        $locations = [];
        $pageToken = null;

        do {
            $payload = $this->authorizedRequest($connection, 'get', self::LOCATION_URL.'/'.$accountName.'/locations', [
                'readMask' => 'name,title',
                'pageSize' => 100,
                'pageToken' => $pageToken,
            ]);

            foreach ($payload['locations'] ?? [] as $location) {
                $locationName = (string) ($location['name'] ?? '');

                $locations[] = [
                    'name' => $this->resolveReviewsParent($locationName, $accountName),
                    'business_information_name' => $locationName,
                    'label' => (string) ($location['title'] ?? $location['name'] ?? 'Lokalizacja Google'),
                    'account_name' => $accountName,
                    'account_label' => $accountLabel,
                ];
            }

            $pageToken = $payload['nextPageToken'] ?? null;
        } while (filled($pageToken));

        return array_values(array_filter($locations, fn (array $location): bool => filled($location['name'])));
    }

    /**
     * @return array{created:int,updated:int,skipped:int}
     */
    public function syncReviews(GoogleBusinessProfileConnection $connection, bool $publish = true): array
    {
        if (! $connection->hasSelectedLocation()) {
            throw new RuntimeException('Najpierw wybierz lokalizację Google Business Profile.');
        }

        $googleReviews = $this->listReviews($connection, $connection->google_location_name);
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $nextSort = ((int) Review::max('sort')) ?: 0;

        DB::transaction(function () use ($googleReviews, $publish, &$created, &$updated, &$skipped, &$nextSort): void {
            foreach ($googleReviews as $googleReview) {
                $mapped = $this->mapGoogleReviewToLocalReview($googleReview, $publish);

                if ($mapped === null) {
                    $skipped++;

                    continue;
                }

                $review = Review::query()
                    ->where('source', 'google_business_profile')
                    ->where('source_review_id', $mapped['source_review_id'])
                    ->first();

                if (! $review) {
                    $review = Review::query()
                        ->where('name', $mapped['name'])
                        ->where('date', $mapped['date'])
                        ->where('review', $mapped['review'])
                        ->first();
                }

                if ($review) {
                    $review->fill($mapped);
                    $review->save();
                    $updated++;

                    continue;
                }

                $nextSort++;
                $mapped['sort'] = $nextSort;

                Review::create($mapped);
                $created++;
            }
        });

        $connection->forceFill([
            'last_synced_at' => now(),
            'last_sync_error' => null,
        ])->save();

        return [
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listReviews(GoogleBusinessProfileConnection $connection, string $locationName): array
    {
        $reviews = [];
        $pageToken = null;

        do {
            $reviewsParent = $this->resolveReviewsParent($locationName, $connection->google_account_name);

            $payload = $this->authorizedRequest($connection, 'get', self::REVIEWS_URL.'/'.$reviewsParent.'/reviews', [
                'pageSize' => 50,
                'pageToken' => $pageToken,
            ]);

            foreach ($payload['reviews'] ?? [] as $review) {
                $reviews[] = $review;
            }

            $pageToken = $payload['nextPageToken'] ?? null;
        } while (filled($pageToken));

        return $reviews;
    }

    /**
     * @param  array<string, mixed>  $review
     * @return array<string, mixed>|null
     */
    public function mapGoogleReviewToLocalReview(array $review, bool $publish): ?array
    {
        $reviewId = trim((string) ($review['reviewId'] ?? ''));

        if ($reviewId === '') {
            return null;
        }

        $reviewer = Arr::get($review, 'reviewer', []);
        $name = trim((string) ($reviewer['displayName'] ?? ''));
        $comment = $this->normalizeGoogleReviewComment((string) ($review['comment'] ?? ''));

        try {
            $date = filled($review['createTime'] ?? null)
                ? Carbon::parse((string) $review['createTime'])->toDateString()
                : now()->toDateString();
        } catch (\Throwable) {
            $date = now()->toDateString();
        }

        return [
            'name' => $name !== '' ? $name : 'Anonimowa opinia',
            'source' => 'google_business_profile',
            'source_review_id' => $reviewId,
            'date' => $date,
            'amount' => 1,
            'rating' => $this->normalizeStarRating((string) ($review['starRating'] ?? 'FIVE')),
            'color' => $this->resolveReviewColor($name),
            'review' => $comment,
            'avatar_url' => trim((string) ($reviewer['profilePhotoUrl'] ?? '')) ?: null,
            'is_published' => $publish,
        ];
    }

    public function normalizeGoogleReviewComment(string $comment): string
    {
        $comment = trim(str_replace(["\r\n", "\r"], "\n", $comment));

        if ($comment === '') {
            return '';
        }

        if (str_contains($comment, '(Original)')) {
            return trim(Str::after($comment, '(Original)'));
        }

        return trim((string) preg_replace('/\n*\(Translated by Google\).*$/su', '', $comment));
    }

    public function resolveReviewColor(string $name): string
    {
        $palette = [
            'red',
            'orange',
            'amber',
            'yellow',
            'lime',
            'green',
            'emerald',
            'teal',
            'cyan',
            'sky',
            'blue',
            'indigo',
            'violet',
            'purple',
            'pink',
            'rose',
        ];

        $seed = sprintf('%u', crc32(mb_strtolower(trim($name) !== '' ? $name : 'anonim')));

        return $palette[((int) $seed) % count($palette)];
    }

    public function normalizeStarRating(string $rating): int
    {
        return match (strtoupper(trim($rating))) {
            'ONE' => 1,
            'TWO' => 2,
            'THREE' => 3,
            'FOUR' => 4,
            'FIVE' => 5,
            default => 5,
        };
    }

    /**
     * @param  array<string, scalar|null>  $query
     * @return array<string, mixed>
     */
    private function authorizedRequest(
        GoogleBusinessProfileConnection $connection,
        string $method,
        string $url,
        array $query = [],
    ): array {
        $connection = $this->refreshAccessToken($connection);
        $query = array_filter($query, fn ($value) => $value !== null && $value !== '');
        $accessToken = $connection->accessTokenValue();

        if (! filled($accessToken)) {
            throw new RuntimeException('Brakuje access tokena Google Business Profile.');
        }

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->{$method}($url, $query);

        return $this->decodeResponse($response, 'Google Business Profile API zwróciło błąd.', [
            'method' => strtoupper($method),
            'url' => $url,
            'query' => $query,
        ]);
    }

    private function resolveReviewsParent(string $locationName, ?string $accountName): string
    {
        $locationName = trim($locationName, '/');

        if ($locationName === '' || Str::startsWith($locationName, 'accounts/')) {
            return $locationName;
        }

        if (Str::startsWith($locationName, 'locations/') && filled($accountName)) {
            return trim((string) $accountName, '/').'/'.$locationName;
        }

        return $locationName;
    }

    private function signAuthorizationStatePayload(string $payload): string
    {
        return hash_hmac('sha256', $payload, (string) config('app.key'));
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string
    {
        return base64_decode(strtr($value, '-_', '+/')) ?: '';
    }

    /**
     * @return array<string, mixed>
     */
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
        $reasons = collect(Arr::get($googleError, 'errors', []))
            ->map(fn (array $error): string => trim(implode(': ', array_filter([
                $error['domain'] ?? null,
                $error['reason'] ?? null,
            ]))))
            ->filter()
            ->unique()
            ->implode(', ');
        $bodySnippet = Str::limit(trim(preg_replace('/\s+/', ' ', $response->body()) ?: ''), 500);

        $details = array_filter([
            'HTTP '.$response->status(),
            $status !== '' ? "status: {$status}" : null,
            filled($code) ? "code: {$code}" : null,
            $reasons !== '' ? "reason: {$reasons}" : null,
            ! is_array($payload) && $bodySnippet !== '' ? "response: {$bodySnippet}" : null,
        ]);

        Log::warning('Google Business Profile API request failed.', array_filter([
            ...$context,
            'status' => $response->status(),
            'google_error' => $googleError,
            'response_snippet' => $bodySnippet,
        ]));

        throw new RuntimeException($message.' ['.implode('; ', $details).']');
    }
}
