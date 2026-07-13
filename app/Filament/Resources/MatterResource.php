<?php

namespace App\Filament\Resources;

use App\Filament\Crm\Resources\CHFPotentialMatterResource;
use App\Filament\Resources\MatterResource\Pages\CreateMatter;
use App\Filament\Resources\MatterResource\Pages\EditMatter;
use App\Filament\Resources\MatterResource\Pages\ListMatters;
use App\Forms\departamentForm;
use App\Models\Branch;
use App\Models\Contact;
use App\Models\Departament;
use App\Models\Matter;
use App\Models\TemplateStage;
use App\Models\User;
use App\Models\Website\Lead as WebsiteLead;
use App\Support\Website\PostalCodeLookup;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\HtmlString;

class MatterResource extends Resource
{
    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'sprawy';

    protected static ?string $model = Matter::class;

    protected static ?string $navigationLabel = 'Sprawy';

    protected static ?string $modelLabel = 'Sprawa';

    protected static ?string $pluralModelLabel = 'Sprawy';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $shouldRegisterNavigation = false;

    public static function getEditUrlForMatter(?Matter $matter, array $query = []): ?string
    {
        if (! $matter) {
            return null;
        }

        $resource = static::getResourceForMatter($matter);
        $panel = $resource === CHFPotentialMatterResource::class ? 'crm' : 'kancelaria';
        $url = $resource::getUrl('edit', ['record' => $matter], panel: $panel);

        if ($query === []) {
            return $url;
        }

        return $url.'?'.http_build_query($query);
    }

    public static function getResourceForMatter(Matter $matter): string
    {
        return match ($matter->category) {
            'CHF' => $matter->is_matter ? CHFMatterResource::class : CHFPotentialMatterResource::class,
            'Powództwo banku' => BankMatterResource::class,
            'O zapłatę' => CHFPaymentMatterResource::class,
            'Sprawy inne' => OtherMatterResource::class,
            default => static::class,
        };
    }

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

    // public static function categories() {
    //     return [
    //         'CHF' => 'CHF',
    //         'Powództwo banku' => 'Powództwo banku',
    //         'Powództwo banku' => 'Powództwo banku',
    //         'Sprawy inne' => 'Sprawy inne'
    //     ];
    // }

    public static function branches()
    {
        return Branch::query()
            ->ordered()
            ->pluck('label', 'label')
            ->all();
    }

    public static function branch_lawyer()
    {
        return Branch::query()
            ->ordered()
            ->pluck('user_id', 'label')
            ->all();
    }

    public static function branchOptionsForMatter(?Matter $record = null): array
    {
        return Branch::query()
            ->when(
                $record?->branch_id,
                fn (QueryBuilder $query): QueryBuilder => $query
                    ->where(fn (QueryBuilder $query): QueryBuilder => $query
                        ->acceptingNewMatters()
                        ->orWhere('id', $record->branch_id)),
                fn (QueryBuilder $query): QueryBuilder => $query->acceptingNewMatters(),
            )
            ->ordered()
            ->pluck('label', 'id')
            ->all();
    }

    public static function defaultBranch(): ?Branch
    {
        return Branch::query()
            ->defaultForNewMatters()
            ->first()
            ?? Branch::query()->acceptingNewMatters()->ordered()->first();
    }

    public static function notificationRecipientsForm(Schema $schema): Schema
    {
        return $schema->components([
            static::notificationRecipientsSection(),
        ]);
    }

    public static function form(Schema $schema, $category = 'CHF', $is_matter = 1): Schema
    {
        return $schema
            ->components([
                Group::make()->schema([
                    Section::make('Szczegóły sprawy')
                        ->collapsible()
                        ->schema([
                            TextInput::make('label')
                                ->required()
                                ->label('Identyfikator sprawy')
                                ->placeholder('Np. „Nowak Piotr i Anna / mBank”')->columnSpan(2),
                            Hidden::make('branch')->default(fn () => static::defaultBranch()?->label ?? 'Głogów'),
                            Select::make('branch_id')
                                ->required()
                                ->options(fn (?Matter $record) => static::branchOptionsForMatter($record))
                                ->default(fn () => static::defaultBranch()?->getKey())
                                ->label('Oddział')
                                ->live()
                                ->afterStateHydrated(function (Set $set, ?string $state): void {
                                    if (! $state) {
                                        return;
                                    }

                                    $branch = Branch::find($state);

                                    if (! $branch) {
                                        return;
                                    }

                                    $set('branch', $branch->label);
                                })
                                ->afterStateUpdated(function (Set $set, $state) {
                                    if ($state) {
                                        $branch = Branch::find($state);

                                        if (! $branch) {
                                            return;
                                        }

                                        $set('lawyer_id', $branch->user_id);
                                        $set('branch', $branch->label);
                                    }
                                })
                                ->native(false),
                            Select::make('lawyer_id')
                                ->required()
                                ->default(1)
                                // ->relationship(name: 'opiekun', titleAttribute: 'name')
                                ->options(function () {
                                    // return User::role(['Super Admin', 'Partner', 'Samodzielny prawnik'])->pluck('name', 'id');
                                    return User::responsible_lawyers()->pluck('name', 'id');
                                })
                                ->label('Referat')
                                ->native(false),
                            Hidden::make('category')
                                ->required()
                                ->label('Rodzaj sprawy')
                                ->default($category),
                            // ->options(TemplateStageResource::rodzaje_spraw())
                            // ->native(false),
                            Hidden::make('is_matter')
                                ->required()
                                ->label('Typ')
                                ->default($is_matter),
                            DatePicker::make('start')->label('Data rozpoczęcia')->visible($is_matter),
                            DatePicker::make('end')->label('Data zakończenia')->visible($is_matter),
                            TextInput::make('gdrive')->label('Folder sprawy')->columnSpan(2),
                            Toggle::make('is_archived')->label('Zarchiwizowana?')->columnSpan(2),

                        ])->columns(2),

                    Section::make('Pełnomocnik banku')
                        ->visibleOn('edit')
                        ->hidden(! $is_matter)
                        ->collapsible()
                        ->schema([

                            Select::make('opponent_lawyer_id')
                                ->label('Pełnomocnik')
                                ->relationship(name: 'opponent_lawyer', titleAttribute: 'sort_name')
                                ->createOptionForm(contactForm('Pełnomocnik'))
                                ->editOptionForm(contactForm())
                                ->searchable()
                                ->live()
                                ->afterStateUpdated(function (Set $set, $state) {

                                    if ($state === null) {

                                        $set('opponent_lawfirm_id', null);

                                    } else {

                                        $contact = Contact::where('id', $state)->first();

                                        if (! empty($contact->lawfirm_id)) {
                                            $set('opponent_lawfirm_id', $contact->lawfirm_id);
                                        }

                                        if (! empty($contact->departament_id)) {
                                            $set('opponent_departament_id', $contact->departament_id);
                                        }

                                    }

                                }),

                            Select::make('opponent_lawfirm_id')
                                ->label('Kancelaria')
                                ->relationship(
                                    name: 'opponent_lawfirm',
                                    titleAttribute: 'sort_name',
                                )
                                ->createOptionForm(contactForm('Kancelaria'))
                                ->editOptionForm(contactForm())
                                ->searchable()
                                ->live(),
                            //                             ->suffixAction(
                            //                                 Action::make('Kopiuj adres')
                            //                                     ->icon('heroicon-m-document-duplicate')
                            //                                     ->color('gray')
                            //                                     ->hidden(function ($record) {
                            //                                         return !$record || $record->opponent_departament || !$record->opponent_lawfirm || !$record->opponent_lawfirm->organization || !$record->opponent_lawfirm->address || !$record->opponent_lawfirm->zip_code || !$record->opponent_lawfirm->city;
                            //                                     })
                            //                                     ->action(function ($record, $livewire) {
                            //                                         $livewire->dispatch('copy-to-clipboard', $record->opponent_lawfirm->organization. '
                            // '.$record->opponent_lawfirm->address.'
                            // '.$record->opponent_lawfirm->zip_code.' '.$record->opponent_lawfirm->city);

                            //                                         Notification::make('')->title('Skopiowano adres do schowka.')->success()->send();
                            //                                     })
                            //                             ),

                            Select::make('opponent_departament_id')
                                ->hidden(function (Get $get) {

                                    if (empty($get('opponent_lawyer_id'))) {
                                        return true;
                                    } else {
                                        return Departament::where('contact_id', $get('opponent_lawfirm_id'))->count() == 0;
                                    }

                                })
                                ->label('Odział kancelarii')
                                ->createOptionForm(fn (Get $get) => departamentForm::form($get('opponent_lawfirm_id')))
                                ->editOptionForm(fn (Get $get) => departamentForm::form($get('opponent_lawfirm_id')))
                                ->relationship(
                                    name: 'opponent_departament',
                                    titleAttribute: 'label',
                                    modifyQueryUsing: fn (QueryBuilder $query, Get $get) => $query->where('contact_id', $get('opponent_lawfirm_id')),
                                )
                                ->preload()
//                             ->suffixAction(
//                                 Action::make('Kopiuj adres')
//                                     ->icon('heroicon-m-document-duplicate')
//                                     ->color('gray')
//                                     ->hidden(function ($record) {
//                                         return !$record || !$record->opponent_lawfirm || !$record->opponent_lawfirm->organization || !$record->opponent_departament || !$record->opponent_departament->address || !$record->opponent_departament->zip_code || !$record->opponent_departament->city;
//                                     })
//                                     ->action(function ($record, $livewire) {
//                                         $livewire->dispatch('copy-to-clipboard', $record->opponent_lawfirm->organization. '
// '.$record->opponent_departament->label.'
// '.$record->opponent_departament->address.'
// '.$record->opponent_departament->zip_code.' '.$record->opponent_departament->city);

//                                         Notification::make('')->title('Skopiowano adres do schowka.')->success()->send();
//                                     })
//                             )
                                ->searchable(),
                        ]),
                    static::sourceLeadFormSection((bool) $is_matter),
                ]),
                Group::make()->schema([

                    Section::make('Informacje od klienta')
                        ->collapsible()
                        ->schema([
                            Builder::make('userinfo')
                                ->label(fn (?array $state) => count($state) > 0 ? 'Informacje' : 'Dotychczas nie dodano żadnych informacji.')
                                ->collapsible()
                                ->collapsed()
                                ->deleteAction(fn (Action $action) => $action->requiresConfirmation())
                                ->blockNumbers(false)
                                ->addable(fn (?array $state) => count($state) < 3)
                                ->blocks([
                                    Block::make('Status konsumenta')->maxItems(1)
                                        ->schema([

                                            Radio::make('czy_kiedykolwiek_dzialalnosc')
                                                ->label('Czy kredytobiorcy kiedykolwiek prowadzili działalność gospodarczą?')
                                                ->options(['Tak' => 'Tak', 'Nie' => 'Nie'])
                                                ->inline()
                                                ->inlineLabel(false)
                                                ->live(),

                                            Radio::make('dzialalnosc_w_chwili_umowy')
                                                ->label('Czy prowadzili w chwili zawierania umowy kredytowej?')
                                                ->visible(fn (Get $get): string => $get('czy_kiedykolwiek_dzialalnosc') == 'Tak')
                                                ->options(['Tak' => 'Tak', 'Nie' => 'Nie'])
                                                ->inline()
                                                ->inlineLabel(false)
                                                ->live(),

                                            Textarea::make('dzialalnosc_okres')
                                                ->label('W jakim okresie była prowadzona działalność?')
                                                ->hidden(fn (Get $get): string => ($get('dzialalnosc_w_chwili_umowy') == null || $get('czy_kiedykolwiek_dzialalnosc') == 'Nie')),

                                            Textarea::make('dzialalnosc_charakter')
                                                ->label('Na czym polegała ta działalność?')
                                                ->hidden(fn (Get $get): string => ($get('dzialalnosc_w_chwili_umowy') == null || $get('czy_kiedykolwiek_dzialalnosc') == 'Nie')),

                                            Textarea::make('dzialalnosc_zwiazek')
                                                ->label('Czy działalność gospodarcza miała związek z kredytowaną nieruchomością? W szczególności czy była na nieruchomości zarejestronwa i prowadzona?')
                                                ->hidden(fn (Get $get): string => ($get('dzialalnosc_w_chwili_umowy') == null || $get('czy_kiedykolwiek_dzialalnosc') == 'Nie')),

                                            Textarea::make('dzialalnosc_koszty')
                                                ->label('Czy koszty związane z kredytem lub z korzystaniem z nieruchomości były rozliczane w ramach działalności gospodarczej?')
                                                ->hidden(fn (Get $get): string => ($get('dzialalnosc_w_chwili_umowy') == null || $get('czy_kiedykolwiek_dzialalnosc') == 'Nie')),

                                            Radio::make('najem')
                                                ->label('Czy nieruchomość była wynajmowana?')
                                                ->options(['Tak' => 'Tak', 'Nie' => 'Nie'])
                                                ->inline()
                                                ->inlineLabel(false)
                                                ->live(),

                                            Textarea::make('najem_szczegoly')
                                                ->label('W jakim okresie? Komu? Na jakie cele?')
                                                ->visible(fn (Get $get): string => $get('najem') == 'Tak'),

                                            Radio::make('zamieszkanie')
                                                ->label('Czy kredytobiorcy nadal zamieszkują na nieruchomości?')
                                                ->options(['Tak' => 'Tak', 'Nie' => 'Nie'])
                                                ->inline()
                                                ->inlineLabel(false)
                                                ->live(),

                                            Textarea::make('zamieszkanie_do')
                                                ->label('Od kiedy nie zamieszkują? Dlaczego?')
                                                ->visible(fn (Get $get): string => $get('zamieszkanie') == 'Nie'),

                                            Textarea::make('wyksztalcenie')
                                                ->label('Wykształcenie w chwili zawierania umowy kredytowej?'),

                                            Textarea::make('zawod')
                                                ->label('Zawód w chwili zawierania umowy kredytowej?'),

                                            Radio::make('wczesniejszy_kredyt')
                                                ->label('Czy kredytobiorcy posiadali wcześniej kredyt powiązany z walutą obcą?')
                                                ->options(['Tak' => 'Tak', 'Nie' => 'Nie'])->inline()->inlineLabel(false)->live(),

                                            Textarea::make('wczesniejszy_kredyt_desc')
                                                ->label('Z którego roku? Na co zaciągnięty? Kiedy spłacony?')
                                                ->visible(fn (Get $get): string => $get('wczesniejszy_kredyt') == 'Tak'),

                                        ]),

                                    Block::make('Okoliczności zawarcia umowy')->maxItems(1)
                                        ->schema([

                                            Textarea::make('dlaczego_walutowy')
                                                ->label('Dlaczego kredytobiorcy zaciagnęli kredyt powiązany z walutą obcą?'),

                                            Radio::make('zdolnosc_pln')->options(['Tak' => 'Tak', 'Nie' => 'Nie', 'Nie pamiętają' => 'Nie pamiętają'])
                                                ->label('Czy była informacja o braku zdolności w PLN?')->inline()->inlineLabel(false),

                                            Radio::make('gdzie_formalnosci')->options(['pośrednik' => 'U pośrednika', 'bank' => 'W banku', 'mix' => 'U pośrednika, ale podpisanie umowy w banku', 'Nie pamiętają' => 'Nie pamiętają'])
                                                ->label('Gdzie załatwiano formalności związane z kredytem?')->inline()->inlineLabel(false),

                                            Radio::make('info_stabilnosc')->options(['Tak' => 'Tak', 'Nie' => 'Nie', 'Nie pamiętają' => 'Nie pamiętają'])
                                                ->label('Czy informowano o stabilności waluty obcej?')->inline()->inlineLabel(false),

                                            Radio::make('historyczne_kursy')->options(['Tak' => 'Tak', 'Nie' => 'Nie', 'Nie pamiętają' => 'Nie pamiętają'])
                                                ->label('Czy pokazywano historyczne kursy?')->inline()->inlineLabel(false),

                                            Radio::make('symulacje')->options(['Tak' => 'Tak', 'Nie' => 'Nie', 'Nie pamiętają' => 'Nie pamiętają'])
                                                ->label('Czy pokazano symulacje obrazujące skutki wzrostu kursu?')->inline()->inlineLabel(false),

                                            Radio::make('porownanie')->options(['Tak' => 'Tak', 'Nie' => 'Nie', 'Nie pamiętają' => 'Nie pamiętają'])
                                                ->label('Czy porównywano parametry kredytu PLN i powiązanego z walutą?')->inline()->inlineLabel(false),

                                            Radio::make('info_ryzyko')->options(['Tak' => 'Tak', 'Nie' => 'Nie', 'Nie pamiętają' => 'Nie pamiętają'])
                                                ->label('Czy informowano o „ryzyku kursowym”?')->inline()->inlineLabel(false)->live(),

                                            Textarea::make('co_o_ryzyku')
                                                ->label('Jakie informacje o ryzyku kursowym przedstawiono?')
                                                ->visible(fn (Get $get): string => $get('info_ryzyko') == 'Tak'),

                                            Radio::make('splata_innego')->options(['Tak' => 'Tak', 'Nie' => 'Nie'])
                                                ->label('Czy kredyt zaciągnięty na spłatę innego kredytu?')->inline()->inlineLabel(false)->live(),

                                            Textarea::make('inny_kredyt')
                                                ->label('Czy wcześniejszy kredyt powiązany z walutą? W którym roku zaciągnięty?')
                                                ->visible(fn (Get $get): string => $get('splata_innego') == 'Tak'),

                                        ]),

                                    Block::make('Wykonywanie umowy')->maxItems(1)
                                        ->schema([

                                            Radio::make('waluta_splaty')->options(['PLN' => 'Cały czas PLN', 'mix' => 'Początkowo PLN, następnie w walucie obcej', 'obca' => 'Od początku w walucie obcej'])
                                                ->label('W jakiej walucie spłacano kredyt?')->inline()->inlineLabel(false),

                                            Radio::make('sposob_splaty')->options(['pobranie' => 'Bank pobierał z konta', 'przelew' => 'Przelew na konto banku', 'inaczej' => 'Inaczej'])
                                                ->label('W jaki sposób spłacano raty?')->inline()->inlineLabel(false)->live(),

                                            Textarea::make('sposob_splaty_desc')
                                                ->label('Na czym polegał inny sposób spłacania rat?')
                                                ->visible(fn (Get $get): string => $get('sposob_splaty') == 'inaczej'),

                                            Radio::make('czy_splacony')->options(['Tak' => 'Tak', 'Nie' => 'Nie'])
                                                ->label('Czy kredyt został już spłacony?')->inline()->inlineLabel(false)->live(),

                                            Radio::make('przedterminowa_splata')->options(['Tak' => 'Tak', 'Nie' => 'Nie'])
                                                ->label('Czy kredytobiorcy rozważają przedterminową spłatę kredytu?')->inline()->inlineLabel(false)->live()
                                                ->visible(fn (Get $get): string => $get('czy_splacony') == 'Nie'),

                                            Radio::make('wielu_kredytobiorcow')->options(['Tak' => 'Tak', 'Nie' => 'Nie'])
                                                ->label('Czy jest więcej niż 1 kredytobiorca?')->inline()->inlineLabel(false)->live(),

                                            Textarea::make('sposob_splaty_przez_wielu')
                                                ->label('Z czyich środków spłacany był kredyt? Z majątku wspólnego? W cześciach równych przez każdego kredytobiorcę? Tylko przez niektórych kredytobiorców?')
                                                ->visible(fn (Get $get): string => $get('wielu_kredytobiorcow') == 'Tak'),

                                            Radio::make('kwestionowanie')->options(['Tak' => 'Tak', 'Nie' => 'Nie'])
                                                ->label('Czy ważność umowy była przez kredytobiorców już kwestionowana?')->inline()->inlineLabel(false)->live(),

                                            Textarea::make('kwestionowanie_desc')
                                                ->label('W jaki sposób kwestionowano ważność umowy? Kiedy?')
                                                ->visible(fn (Get $get): string => $get('kwestionowanie') == 'Tak'),

                                            Radio::make('spory')->options(['Tak' => 'Tak', 'Nie' => 'Nie'])
                                                ->label('Czy toczyły się jakieś postępowania sądowe dot. umowy kredytowej?')->inline()->inlineLabel(false)->live(),

                                            Textarea::make('spory_desc')
                                                ->label('Jakie postępowania się toczyły? W jaki sposób się zakończyły?')
                                                ->visible(fn (Get $get): string => $get('spory') == 'Tak'),

                                            Radio::make('istotne')->options(['Tak' => 'Tak', 'Nie' => 'Nie'])
                                                ->label('Czy w sprawie występują inne istotne okoliczności?')->inline()->inlineLabel(false)->live(),

                                            Textarea::make('istotne_desc')
                                                ->label('Jakie to okoliczności?')
                                                ->visible(fn (Get $get): string => $get('istotne') == 'Tak'),
                                        ]),

                                ])->addActionLabel('Dodaj nowe informacje'),
                        ])
                        ->hidden(fn ($record) => ! $record || $record->category != 'CHF'),

                    /*
                Section::make('Kredytobiorcy')

                    ->hidden(fn ($record) => $record->credits()->count() === 0)
                    ->collapsible()
                    ->schema([

                        Repeater::make('credits')
                            ->label('')
                            ->addable(function (array $state) {

                                // dd($state);
                            })
                            ->itemLabel(function (array $state) {

                                $credits = Matter::where('id', $state['matter_id'])->first()->credits->count();

                                return $credits > 1 ? 'Umowa nr ' . $state['number'] : null;

                            })
                            ->deletable(false)
                            ->relationship('credits')
                            ->schema([
                                Repeater::make('contacts')
                                ->relationship('contactCredit')
                                ->label('')
                                ->required()
                                ->orderColumn('sort')
                                ->addActionLabel('Dodaj kredytobiorcę')
                                ->simple(
                                    Select::make('contact_id')
                                        ->label('')
                                        ->native(false)
                                        ->required()
                                        ->createOptionForm(contactForm('Kredytobiorca'))
                                        ->editOptionForm(contactForm())
                                        ->createOptionModalHeading('Nowy kredytobiorca')
                                        ->relationship('borrower', 'sort_name')
                                        ->searchable()
                                )->columnSpan(2)
                                ->deleteAction(
                                    fn (Action $action) => $action->requiresConfirmation(),
                                )
                            ]),

                    ])
                    ->hidden(fn ($record) =>  !$record || $record->category != 'CHF'),
*/

                    static::notificationRecipientsSection(),

                ]),

            ]);
    }

    protected static function sourceLeadFormSection(bool $isMatter): Section
    {
        return Section::make('Dane z formularza leada')
            ->collapsible()
            ->schema([
                Placeholder::make('source_lead_name')
                    ->label('Imię i nazwisko')
                    ->content(fn (?Matter $record): string => self::sourceLeadText(self::sourceLead($record)?->name)),
                Placeholder::make('source_lead_email')
                    ->label('E-mail')
                    ->content(fn (?Matter $record): string => self::sourceLeadText(self::sourceLead($record)?->email)),
                Placeholder::make('source_lead_phone')
                    ->label('Telefon')
                    ->content(fn (?Matter $record): string => self::sourceLeadText(self::sourceLead($record)?->phone)),
                Placeholder::make('source_lead_postal_location')
                    ->label('Lokalizacja')
                    ->content(fn (?Matter $record): string => self::sourceLeadPostalLocation(self::sourceLead($record))),
                Placeholder::make('source_lead_bank')
                    ->label('Bank')
                    ->content(fn (?Matter $record): string => self::sourceLeadText(self::sourceLead($record)?->bank)),
                Placeholder::make('source_lead_contract_year_range')
                    ->label('Rok umowy')
                    ->content(fn (?Matter $record): string => self::sourceLeadText(self::sourceLead($record)?->contract_year_range)),
                Placeholder::make('source_lead_credit_currency')
                    ->label('Waluta kredytu')
                    ->content(fn (?Matter $record): string => self::sourceLeadText(self::sourceLead($record)?->credit_currency)),
                Placeholder::make('source_lead_credit_amount_range')
                    ->label('Kwota kredytu')
                    ->content(fn (?Matter $record): string => self::sourceLeadText(self::sourceLead($record)?->credit_amount_range)),
                Placeholder::make('source_lead_credit_status')
                    ->label('Status kredytu')
                    ->content(fn (?Matter $record): string => self::sourceLeadText(self::sourceLead($record)?->credit_status)),
                Placeholder::make('source_lead_has_contract')
                    ->label('Czy klient ma umowę?')
                    ->content(fn (?Matter $record): string => self::sourceLeadHasContract(self::sourceLead($record))),
                Placeholder::make('source_lead_additional_info')
                    ->label('Dodatkowe informacje')
                    ->content(fn (?Matter $record): HtmlString => self::sourceLeadAdditionalInfo(self::sourceLead($record)))
                    ->columnSpanFull(),
            ])
            ->columns(2)
            ->visibleOn('edit')
            ->hidden(fn (?Matter $record): bool => $isMatter || ! $record?->sourceWebsiteLead);
    }

    private static function sourceLead(?Matter $matter): ?WebsiteLead
    {
        return $matter?->sourceWebsiteLead;
    }

    private static function sourceLeadText(?string $value): string
    {
        return filled($value) ? $value : '-';
    }

    private static function sourceLeadHasContract(?WebsiteLead $lead): string
    {
        if (! $lead) {
            return '-';
        }

        return $lead->has_contract ? 'Tak' : 'Nie';
    }

    private static function sourceLeadPostalLocation(?WebsiteLead $lead): string
    {
        if (! $lead) {
            return '-';
        }

        app(PostalCodeLookup::class)->fillMissingLeadRegion($lead);

        if (blank($lead->postal_code)) {
            return '-';
        }

        return collect([
            $lead->postal_code,
            filled($lead->postal_county) ? 'powiat '.$lead->postal_county : null,
            filled($lead->postal_voivodeship) ? 'województwo '.$lead->postal_voivodeship : null,
        ])->filter()->implode(', ');
    }

    private static function sourceLeadAdditionalInfo(?WebsiteLead $lead): HtmlString
    {
        $additionalInfo = self::sourceLeadAdditionalInfoText($lead);

        if (blank($additionalInfo)) {
            return new HtmlString('-');
        }

        return new HtmlString('<div class="prose prose-sm max-w-none">'.nl2br(e($additionalInfo)).'</div>');
    }

    private static function sourceLeadAdditionalInfoText(?WebsiteLead $lead): ?string
    {
        if (! $lead) {
            return null;
        }

        if (filled($lead->additional_info)) {
            return trim((string) $lead->additional_info);
        }

        if (blank($lead->message)) {
            return null;
        }

        if (! preg_match('/(?:^|\R)Dodatkowe informacje:\s*(.+)\z/su', (string) $lead->message, $matches)) {
            return null;
        }

        $additionalInfo = trim($matches[1]);

        return $additionalInfo === '' ? null : $additionalInfo;
    }

    protected static function notificationRecipientsSection(): Section
    {
        return Section::make('Klienci przypisani do sprawy')
            ->collapsible()
            ->label(fn (?array $state) => count($state) > 1 ? 'Przypisani klienci' : 'Dotychczas nie przypisano żadnych klientów.')
            ->schema([
                Repeater::make('contactMatters')
                    ->label(fn (?array $state) => count($state) > 0 ? 'Przypisani klienci' : 'Dotychczas nie przypisano żadnego klienta.')
                    ->relationship('contactMatters')
                    ->defaultItems(0)
                    ->addActionLabel('Dodaj klienta do sprawy')
                    ->schema([

                        Select::make('contact_id')
                            ->label('Klient')
                            ->relationship(
                                name: 'contact',
                                titleAttribute: 'sort_name',
                                modifyQueryUsing: fn ($query) => $query->where('category', 'Kredytobiorca')->orderBy('last_name')->orderBy('first_name'),
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm(contactForm('Kredytobiorca'))
                            ->editOptionForm(contactForm())
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->live(),

                        Placeholder::make('contact_email_info')
                            ->label('E-mail')
                            ->content(function (Get $get) {
                                $contactId = $get('contact_id');
                                $receivesNotifications = (bool) $get('receives_notifications');

                                if (! $contactId) {
                                    return new HtmlString('<span class="text-gray-500">Brak wybranego kontaktu</span>');
                                }

                                $email = Contact::where('id', $contactId)->value('email');

                                if ($email) {
                                    return new HtmlString(
                                        '<span>'.e($email).'</span>'
                                    );
                                }

                                if ($receivesNotifications) {
                                    return new HtmlString(
                                        '<span class="font-bold text-danger-600">Brak adresu e-mail, choć kontakt ma włączone powiadomienia.</span>'
                                    );
                                }

                                return new HtmlString(
                                    '<span class="text-gray-500">Brak adresu e-mail</span>'
                                );
                            }),

                        Checkbox::make('receives_notifications')
                            ->label('Otrzymuje powiadomienia')
                            ->live()
                            ->rule(function (Get $get) {
                                return function (string $attribute, $value, Closure $fail) use ($get) {
                                    if (! $value) {
                                        return;
                                    }

                                    $contactId = $get('contact_id');

                                    if (! $contactId) {
                                        $fail('Najpierw wybierz klienta.');

                                        return;
                                    }

                                    $email = Contact::where('id', $contactId)->value('email');

                                    if (! filled($email)) {
                                        $fail('Nie można włączyć powiadomień dla kontaktu bez adresu e-mail.');
                                    }
                                };
                            }),

                    ])
                    ->deleteAction(
                        fn (Action $action) => $action->requiresConfirmation(),
                    ),
            ])
            ->visible(fn ($record) => $record && $record->is_matter);
    }

    public static function table(Table $table, $show_created_at = false, $stages_hidden = false): Table
    {
        return $table

            ->columns([
                TextColumn::make('label')
                    ->weight(FontWeight::Bold)
                    ->size(TextSize::Medium)
                    ->searchable()
                    ->label('Sprawa'),
                TextColumn::make('currentStage.parent')
                    ->hidden($stages_hidden)
                    ->label('Etap')
                    ->toggleable(),
                TextColumn::make('currentStage.label')
                    ->hidden($stages_hidden)
                    ->label('Status')
                    ->toggleable(),
                TextColumn::make('lawyer.name')
                    ->label('Referat')
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_matter')
                    ->label('Sprawa?')
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_archived')
                    ->label('Zarchwizowana?')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->date()
                    ->label('Data utworzenia')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: ! $show_created_at),
                TextColumn::make('start')
                    ->date()
                    ->label('Start')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('end')
                    ->date()
                    ->label('Koniec')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([

                // Etap i status
                Filter::make('s')
                    ->schema([

                        Select::make('ps')
                            ->label('Etap')
                            ->options(TemplateStageResource::kategorie())
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('stage_id', null)),

                        Select::make('stage_id')
                            ->label('Status')
                            ->hidden(fn (Get $get): string => $get('ps') == null)
                            ->options(fn (Get $get) => TemplateStage::where('parent', $get('ps'))->where('is_active', true)->pluck('label', 'id')->unique())
                            ->native(false)
                            ->live(),
                    ])
                    ->query(
                        function (QueryBuilder $query, array $data): QueryBuilder {
                            return $query
                                ->when(
                                    $data['stage_id'],
                                    fn (QueryBuilder $query, $stageId): QueryBuilder => $query->where('current_template_stage_id', $stageId),
                                )
                                ->when(
                                    $data['ps'] && ! $data['stage_id'],
                                    fn (QueryBuilder $query): QueryBuilder => $query->whereHas(
                                        'currentStage',
                                        fn (QueryBuilder $query): QueryBuilder => $query->where('parent', $data['ps']),
                                    ),
                                );
                        }
                    ),

                TernaryFilter::make('is_matter')->label('Typ')->native(false)
                    ->placeholder('Wszystkie')
                    ->trueLabel('Tylko sprawy')
                    ->falseLabel('Tylko szanse'),
                // ->toggle()
                // ->default(true)
                // ->label('Ukryj szanse')
                // ->query(function (QueryBuilder $query, array $data): QueryBuilder {
                // return $query->where('is_matter', 0);
                // }),

                // Oddział
                SelectFilter::make('branch_id')
                    ->label('Oddział')
                    ->options(fn () => Branch::query()->ordered()->pluck('label', 'id'))
                    ->native(false),

                // Referat
                SelectFilter::make('lawyer')->label('Referat')->relationship('lawyer', 'name')->native(false),

                TernaryFilter::make('active')
                    ->label('Aktywne')
                    ->placeholder('Wszystkie')
                    ->trueLabel('Tylko aktywne')
                    ->falseLabel('Tylko zakończone')
                    ->native(false)
                    ->queries(
                        true: fn (QueryBuilder $query) => $query->whereNull('end'),
                        false: fn (QueryBuilder $query) => $query->whereNotNull('end'),
                        blank: fn (QueryBuilder $query) => $query,
                    ),

            ])
            // ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make()->iconButton(),
            ]);
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
            'index' => ListMatters::route('/'),
            'create' => CreateMatter::route('/create'),
            'edit' => EditMatter::route('/{record}/edit'),
        ];
    }
}
