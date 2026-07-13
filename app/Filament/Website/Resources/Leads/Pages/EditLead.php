<?php

namespace App\Filament\Website\Resources\Leads\Pages;

use App\Filament\Website\Resources\Leads\Actions\MarkLeadAsIncorrectlyQualifiedAction;
use App\Filament\Website\Resources\Leads\Actions\RejectLeadAction;
use App\Filament\Website\Resources\Leads\LeadResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLead extends EditRecord
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            RejectLeadAction::make(),
            MarkLeadAsIncorrectlyQualifiedAction::make(),
            DeleteAction::make(),
        ];
    }
}
