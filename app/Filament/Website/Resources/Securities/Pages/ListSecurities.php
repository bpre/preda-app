<?php

namespace App\Filament\Website\Resources\Securities\Pages;

use App\Filament\Website\Resources\Securities\SecurityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSecurities extends ListRecords
{
    protected static string $resource = SecurityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
