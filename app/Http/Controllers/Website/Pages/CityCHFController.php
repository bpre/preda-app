<?php

namespace App\Http\Controllers\Website\Pages;

use App\Models\Website\City;
use Illuminate\Http\Request;
use App\Models\Website\Office;
use App\Http\Controllers\Controller;
use App\Facades\Website\Seo;

class CityCHFController extends Controller
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


        Seo::title('Kredyty frankowe ' . $city->city);
        Seo::description('Kredyty frankowe '.$city->city.'. Sprawdź ofertę Kancelarii Adwokackiej specjalizującej się w sprawach kredytów frankowych - '. $city->city);

        return view('pages.city-chf', [
            'city' => $city,
            'office' => $office,
            'h1' => 'Kredyty frankowe '. $city->city,
            'h2' => 'Ponad '.config('app.ile_wygranych').' wygranych spraw z bankami',
            'content' => 'Pomagamy kredytobiorcom '. $city->form_z .' uwolnić się od „kredytów frankowych”. Zapewniamy kompleksową pomoc prawną - od analizy umowy do rozliczenia z bankiem.'
        ]);
    }
}
