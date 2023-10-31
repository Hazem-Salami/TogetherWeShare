<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Traits\GeneralTrait;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use JWTAuth;

class TestUser extends BaseMiddleware
{
    use GeneralTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null){
        try{
            if($guard != null){
                auth()->shouldUse($guard); //shoud you user guard / table
                $token = $request->header('remember_token');
                $request->headers->set('remember_token', (string) $token, true);
                $request->headers->set('Authorization', 'Bearer '.$token, true);
                try {
                    // check authenticted user
                    $user = JWTAuth::parseToken()->authenticate();
                } catch (TokenExpiredException $e) {
                    return  $this -> returnError($e->getMessage());
                } catch (JWTException $e) {
                    return  $this -> returnError($e->getMessage());
                }
            }
            return $next($request);
        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }
}
