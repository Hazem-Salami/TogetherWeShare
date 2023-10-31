<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Traits\GeneralTrait;
use App\Model\FrindModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TestFrind
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
                'id_frind' => ['required','exists:frind_models,id'],
            ];
            
            $validator = Validator::make($request->all(), $rules);
            if( $validator->fails()){
                return $this->returnValidationError($validator);
            }

            $frind = FrindModel::find($request -> id_frind);
            $token = $request -> header('remember_token');
            $user = Auth::guard('user')->setToken($token)->user();
            
            if($frind->usermodel_id == $user->id){
                return $this->returnError('Not found.');
            }
            if($frind->usermodelx_id != $user->id){
                return $this->returnError('Not found.');
            }

            return $next($request);

        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }
}
