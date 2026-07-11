<?php

namespace App\Filament\Website\Resources\Banks\Schemas;

use Illuminate\Support\Str;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use App\FilamentPlugins\RichContent\RelativeLinks;

class BankForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('bank')
                    ->label('Bank')
                    ->required(),
                TextInput::make('label')
                    ->label('Bank (nazwa skrócona')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function($state, Set $set, Get $get) {
                        if(empty($get('slug')))
                        {
                            $set('slug', Str::slug($state));
                        }
                    }),
                TextInput::make('slug')
                    ->label('URL')
                    ->required(),
                Select::make('successor_id')
                    ->label('Następca')
                    ->relationship(name: 'successor', titleAttribute: 'label')
                    ->native(false),

                Section::make()->schema([

                    TextInput::make('form_a')
                        ->label('Forma 1 (nie ma...')
                        ->required(),
                    TextInput::make('form_e')
                        ->label('Forma 2 (...dzieli od Głogowa')
                        ->required(),
                    TextInput::make('form_w')
                        ->label('Forma 3 (byłem [w]...')
                        ->required(),
                    TextInput::make('form_z')
                        ->label('Forma 4 (zawarłem umowę [z]...')
                        ->required(),

                ])->columnSpanFull()->columns(4),


                Toggle::make('is_operational')->label('Bank prowadzi aktualnie działalność operacyjną'),

                Toggle::make('is_published')->label('Publikuj stronę banku'),

                RichEditor::make('desc_chf')
                    ->label('Opis (CHF)')
                    ->plugins([
                        RelativeLinks::make(),
                    ]),

                RichEditor::make('desc_eur')
                    ->label('Opis (EUR)')
                    ->plugins([
                        RelativeLinks::make(),
                    ]),
            ]);
    }
}
