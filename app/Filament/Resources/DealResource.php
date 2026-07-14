<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CHFMatterResource\RelationManagers\DealsRelationManager;
use App\Filament\Resources\CHFMatterResource\RelationManagers\OffersRelationManager;
use App\Filament\Resources\DealResource\Pages\CreateDeal;
use App\Filament\Resources\DealResource\Pages\EditDeal;
use App\Filament\Resources\DealResource\Pages\ListDeals;
use App\Forms\creditForm;
use App\Http\Controllers\PrintController;
use App\Models\Credit;
use App\Models\Deal;
use App\Models\Matter;
use App\Models\Offer;
use App\Models\Payment;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\Route;

class DealResource extends Resource
{
    protected static ?int $navigationSort = 6;

    protected static ?string $slug = 'zlecenia';

    protected static ?string $model = Deal::class;

    protected static ?string $navigationLabel = 'Zlecenia';

    protected static ?string $modelLabel = 'Zlecenie';

    protected static ?string $pluralModelLabel = 'Zlecenia';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $shouldRegisterNavigation = false;

    protected function shouldPersistTableColumnSearchInSession(): bool
    {
        return true;
    }

    protected function shouldPersistTableFiltersInSession(): bool
    {
        return true;
    }

    protected function shouldPersistTableSearchInSession(): bool
    {
        return true;
    }

    protected function shouldPersistTableSortInSession(): bool
    {
        return true;
    }

    public static function warianty_opcje()
    {
        return [
            'Premium' => 'Premium',
            'Premium (do 85k PLN, 34k CHF)' => 'Premium (do 85k PLN, 34k CHF)',
            'Optymalny' => 'Optymalny',
            'Optymalny (do 85k PLN, 34k CHF)' => 'Optymalny (do 85k PLN, 34k CHF)',
            'Ekonomiczny' => 'Ekonomiczny',
            'Ekonomiczny (do 85k PLN, 34k CHF)' => 'Ekonomiczny (do 85k PLN, 34k CHF)',
            'Warunki indywidualne' => 'Warunki indywidualne',
            '2026: Bezpieczny start (z premią)' => '2006: Bezpieczny start (z premią)',
            '2026: Bez premii' => '2026: Bez premii',
        ];
    }

    public static function warianty()
    {

        return [

            'Premium' => [
                'stage_one_fee' => 10000,
                'stage_two_fee' => 5000,
                'hearing_fee' => 500,
                'bonus_fee' => 0,
                'is_bonus' => false,
                'installments' => 4,
            ],
            'Optymalny' => [
                'stage_one_fee' => 6000,
                'stage_two_fee' => 3000,
                'hearing_fee' => 500,
                'bonus_fee' => 10000,
                'is_bonus' => true,
                'installments' => 4,
            ],
            'Ekonomiczny' => [
                'stage_one_fee' => 2000,
                'stage_two_fee' => 1000,
                'hearing_fee' => 500,
                'bonus_fee' => 20000,
                'is_bonus' => true,
                'installments' => 4,
            ],

            'Premium (do 85k PLN, 34k CHF)' => [
                'stage_one_fee' => 8000,
                'stage_two_fee' => 4000,
                'hearing_fee' => 500,
                'bonus_fee' => 0,
                'is_bonus' => false,
                'installments' => 4,
            ],
            'Optymalny (do 85k PLN, 34k CHF)' => [
                'stage_one_fee' => 6000,
                'stage_two_fee' => 3000,
                'hearing_fee' => 500,
                'bonus_fee' => 7000,
                'is_bonus' => true,
                'installments' => 4,
            ],
            'Ekonomiczny (do 85k PLN, 34k CHF)' => [
                'stage_one_fee' => 2000,
                'stage_two_fee' => 1000,
                'hearing_fee' => 500,
                'bonus_fee' => 17000,
                'is_bonus' => true,
                'installments' => 4,
            ],

            '2026: Bezpieczny start (z premią)' => [
                'stage_one_fee' => 999,
                'stage_two_fee' => 0,
                'hearing_fee' => 0,
                'bonus_minimum' => 0,
                'bonus_percent' => 35,
                'is_bonus' => true,
                'installments' => 1,
            ],
            '2026: Bez premii' => [
                'stage_one_fee' => 12000,
                'stage_two_fee' => 6000,
                'hearing_fee' => 500,
                'bonus_minimum' => 0,
                'bonus_percent' => 0,
                'is_bonus' => false,
                'installments' => 4,
                'bonus_fee' => 0,
            ],

        ];

    }

    public static function wps_fees()
    {
        return [
            '900.00' => 'do 5.000 PLN',
            '1800.00' => '5.001 - 10.000 PLN',
            '3600.00' => '10.001 - 50.000 PLN',
            '5400.00' => '50.001 - 200.000 PLN',
            '10800.00' => 'powyżej 200.000 PLN',
        ];
    }

    public static function form(Schema $schema): Schema
    {

        return $schema
            ->components([

                Group::make()->schema([
                    Section::make('Szczegóły zlecenia')->schema([

                        Select::make('label')->label('Wariant')
                            // ->default('2026: Bez premii')
                            ->options(DealResource::warianty_opcje())
                            ->placeholder('Warunki indywidualne')
                            ->afterStateUpdated(function (Set $set, ?string $state, RelationManager $livewire) {

                                if ($state == null) {
                                    $set('label', 'Warunki indywidualne');
                                }

                                if ($state == '2026: Bez premii') {

                                    $matter_id = $livewire->getOwnerRecord()->id;

                                    $offer = Offer::where('matter_id', $matter_id)->orderBy('id', 'desc')->first();

                                    if (! empty($offer)) {
                                        $set('stage_one_fee', $offer->max_wstepna);
                                        $set('stage_two_fee', $offer->max_druga_instancja);
                                        $set('hearing_fee', $offer->max_rozprawa);
                                    }

                                    $set('is_bonus', false);
                                    $set('bonus_fee', 0);

                                } elseif ($state == '2026: Bezpieczny start (z premią)') {

                                    $matter_id = $livewire->getOwnerRecord()->id;

                                    $offer = Offer::where('matter_id', $matter_id)->orderBy('id', 'desc')->first();

                                    if (! empty($offer)) {
                                        $set('stage_one_fee', $offer->start_wstepna);
                                        $set('bonus_fee', $offer->start_premia);
                                        $set('bonus_percent', $offer->start_procent_limit);
                                    }

                                    // Jeśli nie ma oferty - pobierz umowy, przelicz w razie konieczności na PLN

                                    else {

                                        $credits = $livewire->getOwnerRecord()->credits;

                                        if (! empty($credits)) {

                                            $kwota = 0;

                                            foreach ($credits as $credit) {

                                                $waluta = $credit->credit_amount_currency;

                                                if ($waluta === 'PLN') {

                                                    $kwota += $credit->credit_amount_value;

                                                } else {

                                                    $kwota += OffersRelationManager::fx($credit->date, $waluta, round($credit->credit_amount_value));

                                                }

                                            }

                                            $set('bonus_fee', $kwota > 150000 ? 30000 : 20000);

                                        }

                                    }

                                    $set('is_bonus', true);

                                } elseif ($state == 'Warunki indywidualne') {

                                    $set('is_bonus', true);
                                } else {
                                    $set('stage_one_fee', DealResource::warianty()[$state]['stage_one_fee']);
                                    $set('stage_two_fee', DealResource::warianty()[$state]['stage_two_fee']);
                                    $set('bonus_fee', DealResource::warianty()[$state]['bonus_fee']);
                                    // $set('bonus_percent', DealResource::warianty()[$state]['bonus_percent']);
                                    $set('is_bonus', DealResource::warianty()[$state]['is_bonus']);
                                    $set('hearing_fee', DealResource::warianty()[$state]['hearing_fee']);
                                    $set('installments', DealResource::warianty()[$state]['installments']);
                                }

                            })
                            ->live()
                            ->required()
                            ->native(false)
                            ->columnSpan(8),
                        DatePicker::make('date')->label('Data')
                            ->default(now())
                            ->required()
                            ->columnSpan(4),

                        TextInput::make('stage_one_fee')
                            ->label('Opłata za I etap')->default(DealResource::warianty()['Premium']['stage_one_fee'])->required()
                            ->disabled(fn (Get $get) => substr($get('label'), 0, 7) == 'Wariant')
                            ->dehydrated()
                            ->suffix('zł')
                            ->stripCharacters(',')->numeric()
                            ->columnSpan(4),
                        TextInput::make('stage_two_fee')
                            ->label('Opłata za II etap')->default(DealResource::warianty()['Premium']['stage_two_fee'])->required()
                            ->disabled(fn (Get $get) => substr($get('label'), 0, 7) == 'Wariant')
                            ->dehydrated()
                            ->suffix('zł')
                            ->stripCharacters(',')->numeric()
                            ->columnSpan(4),
                        TextInput::make('hearing_fee')
                            ->label('Opłata za rozprawę')->default('500')->required()
                            // ->disabled(fn (Get $get) => substr($get('label'), 0, 7) == 'Wariant')
                            ->dehydrated()
                            ->suffix('zł')
                            ->stripCharacters(',')->numeric()
                            ->columnSpan(4),
                        TextInput::make('installments')->default('4')->label('Liczba rat')->required()->columnSpan(2),
                        DatePicker::make('first_installment_date')->default(strtotime('+7 day'))->label('Data pierwszej raty')
                            ->columnSpan(3),
                        Toggle::make('is_bonus')
                            ->default(false)
                            // ->disabled(fn (Get $get) => substr($get('label'), 0, 7) == 'Wariant' || substr($get('label'), 0, 4) == '2026')
                            ->hidden()
                            ->dehydrated()
                            ->dehydratedWhenHidden()
                            ->label('is_bonus')
                            ->inline(false)
                            ->reactive()
                            ->columnSpan(1),
                        // TextInput::make('bonus_percent')
                        //     ->live()
                        //     ->default(6)
                        //     ->disabled(fn (Get $get) => substr($get('label'), 0, 7) == 'Wariant')
                        //     ->dehydrated()
                        //     ->hidden(fn (Get $get): bool => $get('is_bonus') == false)
                        //     ->label(fn(Get $get) => substr($get('label'), 0, 4) == '2026' ? 'Premia [%] max' : 'Premia [%]')
                        //     ->afterStateUpdated(function (Get $get, Set $set) {
                        //         $premia = $get('bonus_percent') * $get('bonus_credit_amount') / 100;

                        //         if($get('label') !== '2026: Bezpieczny start (z premią)') {
                        //             $set('bonus_fee', $premia < $get('bonus_minimum') ? $get('bonus_minimum') : $premia);
                        //         }
                        //     })
                        //     ->columnSpan(2),
                        TextInput::make('bonus_fee')
                            ->live()
                            ->default(0)
                            ->disabled(fn (Get $get) => $get('is_bonus') == false)
                            ->dehydrated()
                            // ->hidden(fn (Get $get): bool => $get('is_bonus') == false || substr($get('label'), 0, 4) == '2026')
                            ->label('Premia')
                            ->stripCharacters(',')->numeric()
                            // ->afterStateUpdated(function (Get $get, Set $set) {
                            //     $premia = $get('bonus_percent') * $get('bonus_credit_amount') / 100;
                            //     $set('bonus_fee', $premia < $get('bonus_minimum') ? $get('bonus_minimum') : $premia);
                            // })
                            ->columnSpan(3)
                            ->suffix('zł'),
                        // TextInput::make('bonus_credit_amount')
                        //     ->live()
                        //     ->required()
                        //     ->hidden(fn (Get $get): bool => $get('is_bonus') == false || substr($get('label'), 0, 4) == '2026')
                        //     ->label('Od jakiej kwoty liczona premia?')
                        //     ->stripCharacters(',')->numeric()
                        //     ->columnSpan(6)
                        //     ->afterStateUpdated(function (Get $get, Set $set) {
                        //         $premia = $get('bonus_percent') * $get('bonus_credit_amount') / 100;
                        //         $set('bonus_fee', $premia < $get('bonus_minimum') ? $get('bonus_minimum') : $premia);
                        //     }),
                        // TextInput::make('bonus_fee')
                        //     ->label('Premia kwotowo')
                        //     ->required()
                        //     ->hidden(fn (Get $get): bool => $get('is_bonus') == false)
                        //     ->stripCharacters(',')->numeric()
                        //     ->columnSpan(fn (Get $get) => substr($get('label'), 0, 4) == '2026' ? 3 : 6),

                        Select::make('wps_fee')->label('Przewidywany WPS')
                            ->options(DealResource::wps_fees())
                            ->native(false)
                            ->columnSpan(4),

                    ])->columns(12),

                    Toggle::make('is_draft')
                        ->default(false)
                        ->label('Szkic?')
                        ->hint('/ zlecenie przygotowane wyłącznie w celu przekazania klientowi projektu umowy')
                    // ->inline(false)
                        ->reactive(),

                ])->columnSpan(2),

                Group::make()->schema([

                    Section::make('Umowa kredytowa')->schema([
                        Select::make('credits')->label('')
                            ->required()
                            ->createOptionForm(creditForm::form())
                            ->relationship(
                                name: 'credits',
                                titleAttribute: 'number',
                            )
                            ->options(function (?Deal $record, $livewire) {
                                $matter_id = static::resolveMatterIdForDealForm($record, $livewire);

                                if (blank($matter_id)) {
                                    return [];
                                }

                                return Credit::where('matter_id', $matter_id)->pluck('number', 'id');
                            })
                            ->multiple()
                            ->default(function (?Deal $record, $livewire) {
                                $matter_id = static::resolveMatterIdForDealForm($record, $livewire);
                                $matter = Matter::find($matter_id);

                                return $matter?->credits()->pluck('id')->toArray() ?? [];
                            }),
                    ])->collapsible()->columnSpan(1),

                    Section::make('Zleceniodawcy')->schema([
                        Repeater::make('contacts')
                            ->relationship('contactDeal')
                            ->label('')
                            ->required()
                            ->orderColumn('sort')
                            ->addActionLabel('Dodaj zleceniodawcę')
                            ->default(
                                function (?Deal $record, $livewire) {
                                    $matter_id = static::resolveMatterIdForDealForm($record, $livewire);
                                    $credits = Credit::where('matter_id', $matter_id)->get();
                                    $items = [];
                                    foreach ($credits as $credit) {
                                        $debtors = $credit->credit_contacts->pluck('id', 'label')->toArray();
                                        foreach ($debtors as $debtor) {
                                            array_push($items, $debtor);
                                        }
                                    }

                                    return $items;
                                }
                            )
                            ->simple(

                                Select::make('contact_id')
                                    ->label('')
                                    ->native(false)
                                    ->required()
                                    ->createOptionForm(contactForm('Kredytobiorca'))
                                    ->editOptionForm(contactForm())
                                    ->createOptionModalHeading('Nowy kredytobiorca')
                                    ->relationship('contact', 'sort_name')
                                    ->searchable()
                            ),
                    ])->collapsible()->columnSpan(1),

                ])->columnSpan(1),

            ])->columns(3);
    }

    public static function is_relation()
    {
        return Route::currentRouteName() == 'filament.kancelaria.resources.zlecenia.index';
    }

    protected static function resolveMatterIdForDealForm(?Deal $record, mixed $livewire): ?string
    {
        return $record?->matter_id
            ?? $record?->matter?->getKey()
            ?? ($livewire instanceof RelationManager ? $livewire->getOwnerRecord()?->getKey() : null);
    }

    public static function table(Table $table, bool $hideDraftsByDefault = true): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')->label('Data'),
                TextColumn::make('label')->label('Nazwa'),
                SelectColumn::make('wps_fee')->label('Przewidywany w.p.s.')->options(DealResource::wps_fees())->toggleable()->toggledHiddenByDefault(),
                TextColumn::make('matter.label')->label('Sprawa')->limit(50)->toggleable()->hiddenOn(DealsRelationManager::class)
                    ->url(fn (Deal $record) => MatterResource::getEditUrlForMatter($record->matter)),
            ])
            ->filters([

                SelectFilter::make('label')->label('Wariant')
                    ->options(DealResource::warianty_opcje())
                    ->native(false),

                Filter::make('is_draft')
                    ->toggle()
                    ->default($hideDraftsByDefault)
                    ->label('Ukryj szkice')
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->where('is_draft', 0);
                    }),

                Filter::make('scopeMine')
                    ->toggle()
                    ->label('Tylko mój referat')
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->mine();
                    })
                    ->hiddenOn(DealsRelationManager::class)
                    ->hidden(! auth()->user()->is_lawyer),
            ])
            ->headerActions([
                CreateAction::make()
                    ->modalWidth('7xl')
                    ->createAnother(false)
                    ->hidden(fn () => DealResource::is_relation())
                    ->modalHeading('Nowe zlecenie'),
            ])
            ->recordActions([

                Action::make('generuj płatności')
                    ->label('Generuj płatności')
                    ->iconButton()
                    ->icon('heroicon-o-calculator')
                    ->hidden(fn () => ! auth()->user()->can('view_any_payment') || DealResource::is_relation())
                    ->action(function ($record, RelationManager $livewire) {

                        if ($record->installments > 0) {
                            for ($i = 0; $i <= 3; $i++) {

                                $label = 'I instancja / '.$i + 1 .' rata';

                                if (Payment::where('label', $label)->where('matter_id', $record->matter_id)->count() == 0) {
                                    Payment::create([
                                        'label' => $label,
                                        'deadline' => date('Y-m-d', strtotime("+$i months", strtotime($record->first_installment_date))),
                                        'amount' => $record->stage_one_fee / $record->installments,
                                        'matter_id' => $record->matter_id,
                                    ]);
                                }

                            }

                            for ($i = 0; $i <= 1; $i++) {

                                $label = 'II instancja / '.$i + 1 .' rata';

                                if (Payment::where('label', $label)->where('matter_id', $record->matter_id)->count() == 0) {
                                    Payment::create([
                                        'label' => $label,
                                        'deadline' => null,
                                        'amount' => $record->stage_one_fee / $record->installments,
                                        'matter_id' => $record->matter_id,
                                    ]);
                                }
                            }
                        }

                        if (Payment::where('label', 'KZP')->where('matter_id', $record->matter_id)->count() == 0) {
                            Payment::create([
                                'label' => 'KZP',
                                'deadline' => null,
                                'amount' => $record->wps_fee * 1.75,
                                'matter_id' => $record->matter_id,
                            ]);
                        }

                        if ($record->bonus_fee) {
                            if (Payment::where('label', 'Premia')->where('matter_id', $record->matter_id)->count() == 0) {
                                Payment::create([
                                    'label' => 'Premia',
                                    'deadline' => null,
                                    'amount' => $record->bonus_fee,
                                    'matter_id' => $record->matter_id,
                                ]);
                            }

                        }

                        $livewire->dispatch('refresh-payments');

                    }),

                Action::make('pobierz')
                    ->label('Pobierz zlecenie')
                    ->iconButton()
                    ->icon('heroicon-o-document-arrow-down')
                    ->schema(function ($record) {
                        return [

                            Section::make()->schema([

                                Select::make('reprezentant')
                                    ->label('Kto podpisuje umowę:')
                                    ->required()
                                    ->default($record->matter->lawyer_id)
                                    ->options(function () {
                                        return User::responsible_lawyers()->pluck('name', 'id');
                                    })
                                    ->native(false),

                                Select::make('pelnomocnik')
                                    ->label('Pełnomocnictwo dla:')
                                    ->multiple()
                                    ->required()
                                    ->maxItems(3)
                                    ->maxItemsMessage('Możesz wybrać maksymalnie 3 osoby.')
                                    ->rules(['array', 'min:1', 'max:3'])
                                    ->validationMessages([
                                        'min' => 'Musisz wybrać co najmniej 1 osobę.',
                                        'max' => 'Możesz wybrać maksymalnie 3 osoby.',
                                        'array' => 'Nieprawidłowy format wyboru.',
                                    ])
                                    ->default([$record->matter->lawyer_id])
                                    // ->default(1)
                                    ->options(function () {
                                        return User::responsible_lawyers()->pluck('name', 'id');
                                    })
                                    ->native(false),

                                Select::make('miejsce_podpisania')
                                    ->label('Miejsce podpisania:')
                                    ->required()
                                    ->default(function ($record) {
                                        if ($record->matter?->branchUnit?->isRemote()) {
                                            return 'Głogów';
                                        }

                                        return match ($record->matter?->branchUnit?->label) {
                                            'Legnica' => 'Legnica',
                                            'Leszno' => 'Leszno',
                                            'Wrocław' => 'Wrocław',
                                            'Zielona Góra' => 'Zielona Góra',
                                            default => 'Głogów',
                                        };
                                    })
                                    ->options(
                                        [
                                            'Głogów' => 'Głogów',
                                            'Legnica' => 'Legnica',
                                            'Leszno' => 'Leszno',
                                            'Wrocław' => 'Wrocław',
                                            'Zielona Góra' => 'Zielona Góra',
                                        ]
                                    )
                                    ->native(false),

                                DatePicker::make('data_pelnomocnictwa')
                                    ->label('Data pełnomocnictwa')
                                    ->default(fn ($record) => $record->date),
                                // ->default(now()),

                                Toggle::make('pelnomocnictwo_powodztwo_banku')
                                    ->label('Pełnomocnictwo do sprawy z powództwa banku?')
                                    ->columnSpanFull(),

                                // Toggle::make('osobne_dla_kazdego_klienta')
                                //     ->label('Osobne dokumenty dla każdego klienta?')
                                //     ->columnSpanFull()

                                Hidden::make('osobne_dla_kazdego_klienta')
                                    ->default(false),

                                Toggle::make('pozyczka')
                                    ->label('Zamień wszędzie słowo "kredyt" słowem "pożyczka"')
                                    ->columnSpanFull(),

                            ])->columns(2),

                        ];
                    })
                    ->action(fn ($record, $data) => PrintController::pobierzZlecenie($record, $data)),

                EditAction::make()->modalWidth('7xl')->iconButton(),

                DeleteAction::make()
                    ->before(function ($record) {
                        $record->credits()->detach();
                    })
                    ->iconButton(),
            ])->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeals::route('/'),
            'create' => CreateDeal::route('/create'),
            'edit' => EditDeal::route('/{record}/edit'),
        ];
    }
}
