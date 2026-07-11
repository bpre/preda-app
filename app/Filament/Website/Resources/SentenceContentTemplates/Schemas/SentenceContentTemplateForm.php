<?php

namespace App\Filament\Website\Resources\SentenceContentTemplates\Schemas;

use App\Models\Website\SentenceContentTemplate;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SentenceContentTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make([
                    Section::make('Szablon')->schema([
                        TextInput::make('name')
                            ->label('Nazwa robocza')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(8),
                        Toggle::make('is_active')
                            ->label('Aktywny')
                            ->default(true)
                            ->inline(false)
                            ->columnSpan(2),
                        Select::make('section')
                            ->label('Sekcja')
                            ->options(SentenceContentTemplate::sectionOptions())
                            ->native(false)
                            ->required()
                            ->columnSpan(5),
                        Select::make('instance')
                            ->label('Instancja')
                            ->options([
                                1 => 'I instancja',
                                2 => 'II instancja',
                                3 => 'Postępowanie kasacyjne',
                            ])
                            ->placeholder('Dowolna')
                            ->native(false)
                            ->columnSpan(5),
                        TextInput::make('priority')
                            ->label('Priorytet')
                            ->numeric()
                            ->default(0)
                            ->columnSpan(3),
                        Select::make('selection_mode')
                            ->label('Wybór wariantu')
                            ->options(SentenceContentTemplate::selectionModeOptions())
                            ->default('random')
                            ->native(false)
                            ->required()
                            ->columnSpan(7),
                        Textarea::make('content')
                            ->label('Treść')
                            ->helperText('Dostępne placeholdery m.in.: {court}, {court_locative}, {court_genitive}, {bank}, {bank_form_a}, {bank_form_z}, {credit_name}, {credit_year}, {credit_profit}, {credit_payoff}, {lawyer_phrase}, {sign}.')
                            ->rows(8)
                            ->required()
                            ->columnSpanFull(),
                        Textarea::make('note')
                            ->label('Notatka')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(10),
                ])->columnSpan(7),

                Group::make([
                    Section::make('Warunki')->schema([
                        CheckboxList::make('all_of')
                            ->label('Wszystkie muszą być spełnione')
                            ->options(SentenceContentTemplate::conditionOptions())
                            ->columns(1)
                            ->searchable()
                            ->columnSpanFull(),
                        CheckboxList::make('any_of')
                            ->label('Wystarczy jeden')
                            ->options(SentenceContentTemplate::conditionOptions())
                            ->columns(1)
                            ->searchable()
                            ->columnSpanFull(),
                        CheckboxList::make('none_of')
                            ->label('Żaden nie może być spełniony')
                            ->options(SentenceContentTemplate::conditionOptions())
                            ->columns(1)
                            ->searchable()
                            ->columnSpanFull(),
                    ])->collapsible(),
                ])->columnSpan(5),
            ])
            ->columns(12);
    }
}
