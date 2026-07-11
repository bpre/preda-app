<?php

namespace App\View\Components\Partial;

use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;
use App\Enums\Website\Frontend\Office;

class Author extends Component
{
    public function __construct(public $author) {}

    public function render(): View|Closure|string
    {

        return view('partial.author');
    }
}
