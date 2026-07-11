<?php

namespace App\View\Components\Partial;

use Closure;
use App\Models\Website\Office;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class Offices extends Component
{
    public function __construct(public bool $light = true) {}

    public function render(): View|Closure|string
    {
        $offices = Office::query()
            ->active()
            ->where('is_headquarters', false)
            ->ordered()
            ->get();

        return view('partial.offices', compact('offices'));
    }
}
