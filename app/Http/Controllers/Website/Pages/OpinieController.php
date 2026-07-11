<?php

namespace App\Http\Controllers\Website\Pages;

use App\Http\Controllers\Controller;
use App\Facades\Website\Seo;

class OpinieController extends Controller
{
    public function __invoke()
    {

        Seo::title('Opinie klientów o kancelarii');
        Seo::description('Zobacz, co mówią o nas nasi Klienci');

        return view('pages.opinie', [
            'h1' => 'PRĘDA Kancelaria Adwokacka - opinie',
            'h2' => 'Zobacz, co mówią o nas nasi Klienci'
        ]);
    }

}
