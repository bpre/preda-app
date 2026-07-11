<?php

namespace App\Filament\Resources\LetterNotificationTemplateResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\LetterNotificationTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLetterNotificationTemplate extends EditRecord
{
    protected static string $resource = LetterNotificationTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
