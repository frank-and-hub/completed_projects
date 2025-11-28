<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    /**
     * Middleware to handle authentication and authorization for admin users.
     * Ensures that only authenticated admin users can access certain routes or resources.
     *
     */
    {
        $role = Auth::user()->getRoleNames()->first();
        if($role != 'admin'){
            return Redirect(route('adminSubUser.dashboard'));
        }

        return $next($request);
    }
}
