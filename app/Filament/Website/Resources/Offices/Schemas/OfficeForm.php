<?php

namespace App\Filament\Website\Resources\Offices\Schemas;

use Illuminate\Support\Str;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Utilities\Set;

class OfficeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Group::make([

                    TextInput::make('city')
                        ->label('Miasto')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Set $set, ?string $state)  {
                            $set('slug', Str::slug($state));
                        }),
                    TextInput::make('form_w')
                        ->label('Forma w...')
                        ->placeholder('w ...')
                        ->required(),
                    TextInput::make('slug')
                        ->label('URL')
                        ->required(),
                    Textarea::make('address')
                        ->label('Adres')
                        ->required(),
                    Toggle::make('is_headquarters')
                        ->label('Siedziba?')
                        ->required(),
                    Toggle::make('is_active')
                        ->label('Aktywny na stronie?')
                        ->default(true)
                        ->required(),
                    Select::make('director_id')
                        ->label('Osoba kierująca')
                        ->relationship(
                            name: 'director',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn ($query) => $query
                                ->where('is_lawyer', true)
                                ->orderBy('name'),
                        )
                        ->searchable()
                        ->preload()
                        ->required()
                        ->native(false)

                    ]),

                    Group::make([

                        RichEditor::make('description')
                            ->label('Opis')
                    ])



            ]);
    }
}
