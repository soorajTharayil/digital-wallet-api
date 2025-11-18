<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Throwable;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (Throwable $exception) {
            return new JsonResponse([
                'message' => 'Unauthorized.',
                'error' => $exception->getMessage(),
            ], 401);
        }

        return $next($request);
    }
}
