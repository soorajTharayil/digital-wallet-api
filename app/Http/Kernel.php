<?php

namespace App\Http;

use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Middleware\RequestThrottleMiddleware;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Routing\Middleware\SubstituteBindings;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     */
    protected $middleware = [];

    /**
     * The application's route middleware groups.
     */
    protected $middlewareGroups = [
        'api' => [
            ForceJsonResponse::class,
            'throttle:api',
            SubstituteBindings::class,
        ],

        'web' => [
            SubstituteBindings::class,
        ],
    ];

    /**
     * The application's middleware aliases.
     */
    protected $middlewareAliases = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \App\Http\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'jwt.auth' => JwtMiddleware::class,
        'throttle.custom' => RequestThrottleMiddleware::class,
        'force.json' => ForceJsonResponse::class,
    ];
}
