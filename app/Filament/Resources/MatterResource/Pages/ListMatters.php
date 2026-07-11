<?php

namespace App\Filament\Resources\MatterResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\MatterResource;
use App\Filament\Support\PresetTab;
use App\Filament\AdvancedTables\Concerns\InteractsWithAdvancedTablePresetTabs;

class ListMatters extends ListRecords
{
    use InteractsWithAdvancedTablePresetTabs;


    protected static string $resource = MatterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'processing' => PresetTab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('id', '!=', 'processing'))
                ->favorite()->default(),
            'delivered' => PresetTab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('id', 0)),
        ];
    }
}
