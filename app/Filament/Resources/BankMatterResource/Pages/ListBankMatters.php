<?php

namespace App\Filament\Resources\BankMatterResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\BankMatterResource;
use App\Filament\Support\PresetTab;
use App\Filament\AdvancedTables\Concerns\InteractsWithAdvancedTablePresetTabs;

class ListBankMatters extends ListRecords
{
    use InteractsWithAdvancedTablePresetTabs;

    protected static string $resource = BankMatterResource::class;

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
            'Zarchiwizowane' => PresetTab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('is_archived', 1))
                ->icon('heroicon-o-archive-box')
                ->favorite()
            // 'Wszystkie' => PresetTab::make()
            //     ->modifyQueryUsing(fn ($query) => $query)
            //     ->icon('heroicon-o-list-bullet')
            //     ->favorite()
        ];
    }

}
