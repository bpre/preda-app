<?php

namespace App\Filament\Website\Resources\Posts\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Fieldset;
use App\FilamentPlugins\RichContent\RelativeLinks;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Group::make([
                    TextInput::make('title')
                        ->label('Tytuł')
                        ->required(),
                    Textarea::make('excerpt')
                        ->label('Zajawka')
                        ->rows(3)
                        ->required(),
                    RichEditor::make('content')
                        ->label('Treść')
                        ->required()
                        ->plugins([
                            RelativeLinks::make(),
                        ])

                ])->columnSpan(3),

                Group::make([
                    Fieldset::make()->schema([
                        Toggle::make('is_published')->label('Opublikowany?')->columnSpanFull(),
                        TextInput::make('slug')->label('URL')->required()->columnSpanFull(),

                        Select::make('author_id')
                            ->label('Autor')
                            ->relationship(
                                name: 'author',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query
                                    ->where('is_lawyer', true)
                                    ->orderBy('name'),
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),

                        DatePicker::make('date')->required()->label('Data'),

                        Select::make('category')
                            ->label('Kategoria')
                            ->options([
                                'blog' => 'blog',
                                'orzecznictwo' => 'orzecznictwo'
                            ])
                            ->default('blog')
                            ->native(false)
                            ->columnSpanFull(),

                        Textarea::make('metatitle')->required()->columnSpanFull(),
                        Textarea::make('metadescription')->required()->rows(4)->columnSpanFull(),

                        DatePicker::make('modified_at')->label('Aktualizacja'),
                        DatePicker::make('reviewed_at')->label('Przegląd'),

                        Repeater::make('alternative_slugs')
                            ->label('Alternatywne adresy URL')
                            ->simple(
                                TextInput::make('slug')->label('URL')
                            )
                            ->columnSpanFull()

                    ])->columns(2)
                ])->columnSpan(2)

            ])->columns(5);
    }
}
