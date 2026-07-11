<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\PaymentResource\Widgets\StatsOverview;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\PaymentResource;
use App\Filament\Support\PresetTab;
use App\Filament\AdvancedTables\Concerns\InteractsWithAdvancedTablePresetTabs;

class ListPayments extends ListRecords
{
    use InteractsWithAdvancedTablePresetTabs;


    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }


    public function getTabs(): array
    {
        return [
            'Zapłacone' => PresetTab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('is_paid', 1))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->favorite()->default(),
            'Po terminie' => PresetTab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('is_paid', 0)->where('deadline', '<', now()))
                ->icon('heroicon-o-calendar-days')
                ->color('danger')
                ->favorite(),
            'Przyszłe' => PresetTab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('is_paid', 0)->where('deadline', '>=', now()))
                ->icon('heroicon-o-star')
                ->color('warning')
                ->favorite(),
            'Potencjalne' => PresetTab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('is_paid', 0)->where('deadline', null))
                ->icon('heroicon-o-star')
                ->color('gray')
                ->favorite(),
            'Wszystkie' => PresetTab::make()
                // ->modifyQueryUsing(fn ($query) => $query->where('is_matter', 1)->where('is_archived', 0))
                ->icon('heroicon-o-list-bullet')
                ->favorite()->default(),
        ];
    }

}
