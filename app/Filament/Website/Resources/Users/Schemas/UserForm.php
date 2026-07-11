<?php

namespace App\Filament\Website\Resources\Users\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\DateTimePicker;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Group::make([

                    TextInput::make('name')
                        ->label('Imię i nazwisko')
                        ->required(),
                    TextInput::make('email')
                        ->label('E-mail')
                        ->email()
                        ->readonly(),
                    TextInput::make('website_title')
                        ->label('Tytuł zawodowy / stanowisko'),
                    RichEditor::make('website_description')
                        ->label('Opis'),
                    Toggle::make('website_is_published')
                        ->label('Wyświetlać na stronie?'),
                ])

            ]);
    }
}
