<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class Authenticate extends Middleware
{
    protected function redirectTo($request): ?string
    {
        if (! $request->expectsJson()) {
            // Detect path prefix or customize per guard
            if ($request->is('admin/*')) {
                return route('admin.login');
            }

            // return route('login'); // fallback for web users
        }

        return null;
    }

    public function handle($request, \Closure $next, ...$guards)
    {
        $this->authenticate($request, $guards);

        $user = Auth::user();

        if ($user) {
            $currentToken = $request->user()->currentAccessToken();

            if ($currentToken && $user->last_token_id !== $currentToken->id) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'You have been logged in on another device.',
                ], 403);
            }
        }

        return $next($request);
    }
}
