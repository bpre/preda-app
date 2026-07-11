<?php

namespace App\View\Components\Section;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class HowCanWeHelp extends Component
{

    public function __construct()
    {}

    public function render(): View|Closure|string
    {
        return view('section.how-can-we-help');
    }
}
