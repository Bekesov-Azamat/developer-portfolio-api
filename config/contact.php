<?php

return [
    'rate_limit' => [
        'per_minute' => (int) env('CONTACT_RATE_LIMIT_PER_MINUTE', 5),
    ],
];
