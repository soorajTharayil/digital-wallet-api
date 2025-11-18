<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        // Force Accept header to application/json so Laravel knows to return JSON
        $request->headers->set('Accept', 'application/json');
        
        // Ensure the request knows it expects JSON
        if (!$request->expectsJson()) {
            $request->headers->set('Accept', 'application/json');
        }

        $response = $next($request);

        // Always set Content-Type to JSON for API responses
        if (method_exists($response, 'header')) {
            $response->headers->set('Content-Type', 'application/json');
        }

        return $response;
    }
}
