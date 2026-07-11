<?php

namespace App\Observers;

use App\Models\Credit;
use Illuminate\Support\Str;

class CreditObserver
{

    public function creating(Credit $credit)
    {
        $credit->id = Str::uuid();

    }

    public function deleting(Credit $credit)
    {
        $credit->credit_contacts()->detach();
    }

}
