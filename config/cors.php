<?php

$allowedOrigins = array_values(array_filter(
    array_map(
        'trim',
        explode(
            ',',
            (string) env(
                'CORS_ALLOWED_ORIGINS',
                'http://localhost:5173,http://127.0.0.1:5173',
            ),
        ),
    ),
    static fn (string $origin): bool => $origin !== '',
));

return [
    'paths' => [
        'api',
        'api/*',
    ],

    'allowed_methods' => [
        'GET',
        'POST',
        'OPTIONS',
    ],

    'allowed_origins' => $allowedOrigins,

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Accept',
        'Content-Type',
        'Origin',
        'X-Requested-With',
        'X-Request-ID',
    ],

    'exposed_headers' => [
        'X-Request-ID',
        'Retry-After',
        'X-RateLimit-Limit',
        'X-RateLimit-Remaining',
    ],

    'max_age' => 600,

    'supports_credentials' => false,
];
