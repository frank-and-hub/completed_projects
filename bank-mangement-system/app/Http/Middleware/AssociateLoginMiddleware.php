<?php

namespace App\Http\Middleware;

use Closure;

class AssociateLoginMiddleware
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

        if($request->has(['associate_no','token']))
       {
            $requestedData = $request->only('associate_no','token');
            $associateNo = $requestedData['associate_no'];
            $token = $requestedData['token'];
            if(md5($associateNo) === $token)
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
