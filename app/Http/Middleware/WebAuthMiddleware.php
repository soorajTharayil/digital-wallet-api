<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class WebAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user has a JWT token in session
        if (!session()->has('jwt_token')) {
            return redirect()->route('login.show')->with('error', 'Please login to continue.');
        }

        // Set the token in the request header so JWTAuth can parse it
        $token = session('jwt_token');
        $request->headers->set('Authorization', 'Bearer ' . $token);
        
        try {
            // Authenticate using the token from session
            $user = JWTAuth::parseToken()->authenticate();
            
            if (!$user) {
                session()->forget('jwt_token');
                session()->forget('user');
                return redirect()->route('login.show')->with('error', 'Session expired. Please login again.');
            }
        } catch (Throwable $e) {
            session()->forget('jwt_token');
            session()->forget('user');
            return redirect()->route('login.show')->with('error', 'Invalid session. Please login again.');
        }

        return $next($request);
    }
}

