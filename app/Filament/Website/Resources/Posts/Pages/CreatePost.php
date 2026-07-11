<?php

namespace App\Filament\Website\Resources\Posts\Pages;

use App\Filament\Website\Resources\Posts\PostResource;
use App\Traits\SitemapTrait;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    use SitemapTrait;

    protected static string $resource = PostResource::class;

    protected function afterCreate(): void
    {
        $this->generateSitemap();
    }
}
