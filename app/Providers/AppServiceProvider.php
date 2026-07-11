<?php

namespace App\Providers;

use App\Facades\Website\Seo as SeoFacade;
use App\Filament\Website\Resources\Users\UserResource as WebsiteUserResource;
use App\Jobs\SendLetterNotificationJob;
use App\Models\BankMatter;
use App\Models\CHFMatter;
use App\Models\CHFPaymentMatter;
use App\Models\CHFPotentialMatter;
use App\Models\Comment;
use App\Models\Contact as KancelariaContact;
use App\Models\ContactLetter;
use App\Models\ContactMatter;
use App\Models\Credit;
use App\Models\Deal;
use App\Models\Lead as KancelariaLead;
use App\Models\Letter;
use App\Models\Matter;
use App\Models\Stage;
use App\Models\Task;
use App\Models\User;
use App\Models\Website\Contact as WebsiteContact;
use App\Models\Website\Sentence;
use App\Observers\BankMatterObserver;
use App\Observers\CHFMatterObserver;
use App\Observers\CHFPaymentMatterObserver;
use App\Observers\CHFPotentialMatterObserver;
use App\Observers\CommentObserver;
use App\Observers\ContactLetterObserver;
use App\Observers\ContactMatterObserver;
use App\Observers\ContactObserver as KancelariaContactObserver;
use App\Observers\CreditObserver;
use App\Observers\DealObserver;
use App\Observers\LetterObserver;
use App\Observers\MatterObserver;
use App\Observers\StageObserver;
use App\Observers\TaskObserver;
use App\Observers\Website\ContactObserver as WebsiteContactObserver;
use App\Observers\Website\SentenceObserver;
use App\Services\LetterNotificationQueueMonitor;
use App\Services\Website\Seo;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Filament\Support\Assets\Js;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\View\TablesRenderHook;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Rejestracja SEO jako singleton w kontenerze (obszar "website")
        $this->app->singleton('website.seo', function () {
            return new Seo;
        });

        FilamentColor::register([
            'danger' => Color::Red,
            'gray' => Color::Zinc,
            'info' => Color::Blue,
            'primary' => Color::Rose,
            'success' => Color::Green,
            'warning' => Color::Amber,
            'indigo' => Color::Indigo,
            'secondary' => Color::Cyan,
        ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureShieldPermissionKeys();

        Gate::before(function (object $user): ?bool {
            if (! $user instanceof User) {
                return null;
            }

            return $user->hasRole(config('filament-shield.super_admin.name', 'super_admin'))
                ? true
                : null;
        });

        FilamentAsset::register([
            Js::make('kancelaria-admin', resource_path('js/admin.js')),
        ]);

        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn (): ViewContract => view('head-noindex-nofollow'),
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn (): ViewContract => view('filament/hooks/primary-button-colors'),
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::STYLES_AFTER,
            fn (): ViewContract => view('filament/hooks/content-layout'),
        );

        FilamentView::registerRenderHook(
            TablesRenderHook::TOOLBAR_COLUMN_MANAGER_TRIGGER_AFTER,
            fn (): ViewContract => view('filament/hooks/table-width-toggle'),
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_START,
            fn (): ViewContract => view('filament/hooks/open-directory'),
        );

        $this->registerWebsiteThemeViews();

        Blade::componentNamespace('App\\View\\Components\\Theme', 'theme');
        $this->app['view']->addNamespace('theme', $this->websiteThemeViewPaths('theme'));

        Blade::componentNamespace('App\\View\\Components\\Section', 'section');
        $this->app['view']->addNamespace('section', $this->websiteThemeViewPaths('section'));

        Blade::componentNamespace('App\\View\\Components\\Partial', 'partial');
        $this->app['view']->addNamespace('partial', $this->websiteThemeViewPaths('partial'));

        $this->loadMigrationsFrom([
            database_path('migrations'),
            database_path('migrations/users'),
            database_path('migrations/website'),
            database_path('migrations/kancelaria'),
        ]);

        WebsiteContact::observe(WebsiteContactObserver::class);
        Sentence::observe(SentenceObserver::class);
        KancelariaContact::observe(KancelariaContactObserver::class);
        ContactMatter::observe(ContactMatterObserver::class);
        Matter::observe(MatterObserver::class);
        CHFMatter::observe(CHFMatterObserver::class);
        CHFPaymentMatter::observe(CHFPaymentMatterObserver::class);
        CHFPotentialMatter::observe(CHFPotentialMatterObserver::class);
        BankMatter::observe(BankMatterObserver::class);
        KancelariaLead::observe(MatterObserver::class);
        Credit::observe(CreditObserver::class);
        Letter::observe(LetterObserver::class);
        Stage::observe(StageObserver::class);
        Deal::observe(DealObserver::class);
        ContactLetter::observe(ContactLetterObserver::class);
        Task::observe(TaskObserver::class);
        Comment::observe(CommentObserver::class);

        Queue::before(function (JobProcessing $event): void {
            $this->touchLetterNotificationQueueHeartbeat($event->job?->getQueue());
        });

        Queue::after(function (JobProcessed $event): void {
            $this->touchLetterNotificationQueueHeartbeat($event->job?->getQueue());
        });

        Queue::failing(function (JobFailed $event): void {
            $this->touchLetterNotificationQueueHeartbeat($event->job?->getQueue());
        });

        // ## - SEO -- ##

        /**
         * Zapewnij zmienną $seo w KAŻDYM widoku,
         * ale uniknij rekurencji na naszym partialu `partial.seo`.
         */
        View::composer('*', function ($view) {
            // Nazwa aktualnie renderowanego widoku
            $name = method_exists($view, 'name') ? $view->name() : ($view->getName() ?? '');

            // 1) Nie wstrzykuj $seo do naszego partiala, żeby nie wywołać rekurencji.
            if ($name === 'partial.seo') {
                return;
            }

            // 2) Jeśli już jest ustawione, nic nie rób (np. nadpisane wcześniej).
            $data = $view->getData();
            if (array_key_exists('seo', $data)) {
                return;
            }

            // 3) Wstrzyknij gotowy HTML
            $view->with('seo', SeoFacade::render());
        });

        // Reset stanu po zakończeniu requestu
        app()->terminating(function () {
            SeoFacade::reset();
        });

        // ## - SEO -- ##

    }

    private function configureShieldPermissionKeys(): void
    {
        FilamentShield::buildPermissionKeyUsing(function (string $entity, string $affix, string $subject): string {
            if (
                str_starts_with($entity, 'App\\Filament\\Website\\Resources\\')
                && $entity !== WebsiteUserResource::class
            ) {
                return Str::studly($affix).':'.Str::studly($subject);
            }

            $legacySubject = [
                'BankMatter' => 'bank::matter',
                'CHFMatter' => 'c::h::f::matter',
                'CHFPaymentMatter' => 'c::h::f::payment::matter',
                'CHFPotentialMatter' => 'c::h::f::potential::matter',
                'ContactMatter' => 'contact::matter',
                'DatabaseNotification' => 'notification',
                'ExchangeRate' => 'exchange::rate',
                'LetterNotification' => 'letter::notification',
                'LetterNotificationTemplate' => 'letter::notification::template',
                'OtherMatter' => 'other::matter',
                'PortalUser' => 'portal::user',
                'TemplateStage' => 'template::stage',
            ][$subject] ?? Str::snake($subject);

            return Str::snake($affix).'_'.$legacySubject;
        });
    }

    private function registerWebsiteThemeViews(): void
    {
        $finder = $this->app['view']->getFinder();

        foreach (array_reverse($this->websiteThemeViewPaths()) as $path) {
            $finder->prependLocation($path);
        }
    }

    /**
     * @return array<int, string>
     */
    private function websiteThemeViewPaths(?string $suffix = null): array
    {
        $themes = array_unique(array_filter([
            $this->websiteThemeName(config('website.theme.active', 'flat')),
            $this->websiteThemeName(config('website.theme.fallback', 'flat')),
        ]));

        $paths = [];

        foreach ($themes as $theme) {
            $path = resource_path("views/themes/{$theme}");

            if ($suffix !== null) {
                $path .= "/{$suffix}";
            }

            if (is_dir($path)) {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    private function websiteThemeName(?string $theme): string
    {
        return preg_replace('/[^a-z0-9_-]/i', '', trim((string) $theme)) ?: 'flat';
    }

    protected function touchLetterNotificationQueueHeartbeat(?string $queue): void
    {
        if ($queue !== SendLetterNotificationJob::QUEUE) {
            return;
        }

        app(LetterNotificationQueueMonitor::class)->touchHeartbeat();
    }
}
