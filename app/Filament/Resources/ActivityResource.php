<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\Pages\ListActivities;
use App\Models\Activity;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?int $navigationSort = 6;

    protected static ?string $slug = 'notatki-i-czynnosci';

    protected static ?string $navigationLabel = 'Notatki i czynności';

    protected static ?string $modelLabel = 'Notatka / czynność';

    protected static ?string $pluralModelLabel = 'Notatki i czynności';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('matter', fn (Builder $query): Builder => $query->where('is_matter', true))
            ->with(['matter.lawyer', 'creator']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('matter_id')
                    ->label('Sprawa')
                    ->relationship(
                        name: 'matter',
                        titleAttribute: 'label',
                        modifyQueryUsing: fn (Builder $query): Builder => $query->where('is_matter', true),
                    )
                    ->searchable()
                    ->native(false)
                    ->required()
                    ->columnSpanFull(),
                DatePicker::make('date')
                    ->label('Data')
                    ->required()
                    ->default(now()),
                Select::make('type')
                    ->label('Typ')
                    ->options(Activity::TYPE_LABELS)
                    ->default(Activity::TYPE_NOTE)
                    ->native(false)
                    ->required(),
                Textarea::make('description')
                    ->label('Opis')
                    ->required()
                    ->rows(5)
                    ->columnSpanFull(),
                Toggle::make('is_visible_for_client')
                    ->label('Widoczna dla klienta?')
                    ->live()
                    ->columnSpanFull(),
                DatePicker::make('visible_for_client_from')
                    ->label('Od kiedy?')
                    ->visible(fn (Get $get): bool => (bool) $get('is_visible_for_client'))
                    ->default(now()),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->columns([
                IconColumn::make('read_by_me')
                    ->label('Przeczytana?')
                    ->state(fn (Activity $record): bool => $record->isReadBy(auth()->user()))
                    ->boolean(),
                TextColumn::make('date')
                    ->label('Data')
                    ->date()
                    ->sortable()
                    ->weight(FontWeight::Bold),
                TextColumn::make('matter.label')
                    ->label('Sprawa')
                    ->searchable()
                    ->sortable()
                    ->url(fn (Activity $record): ?string => MatterResource::getEditUrlForMatter($record->matter))
                    ->weight(FontWeight::Bold),
                TextColumn::make('matter.lawyer.name')
                    ->label('Referat')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('type')
                    ->label('Typ')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => Activity::typeLabel($state))
                    ->color(fn (?string $state): string => match ($state) {
                        Activity::TYPE_PHONE_CALL, Activity::TYPE_MEETING => 'info',
                        Activity::TYPE_SMS, Activity::TYPE_EMAIL => 'warning',
                        Activity::TYPE_POST_MEETING_NOTE => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('description')
                    ->label('Opis')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('creator.name')
                    ->label('Dodał(a)')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Dodano')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort(fn (Builder $query): Builder => $query
                ->orderByDesc('date')
                ->orderByDesc('created_at'))
            ->filters([
                Filter::make('scopeMine')
                    ->toggle()
                    ->default(fn (): bool => auth()->user()?->is_lawyer === true)
                    ->label('Tylko mój referat')
                    ->query(fn (Builder $query): Builder => self::scopeMyReferat($query))
                    ->hidden(fn (): bool => auth()->user()?->is_lawyer !== true),
                Filter::make('unread')
                    ->toggle()
                    ->label('Tylko nieprzeczytane')
                    ->query(fn (Builder $query): Builder => self::scopeUnreadForCurrentUser($query)),
                SelectFilter::make('type')
                    ->label('Typ')
                    ->options(Activity::TYPE_LABELS)
                    ->native(false),
                SelectFilter::make('matter_id')
                    ->label('Sprawa')
                    ->relationship(
                        name: 'matter',
                        titleAttribute: 'label',
                        modifyQueryUsing: fn (Builder $query): Builder => $query->where('is_matter', true),
                    )
                    ->searchable()
                    ->native(false),
                TernaryFilter::make('is_visible_for_client')
                    ->label('Widoczna dla klienta')
                    ->native(false),
            ])
            ->recordActions([
                Action::make('markAsRead')
                    ->label('Oznacz jako przeczytaną')
                    ->icon('heroicon-o-check')
                    ->iconButton()
                    ->visible(fn (Activity $record): bool => ! $record->isReadBy(auth()->user()))
                    ->action(function (Activity $record): void {
                        $record->markAsReadBy(auth()->user());

                        Notification::make()
                            ->success()
                            ->title('Oznaczono jako przeczytaną.')
                            ->send();
                    }),
                EditAction::make()
                    ->modalHeading('Edytuj notatkę / czynność')
                    ->modalWidth('md')
                    ->iconButton()
                    ->mutateRecordDataUsing(function (array $data, Activity $record): array {
                        $record->markAsReadBy(auth()->user());

                        return $data;
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('markSelectedAsRead')
                        ->label('Oznacz jako przeczytane')
                        ->icon('heroicon-o-check')
                        ->action(function (Collection $records): void {
                            $records->each(function (Activity $record): void {
                                $record->markAsReadBy(auth()->user());
                            });

                            Notification::make()
                                ->success()
                                ->title('Oznaczono wybrane notatki jako przeczytane.')
                                ->send();
                        }),
                ]),
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = self::unreadCountForCurrentUser();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivities::route('/'),
        ];
    }

    private static function scopeMyReferat(Builder $query): Builder
    {
        $user = auth()->user();

        if (! $user?->is_lawyer) {
            return $query;
        }

        return $query->whereHas('matter', fn (Builder $query): Builder => $query->where('lawyer_id', $user->getKey()));
    }

    private static function scopeUnreadForCurrentUser(Builder $query): Builder
    {
        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        return $query
            ->where(fn (Builder $query): Builder => $query
                ->whereNull('created_by')
                ->orWhere('created_by', '!=', $user->getKey()))
            ->whereDoesntHave('readReceipts', fn (Builder $query): Builder => $query->where('user_id', $user->getKey()));
    }

    private static function unreadCountForCurrentUser(): int
    {
        $user = auth()->user();

        if (! $user?->is_lawyer) {
            return 0;
        }

        $query = Activity::query()
            ->whereHas('matter', fn (Builder $query): Builder => $query
                ->where('is_matter', true)
                ->where('lawyer_id', $user->getKey()));
        self::scopeUnreadForCurrentUser($query);

        return $query->count();
    }
}
