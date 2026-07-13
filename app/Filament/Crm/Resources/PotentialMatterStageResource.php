<?php

namespace App\Filament\Crm\Resources;

use App\Filament\Crm\Resources\PotentialMatterStageResource\Pages\ListPotentialMatterStages;
use App\Models\TemplateStage;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PotentialMatterStageResource extends Resource
{
    public const CATEGORY = 'Potencjalna';

    protected static ?string $model = TemplateStage::class;

    protected static ?int $navigationSort = 20;

    protected static ?string $slug = 'etapy';

    protected static ?string $navigationLabel = 'Etapy potencjalnych spraw';

    protected static ?string $modelLabel = 'Etap potencjalnej sprawy';

    protected static ?string $pluralModelLabel = 'Etapy potencjalnych spraw';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-list-bullet';

    protected static string|\UnitEnum|null $navigationGroup = 'Administracja';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('category', self::CATEGORY);
    }

    public static function canViewAny(): bool
    {
        return self::userCan('view_any_potential_matter_stage');
    }

    public static function canCreate(): bool
    {
        return self::userCan('create_potential_matter_stage');
    }

    public static function canEdit(Model $record): bool
    {
        return self::userCan('update_potential_matter_stage');
    }

    public static function canDelete(Model $record): bool
    {
        return self::userCan('delete_potential_matter_stage');
    }

    public static function canDeleteAny(): bool
    {
        return self::userCan('delete_any_potential_matter_stage');
    }

    public static function canReorder(): bool
    {
        return self::userCan('reorder_potential_matter_stage');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('category')
                    ->default(self::CATEGORY)
                    ->dehydrated(),
                Select::make('parent')
                    ->label('Kategoria')
                    ->options(fn (): array => self::categories())
                    ->default('Pozyskanie klienta')
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
                Toggle::make('is_chf_default')
                    ->label('Domyślny?')
                    ->inline(false)
                    ->disabled(fn (Get $get): bool => ! (bool) $get('is_active'))
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
                    ->label('Domyślny?')
                    ->disabled(fn (TemplateStage $record): bool => ! $record->is_active)
                    ->beforeStateUpdated(function (TemplateStage $record, bool $state): void {
                        if (! $state) {
                            return;
                        }

                        TemplateStage::query()
                            ->where('category', self::CATEGORY)
                            ->update(['is_chf_default' => false]);
                    })
                    ->afterStateUpdated(function (TemplateStage $record, bool $state): void {
                        if ($state) {
                            $record->update(['is_chf_default' => true]);
                        }
                    }),
            ])
            ->filters([
                SelectFilter::make('parent')
                    ->label('Kategorie')
                    ->options(fn (): array => self::categories())
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

    public static function categories(): array
    {
        return [
            'Pozyskanie klienta' => 'Pozyskanie klienta',
        ];
    }

    private static function userCan(string $permission): bool
    {
        return auth()->user()?->can($permission) === true;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPotentialMatterStages::route('/'),
        ];
    }
}
