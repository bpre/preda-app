<?php

namespace App\Http\Controllers\Website\Pages;

use App\Models\Website\Office;
use App\Http\Controllers\Controller;
use App\Facades\Website\Seo;

class KancelariaController extends Controller
{
    public function __invoke()
    {

        Seo::title('O kancelarii');
        Seo::description('Poznaj naszą kancelarię i dowiedz się, w jaki sposób z zaangażowaniem pracujemy na sukces naszych Klientów.');

        return view('pages.kancelaria', [
            'h1' => 'O kancelarii',
            'h2' => 'Kredyty powiązane z walutami obcymi to nasza specjalność',
            'content' => 'Ponad '.config('app.ile_wygranych').' razy wygrywaliśmy z bankami w sprawach dotyczących kredytów frankowych oraz kredytów indeksowanych i denominowanych w euro i dolarach.',
            'offices' => Office::query()->active()->ordered()->get()->pluck('city')->implode(' / ')
        ]);
    }
}
