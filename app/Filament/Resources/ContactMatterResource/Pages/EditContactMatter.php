<?php

namespace App\Filament\Resources\ContactMatterResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\ContactMatterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContactMatter extends EditRecord
{
    protected static string $resource = ContactMatterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
