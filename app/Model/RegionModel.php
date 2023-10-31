<?php

namespace App\Model;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Model\UserModel;

class RegionModel extends Authenticatable implements JWTSubject
{
    //

    protected $fillable = [
        'name',
    ];

    // Relationship one (region) to many (users)
    public function usermodels(){
        return $this->hasMany(UserModel::class,'regionmodel_id','id');
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
