<?php

namespace App\Filament\Website\Resources\Offers\Schemas;

use Livewire\Component;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class OffersForm
{
    public static function configure(Schema $schema): Schema
    {

        $lockIfSent = fn (Component $livewire): bool => filled($livewire->getRecord()?->offer_sent_at);

        return $schema
            ->components([

                Section::make('Zgłoszenie')->schema([


                    Select::make('sex')
                        ->label('Zwrot')
                        ->options([
                            'male' => 'Pan',
                            'female' => 'Pani',
                            'both' => 'Państwo'
                        ])
                        ->required(),
                    TextInput::make('name')
                        ->label('Imię i nazwisko')
                        ->required(),
                    TextInput::make('email')
                        ->label('E-mail')
                        ->required(),
                    TextInput::make('phone')
                        ->label('Nr telefonu')
                        ->required(),
                    TextInput::make('bank')
                        ->label('Bank na umowie')
                        ->required(),
                    TextInput::make('year')
                        ->label('Rok zawarcia umowy')
                        ->required(),
                    TextInput::make('amount')
                        ->label('Kwota kredytu')
                        ->live()
                        ->required(),
                    TextInput::make('variant')
                        ->label('Wariant'),

                ])->columnSpanFull()->columns(4),

                Section::make('Wariant: Start')

                    ->schema([

                        TextInput::make('start_wstepna')
                            ->label('Opłata wstępna')
                            ->disabled($lockIfSent)
                            ->required(fn (Component $livewire) => $livewire->saveIntent === 'confirm')
                            ->suffix('zł'),

                        TextInput::make('start_premia')
                            ->label('Premia')
                            ->disabled($lockIfSent)
                            ->required(fn (Component $livewire) => $livewire->saveIntent === 'confirm')
                            ->suffix('zł'),

                        TextInput::make('start_procent_limit')
                            ->label('Limit procentowy premii')
                            ->disabled($lockIfSent)
                            ->required(fn (Component $livewire) => $livewire->saveIntent === 'confirm')
                            ->suffix('%'),

                        TextInput::make('start_rozprawa')
                            ->label('Opłata za rozprawę')
                            ->disabled($lockIfSent)
                            ->required(fn (Component $livewire) => $livewire->saveIntent === 'confirm')
                            ->suffix('zł'),

                        TextInput::make('start_razem_max')
                            ->label('Razem maksymalnie')
                            ->disabled($lockIfSent)
                            ->required(fn (Component $livewire) => $livewire->saveIntent === 'confirm')
                            ->suffix('zł')
                            ->columnSpanFull(),

                    ])->columns(2),

                Section::make('Wariant: Max')

                    ->schema([

                        TextInput::make('max_wstepna')
                            ->label('Opłata wstępna')
                            ->disabled($lockIfSent)
                            ->required(fn (Component $livewire) => $livewire->saveIntent === 'confirm')
                            ->suffix('zł'),

                        TextInput::make('max_druga_instancja')
                            ->label('Druga instancja')
                            ->disabled($lockIfSent)
                            ->required(fn (Component $livewire) => $livewire->saveIntent === 'confirm')
                            ->suffix('zł'),

                        TextInput::make('max_rozprawa')
                            ->label('Opłata za rozprawę')
                            ->disabled($lockIfSent)
                            ->required(fn (Component $livewire) => $livewire->saveIntent === 'confirm')
                            ->suffix('zł'),

                        TextInput::make('max_rozprawy_limit')
                            ->label('Limit opłat za rozprawy')
                            ->disabled($lockIfSent)
                            ->required(fn (Component $livewire) => $livewire->saveIntent === 'confirm')
                            ->suffix('zł'),

                        TextInput::make('max_razem_max')
                            ->label('Razem maksymalnie')
                            ->disabled($lockIfSent)
                            ->required(fn (Component $livewire) => $livewire->saveIntent === 'confirm')
                            ->suffix('zł')
                            ->columnSpanFull(),

                ])->columns(2)


            ]);
    }
}
