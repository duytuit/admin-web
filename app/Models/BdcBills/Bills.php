<?php

namespace App\Models\BdcBills;

use App\Models\Apartments\Apartments;
use App\Models\Building\Building;
use App\Models\BdcDebitDetail\DebitDetail;
use App\Models\BdcV2DebitDetail\DebitDetail as BdcV2DebitDetailDebitDetail;
use Illuminate\Database\Eloquent\Model;
use App\Models\PublicUser\Users;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Traits\ActionByUser;

class Bills extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'bdc_bills';

    protected $fillable = [
        'bdc_apartment_id', 
        'bdc_building_id', 
        'bill_code', 
        'cost', 
        'cost_free', 
        'customer_name', 
        'customer_address', 
        'provider_address', 
        'deadline', 
        'is_vat', 
        'status', 
        'notify',
        'cycle_name',
        'user_id',
        'approved_id',
        'sender_id',
        'confirm_date',
        'deleted_by'
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartments::class, 'bdc_apartment_id','id');
    }

    public function building()
    {
        return $this->belongsTo(Building::class, 'bdc_building_id', 'id');
    }
    
    public function debitDetail()
    {
        return $this->hasMany(DebitDetail::class, 'bdc_bill_id', 'id');
    }

    public function debitDetailV2()
    {
        return $this->hasMany(BdcV2DebitDetailDebitDetail::class,'bdc_bill_id', 'id');
    }

    public function firstDetail()
    {
        return $this->hasOne(DebitDetail::class, 'bdc_bill_id', 'id');
    }
    public function ApprovedUser()
    {
        return $this->belongsTo(Users::class, 'approved_id');
    }
    public function SenderUser()
    {
        return $this->belongsTo(Users::class, 'sender_id');
    }
    public function pubUser()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }
    public static function get_detail_bill_by_apartment_id($id){

        $rs = Cache::store('redis')->get(env('REDIS_PREFIX') . 'get_detail_billById_'.$id);
 
        if($rs){
             return $rs;
        }
        $rs = DB::table('bdc_bills')->find($id); // lấy ra thông tin dự án
        if(!$rs){
             return false;
        }
         Cache::store('redis')->put(env('REDIS_PREFIX') . 'get_detail_billById_' . $id, $rs,60*60*24);
         return $rs;
    }
}
