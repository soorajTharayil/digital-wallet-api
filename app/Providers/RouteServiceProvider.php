<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/home';

    public function boot(): void
    {
        // Configure rate limiting for API routes
        RateLimiter::for('api', function (Request $request) {
            $userKey = optional($request->user())->id ?: $request->ip();

            return Limit::perMinute((int) config('services.rate_limits.per_minute', 60))
                ->by($userKey)
                ->response(function () {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Too many requests.',
                        'code' => 429,
                    ], 429);
                });
        });

        // Routes are registered in bootstrap/app.php via withRouting(api: ...)
        // No need to register them here to avoid conflicts
    }
}
