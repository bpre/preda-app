<?php

namespace App\View\Components\Section;

use Closure;
use App\Models\Website\Faq as FAQModel;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class Faq extends Component
{

    public function __construct(public $prefix)
    {}

    public function render(): View|Closure|string
    {
        return view('section.faq', [
            'faqs' => $faqs = FAQModel::where('prefix', $this->prefix)->orderBy('sort')->get()
        ]);
    }
}
