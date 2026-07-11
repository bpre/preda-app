<?php

namespace App\View\Components\Theme;

use Closure;
use App\Models\Website\Office;
use App\Support\Website\PracticeContext;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class Header extends Component
{
    public function __construct(public bool $light = true) {}

    public function render(): View|Closure|string
    {

        $offices = Office::query()
            ->active()
            ->ordered()
            ->get();

        return view('theme.header', [
            'offices' => $offices,
            'sidebarContext' => $this->sidebarContext(),
        ]);
    }

    private function sidebarContext(): array
    {
        $switcherOptions = [
            'credits' => [
                'label' => 'Kredyty CHF / EUR',
                'href' => route('homepage'),
            ],
            'family-law' => [
                'label' => 'Rozwód i podział majątku',
                'href' => route('rozwod'),
            ],
        ];

        $activeKey = PracticeContext::current();
        $activeSwitcherOptions = array_intersect_key(
            $switcherOptions,
            array_flip(PracticeContext::activeContexts()),
        );
        $navigationController = Navigation::controllerFor($activeKey);

        return [
            'variant' => $activeKey,
            'button' => $navigationController->button(),
            'switcher' => [
                'current' => $switcherOptions[$activeKey],
                'options' => array_values(array_diff_key($activeSwitcherOptions, [$activeKey => true])),
            ],
        ];
    }
}
