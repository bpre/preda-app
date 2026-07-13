<?php

namespace App\Http\Controllers;

use App\Models\Doc;
use Dompdf\Options;
use App\Models\Deal;
use App\Models\User;
use App\Models\Credit;
use App\Models\Contact;
use App\Services\CreditDocumentPdfService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PrintController extends Controller
{

    public static function pismo(Doc $record) {

        $credit = Credit::find($record->credit_id)->first();

        $ilu = 2;

        $body = preg_replace_callback('#\[(.*?)\]#', function($replacement) use ($ilu) {
            $n = explode('|', $replacement[1]);
            return $n[$ilu == 1 ? '0' : '1'];
        }, $record['body']);


        $pdf = Pdf::loadView('print.pismo', [
            'body' => str_replace(
                array(
                    '@umowaNumer',
                    '@umowaData'
                ),
                array(
                    $credit->number,
                    bp_human_date($credit->date, 'n').' r.'
                ),
                $body
            )
        ])->output();

        return response()->streamDownload(
            fn () => print($pdf),
            'pismo.pdf'
        );
    }

    public static function analizaUmowy(Credit $record)
    {
        $pdf = app(CreditDocumentPdfService::class)->contractAnalysis($record);

        return response()->streamDownload(
            fn () => print($pdf),
            'analiza-umowy.pdf'
        );

    }

    public static function envelopes() {

    }

    public static function printEnvelope($records)
    {

        $options = [
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'debugPng' => true
        ];

        Pdf::setOptions($options);
        $pdf = Pdf::loadView('print.neoznaczki.envelope', [
            'records' => $records
        ])->setPaper('C5', 'landscape')->output();

        return response()->streamDownload(
            fn () => print($pdf),
            date("Y-m-d") . '-koperty.pdf'
        );

    }

    public static function printSendlist($records)
    {

        $options = [
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'debugPng' => true
        ];

        Pdf::setOptions($options);
        $pdf = Pdf::loadView('print.neoznaczki.sendlist', [
            'records' => $records
        ])->setPaper('A4', 'landscape')->output();

        return response()->streamDownload(
            fn () => print($pdf),
            date("Y-m-d") . '-ksiazka-nadawcza.pdf'
        );

    }


    public static function wniosekZaswiadczenie(Credit $credit, $data)
    {
        $pdf = app(CreditDocumentPdfService::class)->certificateRequest($credit, $data);

        return response()->streamDownload(
            fn () => print($pdf),
            'wniosek-o-wydanie-zaswiadczenia.pdf'
        );
    }

    public static function pobierzZlecenie(Deal $deal, $data)
    {

        $deal->load('contactDeal');
        $zleceniodawcy_ids = $deal->contactDeal->pluck('contact_id')->toArray();


        switch($deal->label) {

            case '2026: Bezpieczny start (z premią)':
                $template = 'print.zlecenia.zlecenie2026_premia';
                break;

            case '2026: Bez premii':
                $template = 'print.zlecenia.zlecenie2026_bez';
                break;

            default:

                $template = 'print.zlecenia.zlecenie2026';

        }

        $is_getin = Contact::where('id', $deal->credits[0]->current_bank)->first()->organization == 'Getin Noble Bank S.A. w upadłości';

        // $korporacja = User::find($data['pelnomocnik'])->id == '3' ? 'r.pr.' : 'adw.';

        $pdf = Pdf::loadView($template, [
            'e' => $deal,
            'kredytobiorcy' => Contact::whereIn('id', $zleceniodawcy_ids)->get(),
            'miejsce_podpisania' => $data['miejsce_podpisania'],
            'data_pelnomocnictwa' => $data['data_pelnomocnictwa'],
            'osobne_dla_kazdego_klienta' => $data['osobne_dla_kazdego_klienta'],
            'pelnomocnictwo_powodztwo_banku' => $data['pelnomocnictwo_powodztwo_banku'],
            // 'pelnomocnik' => User::find($data['pelnomocnik'])->name_genitive,
            'pelnomocnicy' => User::whereIn('id', $data['pelnomocnik'])->get(),
            'reprezentant' => User::find($data['reprezentant'])->name_genitive,
            'is_getin' => $is_getin,
            'pozyczka' => $data['pozyczka']
            // 'korporacja' => $korporacja
            ])->output();

        return response()->streamDownload(fn () => print($pdf),
            'zlecenie.pdf'
        );
    }
}
