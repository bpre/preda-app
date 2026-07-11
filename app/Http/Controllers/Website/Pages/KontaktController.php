<?php

namespace App\Http\Controllers\Website\Pages;

use App\Models\Website\Office;
use App\Http\Controllers\Controller;
use App\Facades\Website\Seo;

class KontaktController extends Controller
{
    public function __invoke()
    {
        $offices = Office::query()
            ->active()
            ->ordered()
            ->get();

        $officeForms = $this->formatOfficeForms($offices->pluck('form_w')->all());

        Seo::title('Kontakt');
        Seo::description('Skontaktuj się z naszą kancelarią adwokacką w sprawie kredyty frankowego lub kredytu w euro.');

        return view('pages.kontakt', [
            'h1' => 'Kontakt',
            'h2' => 'Skontaktuj się z nami w dogodny dla Ciebie sposób',
            'content' => $officeForms === null
                ? 'Jesteśmy dla Ciebie dostępni w siedzibie kancelarii. <strong>Prowadzimy sprawy w całej Polsce.</strong>'
                : 'Jesteśmy dla Ciebie dostępni w jednym z naszych oddziałów: '.$officeForms.'. <strong>Prowadzimy sprawy w całej Polsce.</strong>',
            'offices' => $offices,
        ]);
    }

    private function formatOfficeForms(array $forms): ?string
    {
        $forms = array_values(array_filter($forms));

        return match (count($forms)) {
            0 => null,
            1 => $forms[0],
            2 => $forms[0].' i '.$forms[1],
            default => implode(', ', array_slice($forms, 0, -1)).' i '.$forms[array_key_last($forms)],
        };
    }
}
