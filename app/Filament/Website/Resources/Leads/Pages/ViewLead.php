<?php

namespace App\Filament\Website\Resources\Leads\Pages;

use App\Filament\Website\Resources\Leads\Actions\MarkLeadAsIncorrectlyQualifiedAction;
use App\Filament\Website\Resources\Leads\Actions\OpenOrCreatePotentialMatterAction;
use App\Filament\Website\Resources\Leads\Actions\RejectLeadAction;
use App\Filament\Website\Resources\Leads\LeadResource;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;

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
            ActionGroup::make([
                OpenOrCreatePotentialMatterAction::make(),
                RejectLeadAction::make(),
                MarkLeadAsIncorrectlyQualifiedAction::make(),
                EditAction::make(),
            ])->dropdownWidth(Width::Small),
        ];
    }

}
