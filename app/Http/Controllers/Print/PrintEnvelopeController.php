<?php

namespace App\Http\Controllers\Print;

use App\Models\Doc;
use Dompdf\Options;
use App\Models\Deal;
use App\Models\User;
use App\Models\Credit;
use App\Models\Contact;
use Illuminate\Http\Request;
use App\Models\ContactLetter;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use App\Filament\Resources\NeostampResource;

class PrintEnvelopeController extends Controller
{

    private static function parseRecords($records)
    {

        $r = [];
        $i = 0;
        $errors = 0;

        foreach($records as $record)
        {
            foreach($record->recipients as $recipient)
            {
                $i++;

                $matter = $record->matter;

                $contact_letter_record = ContactLetter::where('letter_id', $record->id)->where('contact_id', $recipient->id)->with('departament')->first();

                // NEOZNACZEK

                    $neostamp = $record->contact_letter_neostamp->where('contact_id', $recipient->id)->pluck('neostamp')->first();
                    $contact_letter = ContactLetter::where('contact_id', $recipient->id)->where('letter_id', $record->id)->first();
                    $dir = str_replace('public', 'storage/app/', realpath($_SERVER["DOCUMENT_ROOT"]));
                    $neostampDate = $neostamp?->created_at?->format('Y-m-d');
                    $file = $neostamp && $neostampDate
                        ? 'neoznaczki/' . $neostampDate . '/' . $neostamp->label . '_znaczek.jpg'
                        : null;

                    $r[$i]['delivery_type'] = $contact_letter->delivery_type;

                    $r[$i]['print_envelope'] = in_array($contact_letter->delivery_type, NeostampResource::types());

                    $r[$i]['neostamp']['assigned'] = $neostamp !== NULL;

                    if($file && Storage::disk('local')->exists($file))
                    {
                        $r[$i]['neostamp']['label'] = $neostamp->label;
                        $r[$i]['neostamp']['path'] = $dir . $file;
                        $r[$i]['neostamp']['file_exists'] = TRUE;
                    }
                    else {
                        $r[$i]['neostamp']['file_exists'] = FALSE;
                    }

                    // $r[$i]['neostamp']['display'] = in_array($neostamp?->type, NeostampResource::types());

                // ADRESAT

                    /* Adresat jest organizacją */

                    if($recipient->type == 'organizacja')
                    {
                        $r[$i]['label'] = $recipient->organization;

                        /* Sąd */

                        if($recipient->category == 'Sąd')
                        {
                            /* Wydział przypisujemy tylko wówczas, gdy został uzupełniony w formularzu Korespondencji (pole departament_id w tabel contact_letter) */

                            if($contact_letter_record->departament()->exists())
                            {
                                $r[$i]['label'] .= '<br>' . $contact_letter_record->departament->label;
                                $r[$i]['adres'] = str_replace(', ', '<br>', $contact_letter_record->departament->adr);
                            }
                            else
                            {
                                $r[$i]['adres'] = str_replace(', ', '<br>', $recipient->adr);
                            }
                        } else
                        {
                            $r[$i]['adres'] = str_replace(', ', '<br>', $recipient->adr);
                        }

                    }

                    /* Adresat jest osobą */

                    else
                    {
                        /* Pełnomocnik */

                        if($recipient->category == 'Pełnomocnik')
                        {
                            $r[$i]['label'] = $recipient->profession . ' ' . $recipient->label;

                            /* Jeżeli korespondencja przypisana do sprawy i pełnomocnik jest pełnomocnikiem przypisanym do sprawy - wyświetl kancelarię przypisaną do sprawy,
                            jeśli nie - wyświetl kancelarię przypisaną do pełnomocnika */

                            if($matter && $recipient->id === $matter->opponent_lawyer_id)
                            {

                                if($matter->opponent_lawfirm()->exists())
                                {
                                    $r[$i]['label'] .= '<br>' . $matter->opponent_lawfirm->organization;

                                    if(($matter->opponent_departament()->exists()))
                                    {
                                        $r[$i]['label'] .= '<br>' . $matter->opponent_departament->label;
                                        $r[$i]['adres'] = str_replace(', ', '<br>', $matter->opponent_departament->adr);
                                    }
                                    else{
                                        $r[$i]['adres'] = str_replace(', ', '<br>', $matter->opponent_lawfirm->adr);
                                    }
                                }
                            }
                            else
                            {


                                if($recipient->contact_lawfirm()->exists())
                                {


                                    $r[$i]['label'] .= '<br>' . $recipient->contact_lawfirm->organization;

                                    if(!empty($recipient->contact_departament->label))
                                    {
                                        $r[$i]['label'] .= '<br>' . $recipient->contact_departament->label;
                                        $r[$i]['adres'] = str_replace(', ', '<br>', $recipient->contact_departament->adr);
                                    }
                                    else{

                                        $r[$i]['adres'] = str_replace(', ', '<br>', $recipient->contact_lawfirm->adr);
                                    }
                                }
                            }
                        }

                        else
                        {
                            $r[$i]['label'] = $recipient->label;
                            $r[$i]['adres'] = str_replace(', ', '<br>', $recipient->adr);
                        }

                    }

                // WALIDACJA

                if(empty($r[$i]['adres'])) {
                    $r[$i]['notification'] = 'Uzupełnij dane adresowe kontaktu: ' . $r[$i]['label'];
                    $errors++;
                }

            }
        }

        // NIE DRUKUJ KOPERT JEŚLI SPOSÓB DORĘCZENIA TO: ZŁOŻONO W SĄDZIE

        return array('results' => $r, 'errors' => $errors);

    }

    public static function notify($r)
    {

        foreach($r['results'] as $result)
        {
            if(isset($result['notification']))
            {
                Notification::make()->title($result['notification'])->danger()->send();
            }
        }

    }

    public static function envelope($records)
    {

        $r = PrintEnvelopeController::parseRecords($records);

        if($r['errors'])
        {
            PrintEnvelopeController::notify($r);

        } else
        {

            $options = [
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'debugPng' => true
            ];

            Pdf::setOptions($options);
            $pdf = Pdf::loadView('print.neoznaczki.envelope', [
                'records' => $r['results']
            ])->setPaper('C5', 'landscape')->output();

            return response()->streamDownload(
                fn () => print($pdf),
                date("Y-m-d") . '-koperty.pdf'
            );

        }
    }

    public static function sendlist($records)
    {
        $r = PrintEnvelopeController::parseRecords($records);

        if($r['errors'])
        {
            PrintEnvelopeController::notify($r);

        } else
        {

            $options = [
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'debugPng' => true
            ];

            Pdf::setOptions($options);
            $pdf = Pdf::loadView('print.neoznaczki.sendlist', [
                'records' => $r['results']
            ])->setPaper('A4', 'landscape')->output();

            return response()->streamDownload(
                fn () => print($pdf),
                date("Y-m-d") . '-ksiazka-nadawcza.pdf'
            );

        }
    }


}
