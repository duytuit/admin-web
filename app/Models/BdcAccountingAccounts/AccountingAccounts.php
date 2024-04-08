<?php

namespace App\Models\BdcAccountingAccounts;

use App\Models\PublicUser\Users;
use App\Models\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Traits\ActionByUser;

class AccountingAccounts extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'bdc_accounting_accounts';

    protected $guarded = [];

    const fillable = [
        'tai_khoan_no_pt', 'tai_khoan_co_pt', 'tai_khoan_no_bao_co', 'tai_khoan_co_bao_co',
        'tai_khoan_co_thue', 'tai_khoan_no_thue', 'tai_khoan_co_truoc_vat', 'tai_khoan_no_truoc_vat','default'
    ];

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }
    public static function lists($buildingId){
         return self::where('bdc_building_id',$buildingId)->orderBy('default','desc')->get();
    }
    public static function get_detail_accountingaccount_by_accountingaccount_id($id){

        $rs = Cache::store('redis')->get(env('REDIS_PREFIX') . 'get_detail_accountingaccountById_'.$id);
 
        if($rs){
             return $rs;
        }
        $rs = DB::table('bdc_accounting_accounts')->find($id); // lấy ra thông tin tài khoản hạch toán
        if(!$rs){
             return false;
        }
         Cache::store('redis')->put(env('REDIS_PREFIX') . 'get_detail_accountingaccountById_' . $id, $rs,60*60*24);
         
         return $rs;
    }
}
