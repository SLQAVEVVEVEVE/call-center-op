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

    'paths' => ['api/*', 'broadcasting/auth'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:5173',   // Vite dev server
        'http://localhost',        // Capacitor Android WebView
        'https://localhost',       // Capacitor Android WebView (HTTPS)
        'capacitor://localhost',   // Capacitor iOS (задел)
    ],

    'allowed_origins_patterns' => ['#^http://localhost(:\d+)?$#', '#^http://172\.20\.10\.3(:\d+)?$#'],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Bearer-токен передаётся в заголовке Authorization — cookie не нужны.
    // ['*'] + true — запрещено стандартом CORS, браузер отклонит preflight.
    'supports_credentials' => false,

];
