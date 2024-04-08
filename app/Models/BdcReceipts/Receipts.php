<?php

namespace App\Models\BdcReceipts;

use App\Models\Apartments\Apartments;
use App\Models\BdcAccountingAccounts\AccountingAccounts;
use App\Models\BdcAccountingVouchers\AccountingVouches;
use App\Models\BdcV2PaymentDetail\PaymentDetail;
use App\Models\Building\Building;
use App\Models\PublicUser\Users;
use App\Models\Configs\Configs;
use App\Models\PaymentInfo\PaymentInfo;
use App\Models\PublicUser\UserInfo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Traits\ActionByUser;

class Receipts extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'bdc_receipts';

    protected $fillable = [
        'bdc_bill_id', 
        'bdc_apartment_id', 
        'bdc_building_id', 
        'receipt_code', 
        'cost',
        'cost_paid',
        'customer_name', 
        'customer_address', 
        'provider_address', 
        'bdc_receipt_total', 
        'logs',
        'customer_total_paid',
        'url',
        'type_payment',
        'user_id',
        'config_id',
        'payment_type',
        'type',
        'status',
        'url_payment',
        'vnp_bank_code',
        'vnp_banktranno',
        'vnp_cardtype',
        'vnp_paydate',
        'vnp_transactionno',
        'vnp_responsecode',
        'vnp_currcode',
        'vnp_status',
        'description',
        'tai_khoan_no',
        'tai_khoan_co',
        'ngan_hang',
        'ma_khach_hang',
        'ten_khach_hang',
        'data',
        'create_date',
        'account_balance',
        'feature',
        'config_type_payment',
        'updated_by',
        'trans_id',
        'deleted_at'
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartments::class, 'bdc_apartment_id', 'id');
    }

    public function building()
    {
        return $this->belongsTo(Building::class, 'bdc_building_id');
    }

    public function accounting_account_tai_khoan_co()
    {
        return $this->hasOne(AccountingAccounts::class, 'id', 'tai_khoan_co');
    }

    public function accounting_account_tai_khoan_no()
    {
        return $this->hasOne(AccountingAccounts::class, 'id', 'tai_khoan_no');
    }

    public function payment_info_ngan_hang()
    {
        return $this->hasOne(PaymentInfo::class, 'id', 'ngan_hang');
    }

    public function pubUser()
    {
        return $this->belongsTo(Users::class, 'user_id')
            ->withoutGlobalScope(SoftDeletingScope::class);
    }
    public function pubUserInfo()
    {
        return $this->belongsTo(UserInfo::class, 'user_id','pub_user_id')->where('type',2)->withTrashed();
    }
     public function pubConfig()
    {
        return $this->belongsTo(Configs::class, 'config_id');
    }
    public function AccountingVouches()
    {
        return $this->hasMany(AccountingVouches::class, 'bdc_receipt_id','id');
    }
    public function PaymentDetail()
    {
        return $this->hasMany(PaymentDetail::class, 'bdc_receipt_id','id')->orderBy('bdc_apartment_service_price_id');
    }
    public static function get_detail_receipt_by_receipt_id($id){

        $rs = Cache::store('redis')->get(env('REDIS_PREFIX') . 'get_detail_receiptById_'.$id);
 
        if($rs){
             return $rs;
        }
        $rs = DB::table('bdc_receipts')->find($id); // lấy ra thông tin phiếu thu
        if(!$rs){
             return false;
        }
         Cache::store('redis')->put(env('REDIS_PREFIX') . 'get_detail_receiptById_' . $id, $rs,60*60*24);
         //Cache::store('redis')->forget(env('REDIS_PREFIX') . 'get_detail_receiptById_' . $id);
         return $rs;
    }

    public static function get_detail_receipt_by_debit_id($id){

        $rs = Cache::store('redis')->get(env('REDIS_PREFIX') . 'get_detail_receipt_by_debit_id'.$id);
 
        if($rs){
             return $rs;
        }
        $receipt_ids = PaymentDetail::where(function($query) use($id){
            $query->where('bdc_debit_detail_id',$id);
        })->pluck('bdc_receipt_id');
        if($receipt_ids && count($receipt_ids) > 0){
            foreach ($receipt_ids as $key => $value) {
                $receipt_code = DB::table('bdc_receipts')->find($value); // lấy ra thông tin phiếu thu
                if($receipt_code && $receipt_code->deleted_at != null){
                    $rs[]= '<span style="text-decoration: line-through;">'.$receipt_code->receipt_code.'</span>';
                }else if($receipt_code){
                    $rs[]=$receipt_code->receipt_code;
                }else{
                    $rs[]='hạch toán auto';
                }
            }
        }
        if(!$rs){
             return false;
        }
         Cache::store('redis')->put(env('REDIS_PREFIX') . 'get_detail_receipt_by_debit_id' . $id, $rs,60*60*24);
         return $rs;
    }
    public static function bdcCountReceiptByApartment($id)
    {
        $count_debit = Cache::store('redis')->get(env('REDIS_PREFIX') . 'count_receipt_v2_by_apartment_id_'.$id);

        if($count_debit){
            return $count_debit;
        }

        $count_debit = self::where('bdc_apartment_id',$id)->count();

        Cache::store('redis')->put(env('REDIS_PREFIX') . 'count_receipt_v2_by_apartment_id_' . $id, $count_debit,360);

        return $count_debit;
    }

}
