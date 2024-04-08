<?php

namespace App\Models\Customers;

use App\Models\Apartments\Apartments;
use App\Models\PublicUser\UserInfo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\MyActivityTraits;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Traits\ActionByUser;

class Customers extends Model
{
    use SoftDeletes,MyActivityTraits;
    //
    const TYPE_CUSTOMER = 1;
    use ActionByUser;
    protected $table = 'bdc_customers';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'bdc_apartment_id', 'pub_user_profile_id', 'type', 'note_confirm', 'handover_date', 'status_confirm', 'status_success_handover','is_resident'
    ];

    protected $hidden = [];
    //protected $dates = ['deleted_at'];

    public function pubUserProfile()
    {
        return $this->belongsTo(UserInfo::class, 'pub_user_profile_id', 'id');
    }

    public function bdcApartment()
    {
        return $this->belongsTo(Apartments::class, 'bdc_apartment_id', 'id');
    }
    public function scopeFilter($query, $input)
    {
        foreach ($this->fillable as $value) {
            if (isset($input[$value])) {
                $query->where($value, $input[$value]);
            }
        }

        if (isset($input['keyword'])) {
            $search = $input['keyword'];
            $query->where(function ($q) use ($search) {
                foreach ($this->fillable as $value) {
                    $q->orWhere($value, 'LIKE', '%' . $search . '%');
                }
            });
        }
        return $query;
    }
    public static function get_detail_customer_by_apartment_id($id){

       $rs = Cache::store('redis')->get(env('REDIS_PREFIX') . 'get_detail_customerById_'.$id);

       if($rs){
            return $rs;
       }
       $rs = DB::table('bdc_customers')->where(['bdc_apartment_id'=>$id,'type'=>0])->first(); // lấy ra thông tin chủ hộ
       if(!$rs){
            return false;
       }
        Cache::store('redis')->put(env('REDIS_PREFIX') . 'get_detail_customerById_' . $id, $rs,60*60*24);
        
        return $rs;
    }
}
