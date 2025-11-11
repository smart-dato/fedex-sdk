<?php

// config for SmartDato/Fedex
return [
    /*
    |--------------------------------------------------------------------------
    | FedEx API Environment
    |--------------------------------------------------------------------------
    |
    | Set the environment for FedEx API. Can be 'sandbox' or 'production'.
    |
    */
    'environment' => env('FEDEX_ENVIRONMENT', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | FedEx API Base URLs
    |--------------------------------------------------------------------------
    |
    | The base URLs for sandbox and production environments.
    |
    */
    'base_url' => [
        'sandbox' => 'https://apis-sandbox.fedex.com',
        'production' => 'https://apis.fedex.com',
    ],

    /*
    |--------------------------------------------------------------------------
    | FedEx OAuth Credentials
    |--------------------------------------------------------------------------
    |
    | Your FedEx API credentials for OAuth authentication.
    |
    */
    'client_id' => env('FEDEX_CLIENT_ID'),
    'client_secret' => env('FEDEX_CLIENT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | FedEx Account Number
    |--------------------------------------------------------------------------
    |
    | Your FedEx account number.
    |
    */
    'account_number' => env('FEDEX_ACCOUNT_NUMBER'),

    /*
    |--------------------------------------------------------------------------
    | Default Label Response Options
    |--------------------------------------------------------------------------
    |
    | Default label response format. Can be 'URL_ONLY' or 'LABEL'.
    |
    */
    'label_response_options' => env('FEDEX_LABEL_RESPONSE_OPTIONS', 'URL_ONLY'),

    /*
    |--------------------------------------------------------------------------
    | Token Cache TTL
    |--------------------------------------------------------------------------
    |
    | How long (in seconds) to cache the OAuth token. FedEx tokens typically
    | expire in 3600 seconds (1 hour). We cache for slightly less to be safe.
    |
    */
    'token_cache_ttl' => env('FEDEX_TOKEN_CACHE_TTL', 3500),

    /*
    |--------------------------------------------------------------------------
    | Token Cache Key
    |--------------------------------------------------------------------------
    |
    | The cache key used to store the OAuth token.
    |
    */
    'token_cache_key' => env('FEDEX_TOKEN_CACHE_KEY', 'fedex_oauth_token'),
];
