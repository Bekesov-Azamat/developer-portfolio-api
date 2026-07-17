<?php

return [
    'enabled' => filter_var(
        env('AI_ENABLED', false),
        FILTER_VALIDATE_BOOL,
    ),

    'provider' => env('AI_PROVIDER', 'groq'),

    'providers' => [
        'groq' => [
            'base_url' => rtrim(
                (string) env(
                    'AI_BASE_URL',
                    'https://api.groq.com/openai/v1',
                ),
                '/',
            ),
            'api_key' => env('AI_API_KEY'),
            'model' => env(
                'AI_MODEL',
                'openai/gpt-oss-120b',
            ),
            'connect_timeout' => (float) env(
                'AI_CONNECT_TIMEOUT',
                2,
            ),
            'timeout' => (float) env(
                'AI_TIMEOUT',
                8,
            ),
            'max_output_tokens' => (int) env(
                'AI_MAX_OUTPUT_TOKENS',
                250,
            ),
            'temperature' => (float) env(
                'AI_TEMPERATURE',
                0.2,
            ),
            'reasoning_effort' => env(
                'AI_REASONING_EFFORT',
                'low',
            ),
        ],
    ],

    'fallback_response' => implode(' ', [
        'Спасибо за обращение!',
        'Ваше сообщение успешно получено.',
        'Я ознакомлюсь с ним и свяжусь с вами',
        'по указанным контактным данным.',
    ]),
];
