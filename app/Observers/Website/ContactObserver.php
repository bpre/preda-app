<?php

namespace App\Observers\Website;

use App\Models\Website\Contact;
use App\Enums\Website\ContactCategories;

class ContactObserver
{
    public function creating(Contact $contact)
    {

        if($contact->category->value === ContactCategories::SEDZIA->value) {

            $contact->label = $contact->first_name.' '.$contact->last_name;
            $contact->sort_name = $contact->last_name.', '.$contact->first_name;
        } else {
            $contact->sort_name = $contact->label;
        }
    }

    public function updating(Contact $contact)
    {

        if($contact->category === ContactCategories::SEDZIA->value) {
            $contact->label = $contact->first_name.' '.$contact->last_name;
            $contact->sort_name = $contact->last_name.', '.$contact->first_name;
        } else {
            $contact->sort_name = $contact->label;
        }

    }
}
