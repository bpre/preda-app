<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Actions\Action;
use Filament\Support\Enums\TextSize;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\BulkAction;
use App\Filament\Resources\LetterResource\Pages\ListLetters;
use App\BP\neoznaczki;
use App\Filament\Resources\CHFMatterResource\RelationManagers\LettersRelationManager;
use App\Filament\Resources\LetterResource\Pages;
use App\Http\Controllers\Print\PrintEnvelopeController;
use App\Http\Controllers\PrintController;
use App\Models\Contact;
use App\Models\ContactLetter;
use App\Models\Departament;
use App\Models\Lawsuit;
use App\Models\Letter;
use App\Models\Matter;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class LetterResource extends Resource
{

    protected static ?int $navigationSort = 5;
    protected static ?string $slug = 'korespondencja';
    protected static ?string $model = Letter::class;
    protected static ?string $navigationLabel = 'Korespondencja';
    protected static ?string $modelLabel = 'Korespondencja';
    protected static ?string $pluralModelLabel = 'Korespondencja';
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

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Group::make()->schema([

                        Section::make('Informacje ogólne')
                            ->collapsible()
                            ->schema([

                                Select::make('matter_id')
                                    ->label('Sprawa')
                                    ->relationship(name: 'matter', titleAttribute: 'label')
                                    ->searchable()
                                    ->editOptionForm(fn (Schema $schema): Schema => MatterResource::notificationRecipientsForm($schema))
                                    ->editOptionModalHeading(function (Select $component): string {
                                        $matter = $component->getSelectedRecord();

                                        return filled($matter?->label)
                                            ? 'Sprawa: ' . $matter->label
                                            : 'Edytuj sprawę';
                                    })
                                    ->columnSpanFull()
                                    ->live()
                                    ->hiddenOn(LettersRelationManager::class),

                                Placeholder::make('notification_recipients_info')
                                    ->label('')
                                    ->content(function (Get $get) {
                                        $matterId = $get('matter_id');

                                        if (! $matterId) {
                                            return new HtmlString('<span class="text-gray-500">Wybierz sprawę, aby sprawdzić gotowość powiadomień.</span>');
                                        }

                                        $matter = Matter::query()
                                            ->with(['contacts' => function ($query) {
                                                $query->wherePivot('receives_notifications', true)
                                                    ->whereNotNull('email')
                                                    ->where('email', '!=', '');
                                            }])
                                            ->find($matterId);

                                        $count = $matter?->contacts?->count() ?? 0;

                                        if ($count > 0) {
                                            return new HtmlString(
                                                '<span class="font-medium text-success-600">Po zapisaniu pisma zostaną utworzone powiadomienia dla ' . $count . ' klient' . ($count === 1 ? 'a' : 'ów') . '.</span>'
                                            );
                                        }

                                        return new HtmlString(
                                            '<span class="font-bold text-danger-600">Ta sprawa nie ma żadnego klienta gotowego do powiadomień e-mail. Po zapisaniu pisma nie będzie można od razu wysłać powiadomienia.</span>'
                                        );
                                    })
                                    ->columnSpanFull()
                                    ->hiddenOn(LettersRelationManager::class),

                                ToggleButtons::make('type')
                                    ->label('Rodzaj korespondencji')
                                    ->options(Letter::TYP)
                                    ->inline()
                                    ->live()
                                    ->default('in')
                                    ->columnSpan(['default' => 4, 'lg' => 'full']),

                                TextInput::make('label')
                                    ->label('Nazwa pisma')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(['default' => 4, 'lg' => 'full']),

                                Textarea::make('description')
                                    ->label('Opis')
                                    ->maxLength(255)
                                    ->columnSpan(['default' => 4, 'lg' => 'full']),

                                DatePicker::make('date')
                                    ->label('Data')
                                    ->required()
                                    ->default(now())
                                    ->columnSpanFull(),

                            ]),


/*
                        Select::make('adresat')
                            ->label('Odbiorcy')
                            ->relationship(name: 'recipients', titleAttribute: 'sort_name')
                            ->searchable()
                            ->required()
                            ->multiple()
                            ->createOptionForm(contactForm())
                            ->createOptionModalHeading('Nowy kontakt')
                            ->hidden(fn (Get $get): string => $get('type') == 'in')
                            ->columnSpanFull(),
*/



                    Section::make('Nadawca')
                        ->collapsible()
                        ->hidden(fn (Get $get): string => $get('type') == 'out')
                        ->schema([
                            Select::make('sender_id')
                            ->label('Nadawca')
                            ->relationship(name: 'sender', titleAttribute: 'sort_name')
                            ->searchable()
                            ->required()
                            ->createOptionForm(contactForm())
                            ->createOptionModalHeading('Nowy kontakt')
                            ->columnSpanFull(),
                        ]),

                    Section::make('Odbiorcy')
                        ->collapsible()
                        ->hidden(fn (Get $get): string => $get('type') == 'in')
                        ->schema([

                            CheckboxList::make('suggested')->label('Sugerowani adresaci:')
                                ->dehydrated(false)
                                ->hidden(fn (Get $get): bool => count(static::getSuggestedRecipientOptions($get('matter_id'))) === 0)
                                ->options(fn (Get $get): array => static::getSuggestedRecipientOptions($get('matter_id')))
                                ->columns(2)
                                ->live()
                                ->afterStateUpdated(function(Set $set, Get $get, $state) {

                                    $items = [];

                                    $default_delivery_type = 'Polecony (S)';

                                    if($get('matter_id')) {

                                        $matter = Matter::where('id', $get('matter_id'))->first();

                                        $lawsuits = [];

                                        if($matter->lawsuits()->exists()) {
                                            foreach($matter->lawsuits as $lawsuit) {
                                                array_push($lawsuits, $lawsuit);
                                            }
                                        }

                                        // $opponent_lawyer = $matter->opponent_lawyer;
                                        // $bank = $matter->credits()->first()->current_banks;

                                        // ustal, czy mają departamenty

                                        // w pętli foreach poniżej porównaj, czy $item = ID podmiotu, jeśli tak - sprawdż czy ma departament i dodaj go

                                        $suggestedIds = array_keys(static::getSuggestedRecipientOptions($get('matter_id')));
                                        $selectedSuggestions = array_values(array_intersect(
                                            array_map('strval', $state ?? []),
                                            array_map('strval', $suggestedIds),
                                        ));

                                        foreach($selectedSuggestions as $item) {

                                            $departament = null;

                                            foreach($lawsuits as $lawsuit) {
                                                if((string) $lawsuit->court?->id === (string) $item) {
                                                    $departament = $lawsuit->departament?->id;
                                                }
                                            }

                                            array_push($items, array(
                                                "contact_id" => $item,
                                                "departament_id" => $departament,
                                                "delivery_type" => $default_delivery_type,
                                                "neostamp_id" => null
                                            ));

                                        }



                                        // if(in_array('s1', $state)) {

                                        //     $lawsuit = Lawsuit::where('matter_id', $matter->id)->where('instance', 'I instancja')->first();

                                        //     $contact_id = $lawsuit->court_id;
                                        //     $departament_id = $lawsuit->departament_id ?? null;

                                        //     array_push($items, array(
                                        //         "contact_id" => $contact_id,
                                        //         "departament_id" => $departament_id,
                                        //         "delivery_type" => $default_delivery_type,
                                        //         "neostamp_id" => null
                                        //     ));
                                        // }

                                        // if(in_array('s2', $state)) {

                                        //     $lawsuit = Lawsuit::where('matter_id', $matter->id)->where('instance', 'II instancja')->first();

                                        //     $contact_id = $lawsuit->court_id;
                                        //     $departament_id = $lawsuit->departament_id ?? null;

                                        //     array_push($items, array(
                                        //         "contact_id" => $contact_id,
                                        //         "departament_id" => $departament_id,
                                        //         "delivery_type" => $default_delivery_type,
                                        //         "neostamp_id" => null
                                        //     ));
                                        // }

                                        // if(in_array('pełnomocnik', $state)) {

                                        //     array_push($items, array(
                                        //         "contact_id" => $matter->opponent_lawyer_id,
                                        //         "departament_id" => $matter->opponent_departament_id,
                                        //         "delivery_type" => $default_delivery_type,
                                        //         "neostamp_id" => null
                                        //     ));
                                        // }

                                        // if(in_array('bank', $state)) {

                                        //     $contact_id = $matter->credits()->first()->current_bank;

                                        //     array_push($items, array(
                                        //         "contact_id" => $contact_id,
                                        //         "departament_id" => null,
                                        //         "delivery_type" => $default_delivery_type,
                                        //         "neostamp_id" => null
                                        //     ));
                                        // }

                                        $set('recipients', $items);

                                    }

                                }),

                            Repeater::make('recipients')
                                ->relationship('contactLetter')
                                ->label('Lista odbiorców')
                                ->required()
                                ->live()
                                ->orderColumn('sort')
                                ->addActionLabel('Dodaj odbiorcę')
                                ->reorderable(false)
                                ->afterStateHydrated(function (Set $set, Get $get, $state) {
                                    $selected = [];
                                    if(!empty($state)) {
                                        foreach ($state as $record) {
                                            array_push($selected, $record['contact_id']);
                                        }
                                    }
                                    $set('suggested', array_values(array_intersect(
                                        array_map('strval', $selected),
                                        array_map('strval', array_keys(static::getSuggestedRecipientOptions($get('matter_id')))),
                                    )));
                                })
                                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                    $selected = [];
                                    if(!empty($state)) {
                                        foreach ($state as $record) {
                                            if(!empty($record['contact_id'])) {
                                                array_push($selected, $record['contact_id']);
                                            }
                                        }
                                    }
                                    $set('suggested', array_values(array_intersect(
                                        array_map('strval', $selected),
                                        array_map('strval', array_keys(static::getSuggestedRecipientOptions($get('matter_id')))),
                                    )));
                                })
                                ->schema([

                                    Select::make('contact_id')
                                        ->label('Nazwa odbiorcy')
                                        ->required()
                                        ->live()
                                        // ->createOptionForm(contactForm())
                                        // ->editOptionForm(contactForm())
                                        // ->createOptionModalHeading('Nowy kontakt')
                                        ->relationship('contact', 'sort_name')
                                        ->searchable()
                                        ->preload()
                                        ->columnSpan(2),

                                    Select::make('departament_id')
                                        ->label('Jednostka organizacyjna')
                                        ->options(function (Get $get) {

                                            return Departament::where('contact_id', $get('contact_id'))->get()->sortBy('sort')->pluck('label', 'id');

                                        })
                                        ->native(false)
                                        ->columnSpan(2)
                                        ->hidden(function (Get $get) {

                                            $contact = Contact::where('id', $get('contact_id'))->get()->first();

                                            if(!$contact) { return true; }

                                            return !($get('contact_id') && $contact->departaments()->exists());
                                        }),

                                    Select::make('delivery_type')
                                        ->label('Rodzaj przesyłki')
                                        ->required()
                                        ->live()
                                        ->options(NeostampResource::typesExtended())
                                        ->default('Polecony (S)')
                                        ->native(false),

                                    Select::make('neostamp_id')
                                        // ->label('Numer przesyłki')
                                        ->label(fn ($record)
                                            => $record ? ($record->neostamp_id ? 'Nr przesyłki' : '') : '')
                                        ->relationship('neostamps', 'label')
                                        ->disabled()
                                        ->native(false)
                                        ->hintAction(
                                            Action::make('przypisz neoznaczek')
                                                ->icon('heroicon-m-cpu-chip')
                                                ->action(function ($record, $set) {

                                                    $set('neostamp_id', neoznaczki::przypiszZnaczek($record));

                                                })
                                                ->hidden(function ($record) {
                                                    return $record === NULL || $record->neostamp_id !== NULL;
                                                })
                                        )->hidden(function ($get, $record) {

                                            return $record === NULL ||
                                                !in_array($get('delivery_type'), NeostampResource::types()) || $record->delivery_type != $get('delivery_type');

                                            // if(in_array($get('delivery_type'), NeostampResource::types())) {
                                            //     if($record) {
                                            //         return $record->delivery_type == $get('delivery_type');
                                            //     } else {
                                            //         return false;
                                            //     }
                                            // } else {
                                            //     return true;
                                            // }



                                        }),

                                ])->columns(2)->columnSpan(2)

                    ])

                ])->columnSpan(2),

                Group::make()->schema([

                    FileUpload::make('files')
                    // ->preserveFilenames()
                    ->disk('local')
                    ->storeFileNamesIn('files_names')
                    ->reorderable()
                    ->acceptedFileTypes(['application/pdf'])
                    ->removeUploadedFileButtonPosition('right')
                    ->directory(fn (Get $get): string => 'k2/'.str_replace('-', '', $get('date').md5($get('date'))))
                    ->multiple()
                    ->openable()
                    ->label('Pliki')
                ])->columnSpan(2)

            ])->columns(4);
    }

    protected static function getSuggestedRecipientOptions(?string $matterId): array
    {
        if (blank($matterId)) {
            return [];
        }

        $matter = Matter::query()
            ->with(['lawsuits.court', 'credits.current_banks', 'opponent_lawyer'])
            ->find($matterId);

        if (! $matter) {
            return [];
        }

        $options = [];

        foreach ($matter->lawsuits as $lawsuit) {
            if (filled($lawsuit->court_id) && $lawsuit->court) {
                $options[(string) $lawsuit->court_id] = $lawsuit->court->label;
            }
        }

        $credit = $matter->credits
            ->first(fn ($credit) => filled($credit->current_bank) && $credit->current_banks);

        if ($credit) {
            $options[(string) $credit->current_bank] = $credit->current_banks->label;
        }

        if (filled($matter->opponent_lawyer_id) && $matter->opponent_lawyer) {
            $options[(string) $matter->opponent_lawyer_id] = $matter->opponent_lawyer->label . ' (pełnomocnik)';
        }

        return $options;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('date')
                    ->label('Data')
                    ->date()
                    ->toggleable()
                    ->width('140px')
                    ->sortable(),

                TextColumn::make('label')
                    ->view('filament.tables.letters-label', [
        'isRelationManager' => request()->routeIs('*relation*'),
    ])

                    ->label('Szczegóły')
                    ->searchable()
                    // ->hiddenOn(LettersRelationManager::class)
                    ,

                TextColumn::make('labelx')
                    ->icon(fn (Letter $record): ?HtmlString => match ($record->type) {
                        'out' => new HtmlString('&uarr;'),
                        'in' => new HtmlString('&darr;'),
                        default => null,
                    })
                    ->iconColor(fn (Letter $record): string => match ($record->type) {
                        'out' => 'success',
                        'in' => 'danger',
                        default => 'gray',
                    })
                    // ->visibleOn(LettersRelationManager::class)
                    ->hidden()
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->size(TextSize::Medium)
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    // ->description(fn (Letter $record) => $record->data
                    //  . ($record->description ? ' | '.$record->description : '')
                    // )0
                    ->label('Nazwa'),

                TextColumn::make('contact_letter_neostamp.neostamp.type')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->badge()
                    ->label('Przesyłka')
                    ->listWithLineBreaks(),

                ViewColumn::make('from')->view('filament.tables.letters-from-to')->label('Od / do'),

                // TextColumn::make('matter.label')
                //     ->label('Sprawa')
                //     ->limit(50)
                //     ->toggleable()
                //     ->visible(false)
                //     ->hiddenOn('relation')
                //     ->searchable()
                //     ->url(fn (Letter $record) => $record->matter ? '/kancelaria/' . bp_pl_url($record->matter->category) . '/'.$record->matter->id.'/edit' : null),

            ])
            ->filters([

                Filter::make('scopeMine')
                    ->toggle()
                    ->label('Tylko mój referat')
                    ->query(function (Builder $query, array $data): Builder {
                            return $query->mine();
                    })
                    ->hiddenOn(LettersRelationManager::class)
                    ->hidden(!auth()->user()->is_lawyer),
                DateRangeFilter::make('date')->label('Okres')->withIndicator(),
                // Filter::make('data')
                //     ->form([
                //         DatePicker::make('start')->label('Data początkowa'),
                //         DatePicker::make('end')->label('Data końcowa'),
                //     ])
                //     ->label('Data')
                //     ->indicateUsing(function (array $data): ?string {
                //         if (! $data['start'] && ! $data['end']) {
                //             return null;
                //         }

                //         return 'Data: ' . ($data['start'] ? $data['start'] : '?').' do '.($data['end'] ? $data['end'] : '?');
                //     })
                //     ->query(function (Builder $query, array $data): Builder {
                //         return $query
                //             ->when(
                //                 $data['start'],
                //                 fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                //             )
                //             ->when(
                //                 $data['end'],
                //                 fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                //             );
                //     })

            ])
            ->recordActions([

                EditAction::make()
                    ->iconButton()
                    ->slideOver()
                    ->modalWidth('7xl')
                    ->modalHeading(fn (Letter $record) => 'Edytuj: ' . $record->label ),

                    DeleteAction::make()->iconButton()
                    ->hidden(function (DeleteAction $action, Letter $record) {
                        return ($record->hasAnyRelation());
                    })
                    ->before(function ($record) {
                        $record->recipients()->detach();
                    }),
            ])
            ->toolbarActions([
                // BulkAction::make('Drukuj koperty')
                //     ->form([
                //         TextInput::make('date')
                //     ])
                //     ->action(function ($records, $data) {
                //         //
                //     }),

                BulkActionGroup::make([

                    BulkAction::make('Koperty')->icon('heroicon-m-envelope')
                        // ->before(function($records, $action) {
                        //     $n = new neoznaczki;
                        //     $n->validateNeostamps($records, $action);
                        // })
                        ->action(fn ($records) => PrintEnvelopeController::envelope($records)),
                        // ->action(fn ($records) => PrintController::printEnvelope($records)),

                    BulkAction::make('Książkę nadawczą')->icon('heroicon-o-document-text')
                        // ->before(function($records, $action) {
                        //     $n = new neoznaczki;
                        //     $n->validateNeostamps($records, $action);
                        // })
                        // ->action(fn ($records) => PrintController::printSendlist($records)),
                        ->action(fn ($records) => PrintEnvelopeController::sendlist($records)),

                ])->label('Pobierz')
            ])
            ->checkIfRecordIsSelectableUsing(
                function (Letter $record) {

                    return $record->type == 'out';

                    // $has_neostamp = false;

                    // if($record->contact_letter_neostamp()->exists()) {

                    //     $has_neostamp = count($record->contact_letter_neostamp->whereNotNull('neostamp_id')) > 0;
                    // }

                    // return $has_neostamp;

                }
            )
            ->defaultSort('date', 'desc');
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
            'index' => ListLetters::route('/'),
            // 'create' => Pages\CreateLetter::route('/create'),
            // 'edit' => Pages\EditLetter::route('/{record}/edit'),
        ];
    }
}
