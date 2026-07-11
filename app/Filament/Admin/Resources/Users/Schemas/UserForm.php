<?php

namespace App\Filament\Admin\Resources\Users\Schemas;

use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Dane')
                    ->schema(
                    [
                        TextInput::make('name')
                        ->label('Imię i nazwisko')
                        ->required()
                        ->maxLength(255),

                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        Toggle::make('is_employee')
                            ->inline(false)
                            ->live()
                            ->label('Pracownik?'),

                        Toggle::make('is_lawyer')
                            ->inline(false)
                            ->disabled(fn(Get $get) => !$get('is_employee'))
                            ->label('Prawnik?'),

                        Toggle::make('is_active')
                            ->inline(false)
                            ->label('Aktywny?'),

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
                                    ->action(function(Set $set) {
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
                )->columns(3),

                // Section::make('Sprawy')
                //     ->collapsible()
                //     ->collapsed($form->getOperation() === "edit")
                //     ->schema([
                //         Repeater::make('matters')
                //             ->relationship('matterUser')
                //             ->label('')
                //             ->required()
                //             ->addActionLabel('Dodaj sprawę')
                //             ->simple(

                //                 Select::make('matter_id')
                //                     ->label('')
                //                     ->native(false)
                //                     ->required()
                //                     ->relationship('matter', 'label')
                //                     ->searchable()
                //             )
                //     ])

                // Select::make('matters')
                //     ->label('Sprawy')
                //     ->relationship('matters', 'label')
                //     ->multiple()
                //     ->searchable()
                //     ->columnSpan(6),

            ])->columns(1);
    }
}
