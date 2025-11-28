<?php

namespace App\Http\Middleware;

use Closure;

class MobileRedirectMiddleware
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
        $userAgent = $request->headers->get('User-Agent');
        
        if (preg_match('/(android|iphone|ipad|ipod|blackberry|mobile)/i', $userAgent) || !isset($_SERVER['HTTP_SEC_CH_UA_MOBILE']) ) {

            return redirect()->route('mobile.redirect');
        }
        return $next($request);
    }
}
