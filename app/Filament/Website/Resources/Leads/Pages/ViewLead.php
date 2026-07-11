<?php

namespace App\Filament\Website\Resources\Leads\Pages;

use App\Filament\Website\Resources\Leads\Actions\ChangeLeadStatusAction;
use App\Filament\Website\Resources\Leads\Actions\GenerateLeadResponseAction;
use App\Filament\Website\Resources\Leads\LeadResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLead extends ViewRecord
{
    protected static string $resource = LeadResource::class;

    public function getTitle(): string
    {
        return $this->getRecord()->{static::$resource::getRecordTitleAttribute()};
    }

    protected function getHeaderActions(): array
    {
        return [
            ChangeLeadStatusAction::make(),
            GenerateLeadResponseAction::make(),
            EditAction::make(),
        ];
    }

}
