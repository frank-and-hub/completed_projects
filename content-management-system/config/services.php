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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'MAP_BOX_ACCESS_TOKEN' => env('MAP_BOX_ACCESS_TOKEN'),

    'send_email' => env('SEND_MAIL'),

    'place_api_key' => env('GOOGLE_PLACE_API_KEY'),

    'open_ai' => [
        'key' => env('OPENAI_API_KEY'),
    ],

    'revalidate' => [
        'key' => env('REVALIDATE_SECRET_TOKEN', '1b4db7eb-4057-5ddf-91e0-36dec72071f5'),
        'url' => env('REVALIDATE_URL','https://parkscape-nextjs.vercel.app'),
    ],

];
