<?php

namespace App\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Authenticate extends Middleware
{
    protected function redirectTo($request): ?string
    {
        if (! $request->expectsJson()) {
            // Detect path prefix or customize per guard
            if ($request->is('admin/*')) {
                return route('admin.login');
            }
        }

        return null;
    }

    public function handle($request, \Closure $next, ...$guards)
    {
        try {
            $this->authenticate($request, $guards);

            $user = Auth::user();
            $requestUser = $request->user();

            if ($user) {
                $currentToken = $requestUser->currentAccessToken();

                if ($requestUser->status == 0) {

                    $requestUser?->currentAccessToken()?->delete();
                    $requestUser?->tokens()?->delete();

                    return response()->json([
                        'success' => false,
                        'data' => null,
                        'message' => __('messages.deactivate_account'),
                    ], 403);
                }
                if ($currentToken && $user->last_token_id !== $currentToken->id) {
                    return response()->json([
                        'success' => false,
                        'data' => null,
                        'message' => 'You have been logged in on another device.',
                    ], 403);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'Your account has been deleted or not found. Please contact support for assistance.',
                ], 403);
            }

            return $next($request);
        } catch (AuthenticationException $e) {

            Log::error('AuthenticationException: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Your token has expired. Please log in again.',
            ], 403);
        }
    }
}
