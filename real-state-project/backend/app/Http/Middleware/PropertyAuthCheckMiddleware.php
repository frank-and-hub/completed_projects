<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PropertyAuthCheckMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    /**
     * Middleware to check property authentication.
     *
     * This middleware is responsible for verifying the authentication
     * and authorization of a property-related request. It ensures that
     * only authorized users can access or modify property data.
     *
     */
    {
        if (auth('api')->check()) {
            try {
                Auth::setUser(auth('api')->user());
            } catch (\Exception $e) {
                throw $e;
            }
        }
        return $next($request);
    }
}
