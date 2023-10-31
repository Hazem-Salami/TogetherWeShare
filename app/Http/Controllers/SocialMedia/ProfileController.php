<?php

namespace App\Http\Controllers\SocialMedia;

use App\Http\Controllers\SocialMedia\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash; 
use Illuminate\Support\Facades\Mail;
use App\Notifications\NotificationsManagement;
use App\Model\UserModel;
use App\Model\PostModel;
use App\Model\HaveCertificateModel;
use App\Model\CertificateModel;
use App\Model\RegionModel;
use App\Mail\ContactMail;

class ProfileController extends Controller
{
    //

    /*
     * 
     * Create a new ProfileController instance.
     * 
     * @return void
     * */
    public function __construct(){
        $this->middleware('is_user.guard:user', ['except' => []]);
    }

    /*
     * 
     * getCertificates
     * => get certificates
     * @return \Illuminate\Http\JsonResponse
     * */
    public function getCertificate(){
        $certificates = CertificateModel::all();
        return $this->returnData('certificates',$certificates,'Success task.');
    }

    /*
     * 
     * getRegions
     * => get Regions
     * @return \Illuminate\Http\JsonResponse
     * */
    public function getRegion(){
        $Regions = RegionModel::all();
        return $this->returnData('Regions',$Regions,'Success task.');
    }

    /*
     * 
     * createcertificate
     * => create certificate and save in DB
     * @return void
     * */
    private function createCertificate($user, $datas){
        $certificates = CertificateModel::all();
        $certificatesuser = $user->havecertificatemodels;

        foreach($certificatesuser as $certificateuser){
            $certificateuser->delete();
        }
        foreach($datas as $data){
            $dataArr = array(); 
            $dataArr['usermodel_id'] = $user->id;
            $certificate = $certificates->where('name','=',$data)->first();
            $dataArr['certificatemodel_id']=$certificate->id;
            $havecertificatemodel=new HaveCertificateModel($dataArr);
            $havecertificatemodel->save();
        }
        
    }

    /*
     * 
     * getIdRegion
     * => get id region
     * @return void
     * */
    private function getIdRegion($data){
        $regions = RegionModel::all();
        $region = $regions->where('name','=',$data)->first();
        if($region){
            return $region->id;
        }
    }    

    /*
     * 
     * edit
     * => edit user data and save in DB - need token - format Data: YYYY-MM-DD
     * @return void
     * */    
    public function edit (Request $request){
        try{
            
            Validator::extend('date_multi_format', function($attribute, $value, $formats) {
                foreach($formats as $format) {
                    $parsed = date_parse_from_format($format, $value);
                    if ($parsed['error_count'] === 0 && $parsed['warning_count'] === 0) {
                        return true;
                    }
                }
                return false;
            });

            $token = $request -> header('remember_token');
            $user = Auth::guard('user')->setToken($token)->user();

            $rules=[
                'firstName' => ['string', 'max:255'],
                'lastName' => ['string', 'max:255'],
                'email' => ['string', 'email', 'max:255', Rule::unique('user_models')->ignore($user)],
                'currentPassword' => [Rule::requiredIf($request->password),'string', 'min:8'],
                'password' => ['different:currentPassword','string', 'min:8', 'confirmed'],           
                'city' => ['nullable','string', 'max:255','exists:region_models,name'],
                'work' => ['nullable','string', 'max:255'],
                'about' => ['nullable','string', 'max:255'],
                'pictureProfile' => ['nullable','file','mimes:jpeg,bmp,png,gif,svg,webp'],
                'pictureWall' => ['nullable','file','mimes:jpeg,bmp,png,gif,svg,webp'],
                'birth' => ['nullable','date','date_multi_format:"Y-n-j","Y-m-d"','before:now','after:1900-01-01'],
                'gender' => Rule::in(['Male', 'Female', 'Non']),
                'certificate' => ['nullable','array','exists:certificate_models,name'],
            ];

            $validator = Validator::make($request->all(), $rules);
            if( $validator->fails()){
                return $this->returnValidationError($validator);
            }

            if($request->currentPassword){
                if (!Hash::check($request->currentPassword, $user->password)) {
                    return $this->returnError('The password does not match.');
                }
            }
            if($request->checkEdit=="certificate"){
                $this->createCertificate($user, $request->certificate);
            }

            try{
                if($request->email){
                    if($request->email!=$user['email']){
                        $contact_data = [
                            'fullname' => $user['firstName']." ".$user['lastName'],
                            'email' => $request->email,
                            'subject' => "Confirmation Message",
                            'message' => "successfully registered",
                        ];
                        Mail::to($request->email)->send(new ContactMail($contact_data));
                    }
                }
            }catch(\Swift_TransportException $e){
                    return $this->returnError($e->getMessage());
            }
            if($request->hasFile('pictureProfile')||$request->hasFile('pictureWall')){
                $data=$request->only(['firstName','lastName','email',
                'work','birth','about','gender']);

                $regionmodel_id = $this->getIdRegion($request->city);
                if($regionmodel_id){
                    $data['regionmodel_id'] = $regionmodel_id;
                }

                if($request->hasFile('pictureProfile')){
                    $pictureProfile=$request->pictureProfile->store('Files/UserImages','public');
                    if($user->pictureProfile != 'Files/Virtual/profile.png'){
                        Storage::disk('public')->delete($user->pictureProfile);
                    }
                    $data['pictureProfile']=$pictureProfile;
                }
                if($request->hasFile('pictureWall')){
                    $pictureWall=$request->pictureWall->store('Files/UserImages','public');
                    if($user->pictureWall != 'Files/Virtual/Wall.jpg'){
                        Storage::disk('public')->delete($user->pictureWall);
                    }
                    $data['pictureWall']=$pictureWall;
                }
                if($request->password){
                    $data['password'] = Hash::make($request->password);
                }
                $user->update($data);
            }
            else{
                $data=$request->only(['firstName','lastName','email',
                'work','birth','about','gender']);
                $regionmodel_id = $this->getIdRegion($request->city);
                if($regionmodel_id){
                    $data['regionmodel_id'] = $regionmodel_id;
                }
                if($request->password){
                    $data['password'] = Hash::make($request->password);
                }
                $user->update($data);
            }

            $user->touch();
            return $this->returnSuccess('Success task.');
        }catch(Exception $e){
            return $this->returnError($e->getMessage());
        }
    }

    /*
     * 
     * deletepictureprofile
     * => delete picture profile from DB - need token
     * @return void
     * */    
    public function deletePictureProfile (Request $request){
        try{
            $token = $request -> header('remember_token');
            $user = Auth::guard('user')->setToken($token)->user();
            if($user->pictureProfile != 'Files/Virtual/profile.png'){
                Storage::disk('public')->delete($user->pictureProfile);
                $user->update([
                    'pictureProfile' => 'Files/Virtual/profile.png',
                ]);
            }
            $user->touch();
            return $this->returnSuccess('Success task.');
        }catch(Exception $e){
            return $this->returnError($e->getMessage());
        }
    }

    /*
     * 
     * deletepicturewall
     * => delete picture wall from DB - need token
     * @return void
     * */    
    public function deletePictureWall (Request $request){
        try{
            $token = $request -> header('remember_token');
            $user = Auth::guard('user')->setToken($token)->user();
            if($user->pictureWall != 'Files/Virtual/Wall.jpg'){
                Storage::disk('public')->delete($user->pictureWall);
                $user->update([
                    'pictureWall' => 'Files/Virtual/Wall.jpg',
                ]);
            }
            $user->touch();
            return $this->returnSuccess('Success task.');
        }catch(Exception $e){
            return $this->returnError($e->getMessage());
        }
    }

    /*
     * 
     * createpost
     * => create post and save in DB - need token
     * @return void
     * */    
    public function createPost (Request $request){
        try{
            $rules=[
                'contentText'=> ['nullable', 'string', 'max:255'],
                'contentFile'=> ['nullable', Rule::requiredIf($request->contentText==NULL),'file'],
            ];
            
            $validator = Validator::make($request->all(), $rules);
            if( $validator->fails()){
                return $this->returnValidationError($validator);
            }

            $token = $request -> header('remember_token');
            $user = Auth::guard('user')->setToken($token)->user();

            if($request->hasFile('contentFile')){
                $data=$request->only(['contentText', 'privacy', 'hide','usermodel_id']);
                $contentFile=$request->contentFile->store('Files/Posts','public');
                $data['contentFile']=$contentFile;
                $post=new PostModel($data);
                $user->postmodels()->save($post);
            }
            else{
                $post=new PostModel($request->all());
                $user->postmodels()->save($post);
            }

            $invoice=array();
            $name = $user->firstName.' '.$user->lastName;
            $invoice['user_name']=$name;
            $invoice['notification_type']='CreatePost';
            $invoice['pictureProfile']=$user->pictureProfile;
            $request['console']=true;
            $users = $this->getFrinds($request);
            foreach($users as $global_frind){
                    $global_frind->notify(new NotificationsManagement($invoice));
            }
            $user->touch();
            return $this->returnSuccess('Success task.');
        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }
    
    /*
     * 
     * editpost
     * => edit post and save in DB - need id_post
     * @return void
     * */    
    public function editPost (Request $request){
        try{
            
            $post = PostModel::find($request -> id_post);
            $token = $request -> header('remember_token');
            $user = Auth::guard('user')->setToken($token)->user();

            if($post->usermodel_id != $user->id){
                return $this->returnError('Not found.');
            }

            $rules=[
                'contentText' => ['nullable','string', 'max:255'],
                'contentFile'=> ['nullable', Rule::requiredIf($request->contentText==NULL && $post->contentFile==NULL),'file'],
            ];
            
            $validator = Validator::make($request->all(), $rules);
            if( $validator->fails()){
                return $this->returnValidationError($validator);
            }

            if($request->hasFile('contentFile')){
                $data=$request->only(['contentText', 'Privacy', 'hide','usermodel_id']);
                $contentFile=$request->contentFile->store('Files/Posts','public');
                if($post->contentFile != NUll){
                    Storage::disk('public')->delete($post->contentFile);
                }
                $data['contentFile']=$contentFile;
                $post->update($data);
            }
            else{
                $data=$request->all();
                $post->update($data);
            }
            $user->touch();
            return $this->returnSuccess('Success task.');       
        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }

    /*
     * 
     * deleteContentFile
     * => need id_post
     * @return void
     * */    
    public function deleteContentFile(Request $request){
        try{
            $post = PostModel::find($request -> id_post);
            $token = $request -> header('remember_token');
            $user = Auth::guard('user')->setToken($token)->user();

            if($post->usermodel_id != $user->id){
                return $this->returnError('Not found.');
            }
            if($post->contentFile != NULL){
                Storage::disk('public')->delete($post->contentFile);
                $data['contentFile']=NULL;
                $post->update($data);
            }
            $user->touch();
            return $this->returnSuccess('Success task.');       
        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }

    /*
     * 
     * deletepost
     * => delete post from DB - need id_post
     * @return void
     * */    
    public function deletePost (Request $request){
        try{  
            
            $post = PostModel::find($request -> id_post);
            $token = $request -> header('remember_token');
            $user = Auth::guard('user')->setToken($token)->user();

            if($post->usermodel_id != $user->id){
                return $this->returnError('Not found.');
            }
            
            $comments=$post->commentmodels;
            foreach( $comments as $comment){
                $Replies=$comment->commentmodels;
                foreach( $Replies as $Reply){
                    $Reply->delete();
                }
                $comment->delete();
            }
            
            $Shows=$post->myshowmodels;
            foreach( $Shows as $Show){
                $Show->delete();
            }

            $likes=$post->likemodels;
            foreach( $likes as $like){
                $like->delete();
            }
            
            if($post->contentFile != NUll){
                Storage::disk('public')->delete($post->contentFile);
            }
            $post->delete();
            $user->touch();
            return $this->returnSuccess('Success task.');
        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }

    /*
     * 
     * getposts
     * =>return Posts related to a user - need token
     * @return \Illuminate\Http\JsonResponse
     * */
    public function getPosts/*from user*/(Request $request){
        try{
            $rules=[
                'id_user'=> ['required','exists:user_models,id'],
            ];
            
            $validator = Validator::make($request->all(), $rules);
            if( $validator->fails()){
                return $this->returnValidationError($validator);
            }

            $user_second = UserModel::find($request->id_user);
            $token = $request -> header('remember_token');
            $user = Auth::guard('user')->setToken($token)->user();
            if($user_second==$user){
                $likes=$user->likemodels;
                $posts = $user->postmodels->sortByDesc('created_at')->take($request->num*10)->values();
                foreach($posts as $post){
                    $like=$likes->where('postmodel_id','=',$post->id)->first();
                    if($like){
                        $post['opinion']=$like->opinion;
                    }else{
                        $post['opinion']='None';
                    }
                    $post['likesCount']=(($post->likemodels)->where('opinion','=','Like'))->count();
                    $post['unlikesCount']=($post->likemodels)->where('opinion','=','Unlike')->count();
                }
                $user->touch();
                return $this->returnData('posts',$posts,'Success task.');
            }else{
                $likes=$user->likemodels;
                $request['console']=true;
                $test = $this->isFrind($request);
                $posts=$user_second->postmodels->sortByDesc('created_at')->values();
                if($test != 'Frind'){
                    $posts_general = $posts->where('hide','=',0)->where('privacy','=','General')->take($request->num*10)->values();
                    foreach($posts_general as $post){
                        $like=$likes->where('postmodel_id','=',$post->id)->first();
                        if($like){
                            $post['opinion']=$like->opinion;
                        }else{
                            $post['opinion']='None';
                        }
                        $post['likesCount']=(($post->likemodels)->where('opinion','=','Like'))->count();
                        $post['unlikesCount']=(($post->likemodels)->where('opinion','=','Unlike'))->count();
                    }
                    $user->touch();
                    return $this->returnData('posts',$posts_general,'Success task.');
                }
                $posts_know = $posts->where('hide','=',0)->take($request->num*10)->values();
                foreach($posts_know as $post){
                    $like=$likes->where('postmodel_id','=',$post->id)->first();
                    if($like){
                        $post['opinion']=$like->opinion;
                    }else{
                        $post['opinion']='None';
                    }
                    $post['likesCount']=(($post->likemodels)->where('opinion','=','Like'))->count();
                    $post['unlikesCount']=(($post->likemodels)->where('opinion','=','Unlike'))->count();
                }
                $user->touch();
                return $this->returnData('posts',$posts_know,'Success task.');
            }        

        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }

    /*
     * 
     * getNotifications
     * =>return notifications related to a user - need token
     * @return \Illuminate\Http\JsonResponse
     * */
    public function getNotifications(Request $request){
        try{
            $token = $request -> header('remember_token');
            $user = Auth::guard('user')->setToken($token)->user();
            $user->unreadNotifications()->update(['read_at' => now()]);

            $notifications = $user->notifications->sortBy('created_at')->reverse()->take($request->num*10)->values();
            $user->touch();

            return $this->returnData('notifications',$notifications,'Success task.');        
        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }

    /*
     * 
     * getOrderFrinds
     * => return orderfrinds related to a user - need token
     * @return \Illuminate\Http\JsonResponse
     * */
    public function getOrderFrinds(Request $request){
        try{
            $token = $request -> header('remember_token');
            $user = Auth::guard('user')->setToken($token)->user();

            $frinds = $user->frindmodels->sortBy('updated_at')->reverse();
            $frinds_order = $frinds->where('order','=','Non')->take($request->num*10);
            $user->touch();
            foreach($frinds_order as $frind_order){
                $user = UserModel::find($frind_order->usermodel_id);
                $frind_order['usermodel_id']=$user;
            }
            return $this->returnData('orderfrinds',$frinds_order,'Success task.');
        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }

    /*
     * 
     * getSentOrder
     * => return sentorder related to a user - need token
     * @return \Illuminate\Http\JsonResponse
     * */
    public function getSentOrders(Request $request){
        try{
            $token = $request -> header('remember_token');
            $user = Auth::guard('user')->setToken($token)->user();

            $frinds = $user->frinds->sortBy('updated_at')->reverse();
            $sent_orders = $frinds->where('order','=','Non')->take($request->num*10);
            $user->touch();
            
            return $this->returnData('sentorders',$sent_orders,'Success task.');
        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }
}
