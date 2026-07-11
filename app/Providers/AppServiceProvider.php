<?php

namespace App\Providers;

use App\Services\Website\Seo;
use App\Models\Website\Contact;
use App\Models\Website\Sentence;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use App\Facades\Website\Seo as SeoFacade;
use App\Observers\Website\ContactObserver;
use App\Observers\Website\SentenceObserver;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentColor;

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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        FilamentColor::register([
            'primary' => Color::Rose
        ]);

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
        ]);

        Contact::observe(ContactObserver::class);
        Sentence::observe(SentenceObserver::class);

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
}
