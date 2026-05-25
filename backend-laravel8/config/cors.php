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

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => [
        '*',
        'chrome-extension://*',
        'moz-extension://*',
    ],

    'allowed_origins_patterns' => [
        '#^chrome-extension://.*$#',
        '#^moz-extension://.*$#',
        '#^https?://www\\.amazon\\.(com|eg|co\\.uk|de|fr|it|es|ae|sa|in|jp|ca|com\\.mx|com\\.br)$#',
        '#^https?://amazon\\.(com|eg|co\\.uk|de|fr|it|es|ae|sa|in|jp|ca|com\\.mx|com\\.br)$#',
    ],

    'allowed_headers' => [
        'Accept',
        'Authorization',
        'Content-Type',
        'Origin',
        'X-Requested-With',
    ],

    'exposed_headers' => [],

    'max_age' => 86400, // 24 hours

    'supports_credentials' => true,

];
