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

        // If request has a body but Content-Type is not set or not JSON, check if it's JSON and set it
        $contentType = $request->header('Content-Type');
        $hasJsonContentType = $contentType && str_contains($contentType, 'application/json');
        
        if (!$hasJsonContentType && $request->getContent()) {
            $content = $request->getContent();
            // Check if content is valid JSON
            $decoded = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                // Set Content-Type header so Laravel knows to parse as JSON
                $request->headers->set('Content-Type', 'application/json');
                
                // Merge JSON data into request if it's not already there
                if ($request->request->count() === 0 && !empty($decoded)) {
                    $request->merge($decoded);
                }
            }
        }

        $response = $next($request);

        // Always set Content-Type to JSON for API responses
        if (method_exists($response, 'header')) {
            $response->headers->set('Content-Type', 'application/json');
        }

        return $response;
    }
}
