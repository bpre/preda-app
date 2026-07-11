<?php

namespace App\Filament\Website\Resources\PageSnapshots\Pages;

use App\Filament\Website\Resources\PageSnapshots\PageSnapshotResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPageSnapshot extends EditRecord
{
    protected static string $resource = PageSnapshotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
