<?php

namespace App\Models\PublicUser;

use App\Models\Building\CompanyStaff;
use App\Models\DepartmentStaff\DepartmentStaff;
use App\Models\Permissions\GroupPermissions;
use App\Models\Model;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\Access\Authorizable;
use App\Traits\MyActivityTraits;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Traits\ActionByUser;

class Users extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract, JWTSubject
{
    protected $guard = 'backend_public';
    const USER_APP = 1;
    const USER_WEB = 2;
    const STATUS_ACTIVE = 1;
    use SoftDeletes,
        Authenticatable,
        Authorizable,
        CanResetPassword,
        MustVerifyEmail,
        MyActivityTraits,
        Notifiable;

    use ActionByUser;
    protected $table = 'pub_users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'password', 'email','mobile', 'remember_token','mobile_active','deleted_at','data_type'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Get the info record associated with the user.
     */
    public function info()
    {
        return $this->hasOne('App\Models\PublicUser\UserInfo', 'pub_user_id')->where('type', self::USER_APP);
    }

    public function BDCprofile()
    {
        return $this->hasOne('App\Models\PublicUser\UserInfo', 'pub_user_id')->where('type', self::USER_WEB)->where('status',1);
    }
    public function BDCprofileV2()
    {
        return $this->hasOne('App\Models\PublicUser\UserInfo', 'pub_user_id')->where('type', self::USER_WEB);
    }

    public function profileAll()
    {
        return $this->hasOne('App\Models\PublicUser\UserInfo', 'pub_user_id');
    }
    public function userInfoProfile()
    {
        return $this->hasMany(UserInfo::class, 'pub_user_id');
    }

    public function playnow()
    {
        return $this->hasOne('App\Models\PlayNowAccount');
    }

    public function departmentUser()
    {
        return $this->hasOne(DepartmentStaff::class, 'pub_user_id');
    }

    public function group()
    {
        return $this->hasMany(GroupPermissions::class, 'pub_user_id');
    }

    public function company_staff()
    {
        return $this->hasOne(CompanyStaff::class, 'pub_user_id');
    }

    public function infoWeb()
    {
        return $this->hasMany('App\Models\PublicUser\UserInfo', 'pub_user_id')->where('type', self::USER_WEB)->where('status', self::STATUS_ACTIVE);
    }
    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotifications($token));
    }

    public function getUserInfoId($bdc_building_id)
    {
        $userInfo = $this->infoWeb()->where('bdc_building_id', $bdc_building_id)->first() ?? $this->infoWeb()->first();
        return $userInfo;
    }

    public function appActiveProfile()
    {
        return $this->hasMany('App\Models\PublicUser\UserInfo', 'pub_user_id')->where('type', self::USER_APP)->where('status', self::STATUS_ACTIVE);
    }

    public static function checkEmailPhone($emailPhone)
    {
        return self::where(function($query) use($emailPhone){
               $query->where('email',$emailPhone)
                     ->orWhere('mobile',$emailPhone);
        })->whereHas('BDCprofile')->first();
    }

    public static function get_detail_user_by_user_id($id){

        $rs = Cache::store('redis')->get(env('REDIS_PREFIX') . 'get_detail_userById_'.$id);
 
        if($rs){
             return $rs;
        }
        $rs = DB::table('pub_users')->find($id); // lấy ra thông tin tài khoản
        if(!$rs){
             return false;
        }
         Cache::store('redis')->put(env('REDIS_PREFIX') . 'get_detail_userById_' . $id, $rs,60*60*24);
         
         return $rs;
    }

}

