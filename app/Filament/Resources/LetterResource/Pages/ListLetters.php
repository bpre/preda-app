<?php

namespace App\Filament\Resources\LetterResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\LetterResource;
use App\Filament\Support\PresetTab;
use App\Filament\AdvancedTables\Concerns\InteractsWithAdvancedTablePresetTabs;
use Illuminate\Support\HtmlString;

class ListLetters extends ListRecords
{
    use InteractsWithAdvancedTablePresetTabs;


    protected static string $resource = LetterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->modalWidth('7xl')->createAnother(false)->modalHeading('Nowa korespondencja')->slideOver(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'Cała korespondencja' => PresetTab::make()
                ->icon('heroicon-o-list-bullet')
                ->favorite()->default(),
            'Przychodząca' => PresetTab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('type', 'in'))
                ->icon(new HtmlString('&darr;'))
                ->color('danger')
                ->favorite(),
            'Wychodząca' => PresetTab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('type', 'out'))
                ->icon(new HtmlString('&uarr;'))
                ->color('success')
                ->favorite(),
        ];
    }
}
