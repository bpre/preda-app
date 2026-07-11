<?php

namespace App\Http\Controllers\Website\Pages;

use App\Facades\Website\Seo;
use App\Http\Controllers\Controller;

class PodzialMajatkuController extends Controller
{
    public function __invoke()
    {
        Seo::title('Podział majątku');
        Seo::description('Pomoc prawna w sprawach o podział majątku wspólnego.');

        return view('pages.podzial-majatku', [
            'h1' => 'Podział majątku',
            'h2' => 'Pomagamy uporządkować sprawy majątkowe po rozstaniu',
            'content' => 'Prowadzimy sprawy o podział majątku wspólnego, rozliczenie nakładów, spłat oraz innych roszczeń majątkowych między małżonkami.',
        ]);
    }
}
