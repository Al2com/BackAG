<?php
return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' =>[
    'http://localhost:5173',
    'https://agrogestion.pages.dev', // tu dominio real de Cloudflare Pages
    ],//Cors
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,

];
