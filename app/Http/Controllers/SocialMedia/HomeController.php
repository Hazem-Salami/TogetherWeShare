<?php

namespace App\Http\Controllers\SocialMedia;

use App\Http\Controllers\SocialMedia\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Model\UserModel;
use App\Model\PostModel;
use App\Model\CommentModel;
use App\Model\LikeModel;
use App\Model\FrindModel;
use App\Model\MyShowModel;
use App\Notifications\NotificationsManagement;

class HomeController extends Controller
{
    //

    /*
     *
     * Create a new HomeController instance.
     *
     * @return void
     * */
    public function __construct(){
        $this->middleware('is_user.guard:user');
    }

    /*
     *
     * create comment
     * => create comment and save in DB - need token & id_post
     * @return void
     * */
    public function createComment (Request $request){
        try{
            $rules=[
                'contentText'=> ['nullable', 'string', 'max:255'],
                'contentFile'=> ['nullable', Rule::requiredIf($request->contentText==NULL),
                                 'file','mimes:jpeg,bmp,png,gif,svg,webp'],
            ];

            $validator = Validator::make($request->all(), $rules);
            if( $validator->fails()){
                return $this->returnValidationError($validator);
            }

            $token = $request -> header('remember_token');
            $user = Auth::guard('user')->setToken($token)->user();
            $post = PostModel::find($request -> id_post);

            if($request->hasFile('contentFile')){
                $data=$request->only(['contentText','usermodel_id','postmodel_id']);
                $contentFile=$request->contentFile->store('Files/Comments','public');
                $data['contentFile']=$contentFile;
                $data['usermodel_id']=$user->id;
                $data['postmodel_id']=$post->id;
                $data['commentmodel_id']=$request->id_comment;
                $comment=new CommentModel($data);
                $comment->save();
            }
            else{
                $data=$request->all();
                $data['usermodel_id']=$user->id;
                $data['postmodel_id']=$post->id;
                $data['commentmodel_id']=$request->id_comment;
                $comment=new CommentModel($data);
                $comment->save();
            }

            $owner_user = UserModel::find($post->usermodel_id);

            if($owner_user->id != $user->id){
                $invoice=array();
                $name = $user->firstName.' '.$user->lastName;
                $invoice['user_name']=$name;
                $invoice['notification_type']='CreateComment';
                $invoice['pictureProfile']=$user->pictureProfile;
                $owner_user->notify(new NotificationsManagement($invoice));
            }
            $post->touch();
            $user->touch();
            return $this->returnSuccess('Success task.');
        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }

    /*
     *
     * editcomment
     * => edit comment and save in DB - need id_comment
     * @return void
     * */
    public function editComment (Request $request){
        try{

            $comment = CommentModel::find($request -> id_comment);
            $token = $request -> header('remember_token');
            $user = Auth::guard('user')->setToken($token)->user();
            $post = $comment->postmodel;

            if($comment->usermodel_id != $user->id){
                return $this->returnError('Not found.');
            }

            $rules=[
                'contentText'=> ['nullable', 'string', 'max:255'],
                'contentFile'=> ['nullable', Rule::requiredIf($request->contentText==NULL),
                                 'file','mimes:jpeg,bmp,png,gif,svg,webp'],
            ];

            $validator = Validator::make($request->all(), $rules);
            if( $validator->fails()){
                return $this->returnValidationError($validator);
            }

            if($request->hasFile('contentFile')){
                $data=$request->only(['contentText']);
                $contentFile=$request->contentFile->store('Files/Comments','public');
                if($comment->contentFile != NUll){
                    Storage::disk('public')->delete($comment->contentFile);
                }
                $data['contentFile']=$contentFile;
                $comment->update($data);
            }
            else{
                if($comment->contentFile != NULL){
                    Storage::disk('public')->delete($comment->contentFile);
                }
                $data=$request->all();
                $data['contentFile']=NULL;
                $comment->update($data);
            }

            $owner_user = UserModel::find($post->usermodel_id);
            if($owner_user->id != $user->id){
                $invoice=array();
                $name = $user->firstName.' '.$user->lastName;
                $invoice['user_name']=$name;
                $invoice['notification_type']='EditComment';
                $invoice['pictureProfile']=$user->pictureProfile;
                $owner_user->notify(new NotificationsManagement($invoice));
            }
            $comment->touch();
            $post->touch();
            $user->touch();
            return $this->returnSuccess('Success task.');
        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }

    /*
     *
     * deletecomment
     * => delete comment from DB - need id_comment
     * @return void
     * */
    public function deleteComment (Request $request){
        try{

            $comment = CommentModel::find($request -> id_comment);
            $token = $request -> header('remember_token');
            $user = Auth::guard('user')->setToken($token)->user();
            $post = $comment->postmodel;

            if($post->usermodel_id != $user->id){
                if($comment->usermodel_id != $user->id){
                    return $this->returnError('Not found.');
                }
            }

            $Replies=$comment->commentmodels;
            foreach( $Replies as $Reply){
                $Reply->delete();
            }

            if($comment->contentFile != NUll){
                Storage::disk('public')->delete($comment->contentFile);
            }
            $comment->delete();
            $user->touch();
            return $this->returnSuccess('Success task.');
        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }

    /*
     *
     * getcomment
     * => return comments related to a post - need id_post
     * @return \Illuminate\Http\JsonResponse
     * */
    public function getComments/*from post*/(Request $request){
        try{
            $token = $request -> header('remember_token');
            $user = Auth::guard('user')->setToken($token)->user();
            $post = PostModel::find($request -> id_post);
            $comments = $post->commentmodels->where('commentmodel_id','=',null)->sortBy('updated_at')->reverse()->take($request->num*10)->values();
            foreach($comments as $comment){
                if($post->usermodel_id != $user->id){
                    if($comment->usermodel_id != $user->id){
                        $comment['testDelete']=false;
                    }else{
                        $comment['testDelete']=true;
                    }
                }else{
                    $comment['testDelete']=true;
                }

                if($comment->usermodel_id != $user->id){
                    $comment['testEdit']=false;
                }else{
                    $comment['testEdit']=true;
                }

                $owner_user = UserModel::find($comment->usermodel_id);
                $comment['usermodel_id']=$owner_user;
            }
            return $this->returnData('comments',$comments,'Success task.');
        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }

    /*
     *
     * get comment replies
     * => return comment replies related to a comment - need id_comment
     * @return \Illuminate\Http\JsonResponse
     * */
    public function getReplies/*from comment*/(Request $request){
        try{
            $token = $request -> header('remember_token');
            $user = Auth::guard('user')->setToken($token)->user();
            $comment = CommentModel::find($request -> id_comment);
            $post = $comment->postmodel;
            $replies = $comment->commentmodels->take($request->num*10)->values();
            foreach($replies as $replie){
                if($post->usermodel_id != $user->id){
                    if($replie->usermodel_id != $user->id){
                        $replie['testDelete']=false;
                    }else{
                        $replie['testDelete']=true;
                    }
                }else{
                    $replie['testDelete']=true;
                }

                if($replie->usermodel_id != $user->id){
                    $replie['testEdit']=false;
                }else{
                    $replie['testEdit']=true;
                }

                $owner_user = UserModel::find($replie->usermodel_id);
                $replie['usermodel_id']=$owner_user;
            }
            return $this->returnData('replies',$replies,'Success task.');
        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }

    /*
     *
     * getuser
     * => return user related to a comment - need id_comment
     * @return \Illuminate\Http\JsonResponse
     * */
    public function getUser/*from comment*/(Request $request){
        try{
            $comment = CommentModel::find($request -> id_comment);
            $user = $comment->usermodel;
            return $this->returnData('user',$user,'Success task.');
        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }

    /*
     *
     * get_user
     * => return user related to a id - need id_user
     * @return \Illuminate\Http\JsonResponse
     * */
    public function get_User/*from id_user*/(Request $request){
        try{
            $rules=[
                'id_user'=> ['required','exists:user_models,id'],
            ];

            $validator = Validator::make($request->all(), $rules);
            if( $validator->fails()){
                return $this->returnValidationError($validator);
            }
            $user = UserModel::find($request->id_user);
            return $this->returnData('user',$user,'Success task.');
        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }

    /*
     *
     * createlike
     * => create like or delete if found and edit if select second opinion and save in DB - need id_post & token
     * @return void
     * */
    public function createLike(Request $request){
        try{
            $rules=[
                'opinion'=> ['required',Rule::in(['Like', 'Unlike'])],
            ];

            $validator = Validator::make($request->all(), $rules);
            if( $validator->fails()){
                return $this->returnValidationError($validator);
            }

            $token = $request -> header('remember_token');
            $user = Auth::guard('user')->setToken($token)->user();
            $post = PostModel::find($request -> id_post);

            $invoice=array();
            $owner_user = UserModel::find($post->usermodel_id);
            $name = $user->firstName.' '.$user->lastName;
            $invoice['user_name']=$name;
            $invoice['pictureProfile']=$user->pictureProfile;

            $likes=$user->likemodels;
            $like=$likes->where('postmodel_id','=',$request->id_post)->first();
            if($like){
                if($like->opinion==$request->opinion){
                    $like->delete();
                    return $this->returnSuccess('Success task.');
                }else{
                    $like->update([
                        'opinion'=>$request->opinion,
                    ]);
                    $invoice['notification_type'] = 'EditReact';
                    if($owner_user->id != $user->id){
                        $owner_user->notify(new NotificationsManagement($invoice));
                    }
                    return $this->returnSuccess('Success task.');
                }
            }
            $invoice['notification_type'] = $request->opinion;
            $data=$request->all();
            $data['usermodel_id']=$user->id;
            $data['postmodel_id']=$post->id;
            $like=new LikeModel($data);
            $like->save();

            if($owner_user->id != $user->id){
                $owner_user->notify(new NotificationsManagement($invoice));
            }

            $post->touch();
            $user->touch();
            return $this->returnSuccess('Success task.');
        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }

    /*
     * settingFrind
     * usermodel_id send order to usermodelx_id
     * => send order or remove send order or delete frind & save in DB - need token - need id other user
     * @return void
     * */
    public function managingFrind/*send order or remove send order or delete frind*/(Request $request){
        try{
            $token = $request -> header('remember_token');
            $user = Auth::guard('user')->setToken($token)->user();

            $rules=[
                'id_user'=> ['required','exists:user_models,id'],
            ];

            $validator = Validator::make($request->all(), $rules);
            if( $validator->fails()){
                return $this->returnValidationError($validator);
            }

            $user_second = UserModel::find($request->id_user);

            if($user->id == $user_second->id){
                return $this->returnError('Not found.');
            }

            $orders=$user->frinds;
            $order = $orders->where('usermodelx_id','=',$user_second->id)->first();
            if($order){
                $order->delete();
                return $this->returnSuccess('Success task.');
            }

            $orders=$user->frindmodels;
            $order = $orders->where(['usermodel_id'],'=',$user_second->id)->first();
            if($order){
                if($order->order=='Accept'){
                    $order->delete();
                    return $this->returnSuccess('Success task.');
                }
                return $this->returnError('not found');
            }

            $data=$request->all();
            $data['usermodel_id']=$user->id;
            $data['usermodelx_id']=$user_second->id;
            $frind = $user->frinds;
            $frind=new FrindModel($data);
            $frind->save();

            $invoice=array();
            $name = $user->firstName.' '.$user->lastName;
            $invoice['user_name']=$name;
            $invoice['notification_type'] = 'SendOrder';
            $invoice['pictureProfile']=$user->pictureProfile;
            $user_second->notify(new NotificationsManagement($invoice));
            $user->touch();
            return $this->returnSuccess('Success task.');
        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }

    /*
     *
     * acceptorderfrind
     * => accept frind order & save in DB - need id_frind
     * @return void
     * */
    public function acceptOrderFrind(Request $request){
        try{
            $token = $request -> header('remember_token');
            $user = Auth::guard('user')->setToken($token)->user();
            $frind = FrindModel::find($request->id_frind);
            $frind->update([
                'order' => 'Accept',
            ]);

            $user_second=$frind->user;

            $invoice=array();
            $name = $user->firstName.' '.$user->lastName;
            $invoice['user_name']=$name;
            $invoice['notification_type'] = 'AcceptOrder';
            $invoice['pictureProfile']=$user->pictureProfile;
            $user_second->notify(new NotificationsManagement($invoice));
            $user->touch();
            return $this->returnSuccess('Success task.');
        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }

    /*
     *
     * deletefrind
     * => delete frind order or delete frind & save in DB - need id_frind
     * @return void
     * */
    public function deleteOrderFrind(Request $request){
        try{
            $token = $request -> header('remember_token');
            $user = Auth::guard('user')->setToken($token)->user();
            $frind = FrindModel::find($request->id_frind);
            $frind->delete();
            $user->touch();
            return $this->returnSuccess('Success task.');
        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }

    /*
     *
     * create
     * => create myshow & save in DB
     * @return void
     * */
    private function build/*build myshow*/($post_id,$user_id){
        try{
            MyShowModel::create([
                'postmodel_id' => $post_id,
                'usermodel_id' => $user_id,
            ]);
        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }

    /*
     *
     * search
     * => search in array
     * @return boolean
     * */    
    private function search($onePost,$posts){
        try{
            if(empty($posts)){
                return false;
            }
            foreach ($posts as $post){
                if($post['id'] == $onePost->id){
                    return true;
                }
            }
            return false;
        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }

    /*
     *
     * mainposts
     * => main page psots - need token
     * @return void
     * */
    public function mainPosts(Request $request){
        try{
            $rules=[
                'myshow'=> ['nullable','array'],
            ];

            $validator = Validator::make($request->all(), $rules);
            if( $validator->fails()){
                return $this->returnValidationError($validator);
            }
            $token = $request->header('remember_token');
            $user = Auth::guard('user')->setToken($token)->user();
            $mainpages=PostModel::orderBy('updated_at', 'desc')->get();
            $likes=$user->likemodels;

            if(!empty($request->myshow))
                $posts=$request->myshow;
            else
                $posts=array();

            $request['console']=true;
            $frinds=$this->getFrinds($request);

            $postunknowfrinds=($mainpages->where('hide','=',1))->where('privacy','=','Frinds')->values();
            $postknowfrinds= ($mainpages->where('hide','=',0))->where('privacy','=','Frinds')->values();
            $postunknowgenerals = ($mainpages->where('hide','=',1))->where('privacy','=','General')->values();
            $postknowgenerals = ($mainpages->where('hide','=',0))->where('privacy','=','General')->values();

            $i=1;
            foreach($postknowfrinds as $postknowfrind){
                $user_post = $postknowfrind->usermodel;
                if(!$this->search($postknowfrind,$posts)){
                    if(in_array($user_post,$frinds)||($user_post->id==$user->id)){
                        $i++;
                        $myshow = ($user->myshowmodels)->where('postmodel_id','=',$postknowfrind->id)->first();
                        if(!$myshow){
                            $this->build($postknowfrind->id,$user->id);
                        }else{
                            $myshow->touch();
                        }
                        $like=$likes->where('postmodel_id','=',$postknowfrind->id)->first();
                        if($like){
                            $postknowfrind['opinion']=$like->opinion;
                        }else{
                            $postknowfrind['opinion']='None';
                        }
                        $postknowfrind['likesCount']=(($postknowfrind->likemodels)->where('opinion','=','Like'))->count();
                        $postknowfrind['unlikesCount']=(($postknowfrind->likemodels)->where('opinion','=','Unlike'))->count();
                        array_push($posts,$postknowfrind);
                    }
                }
                if($i==10){
                    break;
                }
            }

            $i=1;
            foreach($postunknowfrinds as $postunknowfrind){
                $user_post = $postunknowfrind->usermodel;
                if(!$this->search($postunknowfrind,$posts)){
                    if(in_array($user_post,$frinds)||($user_post->id==$user->id)){
                        $i++;
                        $myshow = ($user->myshowmodels)->where('postmodel_id','=',$postunknowfrind->id)->first();
                        if(!$myshow){
                            $this->build($postunknowfrind->id,$user->id);
                        }else{
                            $myshow->touch();
                        }
                        $like=$likes->where('postmodel_id','=',$postunknowfrind->id)->first();
                        if($like){
                            $postunknowfrind['opinion']=$like->opinion;
                        }else{
                            $postunknowfrind['opinion']='None';
                        }
                        $postunknowfrind['likesCount']=(($postunknowfrind->likemodels)->where('opinion','=','Like'))->count();
                        $postunknowfrind['unlikesCount']=(($postunknowfrind->likemodels)->where('opinion','=','Unlike'))->count();
                        array_push($posts,$postunknowfrind);
                    }
                }
                if($i==10){
                    break;
                }
            }

            $i=1;
            foreach($postknowgenerals as $postknowgeneral){
                $user_post = $postknowgeneral->usermodel;
                if(!$this->search($postknowgeneral,$posts)){
                    $i++;
                    $myshow = ($user->myshowmodels)->where('postmodel_id','=',$postknowgeneral->id)->first();
                    if(!$myshow){
                        $this->build($postknowgeneral->id,$user->id);
                    }else{
                        $myshow->touch();
                    }
                    $like=$likes->where('postmodel_id','=',$postknowgeneral->id)->first();
                    if($like){
                        $postknowgeneral['opinion']=$like->opinion;
                    }else{
                        $postknowgeneral['opinion']='None';
                    }
                    $postknowgeneral['likesCount']=(($postknowgeneral->likemodels)->where('opinion','=','Like'))->count();
                    $postknowgeneral['unlikesCount']=(($postknowgeneral->likemodels)->where('opinion','=','Unlike'))->count();
                    array_push($posts,$postknowgeneral);
                }
                if($i==10){
                    break;
                }
            }

            $i=1;
            foreach($postunknowgenerals as $postunknowgeneral){
                $user_post = $postunknowgeneral->usermodel;
                if(!$this->search($postunknowgeneral,$posts)){
                    $i++;
                    $myshow = ($user->myshowmodels)->where('postmodel_id','=',$postunknowgeneral->id)->first();
                    if(!$myshow){
                        $this->build($postunknowgeneral->id,$user->id);
                    }else{
                        $myshow->touch();
                    }
                    $like=$likes->where('postmodel_id','=',$postunknowgeneral->id)->first();
                    if($like){
                        $postunknowgeneral['opinion']=$like->opinion;
                    }else{
                        $postunknowgeneral['opinion']='None';
                    }
                    $postunknowgeneral['likesCount']=(($postunknowgeneral->likemodels)->where('opinion','=','Like'))->count();
                    $postunknowgeneral['unlikesCount']=(($postunknowgeneral->likemodels)->where('opinion','=','Unlike'))->count();
                    array_push($posts,$postunknowgeneral);
                }
                if($i==10){
                    break;
                }
            }
            $user->touch();
            return $this->returnData('mainposts',$posts,'Success task.');
        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }

    /*
     *
     * test common frinds
     * => is common frind [true] - is not common frind [false]
     * @return boolean
     * */
    private function isCommonFrind(Request $request){
        try{
            $token = $request -> header('remember_token');
            $user = Auth::guard('user')->setToken($token)->user();
            if($user->id == $request->id_user){
                return false;
            }
            $request['console']=true;
            $frinds=$this->getFrinds($request);

            foreach($frinds as $frind){
                $frind_frinds = $frind->frinds;
                $frind_2 = $frind_frinds->where('usermodelx_id','=',$request->id_user)->first();
                if($frind_2){
                    if($frind_2->order=='Accept'){
                        return true;
                    }
                }

                $frind_frinds = $frind->frindmodels;
                $frind_2 = $frind_frinds->where('usermodel_id','=',$request->id_user)->first();
                if($frind_2){
                    if($frind_2->order=='Accept'){
                        return true;
                    }
                }
            }
            return false;
        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }

    /*
     *
     * suggestionfrinds
     * => suggestion Frinds (work - city - study - gender -common frind) - need token
     * @return \Illuminate\Http\JsonResponse
     * */
    public function suggestionFrinds(Request $request){
        try{
            $token = $request -> header('remember_token');
            $user = Auth::guard('user')->setToken($token)->user();

            $users = UserModel::orderBy('updated_at', 'desc')->get();
            $suggestionfrinds=array();

            $request['console']=true;

            foreach($users as $us){
                $request['id_user']=$us->id;
                if($user->id == $request->id_user){
                    continue;
                }
                $request['id_user']=$us->id;
                $test=$this->isFrind($request);
                if($test == 'NotFrind'){
                    if($us->work!=NULL && $user->work!=NULL){
                        if($us->work == $user->work){
                            array_push($suggestionfrinds,$us);
                        }
                    }else if($us->regionmodel_id == $user->regionmodel_id){
                        array_push($suggestionfrinds,$us);
                    }
                    else if($us->birth!=NULL && $user->birth!=NULL){
                        $diff=date_diff($us->birth,$user->birth,true);
                        if( $diff-> y <= 10 ){
                            array_push($suggestionfrinds,$us);
                        }
                    }else if($us->gender == $user->gender){
                        array_push($suggestionfrinds,$us);
                    }else if($this->isCommonFrind($request)){
                        array_push($suggestionfrinds,$us);
                    }
                }

                if(count($suggestionfrinds)==10){
                    break;
                }
            }
            if(empty($suggestionfrinds)){
                foreach($users as $us){
                    $request['id_user']=$us->id;
                    $test=$this->isFrind($request);
                    if($test == 'NotFrind'){
                        array_push($suggestionfrinds,$us);
                    }
                    if(count($suggestionfrinds)==10){
                        break;
                    }
                }
            }

            $user->touch();
            return $this->returnData('suggestionfrinds',$suggestionfrinds,'Success task.');
        }catch(\Exception $e){
            return $this->returnError($e->getMessage());
        }
    }
}