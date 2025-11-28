<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @param  string|null  $route
     * @param  string[]  $guards
     * @return Response
     */
    public function handle(Request $request, Closure $next, ?string $route = null, ...$guards): Response
    {
        // Check if the user is authenticated
        if (Auth::check()) {
            // Redirect students to their dashboard
            if (Auth::user()) {
                $user = auth()->user();
                $student = $user->student()->first();
                if ($student) {
                    return redirect()->route('Student.dashboard');
                }
            }
            // Redirect admin to their dashboard
            elseif (Auth::user()->hasRole('admin')) {
                dd('fdgfd');
            }
            // Logout and redirect users with unknown roles
            else {
                Auth::logout();
                return redirect()->to('/');
            }
        }

        // Continue to the next middleware if the user is not authenticated
        return $next($request);
    }
}
