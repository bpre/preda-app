<?php

namespace App\Filament\Website\Resources\Securities\Pages;

use App\Filament\Website\Resources\Securities\SecurityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSecurity extends EditRecord
{
    protected static string $resource = SecurityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
