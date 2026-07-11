<?php

namespace App\Http\Controllers\Website\Pages;

use App\Http\Controllers\Controller;
use App\Facades\Website\Seo;

class FAQController extends Controller
{
    public function __invoke()
    {

        Seo::title('Odpowiedzi na częste pytania');
        Seo::description('Poznaj odpowiedzi na częste pytania dotyczące kredytów frankowych i kredytów w euro');

        return view('pages.faq', [
            'h1' => 'Częste pytania',
            'h2' => 'Kredyty frankowe i kredyty w euro - odpowiedzi na częste pytania'
        ]);
    }
}
