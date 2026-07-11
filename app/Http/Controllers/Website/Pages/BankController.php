<?php

namespace App\Http\Controllers\Website\Pages;

use App\Facades\Website\Seo;
use App\Models\Website\Bank;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BankController extends Controller
{
    public function __invoke(Request $request, $slug)
    {

        $bank = Bank::where('slug', $slug)->where('is_published', 1)->firstOrFail();

        Seo::title($bank->label . ' - klauzule niedozwolone w kredytach frankowych' . ($bank->hasEUR() ? ' i kredytach w euro' : null));
        Seo::description('Klauzule niedozwolone w umowach '.$bank->form_a.'. Sprawdź, czy Twoja umowa kredytu frankowego '. ($bank->hasEUR() ? 'lub kredytu w euro' : null).' zawiera klauzule abuzywne.');

        return view('pages.bank', [
            'bank' => $bank,
            'h1' => 'Unieważnij umowę kredytową '. $bank->form_a,
            'h2' => 'Kredyt frankowy'. ($bank->hasEUR() ? ' lub kredyt w euro' : null) .' w '. $bank->form_a .'?',
            'content' => 'Zobacz jakie postanowienia niedozwolone pozwalające unieważnić umowę występują w kredytach oferowanych przez '. $bank->label .'.'
        ]);
    }
}
