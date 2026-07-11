<?php

namespace App\Providers\Filament;

use App\Filament\Website\Resources\Sentences\SentenceResource;
use App\Filament\Website\Widgets\PostsFreshnessWidget;
use App\Http\Middleware\IsActiveUser;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Enums\ThemeMode;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class CmsPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('cms')
            ->domain(config('preda.domains.cms'))
            ->path('')
            ->homeUrl(fn (): string => SentenceResource::getUrl(panel: 'cms'))
            ->authenticatedRoutes(function (): void {
                Route::get('/', fn () => redirect(SentenceResource::getUrl(panel: 'cms')))->name('home');
            })
            ->globalSearch(false)
            ->brandLogo(fn () => view('components.preda.logo-admin'))
            ->login()
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->font('Manrope', provider: LocalFontProvider::class)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->discoverResources(in: app_path('Filament/Website/Resources'), for: 'App\\Filament\\Website\\Resources')
            ->discoverPages(in: app_path('Filament/Website/Pages'), for: 'App\\Filament\\Website\\Pages')
            ->discoverWidgets(in: app_path('Filament/Website/Widgets'), for: 'App\\Filament\\Website\\Widgets')
            ->widgets([
                PostsFreshnessWidget::class,
            ])
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_BEFORE,
                fn () => view('filament.components.panel-switcher'),
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->defaultThemeMode(ThemeMode::Light)
            ->authMiddleware([
                Authenticate::class,
                IsActiveUser::class,
            ]);
    }
}
