<?php

namespace App\Http\Controllers\Website\Pages;

use App\Http\Controllers\Controller;
use App\Facades\Website\Seo;

class KlauzuleNiedozwoloneController extends Controller
{
    public function __invoke()
    {

        Seo::title( 'Klauzule niedozwolone');
        Seo::description('Klauzule niedozwolone w umowach kredytów frankowych i euro. Sprawdź, czy Twoja umowa zawiera klauzule abuzywne."');

        return view('pages.klauzule-niedozwolone', [
            'h1' => 'Klauzule niedozwolone',
            'h2' => 'Klauzule niedozwolone w umowach kredytów frankowych i kredytów w euro',
            'content' => 'Praktycznie wszystkie umowy kredytów powiązanych z walutami obcymi zawierały klauzule niedozwolone. Zobacz przykładowe klauzule niedozwolone występujące w umowach kredytowych poszczególnych banków.
'
        ]);
    }}
