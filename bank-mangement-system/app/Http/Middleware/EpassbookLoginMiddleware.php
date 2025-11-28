<?php

namespace App\Http\Middleware;

use Closure;

class EpassbookLoginMiddleware
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

       
        if($request->has(['token','member_id']))
       {
            $requestedData = $request->only('member_id','token');
            $memberId = $requestedData['member_id'];
            $token = $requestedData['token'];
            

            if(md5($memberId) === $token)
            {
                return $next($request);
            }
            else{
                return response()->json(['message' => 'Invalid token.'], 401);
            }
       }
      else{
            return response()->json(['message' => 'Please Enter Required Details.'], 401);
        }
       
    }
}
