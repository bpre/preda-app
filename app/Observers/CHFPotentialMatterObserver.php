<?php

namespace App\Observers;

use App\Models\Matter;
use App\Support\StageManager;
use Illuminate\Support\Str;

class CHFPotentialMatterObserver
{
    public function creating(Matter $matter): void
    {
        $matter->id = Str::uuid();
        $matter->category = 'CHF';
    }

    public function created(Matter $matter): void
    {
        StageManager::ensureDefaultStage($matter);
    }

    public function deleting(Matter $matter): void
    {
        $matter->stages()->each(fn ($stage) => $stage->delete());
    }

}
