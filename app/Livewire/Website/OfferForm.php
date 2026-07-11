<?php

namespace App\Livewire\Website;

use App\Models\User;
use Livewire\Component;
use Filament\Support\RawJs;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use App\Models\Website\Offer;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Contracts\HasSchemas;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewOfferRequestToAdmin;
use App\Notifications\NewOfferRequestToClient;
use Filament\Schemas\Concerns\InteractsWithSchemas;

class OfferForm extends Component implements HasSchemas
{

    use InteractsWithSchemas;

    public ?array $data = [];

    public $files = [];
    public $content = [];
    public $sent = false;
    public $variant = null;

    public function mount()
    {
        // $query = request()->query();
        // $this->form->fill([
        //     'data.bank' => $query['b'] ?? null,
        //     'data.credit_date' => $query['d'] ?? null,
        //     'data.claim' => 'info_and_offer',
        //     'data.credit_currency' => 'PLN',
        //     'data.credit_period_item' => 'lat'
        // ]);

    }

    public function setVariant($variant): void
    {
        $state = $this->form->getRawState();
        $state['variant'] = $variant;
        $state['amount'] = str_replace(' ', '', $state['amount']);
        $this->form->fill($state);
    }

    public function form(Schema $schema): Schema
    {

        return $schema
            ->components([

                Section::make('Informacje o Twoim kredycie')->schema([

                    Hidden::make('variant')
                        ->label('Variant')
                        ->columnSpanFull(),

                    TextInput::make('bank')
                        ->label('Bank na umowie')
                        ->extraAttributes(['class' => 'z-20'])
                        ->required()
                        ->columnSpan(4),

                    Select::make('year')
                        ->label('Rok umowy')
                        ->placeholder('Wybierz')
                        ->options([
                            '2000' => '2000',
                            '2001' => '2001',
                            '2002' => '2002',
                            '2003' => '2003',
                            '2004' => '2004',
                            '2005' => '2005',
                            '2006' => '2006',
                            '2007' => '2007',
                            '2008' => '2008',
                            '2009' => '2009',
                            '2010' => '2010',
                            '2011' => '2011',
                            '2012' => '2012',
                            '2013' => '2013',
                            '2014' => '2014',
                            '2015' => '2015',
                            '2016' => '2016',
                            '2017' => '2017',
                            '2018' => '2018',
                            '2019' => '2019'
                        ])
                        ->required()
                        ->columnSpan(3),

                    TextInput::make('amount')
                        ->label('Kwota wypłacona przez bank')

                        ->mask(RawJs::make('$money($input, \'.\', \' \', 0)'))

                        ->stripCharacters(' ')

                        ->formatStateUsing(fn ($state) => $state !== null
                            ? number_format((int) $state, 0, '', ' ')
                            : null
                        )

                        ->dehydrateStateUsing(fn ($state) => $state !== null
                            ? (int) str_replace(' ', '', $state)
                            : null
                        )

                        ->rule('integer')
                        ->inputMode('numeric')

                        ->required()
                        ->suffix('zł')
                        ->extraInputAttributes([
                            'class' => 'text-right'
                        ])
                        ->columnSpan(5),


                ])->columns(12)->columnSpanFull(),

                Section::make('Twoje dane')->schema([

                    TextInput::make('name')
                        ->label('Imię i nazwisko')
                        ->extraAttributes(['class' => 'z-20'])
                        ->required(),

                    TextInput::make('phone')
                        ->label('Numer telefonu')
                        ->minLength(9)
                        ->tel()
                        ->required(),

                    TextInput::make('email')
                        ->label('E-mail')
                        ->email()
                        ->required(),

                ])->columns(3)->columnSpanFull(),


                Checkbox::make('policy')
                    ->label('wyrażam zgodę na otrzymanie oferty i przetwarzanie danych osobowych')
                    ->required()
                    ->columnSpanFull(),

                Placeholder::make('No Label')
                    ->hiddenLabel()
                    ->extraAttributes([
                        'class' => 'text-xs text-gray-500 prose-a:text-gray-500',
                        'style' => 'line-height: 105%'
                    ])
                    ->content(fn () => new HtmlString('Administratorem danych osobowych jest PRĘDA Kancelaria Adwokacka - Adwokat Bartosz Pręda z siedzibą w Głogowie. Podanie danych jest dobrowolne. Masz prawo m.in. dostępu do Twoich danych, żądania ich poprawienia oraz usunięcia. Szczegóły w <a href="/polityka-prywatnosci" target="_blank">polityce prywatności</a>.'))
                    ->columnSpanFull(),

                Actions::make([
                        Action::make('Wyślij formularz')
                            ->extraAttributes(['class'=>'ml-auto'])
                            ->submit('create')
                    ])->columnSpanFull(),



            ])->columns(3)

            ->statePath('data')
            ->model(Offer::class);

    }

    public function create()
    {

        $state = $this->form->getState();

        $offer = Offer::create($state);

        $user = User::where('email', 'bartosz.preda@preda.info')->first();
        $user->notify(new NewOfferRequestToAdmin($offer));

        Notification::route('mail', $offer->email)->notify(new NewOfferRequestToClient($offer));

        $this->sent = true;

        $this->dispatch('gtm');
    }


    public function render()
    {
        return view('livewire.website.offer-form');
    }
}
