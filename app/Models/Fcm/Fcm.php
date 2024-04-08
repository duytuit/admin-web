<?php

namespace App\Models\Fcm;

use App\Models\Apartments\Apartments;
use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Models\VehicleCards\VehicleCards;
use App\Models\VehicleCategory\VehicleCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class Fcm extends Model
{
    //
    use ActionByUser;
    protected $table = 'fcms';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'token', 'user_type', 'device_id', 'type_device','bundle_id'
    ];

    protected $hidden = [];
    protected $dates = ['deleted_at'];
    public static function getCountTokenbyUserId($users =[])
    {
        $rs = self::whereIn('user_id', $users)->where('token', '!=', null)->count();
        return $rs;
    }
}
