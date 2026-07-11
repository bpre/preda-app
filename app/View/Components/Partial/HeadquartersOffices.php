<?php

namespace App\View\Components\Partial;

use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class HeadquartersOffices extends Component
{
    public function __construct(public bool $light = true) {}

    public function render(): View|Closure|string
    {

        return view('partial.headquarters-offices');
    }
}
