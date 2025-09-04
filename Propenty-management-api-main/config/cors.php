<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', '*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://house-6g6m.onrender.com',
        'https://house-1-3rtj.onrender.com',
        'https://afrin-houses.vercel.app',
        'http://localhost:3000',
        'http://localhost:5173',
        'http://localhost:5174',
        'http://localhost:5175',
        'http://localhost:5176',
        'http://localhost:5177',
        'http://127.0.0.1:3000',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:5174',
        'http://127.0.0.1:8000',
        'http://127.0.0.1:5175',
        'http://127.0.0.1:5176',
        'http://127.0.0.1:5177',
        // Production frontend URL
        'https://house-1-3rtj.onrender.com',
        // Backend URL (for internal requests)
        'https://house-6g6m.onrender.com',
    ],


    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [
        'Access-Control-Allow-Origin',
        'Access-Control-Allow-Methods',
        'Access-Control-Allow-Headers',
        'Access-Control-Allow-Credentials',
        'Authorization'
    ],

    'max_age' => 0,

    'supports_credentials' => true,

];
