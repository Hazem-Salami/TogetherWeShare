<?php

namespace App\Model;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Model\UserModel;

class FrindModel extends Authenticatable implements JWTSubject
{
    //

    protected $fillable = [
        'order','usermodel_id','usermodelx_id',
    ];

    protected $attributes=[
        'order' => 'Non',
    ];

    /**
     * Relationship one (user) to many (frinds)
     * from me
     * لمعرفة من ارسل طلب الصداقة
    */
    public function user(){
        return $this->belongsTo(UserModel::class,'usermodel_id','id');
    }

    /**
     * Relationship one (user) to many (frinds)
     * from other user
     * لمعرفة من استقبل طلب الصداقة
    */
    public function usermodel(){
        return $this->belongsTo(UserModel::class,'usermodelx_id','id');
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
