<?php

namespace App\Models\BdcV2PaymentDetail;

use App\Models\Apartments\Apartments;
use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Models\BdcReceipts\Receipts;
use App\Models\BdcV2DebitDetail\DebitDetail;
use App\Models\Building\Building;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PaymentDetail extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'bdc_v2_payment_detail';
    protected $primaryKey = 'id';
    /*protected $fillable = [
        'id', // mã debit
        'bdc_building_id', // mã tòa
        'bdc_apartment_id', // mã căn hộ
        'bdc_apartment_service_price_id', // mã dịch vụ
        'cycle_name', // kỳ theo ngày thu tiền
        'bdc_receipt_id', // mã phiếu thu
        'bdc_log_coin_id', // mã log ví
        'bdc_debit_detail_id', // mã debit
        'paid', // số tiền thanh toán
        'paid_date', // ngày thanh toán
        'created_at', // thời gian tạo
        'updated_at', // thời gian cập nhật
        'deleted_at', // thời gian xóa
    ];*/

    protected $guarded = [];

    public function receipt()
    {
        return $this->belongsTo(Receipts::class, 'bdc_receipt_id', 'id');
    }
    public function apartment()
    {
        return $this->belongsTo(Apartments::class, 'bdc_apartment_id','id');
    }

    public function building()
    {
        return $this->belongsTo(Building::class, 'bdc_building_id','id');
    }

    public function debit()
    {
        return $this->belongsTo(DebitDetail::class, 'bdc_debit_detail_id','id');
    }

    public function apartmentServicePrice()
    {
        return $this->belongsTo(ApartmentServicePrice::class, 'bdc_apartment_service_price_id','id');
    }
    public static function get_detail_payment_by_id($id){

        $rs = Cache::store('redis')->get(env('REDIS_PREFIX') . 'get_detail_payment_by_id_'.$id);
 
        if($rs){
             return $rs;
        }
        $rs = self::where('bdc_receipt_id',$id)->get(); // lấy ra thông tin phiếu thu
        if(!$rs){
             return false;
        }
         Cache::store('redis')->put(env('REDIS_PREFIX') . 'get_detail_payment_by_id_' . $id, $rs,60*60*24);
         return $rs;
    }
}
