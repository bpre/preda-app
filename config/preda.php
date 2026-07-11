<?php

$baseDomain = env('APP_DOMAIN', 'preda-app.test');

return [
    'scheme' => env('APP_SCHEME', 'http'),
    'local_port' => env('APP_PORT'),

    'domains' => [
        'public' => env('PUBLIC_DOMAIN', $baseDomain),
        'kancelaria' => env('KANCELARIA_DOMAIN', 'ewidencja.'.$baseDomain),
        'crm' => env('CRM_DOMAIN', 'crm.'.$baseDomain),
        'cms' => env('CMS_DOMAIN', 'cms.'.$baseDomain),
        'portal' => env('PORTAL_DOMAIN', 'portal.'.$baseDomain),
    ],
];
