<?php

namespace App\Http\Controllers\Website\Pages;

use App\Facades\Website\Seo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SplaconyKredytController extends Controller
{
    public function __invoke()
    {

        Seo::title('Spłacony kredyt frankowy? Odzyskaj swoje pieniądze!');
        Seo::description('Zamknięcie kredytu frankowego nie zamyka drogi do odzyskania nadpłaty! Sprawdź, ile możesz odzyskać.');

        return view('pages.splacony-kredyt', [
            'h1' => 'Spłaciłeś kredyt frankowy?',
            'h2' => 'Prawdopodobnie bank wciąż jest Ci winien pieniądze!',
            'content' => 'Zamknięcie kredytu nie zamyka drogi do odzyskania nadpłaty! Odzyskaj to, co nadpłaciłeś przez lata – w gotówce, wraz z odsetkami. Sprawdź bezpłatnie, czy Twoja sprawa nie jest przedawniona.'
        ]);
    }
}
