<?php

namespace App\Http\Controllers\Website\Pages;

use App\Models\Website\City;
use App\Models\Website\Office;
use App\Http\Controllers\Controller;
use App\Facades\Website\Seo;

class KancelariaOfficeController extends Controller
{

    public function __invoke()
    {

        $oddzial = request()->segment(2);

        $city = City::where('slug', $oddzial)->firstOrFail();

        $office = Office::query()
            ->active()
            ->where('slug', request()->segment(2))
            ->with('director')
            ->ordered()
            ->first();

        Seo::title( 'Kancelaria ' . $city->city);
        Seo::description('Kancelaria ' . $city->form_w .'. Poznaj naszą kancelarię i dowiedz się, w jaki sposób z zaangażowaniem pracujemy na sukces naszych Klientów.');

        return view('pages.kancelaria-office', [
            'h1' => 'Kancelaria '. $city->form_w,
            'h2' => 'Pomagamy unieważnić kredyty frankowe oraz kredyty w euro',
            'content' => 'Ponad '.config('app.ile_wygranych').' razy wygrywaliśmy z bankami w sprawach dotyczących kredytów frankowych oraz kredytów indeksowanych i denominowanych w euro i dolarach.',
            'city' => $city,
            'office' => $office
        ]);
    }
}
