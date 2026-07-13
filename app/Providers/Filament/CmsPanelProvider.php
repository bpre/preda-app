<?php

namespace App\Providers\Filament;

use App\Filament\Website\Resources\Banks\BankResource;
use App\Filament\Website\Resources\Cities\CityResource;
use App\Filament\Website\Resources\Contacts\ContactResource;
use App\Filament\Website\Resources\Credits\CreditResource;
use App\Filament\Website\Resources\Faqs\FaqResource;
use App\Filament\Website\Resources\Offices\OfficeResource;
use App\Filament\Website\Resources\Posts\PostResource;
use App\Filament\Website\Resources\Reviews\ReviewResource;
use App\Filament\Website\Resources\Securities\SecurityResource;
use App\Filament\Website\Resources\SentenceContentTemplates\SentenceContentTemplateResource;
use App\Filament\Website\Resources\Sentences\SentenceResource;
use App\Filament\Website\Resources\Users\UserResource;
use App\Filament\Website\Widgets\PostsFreshnessWidget;
use App\Http\Middleware\IsActiveUser;
use App\Support\Website\WebsiteFeatures;
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
use RalphJSmit\Filament\Notifications\FilamentNotifications;

class CmsPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('cms')
            ->domain(config('preda.domains.cms'))
            ->path('')
            ->sidebarFullyCollapsibleOnDesktop()
            ->homeUrl(fn (): string => SentenceResource::getUrl(panel: 'cms'))
            ->authenticatedRoutes(function (): void {
                Route::get('/', fn () => redirect(SentenceResource::getUrl(panel: 'cms')))->name('home');
            })
            ->globalSearch(false)
            ->brandLogo(fn () => view('logo'))
            ->login()
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->font('Manrope', provider: LocalFontProvider::class)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->resources([
                BankResource::class,
                CityResource::class,
                ContactResource::class,
                CreditResource::class,
                FaqResource::class,
                OfficeResource::class,
                PostResource::class,
                ReviewResource::class,
                SecurityResource::class,
                SentenceResource::class,
                UserResource::class,
                ...(WebsiteFeatures::sentenceContentGeneratorEnabled()
                    ? [SentenceContentTemplateResource::class]
                    : []),
            ])
            ->discoverPages(in: app_path('Filament/Website/Pages'), for: 'App\\Filament\\Website\\Pages')
            ->discoverWidgets(in: app_path('Filament/Website/Widgets'), for: 'App\\Filament\\Website\\Widgets')
            ->widgets([
                PostsFreshnessWidget::class,
            ])
            ->renderHook(
                PanelsRenderHook::TOPBAR_LOGO_AFTER,
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
                FilamentNotifications::make(),
            ])
            ->defaultThemeMode(ThemeMode::Light)
            ->databaseNotifications()
            ->authMiddleware([
                Authenticate::class,
                IsActiveUser::class,
            ]);
    }
}
