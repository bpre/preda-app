<?php

namespace App\Filament\Resources\UserResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions;
use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Support\PresetTab;
use App\Filament\AdvancedTables\Concerns\InteractsWithAdvancedTablePresetTabs;

class ListUsers extends ListRecords
{
    use InteractsWithAdvancedTablePresetTabs;



    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->modalHeading('Nowy użytkownik'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'Pracownicy' => PresetTab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('is_employee', 1)->where('is_active', 1))
                ->icon('heroicon-o-user-group')
                ->color('success')
                ->favorite()->default(),
            'Byli pracownicy' => PresetTab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('is_employee', 1)->where('is_active', 0))
                ->icon('heroicon-o-user-group')
                ->color('danger')
                ->favorite(),
            'Klienci' => PresetTab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('is_employee', 0)->where('is_active', 1))
                ->icon('heroicon-o-user-group')
                ->color('info')
                ->favorite(),
            'Klienci (nieaktywni)' => PresetTab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('is_employee', 0)->where('is_active', 0))
                ->icon('heroicon-o-user-group')
                ->color('danger')
                ->favorite()
        ];
    }
}
