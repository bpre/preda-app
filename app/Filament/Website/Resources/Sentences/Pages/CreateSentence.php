<?php

namespace App\Filament\Website\Resources\Sentences\Pages;

use App\Filament\Website\Resources\Sentences\SentenceResource;
use App\Traits\SitemapTrait;
use Filament\Resources\Pages\CreateRecord;

class CreateSentence extends CreateRecord
{

    use SitemapTrait;
    protected static string $resource = SentenceResource::class;

    protected function afterSave(): void
    {
        $this->generateSitemap();
    }
}
