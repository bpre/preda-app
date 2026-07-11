<?php

namespace App\Filament\Website\Resources\Securities\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Builder;

class SecurityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Group::make([

                    Section::make('Szczegóły postanowienia')->schema([

                        TextInput::make('sign')
                            ->label('Sygnatura')
                            ->required(),

                        DatePicker::make('sentence_date')
                            ->label('Data postanowienia')
                            ->required(),

                        Select::make('court_id')
                            ->label('Sąd')
                            ->relationship(
                                name: 'court',
                                titleAttribute: 'label',
                                modifyQueryUsing: fn(Builder $query) => $query->where('category', 'Sąd')->orderBy('label')
                            )
                            ->required()
                            ->searchable(),
                        Select::make('judge_id')
                            ->label('Sędzia')
                            ->relationship(
                                name: 'judge',
                                titleAttribute: 'sort_name',
                                modifyQueryUsing: fn(Builder $query) => $query->where('category', 'Sędzia')->orderBy('sort_name')
                            )
                            ->required()
                            ->searchable(),

                    ])->columns(2)
                        ->collapsible(),

                    Section::make('Umowa')->schema([
                        Select::make('bank_previously_id')
                            ->label('Bank (poprzednio)')
                            ->relationship(
                                name: 'bank_previously',
                                titleAttribute: 'label'
                            )
                            ->required()
                            ->searchable()
                            ->columnSpan(5),
                        Select::make('bank_id')
                            ->label('Bank (obecnie)')
                            ->relationship(
                                name: 'bank',
                                titleAttribute: 'label'
                            )
                            ->required()
                            ->searchable()
                            ->columnSpan(5),
                    ])
                        ->columns(10)
                        ->collapsible()
                ]),

                Group::make([

                    Fieldset::make()->schema([
                        Toggle::make('is_published')->label('Opublikowany?')->columnSpanFull()
                    ]),

                    FileUpload::make('files')
                        ->label('')
                        ->reorderable()
                        ->image()
                        ->imageEditor()
                        ->imageEditorMode(2)
                        ->imagePreviewHeight('500')
                        ->visibility('public')
                        ->disk('public')
                        ->directory('sentences')
                        ->multiple(),
                ])
            ]);

    }
}
