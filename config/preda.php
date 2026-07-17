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

    'legacy_import' => [
        'kancelaria_source_database' => env('LEGACY_IMPORT_KANCELARIA_SOURCE_DATABASE', 'ewidencja'),
        'website_source_database' => env('LEGACY_IMPORT_WEBSITE_SOURCE_DATABASE', 'preda_app'),
    ],
];
