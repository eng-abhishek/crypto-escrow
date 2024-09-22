<?php

namespace App\Http\Middleware;
use Closure;

class VerifyCaptcha
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
        // echo "RAM";
        // echo session()->get('chk_captcha');
        // die;
        // die;
        if(session()->get('chk_captcha') == 404 || session()->get('chk_captcha') == ''){
         return redirect()->route('global.captcha');
        }
        return $next($request);
    }
}
