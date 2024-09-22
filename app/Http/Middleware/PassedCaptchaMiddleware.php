<?php

namespace App\Http\Middleware;

use Closure;

class PassedCaptchaMiddleware
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
        // If user is not admin
        if(session()->has('passed_captcha') || strpos(\Route::currentRouteName(), 'global.captcha') !== false){
            return $next($request);
        }

        return redirect() -> route('global.captcha');
    }
}
