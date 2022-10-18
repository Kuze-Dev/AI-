<?php

use Domain\Auth\Pipes\Login\AttemptToAuthenticate;
use Domain\Auth\Pipes\Login\CheckForTwoFactorAuthentication;
use Domain\Auth\Pipes\Login\EnsureLoginIsNotThrottled;

return [

    'login' => [
        'pipeline' => array_filter([
            EnsureLoginIsNotThrottled::class,
            CheckForTwoFactorAuthentication::class,
            AttemptToAuthenticate::class,
        ]),

        'throttle' => [
            'max_attempts' => env('AUTH_LOGIN_THROTTLE_MAX_ATTEMPTS', 5),
            'decay' => env('AUTH_LOGIN_THROTTLE_DECAY', 60),
        ],
    ],

    'two_factor' => [
        'totp' => [
            'window' => 60,
        ],

        'recovery_codes' => [
            'count' => 10,
        ],

        'safe_devices' => [
            'cookie' => '2fa_remember',
            'max_devices' => 3,
            'expiration_days' => 14,
        ],
    ],

];
