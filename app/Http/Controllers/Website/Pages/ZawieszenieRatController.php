<?php

namespace App\Http\Controllers\Website\Pages;

use App\Facades\Website\Seo;
use Illuminate\Http\Request;
use App\Models\Website\Security;
use App\Http\Controllers\Controller;

class ZawieszenieRatController extends Controller
{
    public function __invoke()
    {

        Seo::title('Zawieszenie rat kredytu frankowego i euro');
        Seo::description('Prawdopodobnie Ty też możesz zawiesić spłaty rat swojego kredytu powiązanego z walutą obcą!');

        $heading = 'Zawieszenie rat kredytu';
        $subheading = 'Zawieszenie rat kredytu jest możliwe!';

        $securities = Security::where('is_published', true)
            ->orderBy('sentence_date', 'desc')->get();

        return view('pages.zawieszenie-rat', [
            'heading'=> $heading,
            'subheading' => $subheading,
            'securities' => $securities,
            'active_banks' => [],
        ]);
    }
}
