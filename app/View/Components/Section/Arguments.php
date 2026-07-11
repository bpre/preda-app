<?php

namespace App\View\Components\Section;

use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class Arguments extends Component
{

    public function __construct()
    {}

    public function render(): View|Closure|string
    {
        return view('section.arguments');
    }
}
