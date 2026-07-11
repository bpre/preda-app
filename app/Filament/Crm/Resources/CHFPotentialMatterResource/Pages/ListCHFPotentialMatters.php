<?php

namespace App\Filament\Crm\Resources\CHFPotentialMatterResource\Pages;

use App\Filament\AdvancedTables\Concerns\InteractsWithAdvancedTablePresetTabs;
use App\Filament\Crm\Resources\CHFPotentialMatterResource;
use App\Filament\Support\PresetTab;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCHFPotentialMatters extends ListRecords
{
    use InteractsWithAdvancedTablePresetTabs;

    protected static string $resource = CHFPotentialMatterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'Otwarte' => PresetTab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('is_archived', 0))
                ->icon('heroicon-o-folder-open')
                ->favorite()->default(),
            'Zarchiwizowane' => PresetTab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('is_archived', 1))
                ->icon('heroicon-o-archive-box')
                ->favorite(),
        ];
    }
}
