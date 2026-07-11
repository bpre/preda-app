<?php

namespace App\Http\Controllers\Website\Admin;

use Throwable;
use Filament\Notifications\Notification;
use App\Http\Controllers\Controller;
use App\Filament\Website\Resources\Reviews\ReviewResource;
use App\Models\Website\GoogleBusinessProfileConnection;
use App\Services\Integrations\GoogleBusinessProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GoogleBusinessProfileCallbackController extends Controller
{
    public function __invoke(Request $request, GoogleBusinessProfileService $googleBusinessProfileService)
    {
        $expectedState = session()->pull('google_business_profile_oauth_state');
        $receivedState = (string) $request->query('state', '');
        $stateMatchesSession = $expectedState && hash_equals((string) $expectedState, $receivedState);
        $stateMatchesSignature = $googleBusinessProfileService->isAuthorizationStateValid($receivedState);

        if (! $stateMatchesSession && ! $stateMatchesSignature) {
            Log::warning('Google Business Profile OAuth state mismatch.', [
                'has_expected_state' => filled($expectedState),
                'has_received_state' => $receivedState !== '',
                'state_matches_session' => $stateMatchesSession,
                'state_matches_signature' => $stateMatchesSignature,
                'request_host' => $request->getHost(),
                'request_scheme' => $request->getScheme(),
                'redirect_uri' => $googleBusinessProfileService->getRedirectUri(),
            ]);

            Notification::make()
                ->danger()
                ->title('Nieprawidłowy stan autoryzacji Google')
                ->body('Spróbuj ponownie połączyć konto Google Business Profile.')
                ->send();

            return redirect(ReviewResource::getUrl(panel: 'cms'));
        }

        Log::info('Google Business Profile OAuth state validated.', [
            'matched_session' => $stateMatchesSession,
            'matched_signature' => $stateMatchesSignature,
            'request_host' => $request->getHost(),
            'request_scheme' => $request->getScheme(),
        ]);

        if ($request->filled('error')) {
            Log::warning('Google Business Profile OAuth returned an error.', [
                'error' => (string) $request->query('error'),
                'error_description' => (string) $request->query('error_description', ''),
            ]);

            Notification::make()
                ->danger()
                ->title('Google przerwało autoryzację')
                ->body((string) $request->query('error_description', $request->query('error')))
                ->send();

            return redirect(ReviewResource::getUrl(panel: 'cms'));
        }

        $code = (string) $request->query('code', '');

        if ($code === '') {
            Log::warning('Google Business Profile OAuth callback has no authorization code.', [
                'request_host' => $request->getHost(),
                'request_scheme' => $request->getScheme(),
            ]);

            Notification::make()
                ->danger()
                ->title('Brakuje kodu autoryzacji Google')
                ->send();

            return redirect(ReviewResource::getUrl(panel: 'cms'));
        }

        try {
            $tokens = $googleBusinessProfileService->exchangeAuthorizationCode($code);

            Log::info('Google Business Profile OAuth token exchange completed.', [
                'has_access_token' => filled($tokens['access_token'] ?? null),
                'has_refresh_token' => filled($tokens['refresh_token'] ?? null),
                'expires_in' => isset($tokens['expires_in']) ? (int) $tokens['expires_in'] : null,
                'has_scope' => filled($tokens['scope'] ?? null),
            ]);

            $connection = GoogleBusinessProfileConnection::query()->firstOrNew();
            $refreshToken = (string) ($tokens['refresh_token'] ?? $connection->refresh_token);

            $connection->fill([
                'access_token' => (string) ($tokens['access_token'] ?? ''),
                'refresh_token' => $refreshToken !== '' ? $refreshToken : null,
                'token_expires_at' => isset($tokens['expires_in']) ? now()->addSeconds(max(((int) $tokens['expires_in']) - 60, 60)) : null,
                'scopes' => (string) ($tokens['scope'] ?? $connection->scopes ?? ''),
                'connected_at' => now(),
            ])->save();

            Log::info('Google Business Profile connection saved.', [
                'connection_id' => $connection->getKey(),
                'has_refresh_token' => $connection->hasRefreshToken(),
                'connected_at' => $connection->connected_at?->toIso8601String(),
            ]);

            try {
                $connection = $googleBusinessProfileService->syncAccountAndLocationOptions($connection);

                if ($connection->hasRefreshToken()) {
                    Notification::make()
                        ->success()
                        ->title('Google Business Profile połączone')
                        ->body(
                            count($connection->available_locations ?? []) === 1
                                ? 'Połączenie działa. Jedyna dostępna lokalizacja została wybrana automatycznie.'
                                : 'Połączenie działa. Wybierz teraz profil Google na liście opinii w Filamencie.'
                        )
                        ->send();
                } else {
                    Notification::make()
                        ->warning()
                        ->title('Google Business Profile wymaga ponownego połączenia')
                        ->body('Google nie zwróciło refresh tokena. Kliknij „Dokończ połączenie Google”, aby ponownie przejść przez ekran zgody.')
                        ->send();
                }
            } catch (Throwable $exception) {
                if ($this->isQuotaExceededError($exception)) {
                    if ($connection->hasRefreshToken()) {
                        Notification::make()
                            ->warning()
                            ->title('Google Business Profile połączone, ale limit zapytań został wyczerpany')
                            ->body('Token został zapisany poprawnie. Odczekaj około minutę i kliknij potem „Odśwież profile Google” w panelu opinii.')
                            ->send();
                    } else {
                        Notification::make()
                            ->warning()
                            ->title('Google Business Profile wymaga ponownego połączenia')
                            ->body('Google nie zwróciło refresh tokena, a limit zapytań został wyczerpany. Kliknij potem „Dokończ połączenie Google”.')
                            ->send();
                    }
                } else {
                    throw $exception;
                }
            }
        } catch (Throwable $exception) {
            Log::error('Google Business Profile OAuth callback failed.', [
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
                'request_host' => $request->getHost(),
                'request_scheme' => $request->getScheme(),
                'redirect_uri' => $googleBusinessProfileService->getRedirectUri(),
            ]);

            Notification::make()
                ->danger()
                ->title('Nie udało się połączyć Google Business Profile')
                ->body($exception->getMessage())
                ->send();
        }

        return redirect(ReviewResource::getUrl(panel: 'cms'));
    }

    protected function isQuotaExceededError(Throwable $exception): bool
    {
        return str_contains(mb_strtolower($exception->getMessage()), 'quota exceeded');
    }
}
