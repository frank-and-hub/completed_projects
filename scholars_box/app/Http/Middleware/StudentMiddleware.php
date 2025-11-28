<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class StudentMiddleware
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
        if (Auth::check() && Auth::user()->hasRole('student')) {
            $user = auth()->user();
            $student = $user->student()->first();
            if ($student) {
                return $next($request);
            }
        } else {
            Auth::logout();
            return redirect()->to('/student/login');
        }

        return redirect(url('/student/login'));
    }
}
