<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthenticateApi
{
    public function handle(Request $request, Closure $next)
    {

        $token = $request->header('Authorization');
        if (!$token) {
            if ($request->expectsJson() || $request->is('api/*')) {
                throw new HttpException(401, 'Unauthorized.');
            } else {
                return redirect('/login');
            }
        }

        if (!$request->user() && $token) {
            $request->headers->set('Authorization', 'Bearer ' . $token);
            if ($user = auth()->user()) {
            } else {
                if ($request->expectsJson() || $request->is('api/*')) {
                    throw new HttpException(401, 'Unauthorized.');
                } else {
                    return redirect('/login');
                }
            }
        }

        if ($request->user()) {
            return $next($request);
        } else {
            if ($request->expectsJson() || $request->is('api/*')) {
                throw new HttpException(401, 'Unauthorized.');
            } else {
                return redirect('/login');
            }
        }
    }
}
