<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting rules for different endpoints
    |
    */

    'auth' => [
        'login' => [
            'max_attempts' => env('RATE_LIMIT_LOGIN', 5),
            'decay_minutes' => env('RATE_LIMIT_LOGIN_DECAY', 15),
        ],
        'register' => [
            'max_attempts' => env('RATE_LIMIT_REGISTER', 3),
            'decay_minutes' => env('RATE_LIMIT_REGISTER_DECAY', 60),
        ],
    ],

    'api' => [
        'general' => [
            'max_attempts' => env('RATE_LIMIT_API', 60),
            'decay_minutes' => env('RATE_LIMIT_API_DECAY', 1),
        ],
        'export' => [
            'max_attempts' => env('RATE_LIMIT_EXPORT', 10),
            'decay_minutes' => env('RATE_LIMIT_EXPORT_DECAY', 60),
        ],
        'analytics' => [
            'max_attempts' => env('RATE_LIMIT_ANALYTICS', 30),
            'decay_minutes' => env('RATE_LIMIT_ANALYTICS_DECAY', 1),
        ],
    ],

    'enabled' => env('RATE_LIMITING_ENABLED', true),
];
