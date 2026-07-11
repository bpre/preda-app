<?php

namespace App\Filament\Website\Resources\Posts\Pages;

use App\Filament\Website\Resources\Posts\PostResource;
use App\Traits\SitemapTrait;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    use SitemapTrait;

    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->after(fn () => $this->generateSitemap()),
        ];
    }

    protected function afterSave(): void
    {
        $this->generateSitemap();
    }
}
