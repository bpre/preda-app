<?php

namespace App\View\Components\Section;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Hero extends Component
{
    public function __construct(
        public ?string $heading = '',
        public ?string $subheading = '',
        public ?string $content = '',

        /*
        $subheadingIsPrimary
        - domyślnie subheading to h3 (ewentualnie h2 przy $useH1 = true); tą opcją zmieniasz na H2 (ewentualnie H1)
        - ta opcja nie zmienia kolejności wyświetlania nagłówków, ale wyłącznie tagi przy nagłówkach
        - ta opcja nie powinna być używana jednocześnie z $useH1 = true, ponieważ sprawdzanie unikalności nagłówków H1 nie będzie działało (coś, co w bazie nie jest nagłówkiem H1, na stronie będzie wyświetlało się jako nagłówek H1)
        */
        public bool $subheadingIsPrimary = false,

        /*
        $displaySubheadingFirst
        - domyślnie $subheading wyświetla się pod $heading; tą opcją możesz to zmienić
        */
        public bool $displaySubheadingFirst = false,

        /*
        $useH1
        - domyślnie $heading i $ubheading wyświetlają się jako H2 i H3; tą opcją zmieniasz H2 na H1 oraz H3 na H2;
        - stosuje się tylko raz na stronie, najczęściej w pierwszej sekcji (na stronie powinien być tylko jeden nagłówek H1)
        */
        public bool $useH1 = false,

        public bool $showButtons = true,
        public string $button_1_text = 'Sprawdź swój kredyt',
        public string $button_1_route = 'analiza',
        public string $button_2_text = 'Oferta',
        public string $button_2_route = 'oferta',

    ) {}

    public function render(): View|Closure|string
    {
        return view('section.hero');
    }
}
