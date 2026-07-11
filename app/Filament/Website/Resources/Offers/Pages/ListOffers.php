<?php

namespace App\Filament\Website\Resources\Offers\Pages;

use App\Filament\Website\Resources\Offers\OffersResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOffers extends ListRecords
{
    protected static string $resource = OffersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
