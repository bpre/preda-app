<?php

namespace App\View\Components\Section;

use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class Motivation extends Component
{

    public function __construct(public ?string $currency = 'CHF')
    {
        $this->currency = $currency ?: 'CHF';
    }
    public function render(): View|Closure|string
    {

        return view('section.motivation', [
            'currency' => $this->currency
        ]);
    }
}
