<?php

return [


    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => ['api/*', 'products', 'products/*', 'categories', 'categories/*', 'brands', 'brands/*', 'storage/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'], // Allow GET, POST, PUT, DELETE, etc.

    'allowed_origins' => [
        'https://chitwancomputer.com.np',
        'https://www.chitwancomputer.com.np',
        // Dev storefront URLs come from CORS_DEV_ORIGINS (comma-separated) in your local .env.
        // Leave it unset on the production server so only the live site can call the API.
        ...array_values(array_filter(array_map('trim', explode(',', (string) env('CORS_DEV_ORIGINS', ''))))),
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'], // Allow all headers

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
