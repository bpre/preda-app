<?php

namespace App\Filament\Website\Resources\SentenceContentTemplates\Pages;

use App\Filament\Website\Resources\SentenceContentTemplates\SentenceContentTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSentenceContentTemplates extends ListRecords
{
    protected static string $resource = SentenceContentTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
