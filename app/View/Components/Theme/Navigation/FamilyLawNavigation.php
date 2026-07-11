<?php

namespace App\View\Components\Theme\Navigation;

class FamilyLawNavigation implements NavigationController
{
    public function items(): array
    {
        return [
            [
                'route' => 'rozwod',
                'text' => 'Rozwód',
            ],
            [
                'route' => 'podzial-majatku',
                'text' => 'Podział majątku',
            ],
            [
                'type' => 'spacer',
            ],
            [
                'route' => 'kancelaria',
                'text' => 'O nas',
            ],
            [
                'route' => 'opinie',
                'text' => 'Opinie klientów',
            ],
            [
                'route' => 'kontakt',
                'text' => 'Kontakt',
            ],
        ];
    }

    public function button(): array
    {
        return [
            'eyebrow' => 'Konsultacja',
            'route' => 'kontakt',
            'text' => 'Umów konsultację',
        ];
    }
}
