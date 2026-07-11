<?php

namespace App\Filament\Pages\Administration;

use App\Models\Matter;
use App\Models\ContactMatter;
use App\Models\Contact;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ImportContactMatters extends Page
{
    use HasPageShield;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected string $view = 'filament.pages.administration.import-contact-matters';

    protected static ?string $navigationLabel = 'Import klientów do spraw';

    protected static ?string $title = 'Import klientów do spraw';

    protected static string | \UnitEnum | null $navigationGroup = 'Administracja';

    protected static ?string $navigationParentItem = 'Powiadomienia (pisma)';

    protected static ?int $navigationSort = 5;

    public ?array $summary = null;

    public function mount(): void
    {
        $this->summary = [
            'matters_checked' => 0,
            'candidates_found' => 0,
            'inserted' => 0,
            'skipped_existing' => 0,
            'updated_sex_from_pesel' => 0,
            'updated_sex_from_first_name' => 0,
            'skipped_sex' => 0,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')
                ->label('Importuj z istniejących relacji')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Import klientów do spraw')
                ->modalDescription('System spróbuje utworzyć przypisania klientów do spraw na podstawie istniejących relacji przez kredyty. Istniejące rekordy nie zostaną zdublowane.')
                ->action(function () {
                    $mattersChecked = 0;
                    $candidatesFound = 0;
                    $inserted = 0;
                    $skippedExisting = 0;

                    Matter::query()
                        ->with([
                            'credits.contactCredit.contact',
                        ])
                        ->chunk(100, function ($matters) use (&$mattersChecked, &$candidatesFound, &$inserted, &$skippedExisting) {
                            foreach ($matters as $matter) {
                                $mattersChecked++;

                                $contacts = $matter->credits
                                    ->flatMap(function ($credit) {
                                        return $credit->contactCredit;
                                    })
                                    ->map(function ($contactCredit) {
                                        return $contactCredit->contact;
                                    })
                                    ->filter(function ($contact) {
                                        return $contact
                                            && $contact->category === 'Kredytobiorca'
                                            && filled($contact->id);
                                    })
                                    ->unique('id')
                                    ->values();

                                $candidatesFound += $contacts->count();

                                foreach ($contacts as $contact) {
                                    $exists = ContactMatter::query()
                                        ->where('matter_id', $matter->id)
                                        ->where('contact_id', $contact->id)
                                        ->exists();

                                    if ($exists) {
                                        $skippedExisting++;
                                        continue;
                                    }

                                    ContactMatter::create([
                                        'matter_id' => $matter->id,
                                        'contact_id' => $contact->id,
                                        'receives_notifications' => false,
                                    ]);

                                    $inserted++;
                                }
                            }
                        });

                    $this->summary = [
                        'matters_checked' => $mattersChecked,
                        'candidates_found' => $candidatesFound,
                        'inserted' => $inserted,
                        'skipped_existing' => $skippedExisting,
                    ];

                    Notification::make()
                        ->title('Import zakończony')
                        ->body("Sprawdzono spraw: {$mattersChecked}, dodano rekordów: {$inserted}, pominięto istniejące: {$skippedExisting}.")
                        ->success()
                        ->send();
                }),

            Action::make('backfillBorrowerSex')
                ->label('Uzupełnij płeć kredytobiorców')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Uzupełnij pole płci')
                ->modalDescription('System spróbuje uzupełnić pole sex dla kontaktów z kategorii Kredytobiorca: najpierw na podstawie PESEL, a jeśli go brak lub jest niepoprawny — na podstawie imienia zakończonego literą „a”.')
                ->action(function () {
                    $updatedFromPesel = 0;
                    $updatedFromFirstName = 0;
                    $skipped = 0;

                    Contact::query()
                        ->where('category', 'Kredytobiorca')
                        ->where(function ($query) {
                            $query->whereNull('sex')->orWhere('sex', '');
                        })
                        ->chunkById(200, function ($contacts) use (&$updatedFromPesel, &$updatedFromFirstName, &$skipped) {
                            foreach ($contacts as $contact) {
                                $pesel = preg_replace('/\D+/', '', (string) $contact->pesel);

                                if (strlen($pesel) === 11) {
                                    $digit = (int) $pesel[9];
                                    $sex = $digit % 2 === 0 ? 'K' : 'M';

                                    $contact->update([
                                        'sex' => $sex,
                                    ]);

                                    $updatedFromPesel++;
                                    continue;
                                }

                                $firstName = trim((string) $contact->first_name);

                                if ($firstName !== '') {
                                    $lastChar = mb_strtolower(mb_substr($firstName, -1));

                                    if ($lastChar === 'a') {
                                        $contact->update([
                                            'sex' => 'K',
                                        ]);

                                        $updatedFromFirstName++;
                                        continue;
                                    }
                                }

                                $skipped++;
                            }
                        }, 'id');

                    $this->summary = array_merge($this->summary ?? [], [
                        'updated_sex_from_pesel' => $updatedFromPesel,
                        'updated_sex_from_first_name' => $updatedFromFirstName,
                        'skipped_sex' => $skipped,
                    ]);

                    Notification::make()
                        ->title('Uzupełnianie pola płci zakończone')
                        ->body("Z PESEL: {$updatedFromPesel}, z imienia: {$updatedFromFirstName}, pominięto: {$skipped}.")
                        ->success()
                        ->send();
                }),
        ];
    }
}
