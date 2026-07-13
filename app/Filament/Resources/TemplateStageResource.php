<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TemplateStageResource\Pages\ListTemplateStages;
use App\Models\TemplateStage;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TemplateStageResource extends Resource
{
    public const POTENTIAL_CATEGORY = 'Potencjalna';

    protected static ?int $navigationSort = 10;

    protected static ?string $slug = 'etapy';

    protected static ?string $model = TemplateStage::class;

    protected static ?string $navigationLabel = 'Domyślne etapy';

    protected static ?string $modelLabel = 'Domyślny etap';

    protected static ?string $pluralModelLabel = 'Domyślne etapy';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Administracja';

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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(fn (Builder $query): Builder => $query
                ->whereNull('category')
                ->orWhere('category', '!=', self::POTENTIAL_CATEGORY));
    }

    public static function kategorie(): array
    {
        return [
            self::POTENTIAL_CATEGORY => [
                'Pozyskanie klienta' => 'Pozyskanie klienta',
            ],
            'CHF' => [
                'Etap przedsądowy' => 'Etap przedsądowy',
                'I instancja' => 'I instancja',
                'II instancja' => 'II instancja',
                'Rozliczenie' => 'Rozliczenie',
            ],
            'O zapłatę' => [
                'I instancja' => 'I instancja',
                'II instancja' => 'II instancja',
                'Rozliczenie' => 'Rozliczenie',
            ],
            'Powództwo banku' => [],
            'Sprawy inne' => [],
        ];
    }

    public static function rodzaje_spraw(): array
    {
        return [
            self::POTENTIAL_CATEGORY => self::POTENTIAL_CATEGORY,
            'CHF' => 'Sprawa CHF',
            'O zapłatę' => 'O zapłatę',
            'Powództwo banku' => 'Powództwo banku',
            'Sprawy inne' => 'Sprawy inne',
        ];
    }

    public static function operationalKategorie(): array
    {
        return collect(static::kategorie())
            ->except(self::POTENTIAL_CATEGORY)
            ->all();
    }

    public static function operationalRodzajeSpraw(): array
    {
        return collect(static::rodzaje_spraw())
            ->except(self::POTENTIAL_CATEGORY)
            ->all();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category')
                    ->label('Rodzaj sprawy')
                    ->options(static::operationalRodzajeSpraw())
                    ->native(false)
                    ->live()
                    ->required()
                    ->default('CHF')
                    ->columnSpanFull(),
                Select::make('parent')
                    ->label('Kategoria')
                    ->options(fn (Get $get): array => static::operationalKategorie()[$get('category') ?: 'CHF'] ?? [])
                    ->native(false)
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('label')
                    ->label('Nazwa etapu')
                    ->required()
                    ->columnSpanFull(),
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
                TextColumn::make('label')
                    ->label('Nazwa')
                    ->searchable(),
                SelectColumn::make('category')
                    ->label('Rodzaj sprawy')
                    ->options(static::operationalRodzajeSpraw())
                    ->placeholder('-'),
                TextColumn::make('parent')
                    ->label('Kategoria'),
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
                ToggleColumn::make('is_chf_default')
                    ->disabled(fn (TemplateStage $record): bool => ! $record->is_active)
                    ->beforeStateUpdated(function (TemplateStage $record): void {
                        TemplateStage::query()
                            ->where('category', $record->category)
                            ->update(['is_chf_default' => false]);
                    })
                    ->afterStateUpdated(function (TemplateStage $record, bool $state): void {
                        if ($state) {
                            $record->update(['is_chf_default' => true]);
                        }
                    })
                    ->label('Domyślny?'),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Rodzaj sprawy')
                    ->options(static::operationalRodzajeSpraw())
                    ->native(false),
                SelectFilter::make('parent')
                    ->label('Kategorie')
                    ->options(static::operationalKategorie())
                    ->native(false),
                TernaryFilter::make('is_active')
                    ->label('Aktywne')
                    ->placeholder('Wszystkie')
                    ->trueLabel('Tylko aktywne')
                    ->falseLabel('Tylko nieaktywne'),
            ])
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->modalWidth('md'),
                DeleteAction::make()
                    ->iconButton()
                    ->hidden(fn (TemplateStage $record): bool => $record->stages()->exists()),
            ])
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
        ];
    }
}
