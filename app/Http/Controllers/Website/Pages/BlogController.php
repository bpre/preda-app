<?php

namespace App\Http\Controllers\Website\Pages;

use App\Http\Controllers\Controller;
use App\Facades\Website\Seo;

class BlogController extends Controller
{
    public function __invoke()
    {

        Seo::title('Blog o kredytach frankowych i euro');
        Seo::description('Artykuły poświęcone zagadnieniom związanym z kredytami frankowymi i kredytami w euro');

        return view('pages.blog', [
            'h1' => 'Blog',
            'h2' => 'Zagadnienia prawne, orzecznictwo, argumentacja, analizy'
        ]);
    }
}
