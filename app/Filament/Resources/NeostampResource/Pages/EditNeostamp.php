<?php

namespace App\Filament\Resources\NeostampResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\NeostampResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNeostamp extends EditRecord
{
    protected static string $resource = NeostampResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
