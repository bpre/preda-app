<?php

namespace App\Filament\Resources\DepartamentResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\DepartamentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDepartament extends EditRecord
{
    protected static string $resource = DepartamentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
