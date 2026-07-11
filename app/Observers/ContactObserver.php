<?php

namespace App\Observers;

use App\Models\Contact;
use Illuminate\Support\Str;

class ContactObserver
{

    public function creating(Contact $contact)
    {
        $contact->id = Str::uuid();

        if($contact->type === 'osoba') {
            $contact->label = $contact->first_name.' '.$contact->last_name;
            $contact->sort_name = $contact->last_name.', '.$contact->first_name;
        } else {
            $contact->label = $contact->organization_short;
            $contact->sort_name = $contact->organization_short;
        }

    }

    public function updating(Contact $contact)
    {

        if($contact->type === 'osoba') {
            $contact->label = $contact->first_name.' '.$contact->last_name;
            $contact->sort_name = $contact->last_name.', '.$contact->first_name;
        } else {
            $contact->label = $contact->organization_short;
            $contact->sort_name = $contact->organization_short;
        }

    }
}
