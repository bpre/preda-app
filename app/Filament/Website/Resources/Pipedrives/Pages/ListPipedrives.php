<?php

namespace App\Filament\Website\Resources\Pipedrives\Pages;

use App\Filament\Website\Resources\Pipedrives\PipedriveResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPipedrives extends ListRecords
{
    protected static string $resource = PipedriveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
