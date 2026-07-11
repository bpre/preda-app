<?php

namespace App\Observers\Website;

use App\Models\Website\Sentence;

class SentenceObserver
{
    public function creating(Sentence $sentence)
    {

        $sentence->slug = wyrok_slug($sentence->court->label, $sentence->sign);

    }

    public function updating(Sentence $sentence)
    {

        $sentence->slug = wyrok_slug($sentence->court->label, $sentence->sign);

    }
}
