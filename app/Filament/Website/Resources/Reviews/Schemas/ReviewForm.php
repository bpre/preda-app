<?php

namespace App\Filament\Website\Resources\Reviews\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\ColorPicker;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make([
                    DatePicker::make('date')->required()->label('Data'),
                    TextInput::make('name')->required()->label('Autor opinii'),
                    TextInput::make('source')->label('Źródło')->disabled()->dehydrated(false),
                    TextInput::make('source_review_id')->label('ID opinii w źródle')->disabled()->dehydrated(false),
                    TextInput::make('avatar_url')->label('URL miniaturki')->url()->columnSpan(2),
                    Textarea::make('review')->label('Opinia')->columnSpan(2)->rows(10),
                    TextInput::make('amount')->required()->label('Liczba opinii'),
                    TextInput::make('rating')
                        ->required()
                        ->label('Ocena')
                        ->default(5)
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(5),
                    ColorPicker::make('color')->required()->label('Kolor'),
                    Toggle::make('is_published')->required()->label('Opublikowana?'),
                ])->columns(2)
            ]);
    }
}
