<?php

namespace App\Filament\Resources\OtherMatterResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\OtherMatterResource;
use App\Filament\Support\PresetTab;
use App\Filament\AdvancedTables\Concerns\InteractsWithAdvancedTablePresetTabs;

class ListOtherMatters extends ListRecords
{
    use InteractsWithAdvancedTablePresetTabs;

    protected static string $resource = OtherMatterResource::class;
    protected static ?string $title = 'Sprawy inne';

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
                ->modifyQueryUsing(fn ($query) => $query->where('is_matter', 1)->where('is_archived', 0))
                ->icon('heroicon-o-folder-open')
                ->favorite()->default(),
            'Szanse' => PresetTab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('is_matter', 0)->where('is_archived', 0))
                ->icon('heroicon-o-bookmark')
                ->favorite(),
            'Zarchiwizowane' => PresetTab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('is_archived', 1))
                ->icon('heroicon-o-archive-box')
                ->favorite(),
            'Wszystkie' => PresetTab::make()
                ->modifyQueryUsing(fn ($query) => $query)
                ->icon('heroicon-o-list-bullet')
                ->favorite()
        ];
    }
}
