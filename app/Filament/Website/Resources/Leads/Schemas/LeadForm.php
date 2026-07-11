<?php

namespace App\Filament\Website\Resources\Leads\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;

class LeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Imię i nazwisko'),
                TextInput::make('email')->label('E-mail'),
                TextInput::make('postal_code')
                    ->label('Kod pocztowy')
                    ->placeholder('00-000')
                    ->mask('99-999')
                    ->maxLength(6)
                    ->regex('/^\d{2}-\d{3}$/')
                    ->validationMessages([
                        'regex' => 'Kod pocztowy powinien mieć format 00-000.',
                    ]),
                TextInput::make('phone')->label('Telefon'),
                Section::make('Status')
                    ->schema([
                        TextInput::make('status')
                            ->label('Aktualny status')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Status zmieniaj przyciskiem „Zmień status”, żeby zachować historię.'),
                        DateTimePicker::make('status_changed_at')
                            ->label('Data ostatniej zmiany')
                            ->displayFormat('d.m.Y H:i')
                            ->seconds(false)
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2),
                RichEditor::make('message')->label('Wiadomość'),
                Section::make('Źródło leada')
                    ->schema([
                        TextInput::make('attribution_summary')
                            ->label('Źródło')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('attribution_source')
                            ->label('Źródło techniczne')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('attribution_medium')
                            ->label('Medium')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('attribution_campaign')
                            ->label('Kampania')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('attribution_term')
                            ->label('Fraza / keyword')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('attribution_content')
                            ->label('Treść / reklama')
                            ->disabled()
                            ->dehydrated(false),
                        Textarea::make('attribution_landing_page')
                            ->label('Strona wejścia')
                            ->rows(2)
                            ->disabled()
                            ->dehydrated(false),
                        Textarea::make('attribution_conversion_page')
                            ->label('Strona wysłania formularza')
                            ->rows(2)
                            ->disabled()
                            ->dehydrated(false),
                        Textarea::make('attribution_referrer')
                            ->label('Referrer')
                            ->rows(2)
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2)
            ]);
    }
}
