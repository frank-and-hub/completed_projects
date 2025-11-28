<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CheckTwoFactor
{

    public function handle(Request $request, Closure $next)
    {


        if (!Session::has('pass_two_factor')) {
            // $next($request)->header('Cache-Control', 'no-cache,no-store, max-age=0, must-revalidate')
            // ->header('Pragma', 'no-cache')
            // ->header('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');

            return to_route('login');
        }


        return $next($request);
    }
}
