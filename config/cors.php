<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'http://localhost:5174',
        'http://127.0.0.1:5174',
        'http://localhost:5175',
        'http://127.0.0.1:5175',
        'http://localhost:5176',
        'http://127.0.0.1:5176',
        'http://localhost:3000',
        'http://127.0.0.1:3000',
        'https://clinic-frontend.vercel.app',
    ],

    // Allow any local Vite dev port pattern (5173-5180 range)
    'allowed_origins_patterns' => [
      '^http://localhost:517[0-9]$',
      '^http://127\.0\.0\.1:517[0-9]$',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
