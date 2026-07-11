<?php

namespace App\Http\Controllers\Website\Pages;

use App\Models\Website\Sentence;
use App\Http\Controllers\Controller;
use App\Facades\Website\Seo;

class WyrokController extends Controller
{
    public function __invoke(string $slug)
    {
        $sentence = Sentence::where('is_published', true)->where('slug', $slug)->firstOrFail();

        Seo::title($sentence->metatitle);
        Seo::description($sentence->metadescription);

        return view('pages.wyrok', [
            'sentence' => $sentence,
        ]);
    }
}
