<?php

namespace App\Filament\Resources\TaskResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions;
use App\Filament\Resources\TaskResource;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use AymanAlhattami\FilamentContextMenu\Traits\PageHasContextMenu;

class EditTask extends EditRecord
{

    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }


}
