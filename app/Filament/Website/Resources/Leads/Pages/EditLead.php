<?php

namespace App\Filament\Website\Resources\Leads\Pages;

use App\Filament\Website\Resources\Leads\Actions\MarkLeadAsIncorrectlyQualifiedAction;
use App\Filament\Website\Resources\Leads\Actions\RejectLeadAction;
use App\Filament\Website\Resources\Leads\LeadResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditLead extends EditRecord
{
    protected static string $resource = LeadResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getRecord()->display_name
            ?: parent::getTitle();
    }

    public function getRecordTitle(): string|Htmlable
    {
        return $this->getRecord()->display_name
            ?: parent::getRecordTitle();
    }

    public function getBreadcrumb(): string
    {
        return $this->getRecord()->display_name
            ?: parent::getBreadcrumb();
    }

    protected function getHeaderActions(): array
    {
        return [
            RejectLeadAction::make(),
            MarkLeadAsIncorrectlyQualifiedAction::make(),
            DeleteAction::make(),
        ];
    }
}
