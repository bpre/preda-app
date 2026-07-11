<?php

namespace App\Facades\Website;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\Services\Website\Seo title(?string $title)
 * @method static \App\Services\Website\Seo description(?string $description)
 * @method static \App\Services\Website\Seo noSuffix(bool $state = true)
 * @method static string render()
 * @method static void reset()
 */
class Seo extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        // Klucz kontenera z AppServiceProvider::register()
        return 'website.seo';
    }
}
