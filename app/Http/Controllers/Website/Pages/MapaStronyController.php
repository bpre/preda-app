<?php

namespace App\Http\Controllers\Website\Pages;

use App\Http\Controllers\Controller;
use App\Facades\Website\Seo;

class MapaStronyController extends Controller
{
    public function __invoke()
    {

        Seo::title('Mapa strony');
        Seo::description('Zobacz mapę naszej strony i efektywnie korzystaj z jej zasobów.');

        return view('pages.mapa-strony', [
            'h1' => 'Mapa strony',
            'h2' => 'Przejrzyj mapę naszej strony i efektywnie korzystaj z jej zasobów.'
        ]);
    }
}
