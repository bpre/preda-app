<?php

namespace App\Filament\Website\Resources\SentenceContentTemplates\Pages;

use App\Filament\Website\Resources\SentenceContentTemplates\SentenceContentTemplateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSentenceContentTemplate extends EditRecord
{
    protected static string $resource = SentenceContentTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
