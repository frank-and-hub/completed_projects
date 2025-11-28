<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckDeviceSession
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user) {
            $currentDeviceId = $request->device_id;

            // If device ID doesn't match, user is logged in from another device
            if ($user->device_id && $user->device_id !== $currentDeviceId) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have been logged in on another device.',
                ], 401);
            }
        }

        return $next($request);
    }
}
