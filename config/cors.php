<?php
return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    // En producción solo se permite el dominio real de Cloudflare Pages.
    // localhost (Vite) se añade solo fuera de producción, para desarrollo.
    'allowed_origins' => array_values(array_filter([
        'https://agrogestion.pages.dev', // dominio real de Cloudflare Pages
        env('APP_ENV') !== 'production' ? 'http://localhost:5173' : null,
    ])),//Cors
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,

];
