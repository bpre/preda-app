<?php

namespace App\Http\Controllers\Website\Pages;

use App\Http\Controllers\Controller;
use App\Facades\Website\Seo;

class AnalizaController extends Controller
{

    public function __invoke()
    {

        Seo::title('Sprawdź swój kredyt');
        Seo::description('Dowiedz się, czy możesz unieważnić swoją umowę kredytową. Bezpłatna analiza.');


        return view('pages.analiza', [
            'h1' => 'Sprawdź swoją umowę kredytową',
            'h2' => 'Analiza sprawy - zgłoszenie',
            'content' => 'Wypełnij formularz, by dowiedzieć się, czy możesz unieważnić swój kredyt powiązany z walutą obcą. Analiza Twojego zgłoszenia jest całkowicie bezpłatna i nie rodzi po Twojej stronie żadnych zobowiązań.'
        ]);
    }
}
