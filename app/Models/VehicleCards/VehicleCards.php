<?php

namespace App\Models\VehicleCards;

use App\Models\Vehicles\Vehicles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;
use Illuminate\Support\Facades\Cache;

class VehicleCards extends Model
{
    use SoftDeletes;
    //
    use ActionByUser;
    protected $table = 'bdc_vehicle_cards';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'bdc_vehicle_id', 'code', 'status','description'
    ];

    protected $hidden = [];
    protected $dates = ['deleted_at'];

    public function bdcVehicle()
    {
        return $this->belongsTo(Vehicles::class, 'bdc_vehicle_id', 'id');
    }

    public static function get_detail_vehicle_card_by_id($id){

        $rs = Cache::store('redis')->get(env('REDIS_PREFIX') . 'get_detail_vehicle_cardById_'.$id);
 
        if($rs){
             return $rs;
        }
        $rs = self::where('bdc_vehicle_id',$id)->first(); // lấy ra thông tin dự án
        if(!$rs){
             return false;
        }
         Cache::store('redis')->put(env('REDIS_PREFIX') . 'get_detail_vehicle_cardById_' . $id, $rs,60*60*24);
         return $rs;
    }

}
