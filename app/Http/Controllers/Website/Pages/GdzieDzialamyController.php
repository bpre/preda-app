<?php

namespace App\Http\Controllers\Website\Pages;

use App\Models\Website\City;
use App\Enums\Website\Provinces;
use App\Http\Controllers\Controller;
use App\Facades\Website\Seo;

class GdzieDzialamyController extends Controller
{
    public function __invoke()
    {

        $provinces = Provinces::all();

        Seo::title('Kredyty frankowe i kredyty w euro: gdzie działamy? ');
        Seo::description('Reprezentujemy kredytobiorców z całej Polski. Zobacz, przed jakimi sądami prowadzimy sprawy.');

        return view('pages.gdzie-dzialamy', [
            'provinces' => $provinces,
            'cities' => City::where('is_published', true)->orderBy('city')->get(),
            'h1' => 'Zobacz, gdzie działamy',
            'h2' => 'Pomagamy kredytobiorcom z całej Polski',
            'content' => '
<p>Prowadzimy sprawy w całej Polsce.</p>

<p>Reprezentujemy również kredytobiorców mieszkających za granicą.</p>

<p>Aktualnie świadczymy pomoc prawną w sprawach frankowych na rzecz Klientów mieszkających m.in. w następujących miastach:</p>
            '
        ]);
    }
}
