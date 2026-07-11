<?php

namespace App\Http\Controllers\Website\Pages;

use Illuminate\Http\Request;

use App\Models\Website\Pipedrive;
use App\Http\Controllers\Controller;
use App\Notifications\RemoveRequestMailing;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewOfferRequestMailing;

class NewYearMailingController extends Controller
{
    public function __invoke(?string $typ = null, ?string $ident = null)
    {
        $client = Pipedrive::where('id', $ident)->firstOrFail();


        if($typ == 'o')
        {
            // dd('Dodajemy do bazy rekord -> prośba o ofertę.');

            $client->offer_request = now();
            $client->save();

            $h1 = 'Przyjęliśmy Twoje zgłoszenie';
            $h2 = 'Dziękujemy. Wkrótce otrzymasz od nas spersonalizowaną ofertę.';

            Notification::route('mail', 'bartosz.preda@preda.info')->notify(new NewOfferRequestMailing($client));

        }

        elseif($typ == 'r')
        {
            // dd('Dodajemy do bazy rekord -> prośba o usunięcie z bazy.');

            if($client->remove_request == NULL)
            {
                $h1 = 'Zgłoszenie przyjęte';
                $h2 = 'Twoje dane zostaną usunięte.';

                $client->remove_request = now();
                $client->save();
                Notification::route('mail', 'bartosz.preda@preda.info')->notify(new RemoveRequestMailing($client));

            } else {

                $h1 = 'Akcja została już wykonana';
                $h2 = 'Nie ma potrzeby jej ponawiania.';

            }
        }

        else
        {
            abort(404);
        }


        return view('pages.new-year-mailing', [
            'h1' => $h1,
            'h2' => $h2
        ]);


    }
}
