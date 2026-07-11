<?php

use App\Models\Website\Bank;
use App\Models\Website\City;
use App\Models\Website\Post;
use App\Models\Website\Contact;
use App\Models\Website\Sentence;

return [
    // Wrzucasz tu swoje URL-e
    'urls' => [
        '/',
        '/analiza',
        '/wyroki',
        '/zawieszenie-rat',
        '/kredyty-frankowe',
        '/kredyty-euro',
        '/oferta',
        '/kancelaria',
        '/kancelaria/glogow',
        '/kancelaria/zielona-gora',
        '/kancelaria/legnica',
        '/kancelaria/leszno',
        '/kancelaria/wroclaw',
        '/kontakt',
        '/blog',
        '/orzecznictwo',
        '/faq',
        '/klauzule-niedozwolone',
        '/opinie',
        '/gdzie-dzialamy',
        '/polityka-prywatnosci',
        '/mapa-strony'
    ],
    'sets' => [
        'miasta_chf' => [
            'published_only' => true,
            'model' => City::class,
            'prefix' => 'kredyty-frankowe-'
        ],
        'miasta_eur' => [
            'published_only' => true,
            'model' => City::class,
            'prefix' => 'kredyt-euro-kancelaria-'
        ],
        'banki' => [
            'published_only' => true,
            'model' => Bank::class,
            'prefix' => 'bank/'
        ],
        'blog_artykuly' => [
            'published_only' => true,
            'category' => 'blog',
            'model' => Post::class,
            'prefix' => 'blog/'
        ],
        'blog_orzecznictwo' => [
            'published_only' => true,
            'category' => 'orzecznictwo',
            'model' => Post::class,
            'prefix' => 'orzecznictwo/'
        ],
        'wyrok' => [
            'published_only' => true,
            'model' => Sentence::class,
            'prefix' => 'wyrok/'
        ],
        'sądy' => [
            'published_only' => false,
            'category' => 'Sąd',
            'model' => Contact::class,
            'prefix' => 'wyroki/sad/'
        ],
        'sedziowie' => [
            'published_only' => false,
            'category' => 'Sędzia',
            'model' => Contact::class,
            'prefix' => 'wyroki/sedzia/'
        ],
        'wyroki_banku' => [
            'published_only' => true,
            'model' => bank::class,
            'prefix' => 'wyroki/bank/'
        ]
    ]
];


// MIASTA
// KLAUZULE NIEDOZWOLONE - BANKI
// BLOG
// ORZECZNICTWO
// WYROKI
// WYROKI - SEDZIOWIE
// WYROKI - SADY
// WYROKI - BANK