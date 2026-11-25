<?php

declare(strict_types=1);

use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Laravel\Sanctum\Http\Middleware\AuthenticateSession;

/*
 * The customer app's API tokens (L2). Only tokens open the API: no first-party SPA signs in with the
 * session cookie, so no domain is stateful and the `web` guard (the back office's staff) is never
 * consulted.
 */
return [
    'stateful' => [],

    'guard' => [],

    // Minutes a token lives; a customer signs in again with a new code after sixty days.
    'expiration' => (int) env('SANCTUM_EXPIRATION', 60 * 24 * 60),

    // A prefix lets secret scanners recognise a leaked token.
    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', 'loyalty_'),

    'middleware' => [
        'authenticate_session' => AuthenticateSession::class,
        'encrypt_cookies' => EncryptCookies::class,
        'validate_csrf_token' => ValidateCsrfToken::class,
    ],
];
