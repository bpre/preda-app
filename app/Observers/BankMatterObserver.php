<?php

namespace App\Observers;

use App\Models\Stage;
use App\Models\Matter;
use Illuminate\Support\Str;
use App\Models\TemplateStage;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class BankMatterObserver
{
    public function creating(Matter $matter): void
    {
        $matter->id = Str::uuid();
        $matter->category = 'Powództwo banku';
    }

    public function deleting(Matter $matter): void
    {
        $matter->stages()->each(fn ($stage) => $stage->delete());
    }

}
