<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PortalUserResource\Pages\EditPortalUser;
use App\Filament\Resources\PortalUserResource\Pages\ListPortalUsers;
use App\Models\Contact;
use App\Models\PortalUser;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class PortalUserResource extends Resource implements HasShieldPermissions
{
    public static function getPermissionPrefixes(): array
    {
        return [
            'view_any',
            'view',
            'create',
            'update',
        ];
    }

    protected static ?int $navigationSort = 6;

    protected static ?string $slug = 'konta-portalu';

    protected static ?string $model = PortalUser::class;

    protected static ?string $recordTitleAttribute = 'email';

    protected static ?string $navigationLabel = 'Konta portalu';

    protected static ?string $modelLabel = 'Konto portalu';

    protected static ?string $pluralModelLabel = 'Konta portalu';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-identification';

    protected static string|\UnitEnum|null $navigationGroup = 'Administracja';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dane logowania')
                    ->schema([
                        Select::make('contact_id')
                            ->label('Kontakt')
                            ->relationship(
                                'contact',
                                'sort_name',
                                fn (Builder $query) => $query->orderBy('sort_name'),
                            )
                            ->getOptionLabelFromRecordUsing(fn (Contact $record): string => (string) ($record->sort_name ?: $record->label ?: $record->email ?: $record->id))
                            ->searchable(['sort_name', 'label', 'email'])
                            ->preload()
                            ->live()
                            ->required()
                            ->native(false)
                            ->afterStateUpdated(function (?string $state, Set $set): void {
                                if (! $state) {
                                    return;
                                }

                                $contact = Contact::find($state);

                                if (! $contact) {
                                    return;
                                }

                                $set('name', $contact->sort_name ?: $contact->label);

                                if (filled($contact->email)) {
                                    $set('email', $contact->email);
                                }
                            })
                            ->columnSpanFull(),

                        TextInput::make('name')
                            ->label('Nazwa')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->columnSpan(2),

                        Toggle::make('is_active')
                            ->label('Aktywne?')
                            ->default(true)
                            ->inline(false)
                            ->columnSpan(1),

                        TextInput::make('password')
                            ->label('Hasło')
                            ->password()
                            ->revealable()
                            ->required()
                            ->rule('min:8')
                            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->hintAction(
                                Action::make('generatePassword')
                                    ->label('Generuj hasło')
                                    ->action(fn (Set $set) => $set('password', (string) random_int(10000000, 99999999))),
                            )
                            ->visibleOn('create')
                            ->columnSpanFull(),
                    ])
                    ->columns(5),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nazwa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contact.sort_name')
                    ->label('Kontakt')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Aktywne?'),
                TextColumn::make('created_at')
                    ->label('Utworzono')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                Action::make('changePassword')
                    ->label('Zmiana hasła')
                    ->icon('heroicon-m-key')
                    ->iconButton()
                    ->color('warning')
                    ->modalWidth('md')
                    ->modalHeading('Zmień hasło')
                    ->schema([
                        TextInput::make('password')
                            ->label('Nowe hasło')
                            ->password()
                            ->revealable()
                            ->required()
                            ->rule('min:8')
                            ->confirmed(),
                        TextInput::make('password_confirmation')
                            ->label('Nowe hasło (potwierdź)')
                            ->password()
                            ->revealable()
                            ->required(),
                    ])
                    ->action(function (array $data, PortalUser $record): void {
                        $record->update([
                            'password' => Hash::make($data['password']),
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Hasło zostało zmienione')
                            ->send();
                    }),
                EditAction::make()
                    ->iconButton(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPortalUsers::route('/'),
            'edit' => EditPortalUser::route('/{record}/edit'),
        ];
    }
}
