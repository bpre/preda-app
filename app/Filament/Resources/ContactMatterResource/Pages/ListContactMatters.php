<?php

namespace App\Filament\Resources\ContactMatterResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\ContactMatterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContactMatters extends ListRecords
{
    protected static string $resource = ContactMatterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
