<?php

namespace App\Model;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Model\UserModel;
use App\Model\PostModel;

class MyShowModel extends Authenticatable implements JWTSubject
{
    //

    protected $fillable = [
        'usermodel_id','postmodel_id',
    ];

    // Relationship one (user) to many (myshows)
    public function usermodel(){
        return $this->belongsTo(UserModel::class,'usermodel_id','id');
    }

    // Relationship one (post) to many (myshows)
    public function postmodel(){
        return $this->belongsTo(PostModel::class,'postmodel_id','id');
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
