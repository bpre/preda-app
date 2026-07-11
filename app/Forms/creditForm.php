<?php

namespace App\Forms;

use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Fieldset;
use Filament\Actions\Action;
use Filament\Forms\Components\Builder\Block;
use Filament\Schemas\Components\View;
use Filament\Schemas\Components\Utilities\Set;
use App\Models\Credit;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;


class creditForm {
    public static function form() {
        return [
            Tabs::make('Tabs')->tabs([

                Tab::make('Podstawowe informacje')->schema([

                    // Group::make()->schema([
                        Group::make([

                            Select::make('former_bank')
                            ->label('Bank (na umowie)')
                            ->relationship('former_banks', 'label')
                            ->preload()
                            ->createOptionForm(contactForm('Bank'))
                            ->editOptionForm(contactForm())
                            ->createOptionModalHeading('Nowy bank')
                            ->native(false)
                            ->searchable()
                            ->required(),
                        Select::make('current_bank')
                            ->label('Bank (obecnie)')
                            ->relationship('current_banks', 'label')
                            ->preload()
                            ->createOptionForm(contactForm('Bank'))
                            ->editOptionForm(contactForm())
                            ->createOptionModalHeading('Edytuj bank')
                            ->native(false)
                            ->searchable()
                            ->required(),
                        TextInput::make('number')
                            ->label('Numer umowy')
                            ->required()
                            ->maxLength(255),
                        DatePicker::make('date')
                            ->label('Data na umowie')
                            ->required(),

                        ])->columnSpan(1),

                        // Fieldset::make('Kredytobiorcy')->schema([

                        Group::make()->schema([

                            Repeater::make('contacts')
                                ->relationship('contactCredit')
                                ->label('Kredytobiorcy')
                                ->required()
                                ->orderColumn('sort')
                                ->addActionLabel('Dodaj kredytobiorcę')
                                ->schema([
                                Select::make('contact_id')
                                    ->label('Kredytobiorca')
                                    ->native(false)
                                    ->required()
                                    ->createOptionForm(contactForm('Kredytobiorca'))
                                    ->editOptionForm(contactForm())
                                    ->createOptionModalHeading('Nowy kredytobiorca')
                                    ->relationship('borrower', 'sort_name')
                                    ->searchable()
                            ])->columnSpan(2)

                        ])->columnSpan(1)
                    // ])
                ])->columns(2),

                Tab::make('Parametry umowy, klauzule, ocena prawna')->schema([

                    Builder::make('details')
                        ->label('')
                        ->collapsible()
                        ->collapsed()
                        ->deleteAction( fn (Action $action) => $action->requiresConfirmation(), )
                        ->blockNumbers(false)
                        ->addable(fn (?array $state) => count($state) < 3)
                        ->blocks([
                            Block::make('Parametry umowy')->maxItems(1)
                                ->schema([

                                    Group::make()->schema([

                                        TextInput::make('kwota')->label('Kwota kredytu'),

                                        Select::make('rodzaj-kredytu')->label('Rodzaj kredytu')
                                            ->options([
                                                'Indeksowany' => 'Indeksowany',
                                                'Denominowany' => 'Denominowany',
                                                'PLN przewalutowany' => 'PLN przewalutowany'
                                            ])
                                            ->native(false),

                                        Select::make('waluta')
                                            ->label('Waluta indeksacji / denominacji')
                                            ->required()
                                            ->default('CHF')
                                            ->options([
                                                'CHF' => 'CHF',
                                                'EUR' => 'EUR',
                                                'USD' => 'USD'
                                            ])
                                            ->native(false)

                                    ])->columns(3),

                                    View::make('components.horizontal-line'),

                                    Group::make()->schema([

                                        TextInput::make('liczba-rat')->label('Liczba rat')->columnSpan(3),
                                        TextInput::make('liczba-rat-um')->label('Postanowienie umowy')->columnSpan(2),

                                        View::make('components.horizontal-line')->columnSpan(5),

                                        Textarea::make('rodzaj-rat')->label('Rodzaj rat')->autosize()->columnSpan(3),
                                        TextInput::make('rodzaj-rat-um')->label('Postanowienie umowy')->columnSpan(2),

                                        View::make('components.horizontal-line')->columnSpan(5),

                                        Textarea::make('oprocentowanie')->label('Oprocentowanie')->autosize()->columnSpan(3),
                                        TextInput::make('oprocentowanie-um')->label('Postanowienie umowy')->columnSpan(2),

                                        View::make('components.horizontal-line')->columnSpan(5),

                                        Textarea::make('cel')->label('Cel kredytu')->autosize()->columnSpan(3),
                                        TextInput::make('cel-um')->label('Postanowienie umowy')->columnSpan(2),

                                    ])->columns(5),

                                    ]),

                            Block::make('Klauzule')->maxItems(1)
                                ->schema([

                                    Group::make()->schema([

                                        Textarea::make('klauzule-zbiorczo')->label('Wszystkie zbiorczo')->autosize(),
                                        Textarea::make('pouczenie')->label('Pouczenie o ryzyku w umowie')->autosize(),
                                        Textarea::make('klauzula-ryzyka')->label('Klauzula ryzyka kursowego')->autosize(),
                                        Textarea::make('klauzula-spreadowa')->label('Klauzula spreadowa')->autosize(),
                                        Textarea::make('unww')->label('UNWW')->autosize(),
                                        Textarea::make('zmienne-oprocentowanie')->label('Klauzula zmiennego oprocentowania')->autosize(),
                                        Textarea::make('inne-klauzule')->label('Inne istotne postanowienia')->autosize(),

                                    ])

                                    ]),

                            Block::make('Ocena prawna')->maxItems(1)
                                ->schema([

                                    Group::make()->schema([

                                        RichEditor::make('analiza')->label('Analiza')
                                            ->hintAction(
                                                Action::make('pobierz analizę podobnej umowy')
                                                    ->modalWidth('md')
                                                    ->hidden(fn ($record) => $record == null)
                                                    ->schema([
                                                        Select::make('umowa')->label('Wybierz umowę')
                                                        ->options(function ($record) {

                                                            $query = Credit::where('former_bank', $record?->former_bank)
                                                                ->where('details', 'LIKE', '%"analiza"%')
                                                                ->whereNot('details', 'LIKE', '%"analiza"=""%')
                                                                ->orderBy('date')->get();

                                                            $credits = [];

                                                            if($query) {
                                                                foreach($query as $credit) {
                                                                    $credits[$credit->id] = $credit->date .' / '. $credit->matter?->label;
                                                                }
                                                            }

                                                            return $credits;
                                                        })
                                                        ->native(false)
                                                        ->live()
                                                    ])
                                                    ->action(function (Set $set, $state, array $data) {
                                                        $umowa = Credit::find($data['umowa']);

                                                        $analiza = '';
                                                        foreach($umowa['details'] as $details) {
                                                            if($details['type'] == 'Ocena prawna') {
                                                                $analiza = $details['data']['analiza'];
                                                            }
                                                        }

                                                        $set('analiza', $analiza);
                                                    })

                                            ),
                                        Textarea::make('analiza-uwagi')->label('Uwagi - tylko do wiadomości pracowników kancelarii')->autosize(),
                                        Textarea::make('analiza-uwagi-klient')->label('Uwagi - dla klienta')->autosize(),

                                    ])

                                ])

                        ])->addActionLabel(fn (?array $state) => count($state) === 0 ? 'Dodaj szczegółowe informacje o umowie' : 'Dodaj')

                ])

            ])->columnSpan(2)
        ];
    }
}
