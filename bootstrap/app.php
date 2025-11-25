<?php

use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        RouteServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'web.auth' => \App\Http\Middleware\WebAuthMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Helper function to check if request is for API
        $isApiRequest = function (Request $request): bool {
            // Check multiple ways to detect API requests
            $uri = $request->getRequestUri();
            $path = $request->path();
            $segment = $request->segment(1);
            
            return str_starts_with($uri, '/api/') 
                || str_starts_with($path, 'api/') 
                || $segment === 'api'
                || $request->expectsJson()
                || $request->header('Accept') === 'application/json';
        };

        // Force JSON responses for all API routes - this must be first
        $exceptions->shouldRenderJsonWhen(function (Request $request, \Throwable $e) use ($isApiRequest) {
            return $isApiRequest($request);
        });

        // Handle validation exceptions for API routes
        $exceptions->renderable(function (ValidationException $e, Request $request) use ($isApiRequest) {
            if ($isApiRequest($request)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The given data was invalid.',
                    'errors' => $e->errors(),
                    'code' => $e->status,
                ], $e->status);
            }
        });

        // Handle 404 errors for API routes
        $exceptions->renderable(function (NotFoundHttpException $e, Request $request) use ($isApiRequest) {
            if ($isApiRequest($request)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Resource not found.',
                    'code' => 404,
                ], 404);
            }
        });

        // Catch-all for any HTTP exceptions on API routes
        $exceptions->renderable(function (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e, Request $request) use ($isApiRequest) {
            if ($isApiRequest($request)) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage() ?: 'An error occurred.',
                    'code' => $e->getStatusCode(),
                ], $e->getStatusCode());
            }
        });
    })
    ->create();

