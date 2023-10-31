<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Traits\GeneralTrait;
use Illuminate\Support\Facades\Validator;

class Numeric
{
    use GeneralTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next){
        try{
            $rules=[
                'num'=> ['required','integer','min:1'],
            ];
            
            $validator = Validator::make($request->all(), $rules);
            if( $validator->fails()){
                return $this->returnValidationError($validator);
            }

            return $next($request);

        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }
}
