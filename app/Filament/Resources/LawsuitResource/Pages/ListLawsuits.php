<?php

namespace App\Filament\Resources\LawsuitResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\LawsuitResource;
use App\Filament\Support\PresetTab;
use App\Filament\AdvancedTables\Concerns\InteractsWithAdvancedTablePresetTabs;

class ListLawsuits extends ListRecords
{
    use InteractsWithAdvancedTablePresetTabs;


    protected static string $resource = LawsuitResource::class;

    public function getTabs(): array
    {
        return [
            'W toku' => PresetTab::make()
                ->modifyQueryUsing(fn ($query) => $query->whereNull('end_date'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->favorite()->default(),
            'Zakończone' => PresetTab::make()
                ->modifyQueryUsing(fn ($query) => $query->whereNotNull('end_date'))
                ->icon('heroicon-o-calendar-days')
                ->color('danger')
                ->favorite(),
            'Wszystkie' => PresetTab::make()
                // ->modifyQueryUsing(fn ($query) => $query->where('is_matter', 1)->where('is_archived', 0))
                ->icon('heroicon-o-list-bullet')
                ->favorite()->default(),
        ];
    }
}
