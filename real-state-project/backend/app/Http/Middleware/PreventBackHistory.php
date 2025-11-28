<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventBackHistory
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    /**
     * Middleware to prevent users from navigating back to pages after logging out
     * or performing certain actions by disabling browser caching for specific routes.
     *
     * This middleware is typically used to enhance security and ensure that
     * sensitive data is not accessible after a session ends.
     *
     */
    {
        $response = $next($request);
        $response->headers->set('Cache-Control', 'nocache, no-store, max-age=0, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sun, 02 jan 1990 00.00.00 GMT');
        return $response;
    }
}
