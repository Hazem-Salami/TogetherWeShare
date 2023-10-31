<?php

namespace App\Model;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Model\PostModel;
use App\Model\CommentModel;
use App\Model\LikeModel;
use App\Model\FrindModel;
use App\Model\HaveCertificateModel;
use App\Model\RegionModel;
use App\Model\MyShowModel;

class UserModel extends Authenticatable implements JWTSubject
{
    //

    use Notifiable;

    protected $fillable = [
        'firstName', 'lastName', 'email', 'remember_token', 'password', 'work', 'pictureProfile', 'pictureWall', 'birth', 'about', 'gender', 'regionmodel_id',
    ];

    // The attributes that should be hidden for arrays.
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $attributes=[
        'gender' => 'Non',
        'regionmodel_id' => 1,
        'pictureProfile' => 'Files/Virtual/profile.png',
        'pictureWall' => 'Files/Virtual/Wall.jpg',
    ];

    // Relationship one (user) to many (posts)
    public function postmodels(){
        return $this->hasMany(PostModel::class,'usermodel_id','id');
    }

    // Relationship one (user) to many (comments)
    public function commentmodels(){
        return $this->hasMany(CommentModel::class,'usermodel_id','id');
    }

    // Relationship one (user) to many (likes)
    public function likemodels(){
        return $this->hasMany(LikeModel::class,'usermodel_id','id');
    }

    /**
     * Relationship one (user) to many (frinds)
     * from other user
     * لمعرفة طلبات الصداقة المرسلة
    */
    public function frinds(){
        return $this->hasMany(FrindModel::class,'usermodel_id','id');
    }

    /**
     * Relationship one (user) to many (frinds)
     * from other user
     * لمعرفة طلبات الصداقة المستقبلة
    */
    public function frindmodels(){
        return $this->hasMany(FrindModel::class,'usermodelx_id','id');
    }

    // Relationship one (user) to many (haveCertificates)
    public function havecertificatemodels(){
        return $this->hasMany(HaveCertificateModel::class,'usermodel_id','id');
    }

    // Relationship one (region) to many (users)
    public function regionmodel(){
        return $this->belongsTo(RegionModel::class,'regionmodel_id','id');
    }

    // Relationship one (user) to many (myshows)
    public function myshowmodels(){
        return $this->hasMany(MyShowModel::class,'usermodel_id','id');
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
