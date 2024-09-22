<?php

namespace App\Http\Middleware;
use Closure;
use Carbon\Carbon;
use Auth;

class ExtendSession
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
    
        if($request->session){
         session()->put('session',strtotime(Carbon::now()->addMinute($request->session)));
        }

        if(!is_null(session()->get('session'))){
        
        if(strtotime(Carbon::now()) > session()->get('session')){
        auth()->logout();
        session()->flush();
        return redirect()->route('home');
         }
        }

        return $next($request);
    }
}
