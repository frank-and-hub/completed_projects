<?php

namespace App\Http\Middleware;
use App\Models\IpAddresses;
use Closure;
use App\Models\User;

use Auth;
class CheckStatus
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
        if(Auth::user()->phone_verify == 1 && Auth::user()->email_verify == 1 && Auth::user()->status == 0)
        {
	        /*if ( IpAddresses::where(['user_id' => Auth::user()->id, 'ip_address' => user_ip() ])->count() == 0 ) {
                Auth::guard()->logout();
                session()->forget('fakey');
                session()->flash('alert', 'Oops! Your ip address invalid!');
                return back()->withErrors('message', 'Oops! You have entered invalid credentials');
            }*/
            return $next($request);
        }else{
	        Auth::guard()->logout();
	        session()->forget('fakey');
	        session()->flash('alert', 'Oops! Branch is Deactivate, Please contact to Admin!');
            return redirect()->route('login');
        }

    }
}
