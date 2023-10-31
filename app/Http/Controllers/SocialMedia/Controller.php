<?php

namespace App\Http\Controllers\SocialMedia;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Http\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController{
    
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, GeneralTrait;    

    /*
     * 
     * test frinds
     * => is frind [Frind] - is not frind [PendingRequest or NotFrind]
     * @return string
     * */
    public function isFrind(Request $request){
        try{
            $token = $request -> header('remember_token');
            $user = Auth::guard('user')->setToken($token)->user();
            
            $rules=[
                'id_user'=> ['required','exists:user_models,id'],
            ];
            
            $validator = Validator::make($request->all(), $rules);
            if( $validator->fails()){
                if($request->console){
                    return NULL;
                }
                return $this->returnValidationError($validator);
            }
            if($user->id == $request->id_user){
                if($request->console){
                    return NULL;
                }
                return $this->returnError('Not found.');
            }

            $frinds = $user->frinds;
            $frind = $frinds->where('usermodelx_id','=',$request->id_user)->first();
            if($frind){
                if($frind->order=='Accept'){
                    if($request->console){
                        return 'Frind';
                    }
                    return $this->returnData('relationship','Frind','Success task.');
                }else{
                    if($request->console){
                        return 'PendingRequest';
                    }
                    return $this->returnData('relationship','PendingRequest','Success task.');
                }
            }

            $frinds = $user->frindmodels;
            $frind = $frinds->where('usermodel_id','=',$request->id_user)->first();
            if($frind){
                if($frind->order=='Accept'){
                    if($request->console){
                        return 'Frind';
                    }
                    return $this->returnData('relationship','Frind','Success task.');
                }else{
                    if($request->console){
                        return 'PendingRequest';
                    }
                    return $this->returnData('relationship','PendingRequest','Success task.');
                }
            }
            if($request->console){
                return 'NotFrind';
            }
            return $this->returnData('relationship','NotFrind','Success task.');
        }catch(\Exception $e){
            if($request->console){
                return NULL;
            }
            return $this->returnError($e->getMessage());
        }
    }

    /*
     * 
     * getFrinds
     * => return frinds related to a user - need token
     * @return \Illuminate\Http\JsonResponse
     * */
    public function getFrinds(Request $request){
        try{
            $token = $request -> header('remember_token');
            $user = Auth::guard('user')->setToken($token)->user();
            
            $frinds_accept=array();
            $frinds = $user->frinds;
            $frinds_accept_one = $frinds->where('order','=','Accept');

            foreach($frinds_accept_one as $frind_accept_one){
                $user_one = $frind_accept_one->usermodel;
                array_push($frinds_accept,$user_one);
            }

            $frinds = $user->frindmodels;
            $frinds_accept_two = $frinds->where('order','=','Accept');

            foreach($frinds_accept_two as $frind_accept_two){
                $user_one = $frind_accept_two->user;
                array_push($frinds_accept,$user_one);
            }

            if($request->console){
                return $frinds_accept;
            }

            return $this->returnData('frinds',$frinds_accept,'Success task.');
        }catch(\Exception $e){
            if($request->console){
                return NULL;
            }
            return $this->returnError($e->getMessage());
        }
    }
}
