<?php

namespace App\Traits;

use App\Services\Website\SitemapGenerator;

trait SitemapTrait {

    public function generateSitemap(): string
    {
        return app(SitemapGenerator::class)->generate();
    }
}
