<?php

namespace App\Filament\Resources\DoctemplateResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\DoctemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDoctemplates extends ListRecords
{
    protected static string $resource = DoctemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
