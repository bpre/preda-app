<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\TextSize;
use Filament\Support\Enums\Width;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Schemas\Components\Fieldset;
use App\Filament\TaskComments\Actions\TaskCommentsAction;
use App\Filament\Resources\TaskResource\Pages\ListTasks;
use App\Models\Matter;
use App\Models\Task;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use App\Notifications\TaskDone;
use Filament\Resources\Resource;
use App\Notifications\TaskReopened;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Resources\TaskResource\Pages;
use Filament\Infolists\Components\RepeatableEntry;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;


class TaskResource extends Resource
{

    protected static ?int $navigationSort = 3;
    protected static ?string $slug = 'zadania';
    // protected static ?string $recordTitleAttribute = 'label';
    protected static ?string $navigationLabel = 'Zadania';
    protected static ?string $modelLabel = 'Zadanie';
    protected static ?string $pluralModelLabel = 'Zadania';
    protected static ?string $model = Task::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-list-bullet';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Select::make('matter_id')
                    ->label('Sprawa')
                    ->relationship(name: 'matter', titleAttribute: 'label')
                    ->searchable()
                    ->columnSpanFull()
                    ->live(),

                TextInput::make('label')
                    ->label('Zadanie')
                    ->required(),

                Group::make()->schema([
                    Select::make('priority')
                    ->label('Priorytet')
                    ->native(false)
                    ->default(2)
                    ->options([
                        '1' => 'Niski',
                        '2' => 'Średni',
                        '3' => 'Wysoki'
                    ]),
                    DatePicker::make('not_show_before')->label('Nie pokazuj zadania przed:')
                ])->columns(2),

                Group::make()->schema([
                    Toggle::make('is_private')
                        ->label('Zadanie własne?')
                        ->default(true)
                        ->inline(false)
                        ->live(),
                    Select::make('assigned_to')
                        ->label('Komu przypisać?')
                        ->relationship(
                            name: 'assignee',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn (Builder $query) => $query->where('is_active', 1)->where('is_employee', 1),
                        )
                        ->columnSpan(2)
                        ->native(false)
                        ->hidden(fn (Get $get) => $get('is_private')),
                ])->columns(4)->columnSpanFull(),

            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->recordClasses(fn (Task $record) => match ($record->done_at) {
                NULL => null,
                default => 'opacity-30',
            })
            ->columns([
                ViewColumn::make('done_at')
                    ->label('')
                    ->action(function ($record) {

                        $recipient = User::find($record->created_by);

                        if($record->done_at)
                        {
                            $record->done_at = null;
                            $record->save();

                            Notification::make()->success()->title('Wznowiono wykonywanie zadania.')->send();

                            $recipient->notify(new TaskReopened($record));
                        }
                        else
                        {
                            $record->done_at = now();
                            $record->save();

                            Notification::make()->success()->title('Wykonano zadanie.')->send();

                            $recipient->notify(new TaskDone($record));

                        }

                    })
                    ->view('filament.tables.tasks-checkbox'),
                TextColumn::make('label')
                    ->label('Zadanie')
                     ->view('filament.tables.tasks-label')
                    ->searchable(),
                TextColumn::make('assignee.name')
                    ->label('Zadanie dla')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('task_creator.name')
                    ->label('Dodane przez')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('priority')
                    ->label('Priorytet')
                    ->badge()
                    ->color(fn (string $state) => match (strval($state)) {
                        '1' => 'info',
                        '3' => 'danger',
                        default => 'warning'
                    })
                    ->formatStateUsing(fn($state) => match(strval($state)) {
                        '1' => 'niski',
                        '3' => 'wysoki',
                        default => 'średni'
                    }),
                TextColumn::make('comments_count')
                    ->label('Komentarze')
                    ->badge(fn ($state): bool => ((int) $state) > 0)
                    ->color(fn ($state): ?string => ((int) $state) > 0 ? null : 'gray')
                    ->counts('comments')
                    ->formatStateUsing(fn ($state): string => ((int) $state) > 0 ? (string) $state : 'Brak.'),
                TextColumn::make('not_show_before')
                    ->label('Zaplanowane na')
                    ->placeholder('Najbliższy możliwy')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn($state) => substr($state, 0, 10))
                    ->size(TextSize::ExtraSmall),
                TextColumn::make('created_at')
                    ->label('Kiedy dodane')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->formatStateUsing(fn($state) => substr($state, 0, 10))
                    ->size(TextSize::ExtraSmall),
                TextColumn::make('done_at_date')
                    ->label('Kiedy wykonane')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('-')
                    ->state(fn (Task $record) => $record->done_at)
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query
                        ->orderByRaw('CASE WHEN done_at IS NULL THEN 0 ELSE 1 END')
                        ->orderBy('done_at', $direction)
                        ->orderBy('priority', 'desc')
                        ->orderBy('created_at'))
                    ->formatStateUsing(fn($state) => substr($state, 0, 10))
                    ->size(TextSize::ExtraSmall),
            ])
            ->defaultSort(function (Builder $query, $livewire): Builder {
                if (filled(data_get($livewire->tableFilters, 'matter_id.value'))) {
                    return $query
                        ->orderByRaw('CASE WHEN done_at IS NULL THEN 0 ELSE 1 END')
                        ->orderBy('done_at', 'desc')
                        ->orderBy('priority', 'desc')
                        ->orderBy('created_at');
                }

                return $query->orderByRaw('done_at, priority desc, created_at');
            })
            ->filters([
                Filter::make('c')
                ->schema([
                    Toggle::make('done_at')
                        ->label('Ukryj wykonane')
                        ->default(true),
                    Toggle::make('hide_planned')
                        ->label('Ukryj zaplanowane')
                        ->default(true)
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['done_at'],
                            fn (Builder $query): Builder => $query->whereNull('done_at')
                        )
                        ->when(
                            $data['hide_planned'],
                            fn (Builder $query): Builder => $query->where(function($query) {
                                    return $query->whereNull('not_show_before')->orWhereDate('not_show_before', '<=', now());
                                })
                        );
                })
                ->indicateUsing(function (array $data): array {

                    $indicators = [];

                    if($data['done_at'] ?? null) {
                        $indicators[] = Indicator::make('Ukryj wykonane')->removeField('done_at');
                    }

                    if($data['hide_planned'] ?? null) {
                        $indicators[] = Indicator::make('Ukryj zaplanowane')->removeField('hide_planned');
                    }

                    return $indicators;

                })->columnSpanFull()->columns(2),

                DateRangeFilter::make('created_at')
                    ->label('Kiedy dodane')
                    ->withIndicator(),
                DateRangeFilter::make('not_show_before')
                    ->label('Na kiedy zaplanowane')
                    ->withIndicator(),
                DateRangeFilter::make('done_at')
                    ->label('Kiedy wykonane')
                    ->withIndicator(),
                SelectFilter::make('assigned_to')
                    ->label('Komu zlecone')
                    ->native(false)
                    ->relationship(
                        name: 'assignee',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->where('is_active', 1)->where('is_employee', 1)
                    ),
                SelectFilter::make('matter_id')
                    ->label('Sprawa')
                    ->native(false)
                    ->relationship(
                        name: 'matter',
                        titleAttribute: 'label'
                    )
                    ->searchable()
                    ->columnSpanFull(),

            ])
            ->filtersFormWidth(Width::ExtraLarge)
            ->filtersFormColumns(2)
            ->recordActions([

                EditAction::make()
                    ->iconButton()
                    ->hidden(fn($record) => $record->created_by != auth()->user()->id)
                    ->extraModalFooterActions([
                        DeleteAction::make('Usuń zadanie')
                            ->color('danger')
                            ->requiresConfirmation()
                            ->cancelParentActions(),
                    ]),
                TaskCommentsAction::make(),

            ])->emptyStateHeading('Brak zadań.');
    }
    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([

                    Group::make()->schema([

                        Fieldset::make('')->schema([
                            TextEntry::make('label')
                            ->label('Zadanie'),
                            TextEntry::make('matter.label')
                                ->label(fn (Task $record): string => static::matterTypeLabel($record->matter))
                                ->url(fn (Task $record): ?string => static::matterUrl($record->matter))
                                ->hidden(fn($record) => empty($record->matter)),
                            Group::make()->schema([
                                TextEntry::make('priority')
                                    ->label('Priorytet')
                                    ->badge()
                                    ->color(fn (string $state) => match (strval($state)) {
                                        '1' => 'info',
                                        '3' => 'danger',
                                        default => 'warning'
                                    })
                                    ->formatStateUsing(fn($state) => match(strval($state)) {
                                        '1' => 'niski',
                                        '3' => 'wysoki',
                                        default => 'średni'
                                    }),
                            ])->columns(2),
                            Group::make()->schema([
                                TextEntry::make('is_private')
                                    ->label('Zadanie własne')
                                    ->formatStateUsing(fn($state) => match($state) {
                                        true => 'tak', false => 'nie'
                                    })
                                    ->hidden(fn ($state) => $state === false),
                                TextEntry::make('task_creator.name')
                                    ->label('Zadanie od')
                                    ->hidden(fn ($record) => $record->is_private),
                                TextEntry::make('assignee.name')
                                    ->label('Zadanie dla')
                                    ->hidden(fn($record) => $record->is_private == 1)
                            ])->columns(2),
                            Group::make()->schema([
                                TextEntry::make('created_at')
                                    ->label('Utworzono')
                                    ->placeholder('-'),
                                TextEntry::make('done_at')
                                    ->label('Wykonano')
                                    ->placeholder('-')
                            ])->columns(2)
                        ])->columns(1)

                    ]),

                    Group::make()->schema([
                        RepeatableEntry::make('comments')
                            ->label('Komentarze')
                            ->placeholder('Brak komentarzy.')
                            ->schema([
                                Group::make()->schema([
                                    TextEntry::make('user.name')
                                        ->label('Autor')
                                        ->placeholder('-'),
                                    TextEntry::make('created_at')
                                        ->label('Dodano')
                                        ->dateTime('Y-m-d H:i')
                                        ->placeholder('-'),
                                ])->columns(2),
                                TextEntry::make('comment')
                                    ->label('Komentarz')
                                    ->columnSpanFull(),
                            ])
                            ->columns(1)
                    ])




            ]);
    }

    public static function matterTypeLabel(?Matter $matter): string
    {
        return $matter && ! $matter->is_matter
            ? 'Potencjalna sprawa'
            : 'Sprawa';
    }

    public static function matterUrl(?Matter $matter): ?string
    {
        return MatterResource::getEditUrlForMatter($matter);
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
            'index' => ListTasks::route('/'),
            // 'create' => Pages\CreateTask::route('/create'),
            // 'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
