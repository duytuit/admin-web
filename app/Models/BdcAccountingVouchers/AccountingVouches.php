<?php

namespace App\Models\BdcAccountingVouchers;

use App\Models\Apartments\Apartments;
use App\Models\PublicUser\Users;
use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Service\Service;
use App\Models\BdcDebitDetail\DebitDetail;
use App\Models\BdcReceipts\Receipts;
use App\Traits\ActionByUser;

class AccountingVouches extends Model  // Hạch toán tiền thừa
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'bdc_accounting_vouchers';

    protected $guarded = [];

    const tien_thua = 'tien_thua';
    const hach_toan = 'hach_toan';

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }
    public function service(){
        return $this->belongsTo(Service::class, 'bdc_service_id');
    }
    public function debitdetail(){
        return $this->belongsTo(DebitDetail::class, 'bdc_debit_detail_id');
    }
    public function receipt()
    {
        return $this->belongsTo(Receipts::class, 'bdc_receipt_id');
    }

    public function apartment()
    {
        return $this->belongsTo(Apartments::class, 'bdc_apartment_id');
    }

    public static function total_so_du($buildingId,$ApartmentId)
    {
        $total_tien_thua = self::whereNull('bdc_bill')->where(['bdc_building_id'=>$buildingId,'bdc_apartment_id'=>$ApartmentId ,'type_payment'=>self::tien_thua])->sum('cost_paid');

        return $total_tien_thua;
    }
    public static function total_all_so_du($buildingId)
    {
        $total_tien_thua = self::whereNull('bdc_bill')->where(['bdc_building_id'=>$buildingId,'type_payment'=>self::tien_thua])->sum('cost_paid');

        return $total_tien_thua;
    }
}
