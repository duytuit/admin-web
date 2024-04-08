<?php

namespace App\Models\PublicUser\V2;

use App\Models\Building\Building;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class UserInfoApartment extends Model
{
    use SoftDeletes;

    use ActionByUser;
    protected $table = 'bdc_v2_user_apartment';

    protected $guarded = [];


    public static function getApartmentByUserInfo($userInfo){
        return self::where('user_info_id',$userInfo)->get();
    }
    public function building()
    {
        return $this->hasMany(Building::class, 'building_id', 'id');
    }
}
