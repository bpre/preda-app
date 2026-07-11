<?php

namespace App\Http\Controllers\Website\Pages;

use App\Models\Website\City;
use Illuminate\Http\Request;
use App\Models\Website\Office;
use App\Http\Controllers\Controller;
use App\Facades\Website\Seo;

class CityEURController extends Controller
{
    public function __invoke(Request $request, $slug)
    {

        $city = City::where('slug', $slug)->where('is_published', 1)->firstOrFail();

        $office = Office::query()
            ->active()
            ->where('slug', $slug)
            ->with('director')
            ->ordered()
            ->first();

        Seo::title('Kredyt w euro ' . $city->city);
        Seo::description('Kredyt w euro '.$city->city.'. Sprawdź ofertę Kancelarii Adwokackiej specjalizującej się w sprawach kredytów w euro - '. $city->city);

        return view('pages.city-eur', [
            'city' => $city,
            'office' => $office,
            'h1' => 'Kredyt w euro '. $city->city,
            'h2' => 'Wygrywamy z bankami w sprawach o unieważnienie kredytów indeksowanych i denominowanych w euro',
            'content' => 'Zapewniamy kompleksową pomoc prawną kredytobiorcom '. $city->form_z .' - od analizy umowy do rozliczenia z bankiem.'
        ]);
    }
}
