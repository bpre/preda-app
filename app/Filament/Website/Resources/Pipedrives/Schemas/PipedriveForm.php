<?php

namespace App\Filament\Website\Resources\Pipedrives\Schemas;

use App\Enums\Website\ReviewStatus;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Schema;
use App\Models\Website\Pipedrive;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DateTimePicker;

class PipedriveForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([


                TextInput::make('id')
                    ->readonly(),
                TextInput::make('matter_id')
                    ->readonly(),



                Section::make('Klient')->collapsible()->schema([

                    TextInput::make('name')
                        ->label('Klient')
                        ->columnSpan(4),
                    Select::make('sex')
                        ->label('Płeć')
                        ->options([
                            'k' => 'Kobieta',
                            'm' => 'Mężczyzna'
                        ])
                        ->required()
                        ->columnSpan(2),
                    TextInput::make('email')
                        ->label('E-mail')
                        ->columnSpan(2),
                    TextInput::make('phone')
                        ->label('Telefon')
                        ->columnSpan(2),
                    TextInput::make('city')
                        ->label('Miejscowość')
                        ->columnSpan(2),

                ])->columns(12)->columnSpanFull(),

                Section::make('Umowa kredytowa')
                    ->collapsible()
                    ->schema([

                        Actions::make([

                            Action::make('Folder sprawy')
                                ->color('info')
                                ->hidden(fn (?Pipedrive $record) => blank($record?->gdrive))
                                ->icon('heroicon-m-arrow-top-right-on-square')
                                ->url(fn (?Pipedrive $record) => $record?->gdrive)
                                ->openUrlInNewTab(),

                        ])->columnSpanFull()->alignEnd(),

                        TextInput::make('bank')
                            ->label('Bank')
                            ->required()
                            ->columnSpan(3),
                        TextInput::make('banku')
                            ->label('Banku')
                            ->required()
                            ->columnSpan(3),
                        TextInput::make('year')
                            ->label('Rok')
                            ->required()
                            ->columnSpan(2),
                        TextInput::make('amount')
                            ->label('Kwota')
                            ->columnSpan(2),
                        Select::make('currency')
                            ->options([
                                'CHF' => 'CHF',
                                'EUR' => 'EUR',
                                'USD' => 'USD'
                                ])
                            ->label('Waluta')
                            ->required()
                            ->columnSpan(2),

                    ])->columns(12)->columnSpanFull(),

                Section::make('Wiadomość')
                    ->hidden(fn(?Pipedrive $record) => empty($record->id))
                    ->collapsible()
                    ->schema([

                    RichEditor::make('topic')->label('Temat')
                        ->formatStateUsing(fn(?Pipedrive $record) => 'Kredyt ' . $record?->currency .' (umowa '. $record?->banku . ' z ' . $record?->year . ' r.) - istotna aktualizacja'),

                    RichEditor::make('msg')
                        ->label('Wiadomość')
                        ->formatStateUsing(function($record) {

                            $s = $record?->sex;

                            $f0 = $s == 'k' ? 'Pani' : 'Pan';
                            $f1 = $s == 'k' ? 'Panią' : 'Panem';
                            $f2 = $s == 'k' ? 'Pani' : 'Pana';
                            $f3 = $s == 'k' ? 'Panią' : 'Pana';
                            $f4 = $s == 'k' ? 'a' : 'y';
                            $f5 = $s == 'k' ? 'Pani' : 'Panu';
                            $f6 = $s == 'k' ? 'chciałaby' : 'chciałby';
                            $f7 = $s == 'k' ? 'kontaktowała' : 'kontaktował';

                            return '
Dzień dobry,
<br><br>
jakiś czas temu '.$f7.' się '.$f0.' z naszą kancelarią w sprawie unieważnienia '.$f2.' kredytu powiązanego z '.$record?->currency.' (umowa '.$record?->banku.' z ' . $record?->year . ' r.).
<br><br>
Chciałem '.$f3.' poinformować, że ze względu na obecną, <strong>bardzo korzystną linię orzeczniczą</strong>, wprowadziliśmy <strong>nowy model współpracy</strong>.
Pozwala on na istotne <strong>zminimalizowanie opłat początkowych</strong>.
<br><br>
Jeżeli '.$f6.' '.$f0.' poznać więcej szczegółów, uprzejmie proszę o kliknięcie poniższego linku:
<br><br>
👉 &nbsp; <strong><a href="https://preda.info/r8dsg/o/'.$record?->id.'">Tak, chcę otrzymać aktualną ofertę dla umowy '.$record?->banku.'</a></strong>
<br><br>
(kliknięcie potwierdzi '.$f2.' zainteresowanie, a ja przygotuję i wyślę przygotowaną specjalnie dla '.$f2.' ofertę)
<br><br>
Przy okazji życzę '.$f5.' wszelkiej pomyślności, spokoju i samych dobrych decyzji w Nowym Roku!
<br><br>
Z wyrazami szacunku
<br><br>
Bartosz Pręda<br>
adwokat
<br><br>
<small>P.S. Jeżeli problem kredytu powiązanego z '.$record?->currency.' udało się już '.$f5.' rozwiązać i nie chce '.$f0.' otrzymywać wiadomości od naszej kancelarii, wystarczy kliknąć link: <a href="https://preda.info/r8dsg/r/'.$record?->id.'">proszę o usunięcie moich danych</a>.</small>

                            ';

                        })

                ])->columnSpanFull(),

                Section::make('Weryfikacja')
                    ->collapsible()
                    ->schema([

                        DateTimePicker::make('reviewed')
                            ->label('Data weryfikacji')
                            ->live()
                            ->nullable(),
                        Select::make('review_status')
                            ->label('Status po weryfikacji')
                            ->options([
                                ReviewStatus::REJECTED->value => 'Odrzucona',
                                ReviewStatus::TRANSFERED->value => 'Przekazana',
                            ])
                            ->nullable()
                            ->required(fn (callable $get) => filled($get('reviewed')))
                            ->disabled(fn (callable $get) => blank($get('reviewed')))

                    ]),

                Section::make('Akcje klienta')
                    ->collapsible()
                    ->schema([

                        DateTimePicker::make('remove_request')
                            ->label('Prośba o usunięcie')
                            ->readonly()
                            ->nullable(),

                        DateTimePicker::make('offer_request')
                            ->label('Prośba o ofertę')
                            ->readonly()
                            ->nullable()

                    ])

            ]);
    }
}
