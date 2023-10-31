<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Cookie;

class TestCookie
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next){
        // if ($request->header('socialmedia')!='undefined') {
        //     $token = $request->header('socialmedia'); 
        //     $request->headers->set('remember_token',$token, true);
        // }
        return $next($request);
    }
}
