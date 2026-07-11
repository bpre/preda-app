<?php

namespace App\Observers;

use App\Models\Stage;
use App\Models\Matter;
use Illuminate\Support\Str;
use App\Models\TemplateStage;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class MatterObserver
{
    public function creating(Matter $matter): void
    {
        $matter->id = Str::uuid();
    }

    public function created(Matter $matter): void
    {

        // foreach(TemplateStage::orderBy('sort')->get() as $stage) {

        //     Stage::create([
        //         'label' => $stage->label,
        //         'sort' => $stage->sort,
        //         'parent' => $stage->parent,
        //         'matter_id' => $matter->id,
        //         'stage_id' => $stage->id,
        //         'is_current' => $stage->is_lead_default,
        //         'date' => $stage->is_lead_default ? now() : NULL
        //     ]);

        // }

    }

    public function deleting(Matter $matter): void
    {
        $matter->stages()->each(fn ($stage) => $stage->delete());
    }

}
