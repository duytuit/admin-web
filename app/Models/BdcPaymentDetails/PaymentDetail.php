<?php

namespace App\Models\BdcPaymentDetails;

use App\Models\Apartments\Apartments;
use App\Models\BdcBills\Bills;
use App\Models\BdcDebitDetail\DebitDetail;
use App\Models\BdcReceipts\Receipts;
use App\Models\PublicUser\Users;
use App\Models\Service\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Traits\ActionByUser;

class PaymentDetail extends Model // chi tiết phiếu thu
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'bdc_payment_details';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }

    public function apartment()
    {
        return $this->belongsTo(Apartments::class, 'bdc_apartment_id');
    }

    public function receipt()
    {
        return $this->belongsTo(Receipts::class, 'bdc_receipt_id');
    }

    public function debitdetail(){
        return $this->belongsTo(DebitDetail::class, 'bdc_debit_detail_id');
    }

    public function service(){
        return $this->belongsTo(Service::class, 'bdc_service_id');
    }

    public static function check_total_cost($building_id, $apartment_id, $service_id = 0 , $cycle_name = null){
        $thu = self::select()->where(function($query) use($building_id, $apartment_id, $service_id, $cycle_name){
                   if($service_id > 0){  // có chỉ định
                       $query->where(['bdc_building_id'=>$building_id]);
                       $query->where(['bdc_apartment_id'=>$apartment_id]);
                       $query->where(['bdc_service_id'=>$service_id]);
                   }else{                  // không chỉ định
                      $query->where(['bdc_building_id'=>$building_id]);
                      $query->where(['bdc_apartment_id'=>$apartment_id]);
                      $query->whereNull('bdc_service_id');
                      $query->whereNull('bdc_bill_id');
                      $query->whereNull('bdc_debit_detail_id');
                   }
                   if($cycle_name){
                       $query->where(['cycle_name'=>$cycle_name]);
                   }
        })->where('type','add')->sum('cost');
        $xuat = self::select()->where(function($query) use($building_id, $apartment_id, $service_id, $cycle_name){
            if($service_id > 0){  // có chỉ định
                $query->where(['bdc_building_id'=>$building_id]);
                $query->where(['bdc_apartment_id'=>$apartment_id]);
                $query->where(['bdc_service_id'=>$service_id]);
            }else{                  // không chỉ định
               $query->where(['bdc_building_id'=>$building_id]);
               $query->where(['bdc_apartment_id'=>$apartment_id]);
               $query->whereNull('bdc_service_id');
               $query->whereNull('bdc_bill_id');
               $query->whereNull('bdc_debit_detail_id');
            }
            if($cycle_name){
                $query->where(['cycle_name'=>$cycle_name]);
            }
        })->where('type','sub')->sum('cost');
        return $thu - $xuat;
    }

    public static function sub($building_id, $apartment_id, $receipt_id = null, $service_id, $bill_id, $debitDetail_id, $paid, $typePayment, $cycle_name, $createdDate, $user_id = null){
        return self::create([
            'user_id' => $user_id,
            'bdc_building_id' => $building_id,
            'bdc_apartment_id' => $apartment_id,
            'bdc_receipt_id' => $receipt_id ? $receipt_id : null,
            'bdc_service_id' => $service_id > 0 ? $service_id : null,
            'bdc_bill_id' =>  $service_id > 0 ? $bill_id : null,
            'bdc_debit_detail_id' =>  $service_id > 0 ? $debitDetail_id : null,
            'cost' => $paid,
            'type_payment' => $typePayment,
            'type' => 'sub', // xuất 
            'cycle_name' => $cycle_name,
            'create_date' => $createdDate,
        ]);
    }

    public static function add($building_id, $apartment_id, $receipt_id = null, $service_id, $bill_id, $debitDetail_id, $paid, $typePayment, $cycle_name, $createdDate , $user_id = null){
        return self::create([
            'user_id' => $user_id,
            'bdc_building_id' => $building_id,
            'bdc_apartment_id' => $apartment_id,
            'bdc_receipt_id' => $receipt_id ? $receipt_id : null,
            'bdc_service_id' =>  $service_id > 0 ? $service_id : null,
            'bdc_bill_id' =>  $service_id > 0 ? $bill_id : null,
            'bdc_debit_detail_id' =>  $service_id > 0 ? $debitDetail_id : null,
            'cost' => $paid,
            'type_payment' => $typePayment,
            'type' => 'add', // thu 
            'cycle_name' => $cycle_name,
            'create_date' => $createdDate,
        ]);
    }


}
