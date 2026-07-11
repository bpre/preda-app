<?php

namespace App\Filament\Resources\TemplateStageResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\TemplateStageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTemplateStages extends ListRecords
{
    protected static string $resource = TemplateStageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->modalWidth('md'),
        ];
    }
}
