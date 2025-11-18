<?php

return [

    'wallet' => [
        'default_currency' => env('DEFAULT_CURRENCY', 'USD'),
        'daily_debit_limit' => env('DEFAULT_DAILY_DEBIT_LIMIT', 10000),
    ],

    'fraud' => [
        'high_value_threshold' => env('FRAUD_HIGH_VALUE_THRESHOLD', 5000),
        'time_window_minutes' => env('FRAUD_TIME_WINDOW_MINUTES', 10),
        'max_transactions' => env('FRAUD_MAX_TRANSACTIONS', 3),
    ],

    'rate_limits' => [
        'per_minute' => env('API_RATE_LIMIT_PER_MINUTE', 60),
        'per_hour' => env('API_RATE_LIMIT_PER_HOUR', 1000),
    ],

];
