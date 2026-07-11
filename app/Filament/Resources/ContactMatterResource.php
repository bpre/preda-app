<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Closure;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkAction;
use App\Filament\Resources\ContactMatterResource\Pages\ListContactMatters;
use App\Filament\Resources\ContactMatterResource\Pages\CreateContactMatter;
use App\Filament\Resources\ContactMatterResource\Pages\EditContactMatter;
use App\Models\Matter;
use App\Models\Contact;
use App\Models\ContactMatter;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Validation\Rules\Unique;
use App\Filament\Resources\ContactMatterResource\Pages;

class ContactMatterResource extends Resource
{
    protected static ?string $model = ContactMatter::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Klienci spraw';

    protected static ?string $modelLabel = 'przypisanie klienta do sprawy';

    protected static ?string $pluralModelLabel = 'przypisania klientów do spraw';

    protected static string | \UnitEnum | null $navigationGroup = 'Administracja';

    protected static ?string $navigationParentItem = 'Powiadomienia (pisma)';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('matter_id')
                    ->label('Sprawa')
                    ->options(fn () => Matter::query()
                        ->orderBy('label')
                        ->pluck('label', 'id'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->rules([
                        fn ($record) => function (string $attribute, $value, Closure $fail) use ($record) {
                            $contactId = request()->input('data.contact_id');

                            if (! $contactId) {
                                return;
                            }

                            $query = ContactMatter::query()
                                ->where('matter_id', $value)
                                ->where('contact_id', $contactId);

                            if ($record) {
                                $query->where('id', '!=', $record->id);
                            }

                            if ($query->exists()) {
                                $fail('Ten klient jest już przypisany do tej sprawy.');
                            }
                        },
                    ]),

                Select::make('contact_id')
                    ->label('Klient')
                    ->options(fn () => Contact::query()
                        ->where('category', 'Kredytobiorca')
                        ->orderBy('last_name')
                        ->orderBy('first_name')
                        ->pluck('label', 'id'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->rules([
                        fn ($record) => function (string $attribute, $value, Closure $fail) use ($record) {
                            $matterId = request()->input('data.matter_id');

                            if (! $matterId) {
                                return;
                            }

                            $query = ContactMatter::query()
                                ->where('matter_id', $matterId)
                                ->where('contact_id', $value);

                            if ($record) {
                                $query->where('id', '!=', $record->id);
                            }

                            if ($query->exists()) {
                                $fail('Ten klient jest już przypisany do tej sprawy.');
                            }
                        },
                    ]),

                Toggle::make('receives_notifications')
                    ->label('Otrzymuje powiadomienia')
                    ->rule(function (Get $get) {
                        return function (string $attribute, $value, Closure $fail) use ($get) {
                            if (! $value) {
                                return;
                            }

                            $contactId = $get('contact_id');

                            if (! $contactId) {
                                $fail('Najpierw wybierz klienta.');

                                return;
                            }

                            if (! static::contactHasEmail($contactId)) {
                                $fail('Nie można włączyć powiadomień dla kontaktu bez adresu e-mail.');
                            }
                        };
                    })
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('contact.sort_name')
                    ->label('Klient')
                    ->searchable()
                    ->size('medium')
                    ->weight('bold')
                    ->sortable(),

                TextColumn::make('matter.label')
                    ->label('Sprawa')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('contact.email')
                    ->label('E-mail')
                    ->searchable()
                    ->placeholder('Brak'),

                ToggleColumn::make('receives_notifications')
                    ->label('Powiadomienia')
                    ->sortable()
                    ->updateStateUsing(function (ContactMatter $record, bool $state): bool {
                        if ($state && ! static::canEnableNotificationsForRecord($record)) {
                            static::sendMissingEmailNotification();
                            return false;
                        }

                        $record->update([
                            'receives_notifications' => $state,
                        ]);

                        return $state;
                    }),

                TextColumn::make('created_at')
                    ->label('Dodano')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([

            TernaryFilter::make('is_real_matter')
                ->label('Tylko przyjęte sprawy')
                ->queries(
                    true: fn ($query) => $query->whereHas('matter', fn ($q) => $q->where('is_matter', 1)),
                    false: fn ($query) => $query->whereHas('matter', fn ($q) => $q->where('is_matter', 0)),
                    blank: fn ($query) => $query,
                ),

            TernaryFilter::make('receives_notifications')
                    ->label('Otrzymuje powiadomienia'),

                TernaryFilter::make('has_email')
                    ->label('Ma e-mail')
                    ->queries(
                        true: fn ($query) => $query->whereHas('contact', fn ($q) => $q->whereNotNull('email')->where('email', '!=', '')),
                        false: fn ($query) => $query->whereHas('contact', fn ($q) => $q->whereNull('email')->orWhere('email', '')),
                        blank: fn ($query) => $query,
                    ),

                SelectFilter::make('matter_id')
                    ->label('Sprawa')
                    ->options(fn () => Matter::query()
                        ->orderBy('label')
                        ->pluck('label', 'id'))
                    ->searchable(),

                SelectFilter::make('contact_id')
                    ->label('Klient')
                    ->options(fn () => Contact::query()
                        ->where('category', 'Kredytobiorca')
                        ->orderBy('last_name')
                        ->orderBy('first_name')
                        ->pluck('label', 'id'))
                    ->searchable(),
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),

                BulkAction::make('enable_notifications')
                    ->label('Włącz powiadomienia')
                    ->icon('heroicon-o-bell')
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        $enabled = 0;
                        $skippedWithoutEmail = 0;

                        foreach ($records as $record) {
                            if (! $record instanceof ContactMatter) {
                                continue;
                            }

                            if (! static::canEnableNotificationsForRecord($record)) {
                                $skippedWithoutEmail++;

                                continue;
                            }

                            $record->update([
                                'receives_notifications' => true,
                            ]);

                            $enabled++;
                        }

                        if ($enabled > 0 && $skippedWithoutEmail === 0) {
                            Notification::make()
                                ->title('Włączono powiadomienia')
                                ->body('Zmieniono rekordów: ' . $enabled . '.')
                                ->success()
                                ->send();

                            return;
                        }

                        if ($enabled === 0 && $skippedWithoutEmail > 0) {
                            Notification::make()
                                ->title('Nie włączono powiadomień')
                                ->body('Pominięto ' . $skippedWithoutEmail . ' rekordów bez adresu e-mail.')
                                ->danger()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Włączono powiadomienia częściowo')
                            ->body('Włączono: ' . $enabled . '. Pominięto bez e-maila: ' . $skippedWithoutEmail . '.')
                            ->warning()
                            ->send();
                    }),

                BulkAction::make('disable_notifications')
                    ->label('Wyłącz powiadomienia')
                    ->icon('heroicon-o-bell-slash')
                    ->requiresConfirmation()
                    ->action(fn ($records) => $records->each->update([
                        'receives_notifications' => false,
                    ])),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContactMatters::route('/'),
            'create' => CreateContactMatter::route('/create'),
            'edit' => EditContactMatter::route('/{record}/edit'),
        ];
    }

    protected static function contactHasEmail(string $contactId): bool
    {
        $email = Contact::query()->whereKey($contactId)->value('email');

        return filled($email);
    }

    protected static function canEnableNotificationsForRecord(ContactMatter $record): bool
    {
        if (filled($record->contact?->email)) {
            return true;
        }

        if (! filled($record->contact_id)) {
            return false;
        }

        return static::contactHasEmail((string) $record->contact_id);
    }

    protected static function sendMissingEmailNotification(): void
    {
        Notification::make()
            ->title('Nie można włączyć powiadomień')
            ->body('Ten klient nie ma adresu e-mail.')
            ->danger()
            ->send();
    }
}
