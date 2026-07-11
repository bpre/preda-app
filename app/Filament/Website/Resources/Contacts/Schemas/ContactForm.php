<?php

namespace App\Filament\Website\Resources\Contacts\Schemas;

use App\Enums\Website\ContactCategories;
use Illuminate\Support\Str;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class ContactForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ...static::fields()
            ]);
    }

   public static function fields($category = null): array
    {
        return [
               ToggleButtons::make('category')
                    ->required('Wybierz typ kontaktu')
                    ->options([
                        ContactCategories::SEDZIA->value => ContactCategories::SEDZIA->getLabel(),
                        ContactCategories::SAD->value => ContactCategories::SAD->getLabel(),
                    ])
                    ->label('Typ kontaktu')
                    ->inline()
                    ->grouped()
                    ->default($category ?: ContactCategories::SEDZIA->value)
                    ->live()
                    ->afterStateUpdated(function(Set $set, Get $get) {
                        $set('slug', '');
                        $set('label', '');
                    })
                    ->disabled($category ? true : false)
                    ->dehydrated(),

                Group::make()->schema([

                    TextInput::make('first_name')
                        ->label('Imię')
                        ->required()
                        ->requiredIf('typ', 'osoba')
                        ->maxLength(255),

                    TextInput::make('last_name')
                        ->label('Nazwisko')
                        ->required()
                        ->requiredIf('typ', 'osoba')
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            $set('slug', static::generateSlug($get('first_name'), $get('last_name')));
                        }),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->visible(fn (Get $get): string => $get('category') == ContactCategories::SEDZIA->value),

                Group::make()->schema([
                    TextInput::make('organization')
                        ->label('Pełna nazwa')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('label')
                        ->label('Nazwa skrócona')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            $set('slug', Str::slug($get('label')));
                        })
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->visible(fn (Get $get): string => $get('category') == ContactCategories::SAD->value),

                TextInput::make('slug')
                    ->label('URL')
                    ->unique(ignoreRecord: true)
                    ->alphaDash()
                    ->maxLength(255)
                    ->columnSpan(3)
        ];
    }


    protected static function generateSlug(?string $first_name, ?string $last_name): string
    {
        if (!$first_name || !$last_name) {
            return '';
        }

        return Str::slug($first_name . '-' . $last_name);
    }
}
