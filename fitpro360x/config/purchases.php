<?php

return [

    /*
    |--------------------------------------------------------------------------
    | App Store Shared Secret
    |--------------------------------------------------------------------------
    |
    | This value is used to authenticate your app store receipts.
    | You can find it in App Store Connect > My Apps > App > In-App Purchases.
    |
    */

    'appstore_secret' => env('APPSTORE_SHARED_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Google Play JSON Credentials File
    |--------------------------------------------------------------------------
    |
    | Path to the JSON credentials file used to access Google Play Developer API.
    |
    */

    'google_play' => [
        'credentials' => storage_path('app/google-play/credentials.json'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logger Channel
    |--------------------------------------------------------------------------
    |
    | This value determines the logging channel that should be used by the package.
    |
    */

    'logger_channel' => env('PURCHASES_LOGGER_CHANNEL', null),
];
