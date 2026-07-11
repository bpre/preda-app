<?php

namespace App\Filament\Resources\CHFMatterResource\Pages;

use App\Filament\AdvancedTables\Concerns\InteractsWithAdvancedTablePresetTabs;
use App\Filament\Resources\CHFMatterResource;
use App\Filament\Support\PresetTab;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCHFMatters extends ListRecords
{
    use InteractsWithAdvancedTablePresetTabs;

    protected static string $resource = CHFMatterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nową sprawę CHF'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'Otwarte' => PresetTab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('is_matter', 1)->where('is_archived', 0))
                ->icon('heroicon-o-folder-open')
                ->favorite()->default(),
            // 'Szanse' => PresetTab::make()
            //     ->modifyQueryUsing(fn ($query) => $query->where('is_matter', 0)->where('is_archived', 0)->orderBy('created_at', 'desc'))
            //     ->icon('heroicon-o-bookmark')
            //     ->favorite(),
            'Zarchiwizowane' => PresetTab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('is_matter', 1)->where('is_archived', 1))
                ->icon('heroicon-o-archive-box')
                ->favorite(),
            // 'Wszystkie' => PresetTab::make()
            //     ->modifyQueryUsing(fn ($query) => $query)
            //     ->icon('heroicon-o-list-bullet')
            //     ->favorite()
        ];
    }
}
