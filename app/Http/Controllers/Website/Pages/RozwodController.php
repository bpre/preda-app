<?php

namespace App\Http\Controllers\Website\Pages;

use App\Facades\Website\Seo;
use App\Http\Controllers\Controller;

class RozwodController extends Controller
{
    public function __invoke()
    {
        Seo::title('Rozwód i podział majątku');
        Seo::description('Pomoc prawna w sprawach o rozwód i podział majątku.');

        return view('pages.rozwod', [
            'h1' => 'Rozwód i podział majątku',
            'h2' => 'Pomagamy przejść przez sprawy rodzinne spokojnie i strategicznie',
            'content' => 'Zapewniamy kompleksową pomoc prawną w sprawach o rozwód, alimenty, kontakty z dziećmi oraz podział majątku wspólnego.',
        ]);
    }
}
