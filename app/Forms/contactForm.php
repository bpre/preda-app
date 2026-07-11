<?php

use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Fieldset;
use App\Models\Contact;
use App\Models\Departament;
use App\Rules\Pesel;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilder;

function contactForm($category = null) {
    return [
        Select::make('category')
            ->required('Wybierz typ kontaktu')
            ->options(Contact::S_CATEGORIES)
            ->searchable()
            ->label('Typ kontaktu')
            ->live()
            ->default($category)
            ->afterStateUpdated(function (Set $set, ?string $state) {
                if ($state == null) {
                    $set('type', 'inny');
                } elseif (in_array($state, Contact::ORGANIZATIONS)) {
                    $set('type', 'organizacja');
                } else {
                    $set('type', 'osoba');
                }
            })
            ->columnSpanFull(),

        Hidden::make('type')->default(function (Get $get) {
            $category = $get('category');

            if ($category == null) {
                return 'inny';
            } elseif (in_array($category, Contact::ORGANIZATIONS)) {
                return 'organizacja';
            } else {
                return 'osoba';
            }
        }),

        Fieldset::make('Dane osobowe')->schema([
            TextInput::make('first_name')
                ->label('Imię')
                ->requiredIf('typ', 'osoba')
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                    $pesel = preg_replace('/\D+/', '', (string) $get('pesel'));

                    if (strlen($pesel) === 11) {
                        return;
                    }

                    $firstName = trim((string) $state);

                    if ($firstName === '') {
                        return;
                    }

                    $lastChar = mb_strtolower(mb_substr($firstName, -1));

                    if ($lastChar === 'a') {
                        $set('sex', 'K');
                    }
                }),

            TextInput::make('last_name')
                ->label('Nazwisko')
                ->requiredIf('typ', 'osoba')
                ->maxLength(255),

            TextInput::make('pesel')
                ->rules([new Pesel()])
                ->maxLength(11)
                ->live(onBlur: true)
                ->dehydrateStateUsing(fn ($state) => preg_replace('/\D+/', '', (string) $state))
                ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                    $pesel = preg_replace('/\D+/', '', (string) $state);

                    $set('pesel', $pesel);

                    if (strlen($pesel) === 11) {
                        $digit = (int) $pesel[9];
                        $set('sex', $digit % 2 === 0 ? 'K' : 'M');
                        return;
                    }

                    $firstName = trim((string) $get('first_name'));

                    if ($firstName === '') {
                        return;
                    }

                    $lastChar = mb_strtolower(mb_substr($firstName, -1));

                    if ($lastChar === 'a') {
                        $set('sex', 'K');
                    }
                })
                ->hidden(fn (Get $get): string => $get('category') == 'Pełnomocnik'),

            Select::make('sex')
                ->options(Contact::SEX)
                ->label('Płeć')
                ->requiredIf('typ', 'osoba')
                ->native(false),

            Select::make('profession')
                ->label('Tytuł zawodowy')
                ->options(Contact::TYTUL_ZAWODOWY)
                ->native(false)
                ->hidden(fn (Get $get): string => $get('category') != 'Pełnomocnik'),

            Select::make('lawfirm_id')
                ->label('Kancelaria')
                ->relationship(name: 'contact_lawfirm', titleAttribute: 'label')
                ->searchable()
                ->preload()
                ->native(false)
                ->live()
                ->hidden(fn (Get $get): string => $get('category') != 'Pełnomocnik')
                ->columnSpan(function (Get $get) {
                    return Departament::where('contact_id', $get('lawfirm_id'))->count() == 0 ? 2 : 1;
                }),

            Select::make('departament_id')
                ->label('Oddział')
                ->hidden(function (Get $get) {
                    if (empty($get('lawfirm_id'))) {
                        return true;
                    } else {
                        return (Departament::where('contact_id', $get('lawfirm_id'))->count() == 0);
                    }
                })
                ->relationship(
                    name: 'contact_departament',
                    titleAttribute: 'label',
                    modifyQueryUsing: fn (QueryBuilder $query, Get $get) => $query->where('contact_id', $get('lawfirm_id')),
                )
                ->searchable()
                ->preload()
                ->native(false),
        ])->columns(4)
            ->columnSpanFull()
            ->hidden(fn (Get $get): string => $get('type') != 'osoba'),

        Fieldset::make('Dane identyfikacyjne')->schema([
            TextInput::make('organization')
                ->label('Pełna nazwa')
                ->required()
                ->maxLength(255)
                ->columnSpan(4),

            TextInput::make('organization_short')
                ->label('Nazwa skrócona')
                ->required()
                ->maxLength(255)
                ->columnSpan(3),

            TextInput::make('krs')
                ->maxLength(11)
                ->columnSpan(2),
        ])->columns(9)
            ->columnSpanFull()
            ->hidden(fn (Get $get): string => $get('type') != 'organizacja'),

        Fieldset::make('Dane kontaktowe')->schema([
            TextInput::make('email')
                ->email()
                ->maxLength(255)
                ->label('E-mail'),

            TextInput::make('phone')
                ->label('Telefon')
                ->maxLength(255),
        ])->columns(2)
            ->columnSpanFull(),

        Fieldset::make('Adres')->schema([
            TextInput::make('address')
                ->label('Ulica i numer')
                ->maxLength(255)
                ->columnSpan(3),

            TextInput::make('zip_code')
                ->label('Kod')
                ->maxLength(255),

            TextInput::make('city')
                ->label('Miejscowość')
                ->maxLength(255)
                ->columnSpan(2),
        ])->columns(6)
            ->columnSpanFull()
            ->hidden(fn (Get $get): string => $get('category') == 'Pełnomocnik'),
    ];
}
