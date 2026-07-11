<?php

namespace App\Filament\Resources\DoctemplateResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\DoctemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDoctemplate extends EditRecord
{
    protected static string $resource = DoctemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
