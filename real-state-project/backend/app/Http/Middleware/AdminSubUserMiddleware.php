<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminSubUserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    /**
     * Middleware to handle requests for admin sub-users.
     *
     * This middleware is responsible for ensuring that the incoming request
     * is authorized and valid for admin sub-users. It can be used to apply
     * specific access control logic or restrictions for sub-users under an
     * admin account.
     *
     */
    {
        $role = Auth::user()->getRoleNames()->first();
        if($role == 'admin'){
            return Redirect(route('dashboard'));
        }
        return $next($request);
    }
}
