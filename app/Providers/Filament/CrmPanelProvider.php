<?php

namespace App\Providers\Filament;

use App\Filament\Crm\Resources\CHFPotentialMatterResource as CrmPotentialMatterResource;
use App\Filament\Crm\Resources\LeadResource as CrmLeadResource;
use App\Filament\Website\Resources\Leads\LeadResource as WebsiteLeadResource;
use App\Filament\Website\Resources\Offers\OffersResource as WebsiteOfferResource;
use App\Http\Middleware\IsActiveUser;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Enums\ThemeMode;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class CrmPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('crm')
            ->domain(config('preda.domains.crm'))
            ->path('')
            ->sidebarFullyCollapsibleOnDesktop()
            ->globalSearch(false)
            ->brandLogo(fn () => view('logo'))
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->font('Manrope', provider: LocalFontProvider::class)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->discoverResources(in: app_path('Filament/Crm/Resources'), for: 'App\\Filament\\Crm\\Resources')
            ->resources([
                CrmLeadResource::class,
                CrmPotentialMatterResource::class,
                WebsiteLeadResource::class,
                WebsiteOfferResource::class,
            ])
            ->discoverPages(in: app_path('Filament/Crm/Pages'), for: 'App\\Filament\\Crm\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Crm/Widgets'), for: 'App\\Filament\\Crm\\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->navigationItems([
                NavigationItem::make('Szanse')
                    ->icon('heroicon-o-rectangle-stack')
                    ->url(fn (): string => CrmLeadResource::getUrl(panel: 'crm'))
                    ->hidden(fn (): bool => ! auth()->user()?->can('view_any_lead'))
                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.crm.resources.szanse.*')),

                NavigationItem::make('Potencjalne sprawy')
                    ->icon('heroicon-o-bookmark')
                    ->url(fn (): string => CrmPotentialMatterResource::getUrl(panel: 'crm'))
                    ->hidden(fn (): bool => ! auth()->user()?->can('view_any_c::h::f::potential::matter'))
                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.crm.resources.potencjalne.*')),
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
                FilamentShieldPlugin::make(),
            ])
            ->defaultThemeMode(ThemeMode::Light)
            ->authMiddleware([
                Authenticate::class,
                IsActiveUser::class,
            ]);
    }
}
