<?php

namespace App\View\Components\Theme;

use Closure;
use App\Models\Website\Office;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class Footer extends Component
{
    public function __construct(public bool $light = true) {}

    public function render(): View|Closure|string
    {

        $offices = Office::query()
            ->active()
            ->ordered()
            ->get();

        $kancelaria = [
            array('O nas', 'kancelaria'),
            array('Kontakt', 'kontakt'),
            array('Nasze wyroki', 'wyroki'),
            array('Opinie klientów', 'opinie'),
            array('Polityka prywatności', 'polityka-prywatnosci')
        ];

        $oferta = [
            array('Kredyty frankowe', 'kredyty-frankowe'),
            array('Kredyty w euro', 'kredyty-euro'),
            array('Wynagrodzenie kancelarii', 'oferta'),
            array('Gdzie działamy', 'gdzie-dzialamy'),
            array('Mapa strony', 'mapa-strony')
        ];

        $wiedza = [
            array('Blog', 'blog'),
            array('Orzecznictwo', 'orzecznictwo'),
            array('Częste pytania', 'faq'),
            array('Klauzule niedozwolone', 'klauzule-niedozwolone'),
            array('Spłacony kredyt frankowy', 'splacony-kredyt')
        ];

        return view('theme.footer', [
            'offices' => $offices,
            'kancelaria' => $kancelaria,
            'oferta' => $oferta,
            'wiedza' => $wiedza
        ]);
    }
}
