<?php

namespace App\Model;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Model\HaveCertificateModel;

class CertificateModel extends Authenticatable implements JWTSubject
{
    //

    protected $fillable = [
        'name',
    ];

    // Relationship one (certificate) to many (havecertificates)
    public function havecertificatemodels(){
        return $this->hasMany(HaveCertificateModel::class,'certificatemodel_id','id');
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
