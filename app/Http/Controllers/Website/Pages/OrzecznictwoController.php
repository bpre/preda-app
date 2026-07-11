<?php

namespace App\Http\Controllers\Website\Pages;

use App\Http\Controllers\Controller;
use App\Facades\Website\Seo;

class OrzecznictwoController extends Controller
{
    public function __invoke()
    {

        Seo::title('Orzecznictwo');
        Seo::description('Orzecznictwo w sprawach kredytów frankowych i kredytów w euro');

        return view('pages.orzecznictwo');
    }
}
