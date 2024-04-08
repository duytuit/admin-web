<?php

namespace App\Models\V3\User;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class User extends Model
{
    use SoftDeletes;
    const USER_APP = 1;
    const USER_WEB = 2;
    const STATUS_ACTIVE = 1;
    use ActionByUser;
    protected $table = 'pub_users';


    /**
     * Get the info record associated with the user.
     */
    public function info()
    {
        return $this->hasOne(UserInfo::class, 'pub_user_id');
    }

    public function BDCprofile()
    {
        return $this->hasOne(UserInfo::class, 'pub_user_id')->where('type', self::USER_WEB);
    }

    public function infoWeb()
    {
        return $this->hasMany(UserInfo::class, 'pub_user_id')->where(['type' => self::USER_WEB,'status' => self::STATUS_ACTIVE]);
    }

}
