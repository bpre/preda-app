<?php

namespace App\Filament\Admin\Resources\Users\Tables;

use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Illuminate\Support\Facades\Hash;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ToggleColumn;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                ToggleColumn::make('is_employee')->label('Pracownik?'),
                ToggleColumn::make('is_lawyer')->label('Prawnik?'),
                ToggleColumn::make('is_active')->label('Aktywny?')
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
                    ->hidden(fn ($record) => !$record?->id)
                    ->modalHeading('Zmień hasło')
                    ->schema([
                        TextInput::make('password')
                            ->label('Nowe hasło')
                            ->required()
                            ->rule('min:8')
                            ->confirmed(),
                        TextInput::make('password_confirmation')
                            ->label('Nowe hasło (potwierdź)')
                            ->required()
                    ])
                    ->action(
                        function (array $data, $record) {

                            $data['password'] = Hash::make($data['password']);
                            $record->update($data);

                            Notification::make()->success()->title('Hasło zostało zmienione')->send();

                            return $record;

                        }
                    ),
            ]);
    }
}
