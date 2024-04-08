<?php

namespace App\Models\BdcV2DebitDetail;

use App\Models\Apartments\Apartments;
use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Models\BdcBills\Bills;
use App\Models\Building\Building;
use App\Models\Service\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;
use Illuminate\Support\Facades\Cache;

class DebitDetail extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'bdc_v2_debit_detail';
    protected $primaryKey = 'id';
    /*protected $fillable = [
        'id', // mã debit
        'bdc_building_id', // tòa nhà
        'bdc_bill_id', // hóa đơn
        'bdc_apartment_id', // mã căn hộ
        'bdc_apartment_service_price_id', // mã dịch vụ
        'from_date', // từ ngày nào
        'to_date', // đến ngày nào
        'detail', // chi tiết phí
        'previous_owed', // nợ cũ
        'cycle_name', // kỳ
        'quantity', // số lượng
        'price', // đơn giá
        'sumery', //tổng tiền cần thanh toán
        'discount', // số tiền giảm giá
        'discount_type', // 1: loại giảm giá % hay 0: số tiền cố định,
        'discount_note', // ghi chú giảm giá
        'paid', // số tiền thực tế trả theo đơn
        'paid_by_cycle_name', // số tiền thực tế trả theo kỳ
        'before_cycle_name', // số tiền đầu kỳ
        'after_cycle_name', // số tiền cuối kỳ
        'image', // ảnh điện nước
        'created_at', //tạo lúc
        'deleted_at',  //xóa lúc
    ];*/

    protected $guarded = [];

    public function apartment()
    {
        return $this->belongsTo(Apartments::class, 'bdc_apartment_id','id');
    }

    public function building()
    {
        return $this->belongsTo(Building::class, 'bdc_building_id','id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'bdc_service_id','id');
    }

    public function apartmentServicePrice()
    {
        return $this->belongsTo(ApartmentServicePrice::class, 'bdc_apartment_service_price_id','id');
    }

    public function bill()
    {
        return $this->belongsTo(Bills::class, 'bdc_bill_id','id');
    }
    public function billwithTrashed()
    {
        return $this->belongsTo(Bills::class, 'bdc_bill_id','id')->withTrashed();
    }
    public static function bdcCountDebit($id)
    {
        $count_debit = Cache::store('redis')->get(env('REDIS_PREFIX') . 'count_debit_v2_by_apartment_id_'.$id);
 
        if($count_debit){
             return $count_debit;
        }

        $count_debit = self::where('bdc_apartment_id',$id)->count();
        
        Cache::store('redis')->put(env('REDIS_PREFIX') . 'count_debit_v2_by_apartment_id_' . $id, $count_debit,360);

        return $count_debit;
    }
}
