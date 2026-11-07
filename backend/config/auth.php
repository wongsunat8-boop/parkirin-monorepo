<?php

use App\Models\Pengendara;
use App\Models\User;

return [

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    'guards' => [
        // Staff guard (super_admin, owner, admin, petugas) — session-based
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        // Pengendara (customer) guard — session-based
        'pengendara' => [
            'driver' => 'session',
            'provider' => 'pengendara',
        ],

        // Sanctum API guard for Staff
        'sanctum-user' => [
            'driver' => 'sanctum',
            'provider' => 'users',
        ],

        // Sanctum API guard for Pengendara
        'sanctum-pengendara' => [
            'driver' => 'sanctum',
            'provider' => 'pengendara',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => User::class,
        ],

        'pengendara' => [
            'driver' => 'eloquent',
            'model' => Pengendara::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
        'pengendara' => [
            'provider' => 'pengendara',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
