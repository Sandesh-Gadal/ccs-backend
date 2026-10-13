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
        'https://api.chitwancomputer.com.np'   
         ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'], // Allow all headers

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
