<?php

return [
    'rate_limit' => [
        'per_minute' => (int) env(
            'CONTACT_RATE_LIMIT_PER_MINUTE',
            5,
        ),
    ],

    'mail' => [
        'enabled' => filter_var(
            env('CONTACT_MAIL_ENABLED', false),
            FILTER_VALIDATE_BOOL,
        ),
        'owner_address' => env(
            'CONTACT_OWNER_EMAIL',
        ),
    ],
];
