<?php

namespace App\Models\BdcV2LogCoinDetail;

use App\Models\Apartments\Apartments;
use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Models\BdcReceipts\Receipts;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class LogCoinDetail extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'bdc_v2_log_coin_detail';

    protected $primaryKey = 'id';
    /*protected $fillable = [
        'id', // mã
        'bdc_building_id', // tòa nhà
        'bdc_apartment_id', // mã căn hộ
        'bdc_apartment_service_price_id', // mã dịch vụ
        'cycle_name', // kỳ
        'user_id', // user thanh toán
        'coin', // số tiền thanh toán
        'type', // 0: trừ coin, 1: cộng coin
        'by', // bởi admin nào
        'note', // ghi chú
        'from_type', // từ nguồn nào, 1: nộp tiền thừa từ bảng reciept, 2: từ hạch toán tự động, 3: phân bổ từ ví A sang ví B, 4 hạch toán từ ví khác
        'from_id', // id từ nguồn đấy
        'created_at', // thời gian tạo
        'updated_at', // thời gian cập nhật
        'data', // dữ liệu trước và sau khi thêm coin
    ];*/

    protected $guarded = [];

    public function apartment()
    {
        return $this->belongsTo(Apartments::class, 'bdc_apartment_id','id');
    }

    public function apartmentServicePrice()
    {
        return $this->belongsTo(ApartmentServicePrice::class, 'bdc_apartment_service_price_id','id');
    }

    public function receipt()
    {
        return $this->belongsTo(Receipts::class, 'from_id','id');
    }

    public function receiptNote()
    {
        return $this->belongsTo(Receipts::class, 'note','id');
    }

    public function receipt_trashed()
    {
        return $this->belongsTo(Receipts::class, 'from_id','id')->withTrashed();
    }


}
