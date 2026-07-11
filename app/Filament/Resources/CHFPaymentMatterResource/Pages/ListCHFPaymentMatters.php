<?php

namespace App\Filament\Resources\CHFPaymentMatterResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Support\PresetTab;
use App\Filament\AdvancedTables\Concerns\InteractsWithAdvancedTablePresetTabs;
use App\Filament\Resources\CHFPaymentMatterResource;

class ListCHFPaymentMatters extends ListRecords
{
    use InteractsWithAdvancedTablePresetTabs;

    protected static string $resource = CHFPaymentMatterResource::class;


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
                ->favorite()
        ];
    }
}
