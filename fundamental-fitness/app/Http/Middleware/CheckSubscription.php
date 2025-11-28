<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if($request->user() && $request->user()->is_subscribe != 1){
            return response()->json([
                'status' => false,
                'message' => 'You need to subscribe to access this feature.',
                'data' => (object)[]
            ], 403);
        }
        return $next($request);
    }
}
