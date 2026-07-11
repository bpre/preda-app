<?php

namespace App\Filament\Resources\StageResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\StageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStages extends ListRecords
{
    protected static string $resource = StageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
