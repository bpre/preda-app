<?php

namespace App\Filament\Resources\CHFMatterResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Actions\Action;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Group;
use Filament\Actions\CreateAction;
use Filament\Support\Enums\Width;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use DateTimeInterface;
use Carbon\Carbon;

use Filament\Forms;
use Filament\Tables;
use RuntimeException;
use App\Models\Credit;
use App\Models\Contact;
use Filament\Tables\Table;
use App\Models\ExchangeRate;
use InvalidArgumentException;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\CheckboxList;
use Livewire\Component as LivewireComponent;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class OffersRelationManager extends RelationManager
{
    protected static string $relationship = 'offers';

    protected static ?string $title = 'Oferty';

    protected static ?string $modelLabel = 'Oferta';
    protected static ?string $pluralModelLabel = 'Oferty';



    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Oferta')
                    ->headerActions([
                        Action::make('getData')
                            ->label('Pobierz dane')
                            ->color('gray')
                            ->modal(fn (RelationManager $livewire): bool =>
                                $livewire->getOwnerRecord()->credits()->exists()
                            )
                            ->schema([

                                Select::make('credit_id')
                                        ->label('Umowa kredytowa')
                                        ->live()
                                        ->options(fn (RelationManager $livewire) =>
                                            $livewire->getOwnerRecord()
                                                ->credits()
                                                ->get()
                                                ->mapWithKeys(function ($credit) {
                                                    $date = $credit->signed_at?->format('Y-m-d'); // dopasuj nazwę kolumny
                                                    return [
                                                        $credit->id => "{$credit->former_banks->label}: {$credit->number}" . ($date ? " — {$date}" : ''),
                                                    ];
                                                })
                                                ->all()

                                        )
                                        ->default(function (RelationManager $livewire) {
                                            $credits = $livewire->getOwnerRecord()->credits();

                                            if ($credits->count() === 1) {
                                                return $credits->value('id'); // id tego jedynego
                                            }

                                            return null;
                                        }),

                                        // Checkboxy kontaktów zależne od wybranego kredytu:
                                        CheckboxList::make('credit_contact_ids')
                                            ->label('Kontakty do tej umowy')
                                            ->required()
                                            ->columns(1)
                                            ->visible(fn (Get $get) => filled($get('credit_id')))
                                            ->options(function (RelationManager $livewire, Get $get): array {
                                                $creditId = $get('credit_id');
                                                if (! $creditId) {
                                                    return [];
                                                }

                                                $credit = $livewire->getOwnerRecord()
                                                    ->credits()
                                                    ->whereKey($creditId)
                                                    ->first();

                                                if (! $credit) {
                                                    return [];
                                                }

                                                return $credit->credit_contacts()   // <- Twoja relacja na Credit
                                                    ->get()
                                                    ->mapWithKeys(fn ($contact) => [
                                                        $contact->id => trim(($contact->label ?? '') . ' ' . ($contact->email ? "({$contact->email})" : '')),
                                                    ])
                                                    ->all();
                                            })


                            ])
                            ->action(function (RelationManager $livewire, Get $get, Set $set, array $data): void {

                                $owner = $livewire->getOwnerRecord();

                                // ✅ jeśli jednak nie ma kredytów (np. stan się zmienił) – przerwij
                                if (! $owner->credits()->exists()) {
                                    Notification::make()
                                        ->title('Do tej sprawy nie ma dodanej żadnej umowy kredytowej')
                                        ->danger()
                                        ->send();

                                    return;
                                }

                                $contacts = Contact::whereIn('id', $data['credit_contact_ids'])->get()->pluck('label');

                                if(count($contacts) > 1) {
                                    $set('sex', 'both');
                                    $set('name', join(', ', $contacts->toArray()));
                                } else {

                                    if($this->lastLetterOfFirstName($contacts[0]) === 'a') {
                                        $set('sex', 'female');
                                    } else {
                                        $set('sex', 'male');
                                    }

                                    $set('name', $contacts[0]);

                                }

                                $credit = Credit::where('id', $data['credit_id'])->first();

                                $set('bank', $credit->former_banks->organization);
                                $set('year', substr($credit->date, 0, 4));

                                $kwota = $credit->credit_amount_value;      // 18713.85
                                $waluta = $credit->credit_amount_currency;

                                // dd($credit->credit_currency);

                                $set('amount_orig', number_format($kwota, 0, '', ' ').' '.($waluta == 'PLN' ? 'zł' : $waluta));

                                $set('currency', $waluta);

                                if($waluta === 'PLN') {
                                    $set('amount', round($kwota, 0));
                                    $set('rate', 1);
                                } else {
                                    $fx = $this->fx($credit->date, $waluta, round($kwota, 0));
                                    $set('rate', $fx);
                                    $set('amount', round($fx * $kwota, 0));
                                }

                                if($credit->credit_currency)
                                {
                                    $set('currency_index', $credit->credit_currency);
                                } else {
                                    $set('currency_index', $waluta);
                                }



                            }),
                    ])

                    ->schema([

                        Tabs::make()->tabs([

                            Tab::make('Informacje odstawowe')->schema([

                                Select::make('sex')
                                    ->label('Zwrot')
                                    ->options([
                                        'male' => 'Pan',
                                        'female' => 'Pani',
                                        'both' => 'Państwo'
                                    ])
                                    ->required()
                                    ->columnSpan(2),

                                TextInput::make('name')
                                    ->label('Adresat oferty')
                                    ->required()
                                    ->columnSpan(4),

                                Toggle::make('is_initial_offer')
                                    ->label('Oferta wstępna (przed spotkaniem i analizą wszystkich dokumentów)')
                                    ->hint('dodaje odpowiednie zastrzeżenie w treści oferty')
                                    ->default(true)
                                    ->columnSpanFull(),

                                Toggle::make('show_benefit')
                                    ->label('Pokaż w ofercie potencjalne korzyści z unieważnienia umowy')
                                    ->default(true)
                                    ->columnSpanFull(),

                            ])->columns(12),

                            Tab::make('Umowa kredytowa')->schema([

                                TextInput::make('bank')
                                    ->label('Bank na umowie')
                                    ->required()
                                    ->columnSpan(3),
                                TextInput::make('year')
                                    ->label('Rok zawarcia umowy')
                                    ->required()
                                    ->columnSpan(3),
                                TextInput::make('amount_orig')
                                    ->label('Kwota kredytu')
                                    ->live()
                                    ->required()
                                    ->columnSpan(3),
                                Select::make('currency')
                                    ->options([
                                        'PLN' => 'PLN',
                                        'CHF' => 'CHF',
                                        'EUR' => 'EUR',
                                        'USD' => 'USD',
                                    ])
                                    ->label('Waluta kredytu')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (?string $state, Set $set) {
                                        if ($state && $state !== 'PLN') {
                                            $set('currency_index', $state);
                                        } else {
                                            $set('currency_index', null);
                                        }
                                    })
                                    ->columnSpan(3),

                                TextInput::make('rate')
                                    ->label('Kurs')
                                    ->required()
                                    ->columnSpan(3),

                                TextInput::make('amount')
                                    ->label('Wartość kredytu w PLN')
                                    ->live()
                                    ->required()
                                    ->columnSpan(3),

                                Select::make('currency_index')
                                    ->options([
                                        'CHF' => 'CHF',
                                        'EUR' => 'EUR',
                                        'USD' => 'USD',
                                    ])
                                    ->live()
                                    ->label('Waluta indeksacji / denominacji')
                                    ->required()
                                    ->columnSpan(3),

                                Toggle::make('is_paid_off')->inline(false)->label('Spłacony?'),

                                TextInput::make('benefit')
                                    ->label('Korzyść na podstawie zaświadczenia')
                                    ->numeric()
                                    ->suffix('PLN')
                                    ->columnSpan(3),

                            ])->columns(12),

                            Tab::make('Kontakt')->schema([

                                TextInput::make('phone')
                                    ->label('Telefon do kontaktu')
                                    ->default(function(RelationManager $livewire) {

                                        if($livewire->getOwnerRecord()->lawyer()->exists()) {
                                            return $livewire->getOwnerRecord()->lawyer->phone;
                                        }

                                        return '666 580 580';
                                    })
                                    ->required(),

                                TextInput::make('email')
                                    ->label('E-mail do kontaktu')
                                    ->default(function(RelationManager $livewire) {

                                        if($livewire->getOwnerRecord()->lawyer()->exists()) {
                                            return $livewire->getOwnerRecord()->lawyer->email;
                                        }

                                        return 'kancelaria@preda.info';
                                    })
                                    ->required()

                            ])->columns(4)

                        ])








                    ]),

                Section::make('Warianty')
                    ->headerActions([
                        Action::make('setDefaults')
                            ->label('Ustaw wartości domyślne')
                            ->color('gray')
                            ->action(function (Get $get, Set $set): void {

                                $rawAmount = (string) ($get('amount') ?? '');
                                $amount = (int) preg_replace('/\D+/', '', $rawAmount);

                                if ($amount <= 0) {
                                    Notification::make()
                                        ->title('Najpierw wpisz kwotę kredytu.')
                                        ->body('Bez kwoty nie da się wyliczyć wartości domyślnych.')
                                        ->danger()
                                        ->send();

                                    return; // ✅ nie przeliczaj nic dalej
                                }

                                $start_wstepna = 999;
                                $start_premia  = $amount > 150000 ? 30000 : 25000;

                                $max_wstepna = 12000;
                                $max_druga   = 6000;
                                $max_rozprawa = 500;
                                $max_rozprawy_limit = 1999;

                                // Start
                                $set('start_wstepna', $start_wstepna);
                                $set('start_premia', $start_premia);
                                $set('start_procent_limit', 35);
                                $set('start_rozprawa', 0);
                                $set('start_razem_max', $start_wstepna + $start_premia);

                                // Max
                                $set('max_wstepna', $max_wstepna);
                                $set('max_druga_instancja', $max_druga);
                                $set('max_rozprawa', $max_rozprawa);
                                $set('max_rozprawy_limit', $max_rozprawy_limit);
                                $set('max_razem_max', $max_wstepna + $max_druga + $max_rozprawy_limit);

                                Notification::make()
                                    ->title('Dodano wartości domyślne.')
                                    ->success()
                                    ->send();
                            }),
                    ])
                    ->schema([

                        Group::make()->schema([

                            Section::make('Wariant: Start')
                                ->columnSpan(1)
                                ->schema([

                                    TextInput::make('start_wstepna')
                                        ->live()
                                        ->afterStateUpdated(fn (Set $set, Get $get) => $set('start_razem_max', $get('start_wstepna') + $get('start_premia')))
                                        ->label('Opłata wstępna')
                                        ->suffix('zł'),

                                    TextInput::make('start_premia')
                                        ->live()
                                        ->afterStateUpdated(fn (Set $set, Get $get) => $set('start_razem_max', $get('start_wstepna') + $get('start_premia')))
                                        ->label('Premia')
                                        ->suffix('zł'),

                                    TextInput::make('start_procent_limit')
                                        ->label('Limit procentowy premii')
                                        ->suffix('%'),

                                    TextInput::make('start_rozprawa')
                                        ->label('Opłata za rozprawę')
                                        ->suffix('zł'),

                                    TextInput::make('start_razem_max')
                                        ->label('Razem maksymalnie')
                                        ->suffix('zł')
                                        ->columnSpanFull(),

                                ])->columns(2),

                            Section::make('Wariant: Max')
                                ->columnSpan(1)
                                ->schema([

                                    TextInput::make('max_wstepna')
                                        ->label('Opłata wstępna')
                                        ->afterStateUpdated(fn (Set $set, Get $get) => $set('max_razem_max', $get('max_wstepna') + $get('max_druga_instancja') + $get('max_rozprawy_limit')))
                                        ->live()
                                        ->suffix('zł'),

                                    TextInput::make('max_druga_instancja')
                                        ->label('Druga instancja')
                                        ->afterStateUpdated(fn (Set $set, Get $get) => $set('max_razem_max', $get('max_wstepna') + $get('max_druga_instancja') + $get('max_rozprawy_limit')))
                                        ->live()
                                        ->suffix('zł'),

                                    TextInput::make('max_rozprawa')
                                        ->label('Opłata za rozprawę')
                                        ->suffix('zł'),

                                    TextInput::make('max_rozprawy_limit')
                                        ->readOnly()
                                        ->live()
                                        ->label('Limit opłat za rozprawy')
                                        ->suffix('zł'),

                                    TextInput::make('max_razem_max')
                                        ->label('Razem maksymalnie')
                                        ->suffix('zł')
                                        ->columnSpanFull(),

                            ])->columns(2)

                        ])->columns(2)->columnSpanFull()

                    ])

            ]);
    }


    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('created_at')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Data')
                    // ->formatStateUsing(fn($state) => substr($state, 0, 10)),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->modalWidth(Width::SevenExtraLarge)
                    ->modalHeading('Nowa oferta')
            ])
            ->recordActions([

                Action::make('downloadPdf')
                        ->label('Pobierz ofertę')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function ($record, LivewireComponent $livewire): void {

                            $disk = 'private';

                            // 1) Jeśli PDF już jest – tylko pobierz
                            if ($record->pdf_path && Storage::disk($disk)->exists($record->pdf_path)) {
                                $url = route('offers.pdf.download', $record);

                                // otwórz pobieranie w nowej karcie (pewniejsze w Livewire)
                                $livewire->js("window.open('{$url}', '_blank');"); // :contentReference[oaicite:1]{index=1}
                                return;
                            }

                            // 2) Wygeneruj PDF i zapisz
                            $path = "offers/{$record->id}/offer-{$record->id}.pdf";

                            // Przykład z DOMPDF:
                            $pdf = Pdf::loadView('print.oferta', ['offer' => $record]);
                            Storage::disk($disk)->put($path, $pdf->output());

                            $record->forceFill([
                                'pdf_path' => $path,
                                'pdf_generated_at' => now(),
                            ])->save();

                            Notification::make()
                                ->title('PDF wygenerowany.')
                                ->success()
                                ->send();

                            // 3) Pobierz (już istnieje)
                            $url = route('offers.pdf.download', $record);
                            $livewire->js("window.open('{$url}', '_blank');");
                        }),

                // EditAction::make()->iconButton()->modalWidth(MaxWidth::SevenExtraLarge),
                ViewAction::make()->iconButton()->modalWidth(Width::SevenExtraLarge),
                DeleteAction::make()->iconButton(),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    function parsePlMoney(?string $value): ?float
    {
        if (! $value) return null;

        // usuń walutę i spacje (także NBSP)
        $s = preg_replace('/[^\d,.\-]/u', '', str_replace("\u{00A0}", '', $value));

        if ($s === '' || $s === '-' ) return null;

        // format PL: kropki jako tysiące, przecinek jako dziesiętne
        $s = str_replace('.', '', $s);
        $s = str_replace(',', '.', $s);

        return is_numeric($s) ? (float) $s : null;
    }

    function lastLetterOfFirstName(string $name): string
    {
        $name = trim($name);
        if ($name === '') return '';

        // rozbij po dowolnej liczbie białych znaków (spacje, taby)
        $parts = preg_split('/\s+/', $name);
        $firstName = $parts[0] ?? '';

        if ($firstName === '') return '';

        // bezpiecznie dla polskich znaków (UTF-8)
        return mb_substr($firstName, -1, 1, 'UTF-8');
    }

    public static function fx(string|DateTimeInterface $date, string $currency, float|int|string $amount): float
    {
        $currency = strtoupper(trim($currency));

        $columnMap = [
            'CHF' => 'chf',
            'EUR' => 'eur',
            'USD' => 'usd',
        ];

        if (! isset($columnMap[$currency])) {
            throw new InvalidArgumentException("Nieobsługiwana waluta: {$currency}");
        }

        $column = $columnMap[$currency];

        // normalizacja daty do Y-m-d
        $d = $date instanceof DateTimeInterface
            ? Carbon::instance($date)->startOfDay()
            : Carbon::parse($date)->startOfDay();

        // normalizacja kwoty (gdyby przyszła jako "18.713,85")
        if (is_string($amount)) {
            $s = str_replace("\u{00A0}", ' ', $amount);
            $s = preg_replace('/[^\d,.\- ]/u', '', $s);
            $s = preg_replace('/\s+/', '', $s);
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
            $amount = is_numeric($s) ? (float) $s : 0.0;
        }

        // Szukamy najbliższego kursu <= podanej daty (to jest wydajniejsze niż cofanie pętlą)
        $rate = ExchangeRate::query()
            ->whereDate('date', '<=', $d->toDateString())
            ->orderByDesc('date')
            ->first();

        if (! $rate) {
            throw new RuntimeException("Brak jakichkolwiek kursów w bazie danych (dla daty {$d->toDateString()} i wcześniejszych).");
        }

        $fx = (float) ($rate->{$column} ?? 0);

        if ($fx <= 0) {
            throw new RuntimeException("Brak/niepoprawny kurs {$currency} w kolumnie '{$column}' dla daty {$rate->date}.");
        }

        return $fx;
    }

}
