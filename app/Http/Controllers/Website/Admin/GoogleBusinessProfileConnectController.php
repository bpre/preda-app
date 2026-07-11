<?php

namespace App\Http\Controllers\Website\Admin;

use Filament\Notifications\Notification;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Services\Integrations\GoogleBusinessProfileService;
use App\Filament\Website\Resources\Reviews\ReviewResource;

class GoogleBusinessProfileConnectController extends Controller
{
    public function __invoke(GoogleBusinessProfileService $googleBusinessProfileService)
    {
        if (! $googleBusinessProfileService->isConfigured()) {
            Notification::make()
                ->danger()
                ->title('Brakuje konfiguracji Google Business Profile')
                ->body('Uzupełnij GOOGLE_BUSINESS_PROFILE_CLIENT_ID i GOOGLE_BUSINESS_PROFILE_CLIENT_SECRET w pliku .env.')
                ->send();

            return redirect(ReviewResource::getUrl(panel: 'cms'));
        }

        $state = $googleBusinessProfileService->makeAuthorizationState();

        session()->put('google_business_profile_oauth_state', $state);

        Log::info('Google Business Profile OAuth started.', [
            'request_host' => request()->getHost(),
            'request_scheme' => request()->getScheme(),
            'redirect_uri' => $googleBusinessProfileService->getRedirectUri(),
        ]);

        return redirect()->away($googleBusinessProfileService->getAuthorizationUrl($state));
    }
}
