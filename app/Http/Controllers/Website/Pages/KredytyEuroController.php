<?php

namespace App\Http\Controllers\Website\Pages;

use App\Http\Controllers\Controller;
use App\Facades\Website\Seo;

class KredytyEuroController extends Controller
{
    public function __invoke()
    {

        Seo::title('Kredyty w euro ');
        Seo::description('Kredyt w euro. Sprawdź ofertę Kancelarii Adwokackiej specjalizującej się w sprawach kredytów w euro');

        return view('pages.kredyty-euro', [
            'h1' => 'Kredyty w euro',
            'h2' => 'Wygrywamy z bankami w sprawach o unieważnienie kredytów indeksowanych i denominowanych w euro',
            'content' => 'Zapewniamy kompleksową pomoc prawną - od analizy umowy do rozliczenia z bankiem.'
        ]);
    }
}
