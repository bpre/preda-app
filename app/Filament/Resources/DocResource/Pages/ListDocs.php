<?php

namespace App\Filament\Resources\DocResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions;
use App\Filament\Resources\DocResource;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;

class ListDocs extends ListRecords
{
    protected static string $resource = DocResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()

        ];
    }
}
