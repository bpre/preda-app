<?php

namespace App\View\Components\Theme;

use Closure;
use App\View\Components\Theme\Navigation\CreditsNavigation;
use App\View\Components\Theme\Navigation\FamilyLawNavigation;
use App\View\Components\Theme\Navigation\NavigationController;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;
use Throwable;

class Navigation extends Component
{
    public array $navigation;

    public array $button;

    public function __construct(
        public bool $mobile = false,
        public string $variant = 'credits',
        public ?array $switcher = null,
    )
    {
        $controller = self::controllerFor($variant);

        $this->navigation = $this->withActiveStates($controller->items());
        $this->button = $controller->button();
    }

    public static function controllerFor(string $variant): NavigationController
    {
        return match ($variant) {
            'family-law' => new FamilyLawNavigation(),
            default => new CreditsNavigation(),
        };
    }

    private function withActiveStates(array $items): array
    {
        return array_map(function (array $item): array {
            if (($item['type'] ?? null) === 'spacer') {
                return $item;
            }

            $item['active'] = $this->routeIsActive((string) ($item['route'] ?? ''));

            if (isset($item['more'])) {
                $item['more'] = $this->withActiveStates($item['more']);
                $item['active'] = $item['active'] || collect($item['more'])->contains('active', true);
            }

            return $item;
        }, $items);
    }

    private function routeIsActive(string $route): bool
    {
        if ($route === '') {
            return false;
        }

        if (request()->routeIs($route)) {
            return true;
        }

        try {
            $path = trim(route($route, [], false), '/');
        } catch (Throwable) {
            return false;
        }

        if ($path === '') {
            return request()->is('/');
        }

        return request()->is($path, $path . '/*');
    }

    public function render(): View|Closure|string
    {
        if ($this->mobile) {
            return view('theme.navigation-mobile');
        }

        return view('theme.navigation');
    }
}
