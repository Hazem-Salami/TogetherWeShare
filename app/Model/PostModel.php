<?php

namespace App\Model;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Model\UserModel;
use App\Model\CommentModel;
use App\Model\LikeModel;
use App\Model\MyShowModel;

class PostModel extends Authenticatable implements JWTSubject
{
    //

    protected $fillable = [
        'contentText','contentFile', 'privacy', 'hide','usermodel_id'
    ];

    protected $attributes=[
        'privacy' => 'Frinds',
        'hide' => false,
    ];

    // Relationship one (user) to many (posts)
    public function usermodel(){
        return $this->belongsTo(UserModel::class,'usermodel_id','id');
    }

    // Relationship one (post) to many (comments)
    public function commentmodels(){
        return $this->hasMany(CommentModel::class,'postmodel_id','id');
    }

    // Relationship one (post) to many (likes)
    public function likemodels(){
        return $this->hasMany(LikeModel::class,'postmodel_id','id');
    }
    
    // Relationship one (post) to many (myshows)
    public function myshowmodels(){
        return $this->hasMany(MyShowModel::class,'postmodel_id','id');
    }
    
    /********Override********/

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier(){
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims(){
        return [];
    }
}
