<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Actions\Action;
use Filament\Support\Enums\TextSize;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\LawsuitResource\Pages\ListLawsuits;
use App\Models\Lawsuit;
use Filament\Tables\Table;
use App\Forms\departamentForm;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\LawsuitResource\Pages;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use App\Filament\Resources\CHFMatterResource\RelationManagers\LawsuitsRelationManager;

// require_once('../app/Forms/departamentForm.php');


class LawsuitResource extends Resource
{
    protected static ?int $navigationSort = 5;
    protected static ?string $slug = 'postepowania';
    protected static ?string $model = Lawsuit::class;
    protected static ?string $navigationLabel = 'Postępowania';
    protected static ?string $modelLabel = 'Postępowanie';
    protected static ?string $pluralModelLabel = 'Postępowania';
    protected static bool $shouldRegisterNavigation = false;

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

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('start_date')
                    ->label('Data rozpoczęcia')
                    ->required(),
                DatePicker::make('end_date')
                    ->label('Data zakończenia'),
                Select::make('court_id')
                    ->label('Sąd')
                    ->createOptionForm(contactForm('Sąd'))
                    ->editOptionForm(contactForm())
                    ->relationship(
                        name: 'court',
                        titleAttribute: 'sort_name',
                        modifyQueryUsing: fn (Builder $query) => $query->where('category', 'sad'),
                    )
                    ->preload()
                    ->required()
                    ->searchable()
                    ->live()
                    ->columnSpan(2),
                Select::make('departament_id')
                    ->label('Wydział')
                    ->createOptionForm(fn (Get $get) => departamentForm::form($get('court_id')))
                    ->editOptionForm(fn (Get $get) => departamentForm::form($get('court_id')))
                    ->relationship(
                        name: 'departament',
                        titleAttribute: 'label',
                        modifyQueryUsing: fn (Builder $query, Get $get) => $query->where('contact_id', $get('court_id')),
                        )
                    ->preload()
                    ->native(false)
                    ->suffixAction(
                        Action::make('Kopiuj adres')
                            ->icon('heroicon-m-document-duplicate')
                            ->color('gray')
                            ->hidden(function ($record) {
                                if(!$record) {
                                    return true;
                                }
                                else {
                                    return !$record->court || !$record->court->organization || !$record->departament || !$record->departament->address || !$record->departament->zip_code || !$record->departament->city;
                                }
                            })
                            ->action(function ($record, $livewire) {
                                $livewire->dispatch('copy-to-clipboard', $record->court->organization. '
'.$record->departament->label.'
'.$record->departament->address.'
'.$record->departament->zip_code.' '.$record->departament->city);

                                Notification::make('')->title('Skopiowano adres do schowka.')->success()->send();
                            })
                    )
                    ->columnSpan(2),
                Select::make('judge_id')
                    ->label('Sędzia')
                    ->createOptionForm(contactForm('Sędzia'))
                    ->editOptionForm(contactForm())
                    ->relationship(
                        name: 'judge',
                        titleAttribute: 'sort_name',
                        modifyQueryUsing: fn (Builder $query) => $query->where('category', 'sedzia'),
                    )
                    ->preload()
                    ->searchable()
                    ->columnSpan(2),
                Select::make('instance')
                    ->label('Instancja')
                    ->required()
                    ->options([
                        'I instancja' => 'I instancja',
                        'II instancja' => 'II instancja',
                        'postępowanie kasacyjne' => 'postępowanie kasacyjne'
                    ])
                    ->native(false),
                TextInput::make('signature')
                    ->label('Sygnatura')
                    ->required()
                    ->maxLength(255)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('signature')
                    ->label('Sygnatura')
                    ->size(TextSize::Medium)
                    ->weight(FontWeight::Bold)
                    ->toggleable()->searchable()->sortable(),
                TextColumn::make('court.label')
                    ->label('Sąd')->toggleable()->searchable()->sortable(),
                    // ->description(fn (Lawsuit $record) => $record->departament ? $record->departament->label : ''),
                TextColumn::make('departament.label')
                    ->label('Wydział')->toggleable(isToggledHiddenByDefault: true)->searchable()->sortable()->placeholder('-'),
                TextColumn::make('judge.label')
                    ->label('Sędzia')->toggleable(isToggledHiddenByDefault: true)->placeholder('-')->searchable()->sortable(),
                TextColumn::make('instance')
                    ->label('Instancja')->toggleable(),
                TextColumn::make('matter.label')
                    ->label('Sprawa')->limit(30)->toggleable()->hiddenOn(LawsuitsRelationManager::class)->searchable(),
                TextColumn::make('start_date')
                    ->label('Data rozpoczęcia')->toggleable()->sortable(),
                TextColumn::make('end_date')
                    ->label('Data zakończenia')->toggleable()->sortable()
                    ->placeholder('w toku'),
            ])->defaultSort('start_date', 'desc')
            ->filters([

                // TernaryFilter::make('finished')
                //     ->label('Czy postępowanie zakończone?')
                //     ->placeholder('Pokaż wszystkie')
                //     ->trueLabel('Pokaż tylko zakończone')
                //     ->falseLabel('Pokaż tylko niezakończone')
                //     ->queries(
                //         true: fn (Builder $query) => $query->whereNotNull('end_date'),
                //         false: fn (Builder $query) => $query->whereNull('end_date'),
                //         blank: fn (Builder $query) => $query, // In this example, we do not want to filter the query when it is blank.
                //     ),


                SelectFilter::make('court')
                    ->label('Sąd')
                    ->relationship(
                        name: 'court',
                        titleAttribute: 'label',
                        // modifyQueryUsing: fn (Builder $query) => $query->where('kategoria', 'sad')
                    )
                    ->preload()
                    ->native(false)
                    ->searchable(),

                SelectFilter::make('judge')
                    ->label('Sędzia')
                    ->relationship(
                        name: 'judge',
                        titleAttribute: 'sort_name',
                        // modifyQueryUsing: fn (Builder $query) => $query->where('kategoria', 'sedzia')
                    )
                    ->preload()
                    ->native(false)
                    ->searchable(),

                SelectFilter::make('instance')
                    ->label('Instancja')
                    ->options([
                        'I instancja' => 'I instancja',
                        'II instancja' => 'II instancja',
                        'postępowanie kasacyjne' => 'postępowanie kasacyjne'
                    ])
                    ->native(false),
                DateRangeFilter::make('start_date')->label('Data rozpoczęcia')->withIndicator(),
                DateRangeFilter::make('end_date')->label('Data zakończenia')->withIndicator(),
                ])
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    // ->modalHeading('Edytuj postępowanie')
                    ->modalWidth('xl')
                    ->modalWidth('xl')
                    ->modalHeading(fn (Lawsuit $record) => 'Edytuj postępowanie: ' . $record->signature ),
                DeleteAction::make()->iconButton()
            ])
            ->defaultSort('start_date', 'desc');
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
            'index' => ListLawsuits::route('/'),
            // 'create' => Pages\CreateLawsuit::route('/create'),
            // 'edit' => Pages\EditLawsuit::route('/{record}/edit'),
        ];
    }
}
