<?php

namespace App\View\Components\Section;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Frame extends Component
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

        public bool $alternate = false,
        public bool $full = false,
        public bool $main = false,
        public bool $right = false,
        public bool $more = false,
        public string $extraComponent = '',
        public bool $extraMarginTop = false,
        public string $image = '',
        public string $imageClass = '',
        public string $figcaption = '',
        public int $index = 0
    ) {}

    public function render(): View|Closure|string
    {
        return view('section.frame');
    }

}
