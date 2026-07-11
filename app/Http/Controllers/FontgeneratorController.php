<?php

namespace App\Http\Controllers;

use FontLib\Font;
use Illuminate\Http\Request;

class FontgeneratorController extends Controller
{
    function __invoke()
    {
        $font = Font::load('../storage/fonts/PTSerif-Regular.ttf');
        $font->parse();
        $font->saveAdobeFontMetrics('../storage/fonts/PTSerif-Regular.ufm');
    }
}
