<?php

namespace App\Http\Controllers\Website\Pages;

use App\Http\Controllers\Controller;
use App\Facades\Website\Seo;

class OfertaController extends Controller
{
    public function __invoke()
    {

        Seo::title('Oferta - Wynagrodzenie kancelarii w sprawach kredytów frankowych i euro');
        Seo::description('Sprawdź koszty prowadzenia sprawy kredytu CHF / USD / EUR');

        return view('pages.oferta', [
            'h1' => 'Oferta',
            'h2' => 'Sprawdź koszty prowadzenia sprawy o unieważnienie kredytu powiązanego z walutą obcą.',
            'content' => '
<p>Oferujemy kilka wariantów prowadzenia sprawy dotyczącej kredytu powiązanego z walutą obcą.</p>

<p>Warianty różnią się jedynie sposobem rozliczeń z kancelarią. Zakres usług prawnych świadczonych w ramach każdego z wariantów jest taki sam.</p>

<p>Aby uzyskać spersonalizowaną, pełną ofertę prowadzenia sprawy - prześlij swoją umowę kredytową do analizy.</p>
            '
        ]);
    }
}
