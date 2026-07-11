<?php

namespace App\Observers;

use App\Models\Deal;

class DealObserver
{
    public function deleting(Deal $deal)
    {
        $deal->deal_contacts()->detach();
        $deal->deal_credits()->detach();
    }
}
