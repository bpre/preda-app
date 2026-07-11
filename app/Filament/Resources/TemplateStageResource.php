<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\TemplateStageResource\Pages\ListTemplateStages;
use Filament\Tables\Table;
use App\Models\TemplateStage;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use App\Filament\Resources\TemplateStageResource\Pages;

class TemplateStageResource extends Resource
{

    protected static ?int $navigationSort = 10;
    protected static ?string $slug = 'etapy';
    protected static ?string $model = TemplateStage::class;
    protected static ?string $navigationLabel = 'Domyślne etapy';
    protected static ?string $modelLabel = 'Domyślny etap';
    protected static ?string $pluralModelLabel = 'Domyślne etapy';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string | \UnitEnum | null $navigationGroup = 'Administracja';

    protected function shouldPersistTableColumnSearchInSession(): bool
    {
        return true;
    }
    protected function shouldPersistTableFiltersInSession(): bool
    {
        return true;
    }
    protected function shouldPersistTableSearchInSession(): bool
    {
        return true;
    }
    protected function shouldPersistTableSortInSession(): bool
    {
        return true;
    }

    public static function kategorie(){

        return [
            'Potencjalna' => [
                'Pozyskanie klienta' => 'Pozyskanie klienta',
            ],
            'CHF' => [
                'Etap przedsądowy' => 'Etap przedsądowy',
                'I instancja' => 'I instancja',
                'II instancja' => 'II instancja',
                'Rozliczenie' => 'Rozliczenie'
            ],
            'O zapłatę' => [
                'I instancja' => 'I instancja',
                'II instancja' => 'II instancja',
                'Rozliczenie' => 'Rozliczenie'
            ],
            'Powództwo banku' => [],
            'Sprawy inne' => []

        ];

    }

    public static function rodzaje_spraw(){

        return [
            'Potencjalna' => 'Potencjalna',
            'CHF' => 'Sprawa CHF',
            'O zapłatę' => 'O zapłatę',
            'Powództwo banku' => 'Powództwo banku',
            'Sprawy inne' => 'Sprawy inne'
        ];

    }

    public static function form(Schema $schema): Schema
    {
        return $schema
        ->components([
            Select::make('category')->label('Rodzaj sprawy')
                ->options(TemplateStageResource::rodzaje_spraw())
                ->native(false)
                ->live()
                ->columnSpanFull(),
            Select::make('parent')->label('Kategoria')
                ->options(fn(Get $get) => TemplateStageResource::kategorie()[$get('category') ? $get('category') : 'CHF'])
                ->native(false)
                ->required()->columnSpanFull(),
            TextInput::make('label')->label('Nazwa etapu')
                ->required()->columnSpanFull(),
            Toggle::make('is_active')
                ->label('Aktywny?')
                ->default(true)
                ->inline(false)
                ->columnSpanFull(),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            TextColumn::make('label')->label('Nazwa'),
            SelectColumn::make('category')
                ->label('Rodzaj sprawy')
                ->options(TemplateStageResource::rodzaje_spraw())
                ->placeholder('-'),
            TextColumn::make('parent')->label('Kategoria'),
            ToggleColumn::make('is_active')
                ->label('Aktywny?')
                ->afterStateUpdated(function (TemplateStage $record, bool $state): void {
                    if (! $state) {
                        $record->update([
                            'is_lead_default' => false,
                            'is_chf_default' => false,
                        ]);
                    }
                }),
            // ToggleColumn::make('is_lead_default')
            //     ->beforeStateUpdated(function ($record, $state) {
            //         TemplateStage::query()->update(['is_lead_default' => false]);
            //     })
            //     ->afterStateUpdated(function ($record, $state) {
            //         TemplateStage::query()->where('id', $record->id)->update(['is_lead_default' => true]);
            //     })
            //     ->label('Domyślny dla leadów?'),

            ToggleColumn::make('is_chf_default')
                ->disabled(fn (TemplateStage $record): bool => ! $record->is_active)
                ->beforeStateUpdated(function ($record, $state) {
                    TemplateStage::query()->where('category', $record->category)->update(['is_chf_default' => false]);
                })
                ->afterStateUpdated(function ($record, $state) {
                    TemplateStage::query()->where('id', $record->id)->update(['is_chf_default' => true]);
                })
                ->label('Domyślny?'),

        ])
            ->filters([
                SelectFilter::make('category')->label('Rodzaj sprawy')->options(TemplateStageResource::rodzaje_spraw())->native(false),
                SelectFilter::make('parent')->label('Kategorie')->options(TemplateStageResource::kategorie())->native(false),
                TernaryFilter::make('is_active')
                    ->label('Aktywne')
                    ->placeholder('Wszystkie')
                    ->trueLabel('Tylko aktywne')
                    ->falseLabel('Tylko nieaktywne'),
            ])
            ->recordActions([
                EditAction::make()->iconButton()->modalWidth('md'),
                DeleteAction::make()->iconButton(),
            ])
            // ->defaultGroup('parent')
            // ->groups([
            //     Group::make('parent')
            //         // ->label('Etap')
            //         // ->collapsible()
            //         ->orderQueryUsing(fn (Builder $query, string $direction) => $query->orderBy('parent_sort', $direction)),

            // ])
            ->reorderable('sort')
            ->defaultSort('sort')
            ->paginated(false);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTemplateStages::route('/'),
            // 'create' => Pages\CreateTemplateStage::route('/create'),
            // 'edit' => Pages\EditTemplateStage::route('/{record}/edit'),
        ];
    }
}
