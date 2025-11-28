<?php

namespace App\Http\Middleware;

use App\Models\ExternalPropertyUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class PostmenAuthenticateApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    /**
     * Middleware to handle authentication for the Postmen API.
     * 
     * This middleware is responsible for ensuring that requests to the Postmen API
     * are properly authenticated. It can be used to validate API tokens or other
     * authentication mechanisms required by the Postmen service.
     * 
     */
    {
        if (!$request->getUser() || !$request->getPassword()) {
            return response('Authentication required', Response::HTTP_UNAUTHORIZED)
                ->header('WWW-Authenticate', 'Basic realm="My Realm"');
        } else {
            $checkApiUser = ExternalPropertyUser::with(['agencies'])->whereName($request->getUser())->first();
            if ($checkApiUser && Hash::check($request->getPassword(), $checkApiUser->password)) {
                $request->merge(['authenticated_user' => $checkApiUser]);
                if (!$request->isMethod('get')) {
                    $tempToken = $request->header('access-token');
                    if ($tempToken !== $checkApiUser->api_key) {
                        return response()->json(['message' => 'Invalid Api key'], 401);
                    }
                    return $next($request);
                }
                return $next($request);
            } else {
                return response()->json(['message' => 'Invalid login credentials'], 401);
            }
        }
    }
}
