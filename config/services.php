<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'google_business_profile' => [
        'client_id' => env('GOOGLE_BUSINESS_PROFILE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_BUSINESS_PROFILE_CLIENT_SECRET'),
        'redirect_uri' => env('GOOGLE_BUSINESS_PROFILE_REDIRECT_URI'),
        'reviews_url' => env('GOOGLE_REVIEWS_URL'),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'sentence_generator_ai_enabled' => filter_var(env('OPENAI_SENTENCE_GENERATOR_AI_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
        'sentence_generator_model' => env('OPENAI_SENTENCE_GENERATOR_MODEL', 'gpt-5-mini'),
        'sentence_generator_timeout' => env('OPENAI_SENTENCE_GENERATOR_TIMEOUT', 60),
        'sentence_generator_creative' => filter_var(env('OPENAI_SENTENCE_GENERATOR_CREATIVE', false), FILTER_VALIDATE_BOOLEAN),
    ],

];
