<?php

namespace App\Http\Middleware;


use Closure;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check() && in_array(Auth::user()->role_id, [1,3,4])) {
            return $next($request);
        } else {
            Auth::logout();
            return redirect()->to('/admin/login');
        }

        return redirect(url('/admin/login'));
    }
}
