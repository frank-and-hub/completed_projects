<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
       if (!app()->environment('production')) {
            // If not in production, allow both HTTP and HTTPS
            return $next($request);
        }

        // Force HTTP for the specified subdomain route
        if ($request->route()->named('subdomain.home')) {
            if ($request->secure()) {
                return redirect()->secure($request->getRequestUri(), 302, [], false);
            }
        }

        return $next($request);
    }
}
