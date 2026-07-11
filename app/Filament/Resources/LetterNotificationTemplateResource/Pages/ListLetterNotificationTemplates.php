<?php

namespace App\Filament\Resources\LetterNotificationTemplateResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\LetterNotificationTemplateResource;
use App\Filament\Support\PresetTab;
use App\Filament\AdvancedTables\Concerns\InteractsWithAdvancedTablePresetTabs;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLetterNotificationTemplates extends ListRecords
{
    use InteractsWithAdvancedTablePresetTabs;


    protected static string $resource = LetterNotificationTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'Przychodzące' => PresetTab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('letter_type', 'in'))
                ->icon('heroicon-o-inbox-arrow-down')
                ->color('danger')
                ->favorite()
                ->default(),
            'Wychodzące' => PresetTab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('letter_type', 'out'))
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->favorite(),
        ];
    }
}
