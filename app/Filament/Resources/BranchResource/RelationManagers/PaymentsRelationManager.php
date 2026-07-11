<?php

namespace App\Filament\Resources\BranchResource\RelationManagers;

use App\Filament\AdvancedTables\Concerns\InteractsWithAdvancedTablePresetTabs;
use App\Filament\Resources\PaymentResource;
use App\Filament\Support\PresetTab;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    use InteractsWithAdvancedTablePresetTabs;

    protected static string $relationship = 'chfPayments';

    protected static ?string $title = 'Płatności';

    protected static ?string $navigationLabel = 'Płatności';

    protected static ?string $modelLabel = 'Płatność';

    protected static ?string $pluralModelLabel = 'Płatności';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('label')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return PaymentResource::table($table);
    }

    public function getTabs(): array
    {
        return [
            'Zapłacone' => PresetTab::make()
                ->modifyQueryUsing(fn ($query) => $query->paid())
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->favorite()->default(),
            'Po terminie' => PresetTab::make()
                ->modifyQueryUsing(fn ($query) => $query->overdue())
                ->icon('heroicon-o-calendar-days')
                ->color('danger')
                ->favorite(),
            'Przyszłe' => PresetTab::make()
                ->modifyQueryUsing(fn ($query) => $query->future())
                ->icon('heroicon-o-star')
                ->color('warning')
                ->favorite(),
            'Potencjalne' => PresetTab::make()
                ->modifyQueryUsing(fn ($query) => $query->potential())
                ->icon('heroicon-o-star')
                ->color('gray')
                ->favorite(),
            'Wszystkie' => PresetTab::make()
                ->icon('heroicon-o-list-bullet')
                ->favorite()->default(),
        ];
    }
}
