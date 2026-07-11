<?php

namespace App\View\Components\Theme\Navigation;

class CreditsNavigation implements NavigationController
{
    public function items(): array
    {
        return [
            [
                'route' => 'kredyty-frankowe',
                'text' => 'Kredyty frankowe',
            ],
            [
                'route' => 'kredyty-euro',
                'text' => 'Kredyty EUR',
            ],
            [
                'route' => 'wyroki',
                'text' => 'Nasze wyroki',
            ],
            [
                'route' => '',
                'text' => 'Więcej',
                'more' => [
                    [
                        'route' => 'blog',
                        'text' => 'Blog',
                    ],
                    [
                        'route' => 'orzecznictwo',
                        'text' => 'Orzecznictwo',
                    ],
                    [
                        'route' => 'faq',
                        'text' => 'Częste pytania',
                    ],
                    [
                        'route' => 'klauzule-niedozwolone',
                        'text' => 'Klauzule niedozwolone',
                    ],
                    [
                        'route' => 'splacony-kredyt',
                        'text' => 'Spłacony kredyt frankowy',
                    ],
                ],
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
            'eyebrow' => 'Bezpłatna analiza',
            'route' => 'analiza',
            'text' => 'Sprawdź swój kredyt',
        ];
    }
}
