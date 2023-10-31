<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Traits\GeneralTrait;
use App\Model\PostModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TestPost
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
                'id_post' => ['required','exists:post_models,id'],
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
