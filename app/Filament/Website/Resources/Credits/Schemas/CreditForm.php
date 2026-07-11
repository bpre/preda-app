<?php

namespace App\Filament\Website\Resources\Credits\Schemas;

use Filament\Schemas\Schema;
use App\Enums\Website\Currencies;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class CreditForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('credit_name')
                    ->label('Nazwa')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(8),
                TextInput::make('credit_year')
                    ->label('Rok')
                    ->required()
                    ->maxLength(10)
                    ->columnSpan(2),
                Select::make('credit_currency')
                    ->options(Currencies::class)
                    ->label('Waluta')
                    ->native(false)
                    ->required()
                    ->columnSpan(5),
                Select::make('credit_type')
                    ->label('Typ')
                    ->options([
                        'indeksowany' => 'indeksowany',
                        'denominowany' => 'denominowany',
                    ])
                    ->native(false)
                    ->required()
                    ->columnSpan(5),
                Toggle::make('is_published')
                    ->label('Publikuj na stronie')
                    ->columnSpanFull(),
                Repeater::make('clauses')
                    ->label('Klauzule')
                    ->schema(
                        [

                                Textarea::make('clause')
                                    ->label('Treść')
                                    ->columnSpan(3)
                                    ->rows(3),
                                TextInput::make('item')
                                    ->label('Jednostka'),

                        ]
                    )->columnSpanFull(),
                TextInput::make('sort')
                    ->required()
                    ->numeric()
                    ->default(0),
            ])->columns(10);
    }
}
