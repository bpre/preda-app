<?php

namespace App\Filament\Resources\MatterResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions;
use App\Models\Matter;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\MatterResource;

class EditMatter extends EditRecord
{
    protected static string $resource = MatterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->hidden(fn (Matter $record) => $record->hasAnyRelation()),
        ];
    }
}
