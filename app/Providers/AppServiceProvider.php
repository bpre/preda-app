<?php

namespace App\Providers;

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
use App\Services\Website\Seo;
use App\Services\LetterNotificationQueueMonitor;
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
use Filament\Support\Colors\Color;
use Filament\Support\Assets\Js;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use App\Facades\Website\Seo as SeoFacade;
use App\Observers\Website\ContactObserver as WebsiteContactObserver;
use App\Observers\Website\SentenceObserver;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\View\TablesRenderHook;
use Filament\View\PanelsRenderHook;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Rejestracja SEO jako singleton w kontenerze (obszar "website")
        $this->app->singleton('website.seo', function () {
            return new Seo();
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
