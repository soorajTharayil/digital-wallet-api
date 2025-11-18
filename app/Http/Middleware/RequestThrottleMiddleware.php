<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RequestThrottleMiddleware
{
    public function handle(Request $request, Closure $next, string $key = 'api'): Response
    {
        $identifier = sprintf('%s|%s', $key, $request->user()->id ?? $request->ip());
        $maxAttempts = config('services.rate_limits.per_minute', 60);

        if (RateLimiter::tooManyAttempts($identifier, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($identifier);

            return response()->json([
                'message' => 'Too many requests.',
                'retry_after_seconds' => $retryAfter,
            ], 429);
        }

        RateLimiter::hit($identifier);

        return tap($next($request), function () use ($identifier) {
            // Optional: comment out clear to implement sliding window; keeping clear ensures per-request fairness.
            RateLimiter::clear($identifier);
        });
    }
}
