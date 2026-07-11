<?php

namespace App\Filament\Website\Resources\Sentences\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\CheckboxList;
use App\Enums\Website\ContactCategories;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Fieldset;
use App\Support\Website\WebsiteFeatures;
use App\Models\Website\Sentence;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use App\FilamentPlugins\RichContent\RelativeLinks;
use Filament\Schemas\Components\Utilities\Get;
use App\Filament\Website\Resources\Contacts\Schemas\ContactForm;

class SentenceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Group::make([
                    Section::make('Wpis')->schema([
                        TextInput::make('label')
                            ->label('Tytuł')
                            ->live(debounce: 500)
                            ->helperText(fn (Get $get, ?Sentence $record = null): ?HtmlString => self::duplicateLabelWarning(
                                $get('label'),
                                $record?->getKey(),
                            ))
                            ->required(fn (Get $get): bool => (bool) $get('is_published')),
                        Textarea::make('excerpt')
                            ->label('Zajawka')
                            ->rows(3)
                            ->required(fn (Get $get): bool => (bool) $get('is_published')),
                        RichEditor::make('content')
                            ->label('Treść')
                            ->plugins([
                                RelativeLinks::make(),
                            ])
                            ->required(fn (Get $get): bool => (bool) $get('is_published')),
                    ])
                        ->collapsible(),
                    FileUpload::make('files')
                        ->label('Pliki')
                        ->reorderable()
                        ->appendFiles()
                        ->image()
                        ->imageEditor()
                        ->imageEditorMode(2)
                        ->imagePreviewHeight('500')
                        ->visibility('public')
                        ->disk('public')
                        ->directory('sentences')
                        ->multiple(),
                ])->columnSpan(6),

                Group::make([

                    Fieldset::make()->schema([
                        Toggle::make('is_published')->label('Opublikowany?')->columnSpanFull()
                    ]),

                    Section::make('Szczegóły wyroku')->schema([
                        Select::make('instance')
                            ->label('Instancja')
                            ->options([
                                1 => 'I instancja',
                                2 => 'II instancja',
                                3 => 'Postępowanie kasacyjne',
                            ])
                            ->native(false)
                            ->live()
                            ->columnSpan(5),
                        TextInput::make('sign')
                            ->label('Sygnatura')
                            ->required()
                            ->columnSpan(5),
                        Select::make('court_id')
                            ->label('Sąd')
                            ->searchable()
                            ->relationship(
                                name: 'court',
                                titleAttribute: 'label',
                                modifyQueryUsing: fn(Builder $query) => $query->where('category', 'Sąd')->orderBy('label')
                            )
                            ->createOptionForm(function() {
                                return ContactForm::fields(ContactCategories::SAD->value);
                            })
                            ->required()
                            ->native(false)
                            ->columnSpan(5),
                        Select::make('judge_id')
                            ->label('Sędzia')
                            ->searchable()
                            ->relationship(
                                name: 'judge',
                                titleAttribute: 'sort_name',
                                modifyQueryUsing: fn(Builder $query) => $query->where('category', 'Sędzia')->orderBy('sort_name')
                            )
                            ->createOptionForm(function() {
                                return ContactForm::fields(ContactCategories::SEDZIA->value);
                            })
                            ->required()
                            ->columnSpan(5),
                        Select::make('parent_id')
                            ->label('Wyrok sądu I instancji')
                            ->searchable()
                            ->relationship(
                                name: 'parent',
                                titleAttribute: 'sign',
                                modifyQueryUsing: fn (Builder $query) => $query
                                    ->with('court')
                                    ->where('instance', 1)
                                    ->orderBy('sign')
                            )
                            ->getSearchResultsUsing(fn (string $search): array => Sentence::query()
                                ->with('court')
                                ->where('instance', 1)
                                ->where(function (Builder $query) use ($search): void {
                                    $query
                                        ->where('sign', 'like', "%{$search}%")
                                        ->orWhereHas('court', fn (Builder $query) => $query->where('label', 'like', "%{$search}%"));
                                })
                                ->orderBy('sign')
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(fn (Sentence $sentence): array => [
                                    $sentence->getKey() => self::formatParentSentenceOption($sentence),
                                ])
                                ->all())
                            ->getOptionLabelFromRecordUsing(fn (Sentence $record): string => self::formatParentSentenceOption($record->loadMissing('court')))
                            ->columnSpanFull()
                    ])
                        ->columns(10)
                        ->collapsible(),

                    Section::make('Postępowanie')->schema([
                        DatePicker::make('lawsuit_date')
                            ->label('Data pozwu')
                            ->required()
                            ->hidden(fn (Get $get) => $get('instance') == '2')
                            ->columnSpan(5),
                        DatePicker::make('appeal_date')
                            ->label('Data apelacji')
                            ->required()
                            ->visible(fn (Get $get) => $get('instance') == '2')
                            ->columnSpan(5),
                        DatePicker::make('sentence_date')
                            ->label('Data wyroku')
                            ->required()
                            ->columnSpan(5),
                        TextInput::make('lawyer')
                            ->label('Pełnomocnik')
                            ->default('adw. Bartosz Pręda')
                            ->required()
                            ->columnSpan(4),
                        TextInput::make('wps')
                            ->label('w.p.s.')
                            ->required()
                            ->columnSpan(3),
                        TextInput::make('hearings')
                            ->label('Rozpraw')
                            ->required()
                            ->columnSpan(3),
                        TextInput::make('claim')
                            ->label('Żądanie')
                            ->default('ustalenie')
                            ->required()
                            ->columnSpan(5),
                        TextInput::make('result')
                            ->label('Wynik')
                            ->default('wygrana kredytobiorców')
                            ->required()
                            ->columnSpan(5),
                    ])
                        ->columns(10)
                        ->collapsible(),

                    Section::make('Umowa')->schema([
                        Select::make('bank_previously_id')
                            ->label('Bank (poprzednio)')
                            ->relationship(
                                name: 'bank_previously',
                                titleAttribute: 'label'
                            )
                            ->required()
                            ->searchable()
                            ->columnSpan(5),
                        Select::make('bank_id')
                            ->label('Bank (obecnie)')
                            ->relationship(
                                name: 'bank',
                                titleAttribute: 'label'
                            )
                            ->required()
                            ->searchable()
                            ->columnSpan(5),
                        TextInput::make('credit_year')
                            ->label('Rok umowy')
                            ->required()
                            ->columnSpan(3),
                        TextInput::make('credit_name')
                            ->label('Nazwa umowy')
                            ->required()
                            ->columnSpan(7),
                        Select::make('currency')
                            ->label('Waluta')
                            ->options([
                                'CHF' => 'CHF',
                                'EUR' => 'EUR',
                                'USD' => 'USD'
                            ])
                            ->default('CHF')
                            ->native(false)
                            ->required()
                            ->columnSpanFull(),
                        Toggle::make('is_paid_off')
                            ->label('Spłacony?')
                            ->live()
                            ->inline(false)
                            ->columnSpan(5),
                        TextInput::make('paid_off_year')
                            ->visible(fn(Get $get) => $get('is_paid_off'))
                            ->label('Rok')
                            ->required()
                            ->columnSpan(5),
                    ])
                        ->columns(10)
                        ->collapsible(),

                    Section::make('Kwoty')
                        ->description('Wpisz same wartości liczbowe, bez kropek, przecinków, itd. Kwoty zaokrąglij do pełnych złotych.')
                        ->schema([
                            TextInput::make('credit_payoff')
                                ->label('Kwota wypłacona')
                                ->suffix('PLN')
                                ->columnSpan(5),
                            TextInput::make('credit_profit')
                                ->suffix('PLN')
                                ->label('Korzyść')
                                ->columnSpan(5),
                        ])
                        ->columns(10)
                        ->collapsible(),

                    Section::make('Generator treści')->schema([
                        Repeater::make('ruling_points')
                            ->label('Punkty rozstrzygnięcia')
                            ->helperText('Anonimizuj dane klienta. Nie wpisuj numeru umowy, dokładnej daty umowy ani innych danych pozwalających zidentyfikować sprawę.')
                            ->schema([
                                Textarea::make('text')
                                    ->label('Treść punktu')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ])
                            ->addActionLabel('Dodaj punkt')
                            ->reorderable()
                            ->columnSpanFull(),
                        Select::make('judgment_publication_mode')
                            ->label('Tryb wydania wyroku')
                            ->options([
                                'hearing' => 'Po rozprawie',
                                'closed_session' => 'Na posiedzeniu niejawnym',
                            ])
                            ->native(false)
                            ->columnSpan(5),
                        Select::make('reasoning_source')
                            ->label('Źródło motywów')
                            ->options([
                                'oral' => 'Ustne motywy',
                                'written' => 'Uzasadnienie pisemne',
                                'none' => 'Brak szerszych motywów',
                            ])
                            ->native(false)
                            ->columnSpan(5),
                        Textarea::make('court_reasoning_summary')
                            ->label('Najważniejsze motywy sądu')
                            ->helperText('Krótko, językiem roboczym. Generator wykorzysta to jako szkic, a nie gotową publikację.')
                            ->rows(5)
                            ->columnSpanFull(),
                        CheckboxList::make('evidence_scope')
                            ->label('Postępowanie dowodowe')
                            ->options([
                                'borrower_hearing' => 'Przesłuchanie kredytobiorców',
                                'documents' => 'Dokumenty',
                                'witnesses' => 'Świadkowie',
                                'expert_opinion' => 'Opinia biegłego',
                                'expert_omitted' => 'Pominięcie opinii biegłego',
                                'bank_witness_omitted' => 'Pominięcie świadków banku',
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                        Toggle::make('security_granted')
                            ->label('Było zabezpieczenie?')
                            ->live()
                            ->inline(false)
                            ->columnSpanFull(),
                        Textarea::make('security_note')
                            ->label('Opis zabezpieczenia')
                            ->rows(3)
                            ->visible(fn (Get $get): bool => (bool) $get('security_granted'))
                            ->columnSpanFull(),
                        CheckboxList::make('content_generator_flags')
                            ->label('Zdarzenia procesowe')
                            ->options([
                                'counterclaim_dismissed' => 'Sąd nie uwzględnił powództwa wzajemnego banku',
                                'setoff_dismissed' => 'Sąd nie uwzględnił zarzutu potrącenia',
                                'retention_dismissed' => 'Sąd nie uwzględnił zarzutu zatrzymania',
                            ])
                            ->columns(1)
                            ->columnSpanFull(),
                        Textarea::make('content_note')
                            ->label('Inne istotne informacje do wpisu')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                        ->columns(10)
                        ->collapsible()
                        ->visible(WebsiteFeatures::sentenceContentGeneratorEnabled()),

                    Section::make('Meta')->schema([
                        Textarea::make('metatitle')
                            ->required(fn (Get $get): bool => (bool) $get('is_published'))
                            ->columnSpanFull(),
                        Textarea::make('metadescription')
                            ->required(fn (Get $get): bool => (bool) $get('is_published'))
                            ->rows(2)
                            ->columnSpanFull(),

                    ])
                        ->columns(2)
                        ->collapsible()

                ])->columnSpan(5)

            ])->columns(11);
    }

    private static function formatParentSentenceOption(Sentence $sentence): string
    {
        $court = $sentence->court?->label;

        return trim((string) $sentence->sign) . ($court ? " ({$court})" : '');
    }

    private static function duplicateLabelWarning(?string $label, ?int $ignoredSentenceId = null): ?HtmlString
    {
        $label = trim((string) $label);

        if (mb_strlen($label) < 10) {
            return null;
        }

        $duplicatesCount = Sentence::query()
            ->where('label', $label)
            ->when($ignoredSentenceId, fn (Builder $query) => $query->whereKeyNot($ignoredSentenceId))
            ->count();

        if ($duplicatesCount === 0) {
            return null;
        }

        return new HtmlString(
            '<span class="font-medium text-danger-600 dark:text-danger-400">'
            . 'W bazie jest już wpis o takim samym tytule. Postaraj się o unikalny tytuł.'
            . '</span>'
        );
    }
}
