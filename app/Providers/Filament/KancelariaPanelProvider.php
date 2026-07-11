<?php

namespace App\Providers\Filament;

use App\Filament\AdvancedTables\AdvancedTablesPlugin;
use App\Filament\Pages\Administration\BorrowersWithoutSex;
use App\Filament\Pages\Administration\ImportContactMatters;
use App\Filament\Pages\Administration\MattersWithoutNotificationRecipients;
use App\Filament\Pages\UserPreferences;
use App\Filament\Resources\CHFMatterResource;
use App\Filament\Resources\ContactMatterResource;
use App\Filament\Resources\ContactResource;
use App\Filament\Resources\CreditResource;
use App\Filament\Resources\DealResource;
use App\Filament\Resources\LawsuitResource;
use App\Filament\Resources\LetterResource;
use App\Filament\Resources\OtherMatterResource;
use App\Filament\Resources\PaymentResource;
use App\Http\Middleware\IsActiveUser;
use App\Support\FilamentContentLayout;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Actions\Action as FilamentAction;
use Filament\Enums\ThemeMode;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Platform;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use RalphJSmit\Filament\Notifications\FilamentNotifications;

class KancelariaPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('kancelaria')
            ->domain(config('preda.domains.kancelaria'))
            ->path('')
            ->sidebarFullyCollapsibleOnDesktop()
            ->sidebarWidth((string) config('filament-layout.sidebar_width'))
            ->brandLogo(fn () => view('logo'))
            ->login()
            ->maxContentWidth(FilamentContentLayout::defaultMaxContentWidth())
            ->colors([
                'primary' => Color::Sky,
                'silver' => Color::generateV3Palette('#ccc'),
            ])
            ->font('Manrope', provider: LocalFontProvider::class)
            ->viteTheme('resources/css/filament/kancelaria/theme.css')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->navigationItems([
                NavigationItem::make('Sprawy CHF')
                    ->icon('heroicon-o-rectangle-stack')
                    ->url(fn (): string => CHFMatterResource::getUrl(panel: 'kancelaria'))
                    ->hidden(fn (): bool => ! auth()->user()?->can('view_any_c::h::f::matter'))
                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.kancelaria.resources.chf.*')),

                NavigationItem::make('Sprawy inne')
                    ->icon('heroicon-o-rectangle-stack')
                    ->url(fn (): string => OtherMatterResource::getUrl(panel: 'kancelaria'))
                    ->hidden(fn (): bool => ! auth()->user()?->can('view_any_other::matter'))
                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.kancelaria.resources.sprawy-inne.*')),

                NavigationItem::make('Korespondencja')
                    ->icon('heroicon-o-envelope')
                    ->url(fn (): string => LetterResource::getUrl(panel: 'kancelaria'))
                    ->hidden(fn (): bool => ! auth()->user()?->can('view_any_letter'))
                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.kancelaria.resources.korespondencja.*')),

                NavigationItem::make('Powiadomienia (pisma)')
                    ->icon('heroicon-o-bell-alert')
                    ->group('Administracja')
                    ->sort(48)
                    ->url(function (): string {
                        $user = auth()->user();

                        return match (true) {
                            $user?->can('view_any_contact::matter') => ContactMatterResource::getUrl(panel: 'kancelaria'),
                            $user?->can('page_MattersWithoutNotificationRecipients') => MattersWithoutNotificationRecipients::getUrl(panel: 'kancelaria'),
                            $user?->can('page_ImportContactMatters') => ImportContactMatters::getUrl(panel: 'kancelaria'),
                            $user?->can('page_BorrowersWithoutSex') => BorrowersWithoutSex::getUrl(panel: 'kancelaria'),
                            default => '/',
                        };
                    })
                    ->hidden(function (): bool {
                        $user = auth()->user();

                        return ! (
                            $user?->can('view_any_contact::matter')
                            || $user?->can('page_MattersWithoutNotificationRecipients')
                            || $user?->can('page_ImportContactMatters')
                            || $user?->can('page_BorrowersWithoutSex')
                        );
                    })
                    ->isActiveWhen(fn (): bool => request()->routeIs(
                        'filament.kancelaria.resources.contact-matters.*',
                        'filament.kancelaria.pages.matters-without-notification-recipients',
                        'filament.kancelaria.pages.import-contact-matters',
                        'filament.kancelaria.pages.borrower-without-sexes',
                    )),

                NavigationItem::make('Kontakty')
                    ->icon('heroicon-o-users')
                    ->url(fn (): string => ContactResource::getUrl(panel: 'kancelaria'))
                    ->hidden(fn (): bool => ! auth()->user()?->can('view_any_contact'))
                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.kancelaria.resources.kontakty.*')),

                NavigationItem::make('Umowy kredytowe')
                    ->icon('heroicon-o-document-text')
                    ->url(fn (): string => CreditResource::getUrl(panel: 'kancelaria'))
                    ->hidden(fn (): bool => ! auth()->user()?->can('view_any_credit'))
                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.kancelaria.resources.umowy-kredytowe.*')),

                NavigationItem::make('Postępowania')
                    ->icon('heroicon-o-scale')
                    ->url(fn (): string => LawsuitResource::getUrl(panel: 'kancelaria'))
                    ->hidden(fn (): bool => ! auth()->user()?->can('view_any_lawsuit'))
                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.kancelaria.resources.postepowania.*')),

                NavigationItem::make('Płatności')
                    ->icon('heroicon-o-banknotes')
                    ->group('Zarządzanie')
                    ->url(fn (): string => PaymentResource::getUrl(panel: 'kancelaria'))
                    ->hidden(fn (): bool => ! auth()->user()?->can('view_list_payment'))
                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.kancelaria.resources.platnosci.*')),

                NavigationItem::make('Zlecenia')
                    ->icon('heroicon-o-document-text')
                    ->url(fn (): string => DealResource::getUrl(panel: 'kancelaria'))
                    ->hidden(fn (): bool => ! auth()->user()?->can('view_any_deal'))
                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.kancelaria.resources.zlecenia.*')),
            ])
            ->userMenuItems([
                FilamentAction::make('user-preferences')
                    ->label('Preferencje użytkownika')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->url(fn (): string => UserPreferences::getUrl(panel: 'kancelaria'))
                    ->sort(10),
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
                AdvancedTablesPlugin::make()
                    ->favoritesBarDefaultView(false)
                    ->persistActiveViewInSession()
                    ->resourceEnabled(false)
                    ->userViewsEnabled(false)
                    ->quickSaveMakeFavorite(false)
                    ->createUsingPresetView(false)
                    ->viewManagerInFavoritesBar(false),
                FilamentNotifications::make(),
                FilamentShieldPlugin::make(),
            ])
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->globalSearchFieldSuffix(fn (): ?string => match (Platform::detect()) {
                Platform::Mac => '⌘K',
                Platform::Windows, Platform::Linux => 'Ctrl+K',
                Platform::Other => null,
            })
            ->defaultThemeMode(ThemeMode::Light)
            ->databaseNotifications()
            ->authMiddleware([
                Authenticate::class,
                IsActiveUser::class,
            ]);
    }
}
