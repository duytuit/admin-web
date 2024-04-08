<?php

namespace App\Models\PublicUser\V2;

use App\Traits\MyActivityTraits;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Traits\ActionByUser;

class User extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract, JWTSubject
{
    protected $guard = 'public_user_v2';
    use SoftDeletes,
    Authenticatable,
    Authorizable,
    CanResetPassword,
    MustVerifyEmail,
    MyActivityTraits,
    Notifiable;
    use ActionByUser;
    protected $table = 'bdc_v2_user';

    protected $guarded = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'pword'
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
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->pword;
    }
    public function infoApp()
    {
        return $this->hasOne(UserInfo::class, 'user_id','id');
    }
    public static function changePass($email, $pass)
    {
        return self::where('email', $email)->update(['pword' => Hash::make($pass)]);
    }
    public static function checkEmailPhone($emailPhone)
    {
        return self::where(function($query) use($emailPhone){
               $query->where('email',$emailPhone)
                     ->orWhere('phone',$emailPhone);
        })->first();
    }
}
