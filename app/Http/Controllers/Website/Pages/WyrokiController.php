<?php

namespace App\Http\Controllers\Website\Pages;

use App\Models\Website\Bank;
use App\Models\Website\Contact;
use App\Http\Controllers\Controller;
use App\Facades\Website\Seo;

class WyrokiController extends Controller
{
    public function __invoke(?string $category = null, ?string $slug = null)
    {

        if($category && $slug)
        {

            // Kategoria: Sąd
            if($category == 'sad')
            {

                $court = Contact::where('category', $category)->where('slug', $slug)->firstOrFail();

                $count = count($court->court_published_sentences->toArray());
                $count_zero_message = 'Nie ma opublikowanych wyroków tego sądu.';

                Seo::title($court->organization .' - wyroki w sprawach kredytów frankowych i euro');
                Seo::description('Zobacz wyroki wydane przez ' .$court->organization. ' w sprawach kredytów frankowych i euro prowadzonych przez naszą kancelarię"');

                $heading = 'Wyroki sądu w sprawach prowadzonych przez naszą kancelarię';
                $subheading = $court->organization;

            }

            // Kategoria: Sędzia
            if($category == 'sedzia')
            {

                $judge = Contact::where('category', $category)->where('slug', $slug)->firstOrFail();

                $count = count($judge->judge_published_sentences->toArray());
                $count_zero_message = 'Nie ma opublikowanych wyroków tego sędziego.';

                Seo::title($judge->label .' - wyroki w sprawach kredytów frankowych i euro');
                Seo::description('Zobacz wyroki sędziego ' .$judge->label. ' w sprawach prowadzonych przez naszą kancelarię"');

                $heading = 'Wyroki sędziego w sprawach prowadzonych przez naszą kancelarię';
                $subheading = 'Sędzia: '.$judge->label;

            }

            // Kategoria: Bank
            if($category == 'bank')
            {

                $bank = Bank::where('is_published', 1)->where('slug', $slug)->firstOrFail();

                $count = count($bank->bank_published_sentences->toArray());
                $count_zero_message = 'Nie ma opublikowanych wyroków tego banku.';

                Seo::title($bank->label .' - wyroki w sprawach kredytów frankowych i euro');
                Seo::description('Zobacz wyroki wydane przeciwko ' .$bank->label. ' w sprawach kredytów frankowych i euro prowadzonych przez naszą kancelarię"');

                $heading = 'Wyroki naszej kancelarii w sprawach kredytów frankowych i euro przeciwko '.$bank->label;
                $subheading = $bank->bank .' - wyroki';

            }

        }

        // Kategoria: Kredyty euro
        elseif($category == 'kredyty-euro')
        {

            Seo::title('Wyroki w sprawach kredytów euro');
            Seo::description('Zobacz wyroki w sprawach kredytów euro prowadzonych przez naszą kancelarię. Prawomocnie wygrywamy z bankami w sporach dotyczących kredytów w EUR.');

            $count = 1;
            $count_zero_message = '';

            $heading = 'Wyroki w sprawach kredytów euro';
            $subheading = 'Kredyty euro - wyroki naszej kancelarii';

        }

        // Kategoria: Kredyty frankowe
        elseif($category == 'kredyty-frankowe')
        {

            Seo::title('Wyroki w sprawach kredytów frankowych');
            Seo::description('Zobacz wyroki w sprawach kredytów frankowych prowadzonych przez naszą kancelarię. Prawomocnie wygrywamy z bankami w sporach dotyczących kredytów w CHF.');

            $count = 1;
            $count_zero_message = '';

            $heading = 'Wyroki w sprawach kredytów frankowych';
            $subheading = 'Kredyty frankowe - wyroki naszej kancelarii';

        }

        // Kategoria: Spłacone
        elseif($category == 'splacone')
            {

                Seo::title('Wyroki w sprawach kredytów spłaconych');
                Seo::description('Zobacz wyroki w sprawach spłaconych kredytów frankowych i euro prowadzonych przez naszą kancelarię"');

                $count = 1;
                $count_zero_message = '';

                $heading = 'Wyroki naszej kancelarii w sprawach kredytów spłaconych';
                $subheading = 'Spłacone kredyty frankowe - zobacz wyroki w sprawach prowadzonych przez naszą kancelarię.';

        }


        // STRONA GŁÓWNA WYROKÓW
        else
        {

            Seo::title('Wyroki naszej kancelarii w sprawach kredytów frankowych i euro');
            Seo::description('Prawomocnie wygrywamy z bankami w sprawach kredytów frankowych i euro. Zobacz wyroki w sprawach prowadzonych przez naszą kancelarię.');

            $count = 1;
            $count_zero_message = '';

            $heading = 'Nasze wyroki';
            $subheading = 'Zobacz wyroki w sprawach prowadzonych przez naszą kancelarię.';

        }

        return view('pages.wyroki', [
            'heading'=> $heading,
            'subheading' => $subheading,
            'count' => $count,
            'count_zero_message' => $count_zero_message
        ]);
    }
}
