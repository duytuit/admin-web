<?php

namespace App\Models\PublicUser;

use App\Models\Building\Building;
use App\Models\Building\CompanyStaff;
use App\Models\Customers\Customers;
use App\Models\DepartmentStaff\DepartmentStaff;
use App\Models\Fcm;
use App\Traits\ActionByUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\MyActivityTraits;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UserInfo extends Model
{
    use SoftDeletes, MyActivityTraits;
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    use ActionByUser;
    protected $table = 'pub_user_profile';
    public static $status = [
        self::STATUS_INACTIVE => 'Inactive',
        self::STATUS_ACTIVE => 'Active'
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'pub_user_id', 'type', 'phone', 'email', 'address', 'gender', 'display_name','cmt_address', 'birthday', 'avatar' , 'created_by', 'created_at', 'cmt', 'cmt_nc','app_id','status', 'staff_code', 'bdc_building_id', 'type_profile', 'config_fcm','customer_code','customer_code_prefix','deleted_at','data_type'
    ];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'created_by',
        'updated_by',
        'updated_at',
        'created_at',
    ];

    public function pubusers()
    {
        return $this->belongsTo(Users::class, 'pub_user_id', 'id');
    }

    public function bdcDepartmentStaff()
    {
        return $this->belongsTo(DepartmentStaff::class, 'pub_user_id', 'pub_user_id');
    }

    public function bdcCustomers()
    {
        return $this->hasMany(Customers::class, 'pub_user_profile_id', 'id');
    }
    /*public function fcmsMobile()
    {
        return $this->hasMany(Fcm::class, 'user_id', 'id')->where('type_device','mobile');
    }*/

    public function company_staff()
    {
        return $this->belongsTo(CompanyStaff::class, 'pub_user_id', 'pub_user_id');
    }

    public function building()
    {
        return $this->belongsTo(Building::class, 'bdc_building_id');
    }
    //protected $dates = ['deleted_at'];

    public function getNameAttribute()
    {
        return $this->display_name;
    }

    public function setNameAttribute($value)
    {
        $this->attributes['display_name'] = $value;
        $this->save();
    }
    public static function get_detail_user_info_by_id($id){

        $rs = Cache::store('redis')->get(env('REDIS_PREFIX') . 'get_detail_user_infoById_'.$id);
 
        if($rs){
             return $rs;
        }
        $rs = DB::table('pub_user_profile')->find($id); // lấy ra thông tin khách hàng
        if(!$rs){
             return false;
        }
         Cache::store('redis')->put(env('REDIS_PREFIX') . 'get_detail_user_infoById_' . $id, $rs,60*60*24);
         
         return $rs;
     }
    /**
     * Get the phone record associated with the user.
     */

}
