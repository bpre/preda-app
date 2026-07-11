<?php

namespace App\View\Components\Section;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Office extends Component
{

    public function __construct(public $office, public ?string $currency = 'CHF'){

        $this->currency = $currency ?: 'CHF';

    }

    public function render(): View|Closure|string
    {
        return view('section.office');
    }
}
