<?php

namespace App\Filament\Website\Resources\Pipedrives\Pages;

use App\Filament\Website\Resources\Pipedrives\PipedriveResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPipedrive extends EditRecord
{
    protected static string $resource = PipedriveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
