<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
class PreventDuplicateTabs
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
        $pageIdentifier = 'unique_page_identifier';

        if (Session::get($pageIdentifier)) {
            // Page is already open in another tab
            return response()->json(['error' => 'Page is already open in another tab.']);
        }

        // Set the session variable to indicate that the page is open
        Session::put($pageIdentifier, true);
        
        return $next($request);
    }
}
