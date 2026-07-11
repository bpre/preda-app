<?php

namespace App\Http\Controllers\Website\Pages;

use App\Facades\Website\Seo;
use App\Http\Controllers\Controller;

class HomepageController extends Controller
{
    public function __invoke()
    {

        Seo::title('Kredyty frankowe i kredyty euro - Głogów, Zielona Góra');
        Seo::description('Chcesz uwolnić się od kredytu frankowego lub kredytu w euro? Kancelaria adwokacka z doświadczeniem. Ponad '.config('app.ile_wygranych').' wygranych z bankami.');

        return view('pages.homepage', [
            'h1' => 'Pomagamy unieważnić kredyty frankowe oraz kredyty w euro',
            'h2' => 'Ponad '.config('app.ile_wygranych').' wygranych spraw z bankami.',
            'content' => 'Zapewniamy kompleksową pomoc prawną - od analizy umowy do rozliczenia z bankiem.'
        ]);
    }
}
