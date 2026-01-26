<?php

return [


    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => ['api/*', 'products', 'sanctum/csrf-cookie'], // Include your routes

    'allowed_methods' => ['*'], // Allow GET, POST, PUT, DELETE, etc.

    'allowed_origins' => ['http://localhost:5174','http://localhost:5173', 'http://192.168.1.64:5174','http://192.168.1.147:5174'], // Your React dev URL

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'], // Allow all headers

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
