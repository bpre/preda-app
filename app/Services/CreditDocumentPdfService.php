<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\Credit;
use Barryvdh\DomPDF\Facade\Pdf;

class CreditDocumentPdfService
{
    public function contractAnalysis(Credit $credit): string
    {
        $credit->load('contactCredit');
        $borrowerIds = $credit->contactCredit->pluck('contact_id')->toArray();

        return Pdf::loadView('print.analiza-umowy', [
            'record' => $credit,
            'kredytobiorcy' => Contact::whereIn('id', $borrowerIds)->get(),
            'bankUmowa' => $credit->former_banks->organization,
            'bankObecnie' => $credit->current_banks->organization,
            'nrUmowy' => $credit->number,
            'typKredytu' => bp_findJSON($credit->details, 'rodzaj-kredytu'),
            'kwotaKredytu' => bp_findJSON($credit->details, 'kwota'),
            'oprocentowanie' => bp_findJSON($credit->details, 'oprocentowanie'),
            'oprocentowanie_um' => bp_findJSON($credit->details, 'oprocentowanie-um'),
            'cel' => bp_findJSON($credit->details, 'cel'),
            'cel_um' => bp_findJSON($credit->details, 'cel-um'),
            'liczbaRat' => bp_findJSON($credit->details, 'liczba-rat'),
            'liczbaRat_um' => bp_findJSON($credit->details, 'liczba-rat-um'),
            'rodzajRat' => bp_findJSON($credit->details, 'rodzaj-rat'),
            'rodzajRat_um' => bp_findJSON($credit->details, 'rodzaj-rat-um'),
            'klauzuleNiedozwolone' => bp_findJSON($credit->details, 'klauzule-zbiorczo'),
            'klauzulePouczenia' => bp_findJSON($credit->details, 'pouczenie'),
            'inneKlauzule' => bp_findJSON($credit->details, 'inne-klauzule'),
            'analiza' => bp_findJSON($credit->details, 'analiza'),
            'uwagi' => bp_findJSON($credit->details, 'analiza-uwagi-klient'),
        ])->output();
    }

    /**
     * @param  array{wnioskodawca: string, dokumenty: bool, regulamin: bool, date: mixed}  $data
     */
    public function certificateRequest(Credit $credit, array $data): string
    {
        return Pdf::loadView('print.wniosek', [
            'e' => $credit,
            'wnioskodawca' => Contact::find($data['wnioskodawca']),
            'waluta' => bp_findJSON($credit->details, 'waluta', 'CHF'),
            'dokumenty' => (bool) ($data['dokumenty'] ?? false),
            'regulamin' => (bool) ($data['regulamin'] ?? false),
            'date' => $data['date'] ?? now(),
        ])->output();
    }
}
