<?php

namespace App\Filament\Website\Resources\Leads\Pages;

use App\Filament\AdvancedTables\Concerns\InteractsWithAdvancedTablePresetTabs;
use App\Filament\Support\PresetTab;
use App\Filament\Website\Resources\Leads\LeadResource;
use App\Support\Crm\MarketingAgencyAccess;
use App\Support\Website\LeadStatuses;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLeads extends ListRecords
{
    use InteractsWithAdvancedTablePresetTabs;

    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        if (MarketingAgencyAccess::usesRestrictedLeadView()) {
            return [];
        }

        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        if (MarketingAgencyAccess::usesRestrictedLeadView()) {
            return [];
        }

        return [
            'Przed kwalifikacją' => PresetTab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('status', LeadStatuses::NEW))
                ->icon('heroicon-o-inbox-stack')
                ->favorite()
                ->default(),
            'Zakwalifikowane' => PresetTab::make()
                ->modifyQueryUsing(fn ($query) => $query->whereIn('status', [
                    LeadStatuses::QUALIFIED,
                    LeadStatuses::AUTOMATICALLY_QUALIFIED,
                ]))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->favorite(),
            'Odrzucone' => PresetTab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('status', LeadStatuses::REJECTED))
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->favorite(),
        ];
    }
}
