<?php

namespace App\Filament\Crm\Resources\PotentialMatterStageResource\Pages;

use App\Filament\Crm\Resources\PotentialMatterStageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPotentialMatterStages extends ListRecords
{
    protected static string $resource = PotentialMatterStageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('md'),
        ];
    }
}
