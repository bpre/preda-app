<?php

namespace App\Http\Controllers\Website\Pages;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Facades\Website\Seo;

class KredytyFrankoweController extends Controller
{
    public function __invoke()
    {

        Seo::title('Kredyty frankowe ');
        Seo::description('Kredyty frankowe. Sprawdź ofertę Kancelarii Adwokackiej specjalizującej się w sprawach kredytów frankowych');

        return view('pages.kredyty-frankowe', [
            'h1' => 'Kredyty frankowe',
            'h2' => 'Ponad '.config('app.ile_wygranych').' wygranych spraw z bankami',
            'content' => 'Pomagamy kredytobiorcom uwolnić się od „kredytów frankowych”. Zapewniamy kompleksową pomoc prawną - od analizy umowy do rozliczenia z bankiem.'
        ]);
    }
}
