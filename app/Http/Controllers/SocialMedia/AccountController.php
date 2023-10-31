<?php

namespace App\Http\Controllers\SocialMedia;

use App\Http\Controllers\SocialMedia\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Model\UserModel;
use App\Mail\ContactMail;

class AccountController extends Controller
{
    //

    /*
     * 
     * Create a new AccountController instance.
     * 
     * @return void
     * */
    public function __construct(){
        $this->middleware('is_user.guard:user', ['except' => ['login','register','accountConfirmation','verification','passwordReset']]);
    }

    /*
     * 
     * login 
     * => need to register - return token
     * @return \Illuminate\Http\JsonResponse
     * */
    public function login(Request $request){ 
        try{
            $token = $request -> header('remember_token');
            if($token){
                $user = Auth::guard('user')->setToken($token)->user();
                if($user){
                    return $this->returnData('token',$token,'Success task.');
                }
            }

            $rules=[
                'email' => ['required', 'string', 'email','regex:/(.+)@(.+)\.(.+)/i', 'max:255'],
                'password' => ['required', 'string', 'min:8'],
            ];

            $validator = Validator::make($request->all(), $rules);
            if( $validator->fails()){
                return $this->returnValidationError($validator);
            }

            $data=$request->only(['email','password']);
            $token =Auth::guard('user')->attempt($data);
            $user=Auth::guard('user')->user();
            if(!$user){
                return $this->returnError("This account is not registered.");
            }
            $user->remember_token=$token;
            $user->save();
            $user->touch();
            return $this->returnData('token',$token,'Success task.');
            
        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }

    /*
     * 
     * create 
     * => create user & save in DB
     * @return void
     * */
    private function build/*build user*/($data){
        try{
            UserModel::create([
                'firstName' => $data->firstName,
                'lastName' => $data->lastName,
                'email' => $data->email,
                'password' => Hash::make($data->password),
            ]);
        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }

    /*
     * 
     * register 
     * return token
     * @return \Illuminate\Http\JsonResponse
     * */
    public function register(Request $request){
        try{
            $rules=[
                'firstName' => ['required', 'string', 'max:255'],
                'lastName' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string','email','regex:/(.+)@(.+)\.(.+)/i', 'max:255', 'unique:user_models'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ];

            $validator = Validator::make($request->all(), $rules);
            if( $validator->fails()){
                return $this->returnValidationError($validator);
            }
            try{
                $contact_data = [
                    'fullname' => $request->firstName." ".$request->lastName,
                    'email' => $request->email,
                    'subject' => "Confirmation Message",
                    'message' => "successfully registered",
                ];
                Mail::to($request->email)->send(new ContactMail($contact_data));
            }catch(\Swift_TransportException $e){
                    return $this->returnError($e->getMessage());
            }

            $this->build($request);
            $request['remember_me']=false;
            return $this->login($request);
        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }

    /*
     * 
     * accountconfirmation 
     * return code of 6 digits
     * @return \Illuminate\Http\JsonResponse
     * */
    public function accountConfirmation(Request $request){
        try{
            $rules=[
                'email' => ['required', 'string', 'email','regex:/(.+)@(.+)\.(.+)/i', 'max:255'],
            ];

            $validator = Validator::make($request->all(), $rules);
            if( $validator->fails()){
                return $this->returnValidationError($validator);
            }

            $user=UserModel::where('email','=',$request->email)->first();
            if(!$user){
                return $this->returnError("Wrong email.");            
            }
            $code=((((((((((rand(1,9)*10)+rand(0,9))*10)+rand(0,9))*10)+rand(0,9))*10)+rand(0,9))*10)+rand(0,9));
            try{
                $contact_data = [
                    'fullname' => $user['firstName']." ".$user['lastName'],
                    'email' => $request->email,
                    'subject' => "Verification Message",
                    'message' => $code,
                ];
                Mail::to($request->email)->send(new ContactMail($contact_data));
            }catch(\Swift_TransportException $e){
                    return $this->returnError($e->getMessage());
            }

            return $this->returnData('code',$code,'Success task.');
        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }

    /*
     * 
     * verification 
     * 
     * @return void
     * */    
    public function verification(Request $request){
        try{
            $rules=[
                'code' => ['required', 'numeric', 'digits:6'],
                'correctcode' => ['required', 'numeric', 'digits:6', 'min:10000'],
            ];
            
            $validator = Validator::make($request->all(), $rules);
            if( $validator->fails()){
                return $this->returnValidationError($validator);
            }

            if($request->correctcode!=$request->code){
                return $this->returnError("Wrong code.");            
            }
            
            return $this->returnSuccess('Please press next.');
        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }
    /*
     * 
     * passwordReset 
     * 
     * @return void
     * */    
    public function passwordReset(Request $request){
        try{
            $rules=[
                'email' => ['required', 'string', 'email','regex:/(.+)@(.+)\.(.+)/i', 'max:255'],
                'code' => ['required', 'numeric', 'digits:6'],
                'correctcode' => ['required', 'numeric', 'digits:6', 'min:10000'],
                'password' => ['required','string', 'min:8', 'confirmed'],
            ];

            $validator = Validator::make($request->all(), $rules);
            if( $validator->fails()){
                return $this->returnValidationError($validator);
            }
            $user=UserModel::where('email','=',$request->email)->first();
            if(!$user){
                return $this->returnError("Wrong email.");            
            }
            if($request->correctcode!=$request->code){
                return $this->returnError("Wrong code.");            
            }
            $data['password'] = Hash::make($request->password);
            $user->update($data);
            return $this->returnSuccess('Success task.');
        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }    

    /*
     * 
     * me
     * => return data me by token
     * @return \Illuminate\Http\JsonResponse
     * */
    public function me(Request $request){
        try{
            $token = $request -> header('remember_token');
            $user = Auth::guard('user')->setToken($token)->user();
            $user->touch();
            return $this->returnData('user',$user,'Success task.');
        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }

    /*
     * 
     * meCertificate
     * => return Certificate me by token
     * @return \Illuminate\Http\JsonResponse
     * */    
    public function myCertificate(Request $request){
        try{
            $token = $request -> header('remember_token');
            $user = Auth::guard('user')->setToken($token)->user();
            $user->touch();
            $mycertificate = array();
            $certificates = $user->havecertificatemodels;
            foreach($certificates as $certificate){
                $certificatemodel = $certificate->certificatemodel;
                array_push($mycertificate,$certificatemodel);
            }
            return $this->returnData('certificate',$mycertificate,'Success task.');
        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }

    /*
     * 
     * myRegion
     * => return region me by token
     * @return \Illuminate\Http\JsonResponse
     * */    
    public function myRegion(Request $request){
        try{
            $token = $request -> header('remember_token');
            $user = Auth::guard('user')->setToken($token)->user();
            $user->touch();
            $myregion = $user->regionmodel;
            return $this->returnData('region',$myregion,'Success task.');
        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }
    
    /*
     * 
     * logout 
     * 
     * @return void
     * */
    public function logout(Request $request){
        try{
            return $this->returnSuccess('Success task.');
        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }

}
