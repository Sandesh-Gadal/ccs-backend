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
        'http://localhost:5175', 'http://localhost:5174', 'http://localhost:5173', 'http://192.168.1.64:5174', 'http://192.168.1.147:5174', // React dev URLs
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'], // Allow all headers

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
