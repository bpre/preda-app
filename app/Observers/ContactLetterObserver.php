<?php

namespace App\Observers;

use App\BP\neoznaczki;
use App\Models\Neostamp;
use Illuminate\Support\Str;
use App\Models\ContactLetter;
use Filament\Notifications\Notification;
use App\Filament\Resources\NeostampResource;

class ContactLetterObserver
{

    /*
    private function przypiszZnaczek(ContactLetter $contactLetter, $c = NULL)
    {
        if(in_array($contactLetter->delivery_type, NeostampResource::types())) {

            $neostamp = Neostamp::where('type', $contactLetter->delivery_type)
                ->whereNull('contact_letter_id')
                ->orderBy('created_at')
                ->first();

            if($neostamp === null) {

                Notification::make()
                    ->title('Skończyły się neoznaczki tego typu!')
                    ->body('Dodaj znaczki tego typu, a następnie przypisz znaczek do tej przesyłki.')
                    ->danger()->send();

            } else {

                $neostamp->contact_letter_id = $c ? $c->id : $contactLetter->id;
                $neostamp->save();

                ContactLetter::where('id', $neostamp->contact_letter_id)->update([
                    'neostamp_id' => $neostamp->id
                ]);

                Notification::make()->title('Przypisano Neoznaczek')->success()->send();

            }

        }
    }
    */

    public function created(ContactLetter $contactLetter)
    {

        $c = ContactLetter::where('contact_id', $contactLetter->contact_id)
                ->where('letter_id', $contactLetter->letter_id)->first();

        neoznaczki::przypiszZnaczek($contactLetter, $c);

    }

    public function updating(ContactLetter $contactLetter)
    {

        if($contactLetter->isDirty('delivery_type')) {

            // PRZYWRACAMY NIEUŻYWANY ZNACZEK

            $neostamp = Neostamp::where('contact_letter_id', $contactLetter->id)->first();

            if($neostamp) {

                $neostamp->contact_letter_id = NULL;
                $neostamp->save();

            }

            $contactLetter->neostamp_id = NULL;

            // PRZYPISZ NOWY ZNACZEK

            neoznaczki::przypiszZnaczek($contactLetter);

        }

    }

    public function deleting(ContactLetter $contactLetter)
    {
        $neostamp = Neostamp::where('contact_letter_id', $contactLetter->id)->first();

        if($neostamp) {

            $neostamp->contact_letter_id = NULL;
            $neostamp->save();

        }

        $contactLetter->neostamp_id = NULL;

    }
}
