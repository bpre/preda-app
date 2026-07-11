<?php

namespace App\View\Components\Section;

use Closure;
use App\Models\User;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class Team extends Component
{

    public function __construct(){}

    public function render(): View|Closure|string
    {
        return view('section.team', [
            'team' =>User::where('website_is_published', true)->orderBy('website_sort')->get()
        ]);
    }
}
