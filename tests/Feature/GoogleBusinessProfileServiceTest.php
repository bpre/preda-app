<?php

namespace Tests\Feature;

use App\Models\Website\GoogleBusinessProfileConnection;
use App\Models\Website\Review;
use App\Services\Integrations\GoogleBusinessProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class GoogleBusinessProfileServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_business_profile_redirect_uri_can_be_configured_explicitly(): void
    {
        config([
            'services.google_business_profile.client_id' => 'client-id',
            'services.google_business_profile.client_secret' => 'client-secret',
            'services.google_business_profile.redirect_uri' => 'https://preda.info/website/integrations/google-business-profile/callback',
        ]);

        $service = app(GoogleBusinessProfileService::class);

        $this->assertSame(
            'https://preda.info/website/integrations/google-business-profile/callback',
            $service->getRedirectUri(),
        );

        $authorizationUrl = $service->getAuthorizationUrl('test-state');

        $this->assertStringContainsString(
            'redirect_uri=https%3A%2F%2Fpreda.info%2Fwebsite%2Fintegrations%2Fgoogle-business-profile%2Fcallback',
            $authorizationUrl,
        );
    }

    public function test_google_business_profile_callback_can_save_connection_without_authenticated_session(): void
    {
        config([
            'services.google_business_profile.client_id' => 'client-id',
            'services.google_business_profile.client_secret' => 'client-secret',
            'services.google_business_profile.redirect_uri' => 'https://preda.info/website/integrations/google-business-profile/callback',
        ]);

        $state = app(GoogleBusinessProfileService::class)->makeAuthorizationState();

        Http::fake([
            'oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'access-token',
                'refresh_token' => 'refresh-token',
                'expires_in' => 3600,
                'scope' => 'https://www.googleapis.com/auth/business.manage',
            ]),
            'mybusinessaccountmanagement.googleapis.com/v1/accounts' => Http::response([
                'accounts' => [],
            ]),
        ]);

        $response = $this->get(route('website.integrations.google-business-profile.callback', [
            'state' => $state,
            'code' => 'authorization-code',
        ]));

        $response->assertRedirect();

        $connection = GoogleBusinessProfileConnection::query()->first();

        $this->assertNotNull($connection);
        $this->assertTrue($connection->hasRefreshToken());
        $this->assertSame('https://www.googleapis.com/auth/business.manage', $connection->scopes);
    }

    public function test_sync_reviews_uses_account_scoped_reviews_parent(): void
    {
        Http::fake([
            'mybusiness.googleapis.com/v4/accounts/123/locations/456/reviews*' => Http::response([
                'reviews' => [
                    [
                        'reviewId' => 'review-1',
                        'starRating' => 'FIVE',
                        'createTime' => '2026-05-01T10:00:00Z',
                        'reviewer' => [
                            'displayName' => 'Jan Kowalski',
                            'profilePhotoUrl' => 'https://example.com/avatar.jpg',
                        ],
                        'comment' => implode("\n\n", [
                            'Polecam Kancelarię "Pręda". Pełen profesjonalizm.',
                            '(Translated by Google)',
                            'I recommend the Pręda Law Firm. Completely professional.',
                        ]),
                    ],
                ],
            ]),
        ]);

        $connection = GoogleBusinessProfileConnection::query()->create([
            'google_account_name' => 'accounts/123',
            'google_location_name' => 'locations/456',
            'access_token' => 'access-token',
            'refresh_token' => 'refresh-token',
            'token_expires_at' => now()->addHour(),
        ]);

        $result = app(GoogleBusinessProfileService::class)->syncReviews($connection);

        $this->assertSame(['created' => 1, 'updated' => 0, 'skipped' => 0], $result);
        $this->assertDatabaseHas(Review::class, [
            'source' => 'google_business_profile',
            'source_review_id' => 'review-1',
            'name' => 'Jan Kowalski',
            'review' => 'Polecam Kancelarię "Pręda". Pełen profesjonalizm.',
        ]);

        Http::assertSent(fn ($request): bool => str_starts_with(
            $request->url(),
            'https://mybusiness.googleapis.com/v4/accounts/123/locations/456/reviews',
        ));
    }

    public function test_google_api_errors_include_http_status_and_google_status(): void
    {
        Http::fake([
            'mybusiness.googleapis.com/v4/accounts/123/locations/456/reviews*' => Http::response([
                'error' => [
                    'code' => 403,
                    'status' => 'PERMISSION_DENIED',
                    'message' => 'The caller does not have permission.',
                    'errors' => [
                        [
                            'domain' => 'global',
                            'reason' => 'forbidden',
                        ],
                    ],
                ],
            ], 403),
        ]);

        $connection = GoogleBusinessProfileConnection::query()->create([
            'google_account_name' => 'accounts/123',
            'google_location_name' => 'locations/456',
            'access_token' => 'access-token',
            'refresh_token' => 'refresh-token',
            'token_expires_at' => now()->addHour(),
        ]);

        try {
            app(GoogleBusinessProfileService::class)->listReviews($connection, $connection->google_location_name);
            $this->fail('Expected Google Business Profile API error.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('The caller does not have permission.', $exception->getMessage());
            $this->assertStringContainsString('HTTP 403', $exception->getMessage());
            $this->assertStringContainsString('PERMISSION_DENIED', $exception->getMessage());
            $this->assertStringContainsString('global: forbidden', $exception->getMessage());
        }
    }

    public function test_google_review_comment_keeps_original_text_when_translation_is_first(): void
    {
        $mapped = app(GoogleBusinessProfileService::class)->mapGoogleReviewToLocalReview([
            'reviewId' => 'review-2',
            'starRating' => 'FIVE',
            'comment' => implode("\n\n", [
                '(Translated by Google)',
                'I recommend the Pręda Law Firm. Completely professional.',
                '(Original)',
                'Polecam Kancelarię "Pręda". Pełen profesjonalizm.',
            ]),
            'reviewer' => [
                'displayName' => 'Anna Nowak',
            ],
        ], true);

        $this->assertSame('Polecam Kancelarię "Pręda". Pełen profesjonalizm.', $mapped['review']);
    }
}
