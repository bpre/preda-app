<?php

namespace App\Filament\Resources\LetterResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\LetterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLetter extends EditRecord
{
    protected static string $resource = LetterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
