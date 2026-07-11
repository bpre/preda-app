<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Filament\Resources\UserResource\RelationManagers\TasksAssignedToRelationManager;
use App\Models\User;
use App\Support\PanelAccess;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'uzytkownicy';

    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'Użytkownicy';

    protected static ?string $pluralModelLabel = 'Użytkownicy';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

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

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Dane')
                    ->collapsible()
                    ->collapsed($schema->getOperation() === 'edit')
                    ->columnSpanFull()
                    ->schema(
                        [
                            TextInput::make('name')
                                ->label('Imię i nazwisko')
                                ->required()
                                ->maxLength(255)
                                ->columnspan(3),

                            TextInput::make('signature_title')
                                ->label('Funkcja w podpisie mailowym')
                                ->maxLength(255)
                                ->columnspan(3),

                            TextInput::make('name_genitive')
                                ->label('Imię i nazwisko (w dopełniaczu)')
                                ->maxLength(255)
                                ->columnspan(3),

                            TextInput::make('email')
                                ->label('E-mail')
                                ->email()
                                ->required()
                                ->maxLength(255)
                                ->columnspan(3),

                            TextInput::make('phone')
                                ->label('Numer telefonu')
                                ->required()
                                ->maxLength(255)
                                ->columnspan(3),

                            Toggle::make('is_employee')
                                ->inline(false)
                                ->live()
                                ->label('Pracownik?')
                                ->columnspan(3),

                            Toggle::make('is_lawyer')
                                ->inline(false)
                                ->disabled(fn (Get $get) => ! $get('is_employee'))
                                ->label('Prawnik?')
                                ->columnspan(3),

                            Toggle::make('is_active')
                                ->inline(false)
                                ->label('Aktywny?')
                                ->columnspan(3),

                            TextInput::make('password')
                                ->label('Hasło')
                                // ->password()
                                ->required()
                                ->maxLength(255)
                                ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                                ->dehydrated(fn ($state) => filled($state))
                                ->hintAction(
                                    Action::make('generatePassword')
                                        ->label('Generuj hasło')
                                        ->action(function (Set $set) {
                                            $set('password', rand(10000000, 99999999));
                                        })
                                )
                                ->visibleOn('create')
                                ->columnSpanFull(),

                            Select::make('roles')
                                ->label('Role')
                                ->relationship('roles', 'name')
                                ->preload()
                                ->multiple()
                                ->searchable()
                                ->columnSpanFull(),
                        ]
                    )->columns(12),

                Section::make('Dostęp do paneli')
                    ->description('Role Shield nadal określają szczegółowe uprawnienia wewnątrz paneli.')
                    ->collapsible()
                    ->columnSpanFull()
                    ->schema([
                        CheckboxList::make('panel_access')
                            ->label('Panele')
                            ->options(PanelAccess::options())
                            ->bulkToggleable()
                            ->columns(3)
                            ->helperText('Dostęp działa tylko dla aktywnych użytkowników oznaczonych jako pracownik. Super administrator ma dostęp do wszystkich paneli niezależnie od zaznaczeń.')
                            ->formatStateUsing(fn (?User $record): array => $record ? PanelAccess::directPanelsFor($record) : [])
                            ->columnSpanFull(),
                    ]),

                Section::make('Sprawy')
                    ->collapsible()
                    ->collapsed($schema->getOperation() === 'edit')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('matters')
                            ->relationship('matterUser')
                            ->label('')
                            // ->required()
                            ->addActionLabel('Dodaj sprawę')
                            ->simple(

                                Select::make('matter_id')
                                    ->label('')
                                    ->native(false)
                                    ->required()
                                    ->relationship('matter', 'label')
                                    ->searchable()
                            ),
                    ]),

                // Select::make('matters')
                //     ->label('Sprawy')
                //     ->relationship('matters', 'label')
                //     ->multiple()
                //     ->searchable()
                //     ->columnSpan(6),

            ])->columns(6);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('signature_title')
                    ->label('Funkcja')
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_employee')->label('Pracownik?'),
                ToggleColumn::make('is_lawyer')->label('Prawnik?'),
                ToggleColumn::make('is_active')->label('Aktywny?'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('Zmiana hasła')
                    ->icon('heroicon-m-key')
                    ->iconButton()
                    ->color('warning')
                    ->modalWidth('md')
                    ->hidden(fn ($record) => ! $record?->id)
                    ->modalHeading('Zmień hasło')
                    ->schema([
                        TextInput::make('password')
                            ->label('Nowe hasło')
                            ->required()
                            ->rule('min:8')
                            ->confirmed(),
                        TextInput::make('password_confirmation')
                            ->label('Nowe hasło (potwierdź)')
                            ->required(),
                    ])
                    ->action(
                        function (array $data, $record) {

                            $data['password'] = Hash::make($data['password']);
                            $record->update($data);

                            Notification::make()->success()->title('Hasło zostało zmienione')->send();

                            return $record;

                        }
                    ),
                EditAction::make()->iconButton(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            TasksAssignedToRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            // 'create' => Pages\CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
