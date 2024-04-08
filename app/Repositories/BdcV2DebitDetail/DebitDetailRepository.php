<?php
/*
 * create by tandc
 * */

namespace App\Repositories\BdcV2DebitDetail;

use App\Exceptions\QueueRedis;
use App\Helpers\dBug;
use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Models\BdcBills\Bills;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\BdcV2LogCoinDetail\LogCoinDetailRepository;
use App\Repositories\BdcV2PaymentDetail\PaymentDetailRepository;
use App\Repositories\Building\BuildingRepository;
use App\Repositories\Eloquent\Repository;
use App\Util\Debug\Log;
use App\Util\Redis;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use App\Models\BdcV2DebitDetail\DebitDetail;
use App\Models\Service\Service;
use App\Repositories\BdcBills\V2\BillRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DebitDetailRepository extends Repository
{
    function model()
    {
        return DebitDetail::class;
    }

    /**
     * clearCache : xóa cache
     * @param $apartmentId integer
     * @param $service_price_id integer
     * @param $cycle_name
     * @return boolean
     */
    public static function clearCache(int $apartmentId, int $service_price_id, $cycle_name, $id): bool
    {
        $keyCache = "getDebitByApartmentAndServiceAndCyclename_" . $apartmentId . '_' . $service_price_id . '_' . $cycle_name;
        Cache::forget($keyCache);
        $keyCache = "getDebitByApartmentAndServiceAndCyclenameWithTrashed_" . $apartmentId . '_' . $service_price_id . '_' . $cycle_name;
        Cache::forget($keyCache);
        $keyCache = "getInfoDebitById_" . $id;
        Cache::forget($keyCache);
//        $keyCache = "getDebitByApartmentAndServiceAndCyclenameCus_" . $apartmentId . '_' . $service_price_id . '_' . $cycle_name. '_' . $oper;
        return true;
    }

    /**
     * updateDebit : cập nhật lại công nợ
     * @param int $id
     * @param int $paid
     * @param int $paid_by_cycle_name
     * @param string $url_image
     * @return mixed
     */

    public static function updateDebit(int $id, $paid = false, $paid_by_cycle_name = false, $url_image = false, $before_cycle_name = false, $after_cycle_name = false)
    {
        $update = DebitDetail::withTrashed()->find($id);
        if (!$update) return false;
        $paid !== false && $update->paid = $paid;
        $paid_by_cycle_name !== false && $update->paid_by_cycle_name = $paid_by_cycle_name;
        $url_image !== false && $update->image = $url_image;
        $before_cycle_name !== false && $update->before_cycle_name = $before_cycle_name;
        $after_cycle_name !== false && $update->after_cycle_name = $after_cycle_name;
        $update->deleted_at = null;
        self::clearCache($update->bdc_apartment_id, $update->bdc_apartment_service_price_id, $update->cycle_name, $id);
        return $update->save();
    }

    public static function updateDebitRestore(int $id, $bdc_bill_id = false, $from_date = false, $to_date = false, $detail = false, $previous_owed = false, $cycle_name, $quantity = false, $price = false, $sumery = false, $discount = false, $discount_type = false, $paid = false, $paid_by_cycle_name = false, $image = false, $discount_note = false, $note = false, $promotion_id = false, $promotion_apartment_id = false)
    {
        $update = DebitDetail::find($id);
        if (!$update) return false;
        $bdc_bill_id !== false && $update->bdc_bill_id = $bdc_bill_id;
        $from_date !== false && $update->from_date = $from_date;
        $to_date !== false && $update->to_date = $to_date;
        $detail !== false && $update->detail = $detail;
        $previous_owed !== false && $update->previous_owed = $previous_owed;
        $cycle_name !== false && $update->cycle_name = $cycle_name;
        $quantity !== false && $update->quantity = $quantity;
        $price !== false && $update->price = $price;
        $sumery !== false && $update->sumery = $sumery;
        $discount !== false && $update->discount = $discount;
        $discount_type !== false && $update->discount_type = $discount_type;
        $paid !== false && $update->paid = $paid;
        $paid_by_cycle_name !== false && $update->paid_by_cycle_name = $paid_by_cycle_name;
        $image !== false && $update->image = $image;
        $discount_note !== false && $update->discount_note = $discount_note;
        $note !== false && $update->note = $note;
        $promotion_id !== false && $update->promotion_id = $discount == false ? $promotion_id : 0;
        $promotion_apartment_id !== false && $update->promotion_apartment_id = $discount == false ? $promotion_apartment_id : 0;
        self::clearCache($update->bdc_apartment_id, $update->bdc_apartment_service_price_id, $update->cycle_name, $id);
        return $update->save();
    }

    /**
     * createOrUpdateDebit : thêm công nợ mới hoặc cập nhật
     * @param int $bdc_building_id
     * @param int $bdc_apartment_id
     * @param int $bdc_bill_id
     * @param int $bdc_apartment_service_price_id
     * @param int $cycle_name
     * @param $from_date
     * @param $to_date
     * @param $detail
     * @param int $quantity
     * @param int $price
     * @param int $sumery
     * @param int $previous_owed
     * @param int $discount
     * @param int $discount_type
     * @param string $discount_note
     * @param int $paid
     * @param int $paid_by_cycle_name
     * @param string $url_image
     * @return mixed
     */
    public static function createOrUpdateDebit(int $bdc_building_id, int $bdc_apartment_id, int $bdc_bill_id, int $bdc_apartment_service_price_id, int $cycle_name, $from_date, $to_date, $detail, int $quantity, int $price, int $sumery, int $previous_owed, int $discount = 0, int $discount_type = 0, string $discount_note = "", int $paid = 0, int $paid_by_cycle_name = 0, string $url_image = "")
    {
        $checkExist = self::getDebitByApartmentAndServiceAndCyclename($bdc_apartment_id, $bdc_apartment_service_price_id, $cycle_name);
        if ($checkExist) {
            self::updateDebit($checkExist->id, $checkExist->paid + $paid, $checkExist->paid_by_cycle_name + $paid_by_cycle_name, $url_image);
        } else {
            self::createDebit($bdc_building_id, $bdc_apartment_id, $bdc_bill_id, $bdc_apartment_service_price_id, $cycle_name, $from_date, $to_date, $detail, $quantity, $price, $sumery, $previous_owed, $discount, $discount_type, $discount_note, $paid, $paid_by_cycle_name, $url_image);
        }
    }

    /**
     * createDebit : thêm công nợ mới
     * @param int $bdc_building_id
     * @param int $bdc_apartment_id
     * @param int $bdc_bill_id
     * @param int $bdc_apartment_service_price_id
     * @param int $cycle_name
     * @param $from_date
     * @param $to_date
     * @param $detail
     * @param int $quantity
     * @param int $price
     * @param int $sumery
     * @param int $previous_owed
     * @param int $discount
     * @param int $discount_type
     * @param string $discount_note
     * @param int $paid
     * @param int $paid_by_cycle_name
     * @param string $url_image
     * @return mixed
     */

    public static function createDebit(int $bdc_building_id, int $bdc_apartment_id, int $bdc_bill_id, int $bdc_apartment_service_price_id, int $cycle_name, $from_date, $to_date, $detail, int $quantity, int $price, int $sumery, int $previous_owed, int $discount = 0, int $discount_type = 0, string $discount_note = "", int $paid = 0, int $paid_by_cycle_name = 0, string $url_image = "", int $promotion_id = 0, int $promotion_apartment_id = 0)
    {
        $checkExist = self::getDebitByApartmentAndServiceAndCyclename($bdc_apartment_id, $bdc_apartment_service_price_id, $cycle_name);

        if ($bdc_apartment_service_price_id === 0) { // thông tin tiền thừa
            $totalByCycleName = LogCoinDetailRepository::getSumPaidLogCoin($bdc_apartment_id, $bdc_apartment_service_price_id, $cycle_name, false);
        } else {
            $totalByCycleName = PaymentDetailRepository::getSumPaidByCycleNameFromReceipt($bdc_apartment_id, $bdc_apartment_service_price_id, $cycle_name);
        }
        $temp_dau_ky = self::getTotalSumeryByCycleNameApartmentServiceCus($bdc_building_id, $bdc_apartment_id, $bdc_apartment_service_price_id, $cycle_name, "<"); // lấy số liệu đầu kỳ
        $dauky_da_thanhtoan = PaymentDetailRepository::getSumPaidByCycleNameFromReceiptCus($bdc_apartment_id, $bdc_apartment_service_price_id, $cycle_name, "<"); // lấy số liệu đã thanh toán đầu kỳ

        if (isset($temp_dau_ky->tong_phat_sinh)) {
            $before_cycle_name = $temp_dau_ky->tong_phat_sinh - $dauky_da_thanhtoan;
        } else {
            $before_cycle_name = $dauky_da_thanhtoan ? -$dauky_da_thanhtoan : 0;
        }
        $getBill = $bdc_bill_id ? BillRepository::getBillById($bdc_bill_id) : false;
        $checkBill = false;
        $sumery_temp = $sumery;
        if ($bdc_bill_id && $getBill) {
            if ($getBill->status >= -2) $checkBill = true;
        }
        if (!$checkBill) {
            $sumery_temp = 0;
        }
        $after_cycle_name = $before_cycle_name + $sumery_temp - $totalByCycleName;

        if ($checkExist && $checkExist->bdc_bill_id == 0) { // cập nhật thông tin debit trường hợp đã tạo debit trước đó
            $update = DebitDetail::find($checkExist->id);
            if (!$update) return false;
            $update->bdc_bill_id = $bdc_bill_id;
            $update->from_date = $from_date;
            $update->to_date = $to_date;
            $update->detail = $detail;
            $update->previous_owed = $previous_owed;
            $update->quantity = $quantity;
            $update->price = $price;
            $update->sumery = $sumery;
            $update->discount = $discount;
            $update->discount_type = $discount_type;
            $update->discount_note = $discount_note;
            $update->paid = $paid;
            $update->before_cycle_name = $before_cycle_name;
            $update->after_cycle_name = $after_cycle_name;
            $update->promotion_id = $promotion_id;
            $update->promotion_apartment_id = $promotion_apartment_id;
            $update->deleted_at = null;
            self::clearCache($update->bdc_apartment_id, $update->bdc_apartment_service_price_id, $update->cycle_name, $checkExist->id);
            return $update->save();
        } else { // thêm mới
            return DebitDetail::create([
                'bdc_building_id' => $bdc_building_id,
                'bdc_apartment_id' => $bdc_apartment_id,
                'bdc_bill_id' => $bdc_bill_id,
                'bdc_apartment_service_price_id' => $bdc_apartment_service_price_id,
                'from_date' => $from_date,
                'to_date' => $to_date,
                'detail' => $detail,
                'previous_owed' => $previous_owed,
                'cycle_name' => $cycle_name,
                'quantity' => $quantity,
                'price' => $price,
                'sumery' => $sumery,
                'discount' => $discount,
                'discount_type' => $discount_type,
                'discount_note' => $discount_note,
                'paid' => $paid,
                'paid_by_cycle_name' => $paid_by_cycle_name,
                'before_cycle_name' => $before_cycle_name,
                'after_cycle_name' => $after_cycle_name,
                'image' => $url_image,
                'promotion_id' => $promotion_id,
                'promotion_apartment_id' => $promotion_apartment_id,
            ]);
        }
    }

    public static function findByBillId($billId)
    {
        return DebitDetail::where('bdc_bill_id', $billId)->get();
    }

    public static function checkStatusAppBKV2($bill_id)
    {
        $bill = Bills::find($bill_id);
        $debit = self::findByBillId($bill_id);
        $sumery = 0;
        $paid = 0;
        $now = Carbon::now();
        $deadline = Carbon::parse($bill->deadline);
        foreach ($debit as $value) {
            $sumery += (int)$value->sumery;
            $paid += (int)$value->paid;
        }
        $status = 4;
        switch ($bill->status) {
            case -3:
                $status = 6;
                break;
            case -2:
                if ($paid >= $sumery) {
                    $status = 1;
                } elseif ($deadline < $now && $sumery > $paid) {
                    $status = 2;
                } elseif ($deadline > $now && $paid > 0 && $paid < $sumery) {
                    $status = 3;
                } else {
                    $status = 5;
                }
                break;
            case 2:
                $status = 1;
                break;
            case 1:
                if ($paid >= $sumery) {
                    $status = 1;
                } elseif ($deadline < $now && $sumery > $paid) {
                    $status = 2;
                } else {
                    $status = 3;
                }
                break;
            default:
                $status = 4;
                break;
        }
        return $status;
    }

    public static function checkStatusAppV2($bill_id)
    {
        $bill = Bills::find($bill_id);
        $debit = self::findByBillId($bill_id);
        $sumery = 0;
        $paid = 0;
        $now = Carbon::now();
        $deadline = Carbon::parse($bill->deadline);
        foreach ($debit as $value) {
            $sumery += (int)$value->sumery;
            $paid += (int)$value->paid;
        }
        $status = 'Chưa có';
        switch ($bill->status) {
            case -3:
                $status = 'Chờ xác nhận';
                break;
            case -2:
                if ($paid >= $sumery) {
                    $status = 'Đã thanh toán';
                } elseif ($deadline < $now && $sumery > $paid) {
                    $status = 'Quá hạn';
                } elseif ($deadline > $now && $paid > 0 && $paid < $sumery) {
                    $status = 'Chờ thanh toán';
                } else {
                    $status = 'Chờ gửi';
                }
                break;
            case 2:
                $status = 'Đã thanh toán';
                break;
            case 1:
                if ($paid >= $sumery) {
                    $status = 'Đã thanh toán';
                } elseif ($deadline < $now && $sumery > $paid) {
                    $status = 'Quá hạn';
                } else {
                    $status = 'Chờ thanh toán';
                }
                break;
            default:
                $status = 'Chưa có';
                break;
        }
        $data = [
            'cost' => ($sumery - $paid),
            'status' => $status
        ];
        return $data;
    }

    public static function sumPaiByBill($billId)
    {
        return DebitDetail::where('bdc_bill_id', $billId)->sum('paid');
    }

    public function countDebitByBill($billId)
    {
        return DebitDetail::withTrashed()->where('bdc_bill_id', $billId)->whereHas('billwithTrashed', function ($query) {
            $query->where('status', '>=', -2);
        })->count();
    }

    public static function destroy_list($ids)
    {
        return DebitDetail::destroy($ids);
    }

    public static function sumByBillId($billId)
    {
        return DebitDetail::select(DB::raw('sum(sumery+discount) as tong_tien,sum(discount) as chiet_khau,sum(sumery) as thanh_tien,sum(paid) as thanh_toan'))->where('bdc_bill_id', $billId)->groupBy("bdc_bill_id")->first();
    }

    public static function sumByApartment($apartmentId)
    {
        return DebitDetail::select(DB::raw('sum(sumery+discount) as tong_tien,sum(discount) as chiet_khau,sum(sumery) as thanh_tien,sum(paid) as thanh_toan'))
            ->whereHas('bill', function ($query) {
                $query->where('status', '>=', -2);
            })
            ->where('bdc_apartment_id', $apartmentId)->groupBy("bdc_apartment_id")->first();
    }

    public static function getAllByBillId(array $billIds)
    {
        return DebitDetail::whereIn('bdc_bill_id', $billIds)->get();
    }

    public static function getByApartmentIdOrderByIndexAccounting($buildingId, $apartmentId)
    {
        $apartmentServiceIds = ApartmentServicePrice::where(['bdc_apartment_service_price.bdc_building_id' => $buildingId, 'bdc_apartment_id' => $apartmentId])->where('status', 1)->select('id', 'bdc_service_id')->get();
        $service = Service::where(['bdc_building_id' => $buildingId])->orderBy('index_accounting')->get();
        $debit_detail = DebitDetail::where(['bdc_building_id' => $buildingId, 'bdc_apartment_id' => $apartmentId])
            ->whereHas('bill', function ($query) {
                $query->where('status', '>=', -2);
            })
            ->whereRaw('bdc_v2_debit_detail.sumery - bdc_v2_debit_detail.paid > 0')
            ->get();
        $array_apartment_service = null;
        foreach ($service as $key => $value) {
            $check = $apartmentServiceIds->where('bdc_service_id', $value->id);
            if ($check->count() == 0) continue;
            foreach ($check as $key => $value_1) {
                $check_debit = $debit_detail->where('bdc_apartment_service_price_id', $value_1->id)->sortBy('created_at');
                if ($check_debit->count() > 0)
                    foreach ($check_debit as $key => $value_2) {
                        $array_apartment_service[] = $value_2;
                    }

            }
        }

        return $array_apartment_service;
    }

    public static function getByApartmentIdAndServiceApartment($buildingId, $apartmentId, $ServiceId)
    {
        $array_apartment_service = null;
        $check_apartment_service = ApartmentServicePrice::where(['bdc_apartment_service_price.bdc_building_id' => $buildingId, 'bdc_apartment_id' => $apartmentId, 'status' => 1])->where('id', $ServiceId)->select('id', 'bdc_service_id')->first();
        if ($check_apartment_service) {   // nếu là ID căn hộ dịch vụ
            $debit_detail = DebitDetail::where(['bdc_building_id' => $buildingId, 'bdc_apartment_id' => $apartmentId])
                ->whereHas('bill', function ($query) {
                    $query->where('status', '>=', -2);
                })
                ->whereRaw('bdc_v2_debit_detail.sumery - bdc_v2_debit_detail.paid > 0')
                ->get();


            $check_debit = $debit_detail->where('bdc_apartment_service_price_id', $check_apartment_service->id);
            if ($check_debit && $check_debit->count() > 0)
                foreach ($check_debit as $key => $value_2) {
                    $array_apartment_service[] = $value_2;
                }

        } else {                            // nếu dịch vụ tòa nhà
            $apartmentServiceIds = ApartmentServicePrice::where(['bdc_apartment_service_price.bdc_building_id' => $buildingId, 'bdc_apartment_id' => $apartmentId, 'bdc_service_id' => $ServiceId])->select('id', 'bdc_service_id')->first();
            $debit_detail = DebitDetail::where(['bdc_building_id' => $buildingId, 'bdc_apartment_id' => $apartmentId])
                ->whereHas('bill', function ($query) {
                    $query->where('status', '>=', -2);
                })
                ->whereRaw('bdc_v2_debit_detail.sumery - bdc_v2_debit_detail.paid > 0')
                ->get();
            if ($apartmentServiceIds) {
                $check_debit = $debit_detail->where('bdc_apartment_service_price_id', $apartmentServiceIds->id);
                if ($check_debit->count() > 0)
                    foreach ($check_debit as $key => $value_2) {
                        $array_apartment_service[] = $value_2;
                    }
            }

        }
        return $array_apartment_service;
    }

    public static function getServiceApartment($buildingId, $apartmentId, $ServiceId)
    {
        $check_apartment_service = ApartmentServicePrice::where(['bdc_apartment_service_price.bdc_building_id' => $buildingId, 'bdc_apartment_id' => $apartmentId, 'status' => 1])->where('id', $ServiceId)->select('id', 'bdc_service_id')->first();
        if ($check_apartment_service) {
            return $check_apartment_service->id;
        } else {
            $apartmentServiceId = ApartmentServicePrice::where(['bdc_apartment_service_price.bdc_building_id' => $buildingId, 'bdc_apartment_id' => $apartmentId, 'bdc_service_id' => $ServiceId])->select('id', 'bdc_service_id')->first();
            if (!$apartmentServiceId) {
                return 0;
            }
            return $apartmentServiceId->id;
        }
    }

    public static function getInfoDebitById($id)
    {
        $keyCache = "getInfoDebitById_" . $id;
        return Cache::remember($keyCache, 10, function () use ($keyCache, $id) {
            $rs = DebitDetail::find($id);
            if (!$rs) return null;
            return (object)$rs->toArray();
        });
    }

    public static function findByBuildingApartmentServiceId($buildingId, $apartmentId, $serviceId)
    {
        $currentDate = Carbon::now();
        $prevousDate = $currentDate->subDay(25);
        return DebitDetail::where([
            'bdc_building_id' => $buildingId,
            'bdc_apartment_id' => $apartmentId,
            'bdc_apartment_service_price_id' => $serviceId,
        ])
            ->whereDate('to_date', '<', $prevousDate)
            ->where('sumery', '>', 0)
            ->orderBy('to_date', 'desc')
            ->first();
    }

    public static function findServiceCheckFromDate($apartmentId, $apartmentServicePriceId, $fromDate)
    {
        return DebitDetail::where([
            'bdc_apartment_id' => $apartmentId,
            'bdc_apartment_service_price_id' => $apartmentServicePriceId,
        ])
            ->whereNull('deleted_at')
            ->whereDate('to_date', '>', $fromDate)
            ->where('sumery', '>', 0)
            ->first();
    }

    public static function getDetailBillId($billId)
    {
        $debitDetails = self::findByBillId($billId);
        $debitDetailsResult['vehicle'] = [];
        $debitDetailsResult['service'] = [];
        $debitDetailsResult['other'] = [];
        $debitDetailsResult['first_price'] = [];
        $sumery_total = 0;
        foreach ($debitDetails as $key => $detail) {
            $apartmentServicePrice = ApartmentServicePrice::with('service', 'priceType', 'vehicle', 'progressive')->find($detail->bdc_apartment_service_price_id);
            if (!$apartmentServicePrice) {
                continue;
            }
            $sumery_total += $detail->sumery;
            $arrayDetail = $detail->toArray();
            $arrayDetail['apartmentServicePrice'] = $apartmentServicePrice;
            if ($apartmentServicePrice->bdc_price_type_id == 1) {
                if (@$apartmentServicePrice->vehicle) {
                    $debitDetailsResult['vehicle'][] = (object)$arrayDetail;
                } else {
                    $debitDetailsResult['service'][] = (object)$arrayDetail;
                }
            } else if ($apartmentServicePrice->bdc_price_type_id == 3) {
                $debitDetailsResult['first_price'][] = (object)$arrayDetail;

            } else {
                $debitDetailsResult['other'][] = (object)$arrayDetail;
                $debitDetailsResult['type'] = $apartmentServicePrice->service->type;
                $detail = json_decode(@$detail->detail);
            }
        }
        $debitDetailsResult['sumery_total'] = $sumery_total;
        return $debitDetailsResult;
    }

    public static function getDebitTypeServiceCycleName($buildingId, $apartmentId, $cycle_name, $type)// type = 3 là nước, type =5 là điện
    {
        if (!in_array($type, [3, 5, 6])) { // không phải là điện nước,nước nóng
            return null;
        }
        return DebitDetail::whereHas('apartmentServicePrice', function ($query) use ($type) {
            $query->where('bdc_price_type_id', '<>', 3);
            $query->whereHas('service', function ($query) use ($type) {
                $query->where('type', $type);
            });
        })->where('sumery', '>', 0)->where(['bdc_building_id' => $buildingId, 'bdc_apartment_id' => $apartmentId, 'cycle_name' => $cycle_name])->first();
    }

    public static function getCycleName($buildingId)
    {
        return DebitDetail::where('bdc_building_id', $buildingId)->select('cycle_name')->groupBy('cycle_name')->orderBy('created_at', 'DESC')->pluck('cycle_name')->toArray();
    }

    public static function getDebitByApartmentAndBuilding($buildingId, $apartmentId)
    {
        return DebitDetail::where(['bdc_building_id' => $buildingId, 'bdc_apartment_id' => $apartmentId])
            ->whereHas('bill', function ($query) {
                $query->where('status', '>=', -2);
            })
            ->whereRaw('bdc_v2_debit_detail.sumery - bdc_v2_debit_detail.paid > 0')->get();
    }

    public static function getDebitByApartmentAndServiceAndCyclename($apartment_id, $apartment_service_price_id, $cycle_name, $useCache = true)
    {
        $keyCache = "getDebitByApartmentAndServiceAndCyclename_" . $apartment_id . '_' . $apartment_service_price_id . '_' . $cycle_name;
        if (!$useCache) Cache::forget($keyCache);
        return Cache::remember($keyCache, 24 * 60 * 60, function () use ($keyCache, $apartment_id, $apartment_service_price_id, $cycle_name) {
            $rs = DebitDetail::withTrashed()->where(['bdc_apartment_id' => $apartment_id, 'bdc_apartment_service_price_id' => $apartment_service_price_id, 'cycle_name' => $cycle_name])->first();
            if (!$rs) return null;
            return (object)$rs->toArray();
        });
    }

    public static function getDebitByApartmentAndServiceAndCyclenameWithTrashed($apartment_id, $apartment_service_price_id, $cycle_name, $useCache = true)
    {
        $keyCache = "getDebitByApartmentAndServiceAndCyclenameWithTrashed_" . $apartment_id . '_' . $apartment_service_price_id . '_' . $cycle_name;
        if (!$useCache) Cache::forget($keyCache);
        return Cache::remember($keyCache, 24 * 60 * 60, function () use ($keyCache, $apartment_id, $apartment_service_price_id, $cycle_name) {
            $rs = DebitDetail::withTrashed()->where(['bdc_apartment_id' => $apartment_id, 'bdc_apartment_service_price_id' => $apartment_service_price_id, 'cycle_name' => $cycle_name])->first();
            if (!$rs) return null;
            return (object)$rs->toArray();
        });
    }

    public static function restoreDebitByApartmentAndServiceAndCyclename($debit)
    {
        self::clearCache($debit->bdc_apartment_id, $debit->bdc_apartment_service_price_id, $debit->cycle_name, $debit->id);
        return DebitDetail::withTrashed()->find($debit->id)->restore();
    }

    public static function getDebitByApartmentAndServiceAndCyclenameCus($apartment_id, $apartment_service_price_id, $cycle_name, $oper = "=", $useCache = true)
    {
        $keyCache = "getDebitByApartmentAndServiceAndCyclenameCus_" . $apartment_id . '_' . $apartment_service_price_id . '_' . $cycle_name . '_' . $oper;
        if (!$useCache) Cache::forget($keyCache);
        return Cache::remember($keyCache, 5, function () use ($keyCache, $apartment_id, $apartment_service_price_id, $cycle_name, $oper) {
            $rs = DebitDetail::where(['bdc_apartment_id' => $apartment_id, 'bdc_apartment_service_price_id' => $apartment_service_price_id]);
            if ($cycle_name) $rs->where("cycle_name", $oper, $cycle_name);
            $rs = $rs->get();
            if (!$rs) return null;
            return (object)$rs->toArray();
        });
    }

    /**
     * getDebitByBuilding : lấy thông tin chi tiết công nợ theo tòa
     * @param $buildingId integer
     * @return mixed
     * */

    public static function getDebitByBuilding($request, int $buildingId)
    {

        return DebitDetail::where(function ($query) use ($request,$buildingId) {
            if(\Auth::user()->isadmin !=1){
                $query->where('bdc_building_id', $buildingId);
            }
            if ($request->cycle_name) {
                $linksArray = array_filter($request->cycle_name);
                if (count($linksArray) > 0) {
                    $query->whereIn('cycle_name', $linksArray);
                }
            }
            if ($request->bdc_apartment_id) {
                $query->where('bdc_apartment_id', $request->bdc_apartment_id);
            }
            if ($request->bdc_bill_id) {
                $query->where('bdc_bill_id', $request->bdc_bill_id);
            }

            $query->whereHas('bill', function ($query) use ($request) {
                if ($request->bill_code) {
                    $query->where('bill_code', 'LIKE', '%' . $request->bill_code . '%');
                }
            });

            if ($request->service_group || $request->type_service || $request->bdc_service_id) {
                $query->whereHas('apartmentServicePrice.service', function ($query) use ($request) {

                    if ($request->service_group) {
                        $query->where('service_group', $request->service_group);
                    }
                    if ($request->type_service) {
                        $query->where('type', $request->type_service);
                    }
                    if ($request->bdc_service_id) {
                        $query->where('id', $request->bdc_service_id);
                    }
                });
            }
            if ($request->ip_place_id) {
                $query->whereHas('apartment', function ($query) use ($request) {
                    $query->where('building_place_id', $request->ip_place_id);
                });
            }
            if ($request->new_sumery && is_numeric($request->new_sumery)) {
                $new_sumery = $request->new_sumery;
                $query->whereRaw("sumery-paid > $new_sumery");
            }
        })->whereRaw("sumery+COALESCE(discount,0) >= 0")->orderBy('updated_at', 'desc');
    }

    /**
     * getDebitByBuilding : lấy thông tin chi tiết công nợ theo tòa
     * @param $buildingId integer
     * @return mixed
     * */

    public static function getDebitByApartment($request, int $buildingId)
    {
        return DebitDetail::where('bdc_building_id', $buildingId)->where(function ($query) use ($request) {
            if ($request->cycle_name) {
                $linksArray = array_filter($request->cycle_name);
                if (count($linksArray) > 0) {
                    $query->whereIn('cycle_name', $linksArray);
                }
            }
            if ($request->bdc_apartment_id) {
                $query->where('bdc_apartment_id', $request->bdc_apartment_id);
            }
            if ($request->bdc_bill_id) {
                $query->where('bdc_bill_id', $request->bdc_bill_id);
            }
            if ($request->bill_code) {
                $query->whereHas('bill', function ($query) use ($request) {
                    $query->where('bill_code', 'LIKE', '%' . $request->bill_code . '%');
                });
            }
            if ($request->service_group || $request->type_service || $request->bdc_service_id) {
                $query->whereHas('apartmentServicePrice.service', function ($query) use ($request) {

                    if ($request->service_group) {
                        $query->where('service_group', $request->service_group);
                    }
                    if ($request->type_service) {
                        $query->where('type', $request->type_service);
                    }
                    if ($request->bdc_service_id) {
                        $query->where('id', $request->bdc_service_id);
                    }
                });
            }
            if ($request->ip_place_id) {
                $query->whereHas('apartment', function ($query) use ($request) {
                    $query->where('building_place_id', $request->ip_place_id);
                });
            }
            if ($request->new_sumery) {
                $query->where('sumery', $request->new_sumery);
            }
        })->where('paid', '>', 0)->orderBy('updated_at', 'desc');
    }

    /**
     * sumTotalDebitByBuilding : lấy tổng thành tiền, Giảm trừ, thanh toán, còn nợ chi tiết công nợ theo tòa
     * @param $buildingId integer
     * @return mixed
     * */

    public static function sumTotalDebitByBuilding($request, int $buildingId)
    {
        return DebitDetail::select(DB::raw('sum(sumery) as tong_tien,sum(discount) as chiet_khau,sum(sumery) as thanh_tien,sum(paid) as thanh_toan'))->where('bdc_building_id', $buildingId)->where(function ($query) use ($request) {
            if ($request->cycle_name) {
                $linksArray = array_filter($request->cycle_name);
                if (count($linksArray) > 0) {
                    $query->whereIn('cycle_name', $linksArray);
                }
            }
            if ($request->bdc_apartment_id) {
                $query->where('bdc_apartment_id', $request->bdc_apartment_id);
            }
            if ($request->bdc_bill_id) {
                $query->where('bdc_bill_id', $request->bdc_bill_id);
            }

            $query->whereHas('bill', function ($query) use ($request) {
                if ($request->bill_code) {
                    $query->where('bill_code', 'LIKE', '%' . $request->bill_code . '%');
                }
            });

            if ($request->service_group || $request->type_service || $request->bdc_service_id) {
                $query->whereHas('apartmentServicePrice.service', function ($query) use ($request) {

                    if ($request->service_group) {
                        $query->where('service_group', $request->service_group);
                    }
                    if ($request->type_service) {
                        $query->where('type', $request->type_service);
                    }
                    if ($request->bdc_service_id) {
                        $query->where('id', $request->bdc_service_id);
                    }
                });
            }
            if ($request->ip_place_id) {
                $query->whereHas('apartment', function ($query) use ($request) {
                    $query->where('building_place_id', $request->ip_place_id);
                });
            }
            if ($request->new_sumery && is_numeric($request->new_sumery)) {
                $new_sumery = $request->new_sumery;
                $query->whereRaw("sumery-paid >= $new_sumery");
            }
        })->where('sumery', '<>', 0)->first();
    }

    // lấy tổng tiền theo cycle name
    public static function getTotalSumeryByCycleNameCus($buildingId, $apartment_id, $cycle_name, $oper = "=",$to_date, $from_date, $filterService = false, $useCache = true)
    {
        $rs = DebitDetail::where([
            "bdc_building_id" => $buildingId,
        ])->whereHas('bill', function ($query) {
            $query->where('status', '>=', -2);
        });

        if ($cycle_name) $rs->where("cycle_name", $oper, $cycle_name);
        if ($filterService) $rs->whereIn("bdc_apartment_service_price_id", $filterService);
        if ($apartment_id) $rs->where([
            "bdc_apartment_id" => $apartment_id,
        ]);
        if ($to_date)
        {  
            $rs->where('created_at','<=', Carbon::parse($to_date)->format('Y-m-d').' 23:59:59');
        }
        if ($from_date)
        {  
            $rs->where('created_at','>=', Carbon::parse($from_date)->format('Y-m-d') .' 00:00:00');
        }

        return $rs->select(DB::raw('SUM(paid) as tong_thanh_toan, SUM(sumery) as tong_phat_sinh, SUM(paid_by_cycle_name) as tong_thanh_toan_ky'))->first();
    }

    // lấy tổng tiền theo cycle name
    public static function getTotalSumeryByCycleNameCusNotStatusBill($buildingId, $apartment_id, $cycle_name, $oper = "=",$to_date, $from_date, $filterService = false, $useCache = true)
    {
        $rs = DebitDetail::where([
            "bdc_building_id" => $buildingId,
        ]);

        if ($cycle_name) $rs->where("cycle_name", $oper, $cycle_name);
        if ($filterService) $rs->whereIn("bdc_apartment_service_price_id", $filterService);

        if ($apartment_id) $rs->where([
            "bdc_apartment_id" => $apartment_id,
        ]);
        if ($to_date)
        {  
            $rs->where('created_at','<=', Carbon::parse($to_date)->format('Y-m-d').' 23:59:59');
        }
        if ($from_date)
        {  
            $rs->where('created_at','>=', Carbon::parse($from_date)->format('Y-m-d') .' 00:00:00');
        }
        return $rs->select(DB::raw('SUM(paid) as tong_thanh_toan, SUM(sumery) as tong_phat_sinh, SUM(paid_by_cycle_name) as tong_thanh_toan_ky'))->first();
    }

    // lấy tổng tiền theo cycle name
    public static function getTotalSumeryByMoreCycleNameCus($buildingId, $apartment_id, $cycle_name, $cycle_name_more,$to_date, $from_date, $filterService = false, $useCache = true)
    {
        $rs = DebitDetail::where([
            "bdc_building_id" => $buildingId,
        ])->whereHas('bill', function ($query) {
            $query->where('status', '>=', -2);
        });

        if ($cycle_name) $rs->where("cycle_name", ">=", $cycle_name);
        if ($cycle_name_more) $rs->where("cycle_name", "<=", $cycle_name_more);
        if ($filterService) $rs->whereIn("bdc_apartment_service_price_id", $filterService);

        if ($apartment_id) $rs->where([
            "bdc_apartment_id" => $apartment_id,
        ]);
        if ($to_date)
        {  
            $rs->where('created_at','<=', Carbon::parse($to_date)->format('Y-m-d').' 23:59:59');
        }
        if ($from_date)
        {  
            $rs->where('created_at','>=', Carbon::parse($from_date)->format('Y-m-d') .' 00:00:00');
        }

        return $rs->select(DB::raw('SUM(paid) as tong_thanh_toan, SUM(sumery) as tong_phat_sinh, SUM(paid_by_cycle_name) as tong_thanh_toan_ky'))->first();
    }

    public static function getTotalSumeryByMoreCycleNameCus1($buildingId, $apartment_id, $cycle_name, $cycle_name_more, $filterService = false, $useCache = true)
    {
        $rs = DebitDetail::where([
            "bdc_building_id" => $buildingId,
        ])->whereHas('bill', function ($query) {
            $query->where('status', '>=', -2);
        });

        if ($cycle_name) $rs->where("cycle_name", ">=", $cycle_name);
        if ($cycle_name_more) $rs->where("cycle_name", "<=", $cycle_name_more);
        if ($filterService) $rs->whereIn("bdc_apartment_service_price_id", $filterService);

        if ($apartment_id) $rs->where([
            "bdc_apartment_id" => $apartment_id,
        ]);

        return $rs->select(DB::raw('SUM(paid) as tong_thanh_toan, SUM(sumery) as tong_phat_sinh, SUM(paid_by_cycle_name) as tong_thanh_toan_ky'))->first();
    }

    // lấy tổng tiền theo cycle name
    public static function getTotalSumeryByMoreCycleNameCusNotStatusBill($buildingId, $apartment_id, $cycle_name, $cycle_name_more, $filterService = false, $useCache = true,$to_date, $from_date)
    {
        $rs = DebitDetail::where([
            "bdc_building_id" => $buildingId,
        ]);

        if ($cycle_name) $rs->where("cycle_name", ">=", $cycle_name);
        if ($cycle_name_more) $rs->where("cycle_name", "<=", $cycle_name_more);
        if ($filterService) $rs->whereIn("bdc_apartment_service_price_id", $filterService);

        if ($apartment_id) $rs->where([
            "bdc_apartment_id" => $apartment_id,
        ]);
        if ($to_date)
        {  
            $rs->where('create_date','<=', Carbon::parse($to_date)->format('Y-m-d').' 23:59:59');
        }
        if ($from_date)
        {  
            $rs->where('create_date','>=', Carbon::parse($from_date)->format('Y-m-d') .' 00:00:00');
        }

        return $rs->select(DB::raw('SUM(paid) as tong_thanh_toan, SUM(sumery) as tong_phat_sinh, SUM(paid_by_cycle_name) as tong_thanh_toan_ky'))->first();
    }
    public static function getTotalSumeryByMoreCycleNameCusNotStatusBill1($buildingId, $apartment_id, $cycle_name, $cycle_name_more, $filterService = false, $useCache = true)
    {
        $rs = DebitDetail::where([
            "bdc_building_id" => $buildingId,
        ]);

        if ($cycle_name) $rs->where("cycle_name", ">=", $cycle_name);
        if ($cycle_name_more) $rs->where("cycle_name", "<=", $cycle_name_more);
        if ($filterService) $rs->whereIn("bdc_apartment_service_price_id", $filterService);

        if ($apartment_id) $rs->where([
            "bdc_apartment_id" => $apartment_id,
        ]);

        return $rs->select(DB::raw('SUM(paid) as tong_thanh_toan, SUM(sumery) as tong_phat_sinh, SUM(paid_by_cycle_name) as tong_thanh_toan_ky'))->first();
    }

    // lấy tổng tiền theo cycle name căn hộ
    public static function getTotalSumeryByCycleNameApartmentServiceCus($buildingId, $apartment_id, $service_price_id, $cycle_name, $oper = "=", $useCache = true)
    {
        $rs = DebitDetail::where([
            "bdc_building_id" => $buildingId,
        ])->whereHas('bill', function ($query) {
            $query->where('status', '>=', -2);
        });

        if ($cycle_name) $rs->where("cycle_name", $oper, $cycle_name);
        $rs->where("bdc_apartment_service_price_id", $service_price_id);

        if ($apartment_id) $rs->where([
            "bdc_apartment_id" => $apartment_id,
        ]);

        return $rs->select(DB::raw('SUM(paid) as tong_thanh_toan, SUM(sumery) as tong_phat_sinh, SUM(paid_by_cycle_name) as tong_thanh_toan_ky'))->groupBy('bdc_apartment_id')->first();
    }

    // lấy tổng tiền theo cycle name căn hộ
    public static function getTotalSumeryByCycleNameMoreApartmentServiceCus($buildingId, $apartment_id, $service_price_id, $cycle_name, $cycle_name_more, $useCache = true)
    {
        $rs = DebitDetail::where([
            "bdc_building_id" => $buildingId,
        ])->whereHas('bill', function ($query) {
            $query->where('status', '>=', -2);
        });
        if ($cycle_name) $rs->where("cycle_name", ">=", $cycle_name);
        if ($cycle_name_more) $rs->where("cycle_name", "<=", $cycle_name_more);
        $rs->where("bdc_apartment_service_price_id", $service_price_id);

        if ($apartment_id) $rs->where([
            "bdc_apartment_id" => $apartment_id,
        ]);

        return $rs->select(DB::raw('SUM(paid) as tong_thanh_toan, SUM(sumery) as tong_phat_sinh, SUM(paid_by_cycle_name) as tong_thanh_toan_ky'))->groupBy('bdc_apartment_id')->first();
    }

    // lấy tổng tiền theo cycle name căn hộ
    public static function getTotalSumeryByCycleNameMoreApartmentServiceCusNotStatusBill($buildingId, $apartment_id, $service_price_id, $cycle_name, $cycle_name_more, $useCache = true)
    {
        $rs = DebitDetail::where([
            "bdc_building_id" => $buildingId,
        ]);
        if ($cycle_name) $rs->where("cycle_name", ">=", $cycle_name);
        if ($cycle_name_more) $rs->where("cycle_name", "<=", $cycle_name_more);
        $rs->where("bdc_apartment_service_price_id", $service_price_id);

        if ($apartment_id) $rs->where([
            "bdc_apartment_id" => $apartment_id,
        ]);

        return $rs->select(DB::raw('SUM(paid) as tong_thanh_toan, SUM(sumery) as tong_phat_sinh, SUM(paid_by_cycle_name) as tong_thanh_toan_ky'))->groupBy('bdc_apartment_id')->first();
    }

    // lấy tất cả debit theo kỳ nếu có
    public static function getDebitByCycleName($buildingId, $cycle_name = false, $perPage, $page)
    {
        $rs = DebitDetail::where([
            "bdc_building_id" => $buildingId,
        ]);
        if ($cycle_name) $rs->where([
            "cycle_name" => $cycle_name,
        ]);
        return $rs->orderBy('bdc_apartment_id', 'desc')->paginate($perPage, ['*'], 'page', $page);
    }

    /*
     *  ------ clone phiên bản cũ --------
     * */

    public function getCycleNameV2($buildingId)
    {
        return $this->model->where('bdc_building_id', $buildingId)->groupBy('cycle_name')->orderBy('cycle_name', 'DESC')->pluck('cycle_name')->toArray();
    }

    /**
     * getAllApartmentDetailLastTime : lấy tất cả căn hộ theo kỳ và nhóm lấy mới nhất
     * @param $buildingId
     * @param mixed $cycle_name
     * @param $bdc_apartment_id
     * @param $filterMin
     * @param $filterService
     * @param $perPage
     * @param $page
     * @return boolean
     */

    public static function getAllApartmentDetailLastTime($buildingId, $cycle_name, $bdc_apartment_id, $filterMin, $filterService, $perPage, $page)
    {
        $arrWhere = [
            "bdc_building_id" => $buildingId
        ];
        $bdc_apartment_id && $arrWhere["bdc_apartment_id"] = $bdc_apartment_id;
        $resutl_query = DebitDetail::where($arrWhere)->where("cycle_name", "<=", $cycle_name)
            ->whereNull('deleted_at')
            ->groupBy('bdc_apartment_id', 'bdc_apartment_service_price_id')
            ->select(DB::raw('MAX(id) as id, SUM(sumery) as tong_phat_sinh, SUM(paid_by_cycle_name) as tong_thanh_toan_ky, SUM(sumery-paid_by_cycle_name) as cuoi_ky'));
        if ($filterService) $resutl_query->whereIn('bdc_apartment_service_price_id', $filterService);
        $listId = $resutl_query->get();
        if (!$listId->pluck('id')->toArray()) return null;
        if ($filterMin != false) {
            $listIds = [];
            array_filter($listId->toArray(), function ($item) use ($filterMin, &$listIds) {
                if ((int)$item['cuoi_ky'] >= (int)$filterMin) $listIds[] = $item['id'];
                return (int)$item['cuoi_ky'] >= (int)$filterMin;
            });
        } else {
            $listIds = $listId->pluck('id')->toArray();
        }
        $resutl_query = DebitDetail::whereIn('id', $listIds)->orderBy('bdc_apartment_id', 'desc')->paginate($perPage, ['*'], 'page', $page);
        return $resutl_query;
    }

    public static function getAllApartmentDetailLastTime2($buildingId, $cycle_name, $bdc_apartment_id, $filterMin, $filterService, $perPage, $page)
    {
        $sql1 = "SELECT * FROM bdc_v2_debit_detail WHERE deleted_at is null AND bdc_building_id = :buildingId1 ";
        $sql2 = "SELECT  max(cycle_name) AS cycle_name, bdc_apartment_id, bdc_apartment_service_price_id FROM bdc_v2_debit_detail WHERE deleted_at is null AND bdc_building_id = :buildingId2 ";

        $arrBind = [
            "buildingId1" => $buildingId,
            "buildingId2" => $buildingId,
        ];

        if ($bdc_apartment_id) {
            $sql1 .= " AND bdc_apartment_id= :bdcApartmentId1 ";
            $sql2 .= " AND bdc_apartment_id= :bdcApartmentId2 ";
            $arrBind["bdcApartmentId1"] = $bdc_apartment_id;
            $arrBind["bdcApartmentId2"] = $bdc_apartment_id;
        }

        if ($cycle_name) {
            $sql1 .= " AND cycle_name <= :cycleName1 ";
            $sql2 .= " AND cycle_name <= :cycleName2 ";
            $arrBind["cycleName1"] = $cycle_name;
            $arrBind["cycleName2"] = $cycle_name;
        }

        if ($filterMin !== false) {
            $sql1 .= " AND after_cycle_name >= :filterMin ";
            $arrBind["filterMin"] = $filterMin;
        }

        if ($filterService) {
            $sql1 .= " AND bdc_apartment_service_price_id IN ('" . implode("','", $filterService) . "') ";
            $sql2 .= " AND bdc_apartment_service_price_id IN ('" . implode("','", $filterService) . "') ";
//            $arrBind["filterService1"] = "'".implode("','", $filterService)."'";
//            $arrBind["filterService2"] = "'".implode("','", $filterService)."'";
        }
//        ->paginate($perPage, ['*'],'page',$page)
        $sql2 .= " GROUP BY bdc_apartment_id,bdc_apartment_service_price_id";
        $sql = "SELECT t1.* FROM (" . $sql1 . ") as t1 INNER JOIN ( " . $sql2 . " ) as t2 ON t1.bdc_apartment_service_price_id = t2.bdc_apartment_service_price_id AND t1.bdc_apartment_id = t2.bdc_apartment_id AND t1.cycle_name = t2.cycle_name";

        $data = DB::select((DB::raw($sql)), $arrBind);

        $collect = collect($data);
        $paginationData = new LengthAwarePaginator(
            $collect->forPage($page, $perPage),
            $collect->count(),
            $perPage,
            $page
        );
        return $paginationData;
    }

    public static function getAllApartmentDetailLastTime4($buildingId, $cycle_name, $planceId, $bdc_apartment_id, $filterMin, $filterService, $perPage, $page)
    {
        $sql1 = "SELECT * FROM bdc_v2_debit_detail WHERE deleted_at is null AND bdc_building_id = " . $buildingId . " ";
        $sql2 = "SELECT  max(cycle_name) AS cycle_name, bdc_apartment_id, bdc_apartment_service_price_id FROM bdc_v2_debit_detail WHERE deleted_at is null AND bdc_building_id = " . $buildingId . " ";

        if ($planceId) {

            $sql1 .= " AND bdc_apartment_id IN ('" . implode("','", $planceId) . "') ";
            $sql2 .= " AND bdc_apartment_id IN ('" . implode("','", $planceId) . "') ";

        }

        if ($bdc_apartment_id) {
            $sql1 .= " AND bdc_apartment_id= " . $bdc_apartment_id . " ";
            $sql2 .= " AND bdc_apartment_id= " . $bdc_apartment_id . " ";
        }

        if ($cycle_name) {
            $sql1 .= " AND cycle_name <= " . $cycle_name . " ";
            $sql2 .= " AND cycle_name <= " . $cycle_name . " ";
        }

        if ($filterMin !== false) {
            $sql1 .= " AND after_cycle_name >= " . $filterMin . " ";
        }

        if ($filterService) {
            $sql1 .= " AND bdc_apartment_service_price_id IN ('" . implode("','", $filterService) . "') ";
            $sql2 .= " AND bdc_apartment_service_price_id IN ('" . implode("','", $filterService) . "') ";
        }
        $sql2 .= " GROUP BY bdc_apartment_id,bdc_apartment_service_price_id";
        $sql = "SELECT t1.* FROM (" . $sql1 . ") as t1 INNER JOIN ( " . $sql2 . " ) as t2 ON t1.bdc_apartment_service_price_id = t2.bdc_apartment_service_price_id AND t1.bdc_apartment_id = t2.bdc_apartment_id AND t1.cycle_name = t2.cycle_name";

        return DB::table(DB::raw("($sql) as sub"))->where("cycle_name", "=", $cycle_name)->orWhere([
            ['cycle_name', '<', $cycle_name],
            ['after_cycle_name', '!=', 0]
        ])->paginate($perPage, ['*'], 'page', $page);
    }

    public static function getAllApartmentDetailLastTime3($buildingId, $cycle_name, $bdc_apartment_id, $filterMin, $filterService, $perPage, $page)
    {
        $sql1 = "SELECT * FROM bdc_v2_debit_detail WHERE deleted_at is null AND bdc_building_id = " . $buildingId . " ";
        $sql2 = "SELECT  max(cycle_name) AS cycle_name, bdc_apartment_id, bdc_apartment_service_price_id FROM bdc_v2_debit_detail WHERE deleted_at is null AND bdc_building_id = " . $buildingId . " ";

        if ($bdc_apartment_id) {
            $sql1 .= " AND bdc_apartment_id= " . $bdc_apartment_id . " ";
            $sql2 .= " AND bdc_apartment_id= " . $bdc_apartment_id . " ";
        }

        if ($cycle_name) {
            $sql1 .= " AND cycle_name = " . $cycle_name . " ";
            $sql2 .= " AND cycle_name = " . $cycle_name . " ";
        }

        if ($filterMin !== false) {
            $sql1 .= " AND after_cycle_name >= " . $filterMin . " ";
        }

        if ($filterService) {
            $sql1 .= " AND bdc_apartment_service_price_id IN ('" . implode("','", $filterService) . "') ";
            $sql2 .= " AND bdc_apartment_service_price_id IN ('" . implode("','", $filterService) . "') ";
        }
        $sql2 .= " GROUP BY bdc_apartment_id,bdc_apartment_service_price_id";
        $sql = "SELECT t1.* FROM (" . $sql1 . ") as t1 INNER JOIN ( " . $sql2 . " ) as t2 ON t1.bdc_apartment_service_price_id = t2.bdc_apartment_service_price_id AND t1.bdc_apartment_id = t2.bdc_apartment_id AND t1.cycle_name = t2.cycle_name";
        return DB::table(DB::raw("($sql) as sub"))->paginate($perPage, ['*'], 'page', $page);
    }

    public static function getAllApartmentDetailLastTime3Export($buildingId, $cycle_name, $planceId, $bdc_apartment_id, $filterMin, $filterService)
    {
        $sql1 = "SELECT * FROM bdc_v2_debit_detail WHERE deleted_at is null AND bdc_building_id = " . $buildingId . " ";
        $sql2 = "SELECT  max(cycle_name) AS cycle_name, bdc_apartment_id, bdc_apartment_service_price_id FROM bdc_v2_debit_detail WHERE deleted_at is null AND bdc_building_id = " . $buildingId . " ";

        if ($planceId) {

            $sql1 .= " AND bdc_apartment_id IN ('" . implode("','", $planceId) . "') ";
            $sql2 .= " AND bdc_apartment_id IN ('" . implode("','", $planceId) . "') ";

        }

        if ($bdc_apartment_id) {
            $sql1 .= " AND bdc_apartment_id= " . $bdc_apartment_id . " ";
            $sql2 .= " AND bdc_apartment_id= " . $bdc_apartment_id . " ";
        }

        if ($cycle_name) {
            $sql1 .= " AND cycle_name <= " . $cycle_name . " ";
            $sql2 .= " AND cycle_name <= " . $cycle_name . " ";
        }

        if ($filterMin !== false) {
            $sql1 .= " AND after_cycle_name >= " . $filterMin . " ";
        }

        if ($filterService) {
            $sql1 .= " AND bdc_apartment_service_price_id IN ('" . implode("','", $filterService) . "') ";
            $sql2 .= " AND bdc_apartment_service_price_id IN ('" . implode("','", $filterService) . "') ";
        }
        $sql2 .= " GROUP BY bdc_apartment_id,bdc_apartment_service_price_id";
        $sql = "SELECT t1.* FROM (" . $sql1 . ") as t1 INNER JOIN ( " . $sql2 . " ) as t2 ON t1.bdc_apartment_service_price_id = t2.bdc_apartment_service_price_id AND t1.bdc_apartment_id = t2.bdc_apartment_id AND t1.cycle_name = t2.cycle_name";
        return DB::table(DB::raw("($sql) as sub"))->get();
    }

    public static function getAllApartmentDetailLastTime3ExportByTypeService($buildingId, $cycle_name, $planceId, $bdc_apartment_id, $filterMin, $filterService)
    {
        $sql1 = "SELECT * FROM bdc_v2_debit_detail WHERE deleted_at is null AND bdc_building_id = " . $buildingId . " ";
        $sql2 = "SELECT  max(cycle_name) AS cycle_name, bdc_apartment_id, bdc_apartment_service_price_id FROM bdc_v2_debit_detail WHERE deleted_at is null AND bdc_building_id = " . $buildingId . " ";

        if ($planceId) {

            $sql1 .= " AND bdc_apartment_id IN ('" . implode("','", $planceId) . "') ";
            $sql2 .= " AND bdc_apartment_id IN ('" . implode("','", $planceId) . "') ";

        }

        if ($bdc_apartment_id) {
            $sql1 .= " AND bdc_apartment_id= " . $bdc_apartment_id . " ";
            $sql2 .= " AND bdc_apartment_id= " . $bdc_apartment_id . " ";
        }

        if ($cycle_name) {
            $sql1 .= " AND cycle_name <= " . $cycle_name . " ";
            $sql2 .= " AND cycle_name <= " . $cycle_name . " ";
        }

        if ($filterMin !== false) {
            $sql1 .= " AND after_cycle_name >= " . $filterMin . " ";
        }

        if ($filterService) {
            $sql1 .= " AND bdc_apartment_service_price_id IN ('" . implode("','", $filterService) . "') ";
            $sql2 .= " AND bdc_apartment_service_price_id IN ('" . implode("','", $filterService) . "') ";
        }
        $sql2 .= " GROUP BY bdc_apartment_id,bdc_apartment_service_price_id";
        $sql = "SELECT t1.*,bdc_apartment_service_price.bdc_service_id FROM (" . $sql1 . ") as t1 INNER JOIN ( " . $sql2 . " ) as t2 ON t1.bdc_apartment_service_price_id = t2.bdc_apartment_service_price_id AND t1.bdc_apartment_id = t2.bdc_apartment_id AND t1.cycle_name = t2.cycle_name";
        //$sql .= " INNER JOIN bdc_apartment_service_price ON bdc_apartment_service_price.id = t1.bdc_apartment_service_price_id";
        $sql .= " LEFT JOIN bdc_apartment_service_price ON bdc_apartment_service_price.bdc_apartment_id = t1.bdc_apartment_id WHERE (bdc_apartment_service_price.id = t1.bdc_apartment_service_price_id or t1.bdc_apartment_service_price_id = 0)";
        return DB::table(DB::raw("($sql) as sub GROUP BY sub.id ORDER BY bdc_service_id"))->get();
    }

    /**
     * getAllApartmentLastTime : lấy tất cả căn hộ theo kỳ và nhóm lấy mới nhất
     * @param $buildingId
     * @param mixed $cycle_name
     * @param $bdc_apartment_id
     * @param $perPage
     * @param $page
     * @return boolean
     */

    public static function getAllApartmentLastTime($buildingId, $cycle_name, $bdc_apartment_id, $filterMin, $perPage, $page)
    {
        /*$sql1 = "SELECT * FROM bdc_v2_debit_detail WHERE deleted_at is null AND bdc_building_id = ".$buildingId." ";
        $sql2 = "SELECT  max(cycle_name) AS cycle_name, bdc_apartment_id, bdc_apartment_service_price_id FROM bdc_v2_debit_detail WHERE deleted_at is null AND bdc_building_id = ".$buildingId." ";

        if($bdc_apartment_id){
            $sql1 .= " AND bdc_apartment_id= ".$bdc_apartment_id." ";
            $sql2 .= " AND bdc_apartment_id= ".$bdc_apartment_id." ";
        }

        if($cycle_name){
            $sql1 .= " AND cycle_name <= ".$cycle_name." ";
            $sql2 .= " AND cycle_name <= ".$cycle_name." ";
        }

        if($filterMin !== false){
            $sql1 .= " AND after_cycle_name >= ".$filterMin." ";
        }

        $sql2 .= " GROUP BY bdc_apartment_id,bdc_apartment_service_price_id";
        $sql = "SELECT t1.* FROM (".$sql1.") as t1 INNER JOIN ( ".$sql2." ) as t2 ON t1.bdc_apartment_service_price_id = t2.bdc_apartment_service_price_id AND t1.bdc_apartment_id = t2.bdc_apartment_id AND t1.cycle_name = t2.cycle_name";
        return DB::table(DB::raw("($sql) as sub"))->paginate($perPage, ['*'],'page',$page);*/

        $arrWhere = [
            "bdc_building_id" => $buildingId
        ];
        $bdc_apartment_id && $arrWhere["bdc_apartment_id"] = $bdc_apartment_id;
        $resutl_query = DebitDetail::where($arrWhere)->where("cycle_name", "<=", $cycle_name)
            ->whereNull('deleted_at')
            ->groupBy('bdc_apartment_id')
            ->select(DB::raw('MAX(id) as id, SUM(sumery) as tong_phat_sinh, SUM(paid_by_cycle_name) as tong_thanh_toan_ky, SUM(sumery-paid_by_cycle_name) as cuoi_ky'));
        $listId = $resutl_query->get();
        if (!$listId->pluck('id')->toArray()) return null;
        if ($filterMin != false) {
            $listIds = [];
            array_filter($listId->toArray(), function ($item) use ($filterMin, &$listIds) {
                if ((int)$item['cuoi_ky'] >= (int)$filterMin) $listIds[] = $item['id'];
                return (int)$item['cuoi_ky'] >= (int)$filterMin;
            });
        } else {
            $listIds = $listId->pluck('id')->toArray();
        }
        $resutl_query = DebitDetail::whereIntegerInRaw('id', $listIds)->orderBy('bdc_apartment_id', 'desc')->paginate($perPage, ['*'], 'page', $page);
        return $resutl_query;
    }

    public static function getAllApartmentLastTimeExport($buildingId, $cycle_name, $bdc_apartment_id, $filterMin)
    {
        $arrWhere = [
            "bdc_building_id" => $buildingId
        ];
        $bdc_apartment_id && $arrWhere["bdc_apartment_id"] = $bdc_apartment_id;
        $resutl_query = DebitDetail::where($arrWhere)->where("cycle_name", "<=", $cycle_name)
            ->whereNull('deleted_at')
            ->groupBy('bdc_apartment_id')
            ->select(DB::raw('MAX(id) as id, SUM(sumery) as tong_phat_sinh, SUM(paid_by_cycle_name) as tong_thanh_toan_ky, SUM(sumery-paid_by_cycle_name) as cuoi_ky'));
        $listId = $resutl_query->get();
        if (!$listId->pluck('id')->toArray()) return null;
        if ($filterMin != false) {
            $listIds = [];
            array_filter($listId->toArray(), function ($item) use ($filterMin, &$listIds) {
                if ((int)$item['cuoi_ky'] >= (int)$filterMin) $listIds[] = $item['id'];
                return (int)$item['cuoi_ky'] >= (int)$filterMin;
            });
        } else {
            $listIds = $listId->pluck('id')->toArray();
        }
        $resutl_query = DebitDetail::whereIntegerInRaw('id', $listIds)->orderBy('bdc_apartment_id', 'desc')->get();
        return $resutl_query;
    }

    /**
     * updatePaidByCycleNameFromReceipt : cập nhật lại số tiền đã thu
     * @param int $apartmentId
     * @param mixed $service_price_id
     * @param mixed $cycle_name
     * @param bool $update_before_after
     * @return boolean
     */

    public static function updatePaidByCycleNameFromReceipt(int $apartmentId, $service_price_id = false, $cycle_name = false, bool $update_before_after = true): bool
    {
        if ($service_price_id === 0) { // thông tin tiền thừa
            $totalByCycleName = LogCoinDetailRepository::getSumPaidLogCoin($apartmentId, $service_price_id, $cycle_name, false);
        } else {
            $totalByCycleName = PaymentDetailRepository::getSumPaidByCycleNameFromReceipt($apartmentId, $service_price_id, $cycle_name, false);
        }
        $debitDetailByCycleName = self::getDebitByApartmentAndServiceAndCyclename($apartmentId, $service_price_id, $cycle_name, false);
        if (isset($debitDetailByCycleName->id)) { // có thì update lại
            $totalPaid = false;
            $service_price_id != false && $service_price_id !== 0 && $totalPaid = PaymentDetailRepository::getSumPaidByDebitId($debitDetailByCycleName->id);
            $temp_dau_ky = self::getTotalSumeryByCycleNameApartmentServiceCus($debitDetailByCycleName->bdc_building_id, $debitDetailByCycleName->bdc_apartment_id, $service_price_id, $cycle_name, "<"); // lấy số liệu đầu kỳ
            $dauky_da_thanhtoan = PaymentDetailRepository::getSumPaidByCycleNameFromReceiptCus($debitDetailByCycleName->bdc_apartment_id, $service_price_id, $cycle_name, "<"); // lấy số liệu đã thanh toán đầu kỳ

            if (isset($temp_dau_ky->tong_phat_sinh)) {
                $before_cycle_name = $temp_dau_ky->tong_phat_sinh - $dauky_da_thanhtoan;
            } else {
                $before_cycle_name = $dauky_da_thanhtoan ? -$dauky_da_thanhtoan : 0;
            }
            $getBill = BillRepository::getBillById($debitDetailByCycleName->bdc_bill_id);
            $checkBill = false;
            if ($getBill) {
                if ($getBill->status >= -2) $checkBill = true;
            }
            if (!$checkBill) {
                $debitDetailByCycleName->sumery = 0;
            }
            $after_cycle_name = $before_cycle_name + $debitDetailByCycleName->sumery - $totalByCycleName;
            self::updateDebit($debitDetailByCycleName->id, $totalPaid, $totalByCycleName, false, $before_cycle_name, $after_cycle_name);

            // cập nhật $before_cycle_name và $after_cycle_name các kỳ mới hơn
            if ($update_before_after) {
                $DataUpdate = self::getDebitByApartmentAndServiceAndCyclenameCus($apartmentId, $service_price_id, $cycle_name, ">");
                if ($DataUpdate) foreach ($DataUpdate as $item) {
                    $temp_dau_ky = self::getTotalSumeryByCycleNameApartmentServiceCus($debitDetailByCycleName->bdc_building_id, $debitDetailByCycleName->bdc_apartment_id, $service_price_id, $item["cycle_name"], "<", false); // lấy số liệu đầu kỳ
                    $dauky_da_thanhtoan = PaymentDetailRepository::getSumPaidByCycleNameFromReceiptCus($debitDetailByCycleName->bdc_apartment_id, $service_price_id, $item["cycle_name"], "<", false); // lấy số liệu đã thanh toán đầu kỳ
//                    $before_cycle_name = isset($temp_dau_ky->tong_phat_sinh) ? $temp_dau_ky->tong_phat_sinh - $dauky_da_thanhtoan : $dauky_da_thanhtoan ? - $dauky_da_thanhtoan : 0;

                    if (isset($temp_dau_ky->tong_phat_sinh)) {
                        $before_cycle_name = $temp_dau_ky->tong_phat_sinh - $dauky_da_thanhtoan;
                    } else {
                        $before_cycle_name = $dauky_da_thanhtoan ? (-$dauky_da_thanhtoan) : 0;
                    }

                    $getBill = BillRepository::getBillById($item['bdc_bill_id']);
                    $checkBill = false;
                    if ($getBill) {
                        if ($getBill->status >= -2) $checkBill = true;
                    }
                    if (!$checkBill) {
                        $item['sumery'] = 0;
                    }
                    $after_cycle_name = $before_cycle_name + $item['sumery'] - $item['paid_by_cycle_name'];
                    self::updateDebit($item['id'], false, false, false, $before_cycle_name, $after_cycle_name);
                }
            }
        } else { // không có thì tạo
            $detailApartment = ApartmentsRespository::getInfoApartmentsById($apartmentId);
            if (!isset($detailApartment->building_id)) return false;
            self::createOrUpdateDebit(
                $detailApartment->building_id,
                $apartmentId,
                0,
                $service_price_id,
                $cycle_name,
                '',
                '',
                '[]',
                0, 0, 0, 0, 0, 0, '', 0, $totalByCycleName
            );
            if ($update_before_after) { // tổ hợp kỳ sau
                QueueRedis::setItemForQueue('add_queue_stat_payment_', [
                    "apartmentId" => $apartmentId,
                    "service_price_id" => $service_price_id,
                    "cycle_name" => $cycle_name,
                ]);
            }
        }
        return true;
    }

    public static function warningUpdatePaidByCycleNameFromReceipt(int $apartmentId, $service_price_id = false, $cycle_name = false, bool $update_before_after = true, bool $showDetail = false): bool
    {
        $while_list = [92];

        if ($service_price_id === 0) { // thông tin tiền thừa
            $totalByCycleName = LogCoinDetailRepository::getSumPaidLogCoin($apartmentId, $service_price_id, $cycle_name);
        } else {
            $totalByCycleName = PaymentDetailRepository::getSumPaidByCycleNameFromReceipt($apartmentId, $service_price_id, $cycle_name);
        }
        $debitDetailByCycleName = self::getDebitByApartmentAndServiceAndCyclename($apartmentId, $service_price_id, $cycle_name);
        if (isset($debitDetailByCycleName->id)) { // có thì update lại
            if (in_array($debitDetailByCycleName->bdc_building_id, $while_list)) {
                // bỏ qua các tòa test
                return true;
            }
            $totalPaid = false;
            $service_price_id != false && $service_price_id !== 0 && $totalPaid = PaymentDetailRepository::getSumPaidByDebitId($debitDetailByCycleName->id);
            $temp_dau_ky = self::getTotalSumeryByCycleNameApartmentServiceCus($debitDetailByCycleName->bdc_building_id, $debitDetailByCycleName->bdc_apartment_id, $service_price_id, $cycle_name, "<"); // lấy số liệu đầu kỳ
            $dauky_da_thanhtoan = PaymentDetailRepository::getSumPaidByCycleNameFromReceiptCus($debitDetailByCycleName->bdc_apartment_id, $service_price_id, $cycle_name, "<"); // lấy số liệu đã thanh toán đầu kỳ

            if (isset($temp_dau_ky->tong_phat_sinh)) {
                $before_cycle_name = $temp_dau_ky->tong_phat_sinh - $dauky_da_thanhtoan;
            } else {
                $before_cycle_name = $dauky_da_thanhtoan ? -$dauky_da_thanhtoan : 0;
            }
            $getBill = BillRepository::getBillById($debitDetailByCycleName->bdc_bill_id);
            $checkBill = false;
            if ($getBill) {
                if ($getBill->status >= -2) $checkBill = true;
            }
            if (!$checkBill) {
                $debitDetailByCycleName->sumery = 0;
            }
            $after_cycle_name = $before_cycle_name + $debitDetailByCycleName->sumery - $totalByCycleName;
            Log::dump($debitDetailByCycleName->paid_by_cycle_name != $totalByCycleName);

            if (!$showDetail) {
                $checkSend = Redis::get("checksendwarning_" . $debitDetailByCycleName->id);
                if ($checkSend) {
                    $showDetail = true;
                } else {
                    Redis::setAndExpire("checksendwarning_" . $debitDetailByCycleName->id, 1, 5 * 60 * 60);
                }
            }

            if ($debitDetailByCycleName->paid_by_cycle_name != $totalByCycleName) {
                if ($showDetail) {
                    echo "Thanh toán cũ: " . $debitDetailByCycleName->paid_by_cycle_name;
                    echo "</br>";
                    echo "Thanh toán mới: " . $totalByCycleName . "  -  " . "<a href='/admin/dev/paymentShow?apartmentId=" . $apartmentId . "&cycle_name=" . $cycle_name . "&service_price_id=" . $service_price_id . "' target='_blank'>xem payment chi tiết</a>";
                }

                !$showDetail && env('APP_ENV') !== 'local' && dBug::pushNotification("<strong>Tòa: </strong><pre>$debitDetailByCycleName->bdc_building_id</pre>
<strong>Mã căn: </strong><pre>$debitDetailByCycleName->bdc_apartment_id</pre>
<strong>Mã dịch vụ: </strong><pre>$debitDetailByCycleName->bdc_apartment_service_price_id</pre>
<strong>Kỳ: </strong><pre>$debitDetailByCycleName->cycle_name</pre>
<strong>Id debit: </strong><a href='https://bdcadmin.dxmb.vn/admin/dev/warningPaymentShow?debit_id=" . $debitDetailByCycleName->id . "' >" . $debitDetailByCycleName->id . "</a>
<strong>Lỗi: </strong><pre>số liệu thanh toán sai</pre>", \config('app.telegram_warning_pay'));
                return false;
            }
            Log::dump($debitDetailByCycleName->before_cycle_name != $before_cycle_name);

            if ($debitDetailByCycleName->before_cycle_name != $before_cycle_name) {
                if ($showDetail) {
                    echo "Đầu kỳ cũ: " . $debitDetailByCycleName->before_cycle_name;
                    echo "</br>";
                    echo "Đầu kỳ mới: " . $before_cycle_name;
                }
                !$showDetail && env('APP_ENV') !== 'local' && dBug::pushNotification("<strong>Tòa: </strong><pre>$debitDetailByCycleName->bdc_building_id</pre>
<strong>Mã căn: </strong><pre>$debitDetailByCycleName->bdc_apartment_id</pre>
<strong>Mã dịch vụ: </strong><pre>$debitDetailByCycleName->bdc_apartment_service_price_id</pre>
<strong>Kỳ: </strong><pre>$debitDetailByCycleName->cycle_name</pre>
<strong>Id debit: </strong><a href='https://bdcadmin.dxmb.vn/admin/dev/warningPaymentShow?debit_id=" . $debitDetailByCycleName->id . "' >" . $debitDetailByCycleName->id . "</a>
<strong>Lỗi: </strong><pre>số liệu đầu kỳ sai</pre>", \config('app.telegram_warning_pay'));
                return false;
            }
            Log::dump($debitDetailByCycleName->after_cycle_name != $after_cycle_name);

            if ($debitDetailByCycleName->after_cycle_name != $after_cycle_name) {
                if ($showDetail) {
                    echo "Cuối kỳ cũ: " . $debitDetailByCycleName->after_cycle_name;
                    echo "</br>";
                    echo "Cuối kỳ mới: " . $after_cycle_name;
                }
                !$showDetail && env('APP_ENV') !== 'local' && dBug::pushNotification("<strong>Tòa: </strong><pre>$debitDetailByCycleName->bdc_building_id</pre>
<strong>Mã căn: </strong><pre>$debitDetailByCycleName->bdc_apartment_id</pre>
<strong>Mã dịch vụ: </strong><pre>$debitDetailByCycleName->bdc_apartment_service_price_id</pre>
<strong>Kỳ: </strong><pre>$debitDetailByCycleName->cycle_name</pre>
<strong>Id debit: </strong><a href='https://bdcadmin.dxmb.vn/admin/dev/warningPaymentShow?debit_id=" . $debitDetailByCycleName->id . "' >" . $debitDetailByCycleName->id . "</a>
<strong>Lỗi: </strong><pre>số liệu cuối kỳ sai</pre>", \config('app.telegram_warning_pay'));
                return false;
            }
            Log::dump($debitDetailByCycleName->paid != $totalPaid);

            if ($debitDetailByCycleName->paid != $totalPaid) {
                if ($showDetail) {
                    echo "Hạch toán nợ cũ: " . $debitDetailByCycleName->paid;
                    echo "</br>";
                    echo "Hạch toán nợ mới: " . $totalPaid;
                }

                !$showDetail && env('APP_ENV') !== 'local' && dBug::pushNotification("<strong>Tòa: </strong><pre>$debitDetailByCycleName->bdc_building_id</pre>
<strong>Mã căn: </strong><pre>$debitDetailByCycleName->bdc_apartment_id</pre>
<strong>Mã dịch vụ: </strong><pre>$debitDetailByCycleName->bdc_apartment_service_price_id</pre>
<strong>Kỳ: </strong><pre>$debitDetailByCycleName->cycle_name</pre>
<strong>Id debit: </strong><a href='https://bdcadmin.dxmb.vn/admin/dev/warningPaymentShow?debit_id=" . $debitDetailByCycleName->id . "' >" . $debitDetailByCycleName->id . "</a>
<strong>Lỗi: </strong><pre>số liệu hạch toán sai</pre>", \config('app.telegram_warning_pay'));
                return false;
            }
            Log::dump($debitDetailByCycleName->deleted_at != null);

            if ($debitDetailByCycleName->deleted_at != null) {
                if ($showDetail) {
                    echo "số liệu tổng hợp sai do xóa phát sinh ";
                }

                !$showDetail && env('APP_ENV') !== 'local' && dBug::pushNotification("<strong>Tòa: </strong><pre>$debitDetailByCycleName->bdc_building_id</pre>
<strong>Mã căn: </strong><pre>$debitDetailByCycleName->bdc_apartment_id</pre>
<strong>Mã dịch vụ: </strong><pre>$debitDetailByCycleName->bdc_apartment_service_price_id</pre>
<strong>Kỳ: </strong><pre>$debitDetailByCycleName->cycle_name</pre>
<strong>Id debit: </strong><a href='https://bdcadmin.dxmb.vn/admin/dev/warningPaymentShow?debit_id=" . $debitDetailByCycleName->id . "' >" . $debitDetailByCycleName->id . "</a>
<strong>Lỗi: </strong><pre>số liệu tổng hợp sai do xóa phát sinh</pre>", \config('app.telegram_warning_pay'));
                return false;
            }

//            self::updateDebit($debitDetailByCycleName->id, $totalPaid, $totalByCycleName, false, $before_cycle_name, $after_cycle_name);

            // cập nhật $before_cycle_name và $after_cycle_name các kỳ mới hơn
            if ($update_before_after) {
                $DataUpdate = self::getDebitByApartmentAndServiceAndCyclenameCus($apartmentId, $service_price_id, $cycle_name, ">");
                if ($DataUpdate) foreach ($DataUpdate as $item) {
                    $temp_dau_ky = self::getTotalSumeryByCycleNameApartmentServiceCus($debitDetailByCycleName->bdc_building_id, $debitDetailByCycleName->bdc_apartment_id, $service_price_id, $item["cycle_name"], "<"); // lấy số liệu đầu kỳ
                    $dauky_da_thanhtoan = PaymentDetailRepository::getSumPaidByCycleNameFromReceiptCus($debitDetailByCycleName->bdc_apartment_id, $service_price_id, $item["cycle_name"], "<"); // lấy số liệu đã thanh toán đầu kỳ
//                    $before_cycle_name = isset($temp_dau_ky->tong_phat_sinh) ? $temp_dau_ky->tong_phat_sinh - $dauky_da_thanhtoan : $dauky_da_thanhtoan ? - $dauky_da_thanhtoan : 0;

                    if (isset($temp_dau_ky->tong_phat_sinh)) {
                        $before_cycle_name = $temp_dau_ky->tong_phat_sinh - $dauky_da_thanhtoan;
                    } else {
                        $before_cycle_name = $dauky_da_thanhtoan ? (-$dauky_da_thanhtoan) : 0;
                    }

                    $getBill = BillRepository::getBillById($item['bdc_bill_id']);
                    $checkBill = false;
                    if ($getBill) {
//                        log::dump($getBill->status);
//                        log::dump($debitDetailByCycleName->bdc_bill_id);
                        if ($getBill->status >= -2) $checkBill = true;
                    }
                    if (!$checkBill) {
                        $item['sumery'] = 0;
                    }
                    $after_cycle_name = $before_cycle_name + $item['sumery'] - $item['paid_by_cycle_name'];
                    Log::dump($item['before_cycle_name'] != $before_cycle_name);

                    if (!$showDetail) {
                        $checkSend = Redis::get("checksendwarning_" . $item["id"]);
                        if ($checkSend) {
                            $showDetail = true;
                        } else {
                            Redis::setAndExpire("checksendwarning_" . $item["id"], 1, 5 * 60 * 60);
                        }
                    }

                    if ($item['before_cycle_name'] != $before_cycle_name) {
                        if ($showDetail) {
                            echo "Đầu kỳ cũ: " . $item['before_cycle_name'];
                            echo "</br>";
                            echo "Đầu kỳ mới: " . $before_cycle_name;
                        }
                        !$showDetail && env('APP_ENV') !== 'local' && dBug::pushNotification("<strong>Tòa: </strong><pre>$debitDetailByCycleName->bdc_building_id</pre>
<strong>Mã căn: </strong><pre>$debitDetailByCycleName->bdc_apartment_id</pre>
<strong>Mã dịch vụ: </strong><pre>$debitDetailByCycleName->bdc_apartment_service_price_id</pre>
<strong>Kỳ: </strong><pre>" . $item["cycle_name"] . "</pre>
<strong>Id debit: </strong><a href='https://bdcadmin.dxmb.vn/admin/dev/warningPaymentShow?debit_id=" . $item["id"] . "' >" . $item["id"] . "</a>
<strong>Lỗi: </strong><pre>số liệu đầu kỳ sai, tác động kỳ $debitDetailByCycleName->cycle_name</pre>", \config('app.telegram_warning_pay'));
                        return false;
                    }

                    Log::dump($item['after_cycle_name'] != $after_cycle_name);

                    if ($item['after_cycle_name'] != $after_cycle_name) {
                        if ($showDetail) {
                            echo "Cuối kỳ cũ: " . $item['after_cycle_name'];
                            echo "</br>";
                            echo "Cuối kỳ mới: " . $after_cycle_name;
                        }
                        !$showDetail && env('APP_ENV') !== 'local' && dBug::pushNotification("<strong>Tòa: </strong><pre>$debitDetailByCycleName->bdc_building_id</pre>
<strong>Mã căn: </strong><pre>$debitDetailByCycleName->bdc_apartment_id</pre>
<strong>Mã dịch vụ: </strong><pre>$debitDetailByCycleName->bdc_apartment_service_price_id</pre>
<strong>Kỳ: </strong><pre>" . $item["cycle_name"] . "</pre>
<strong>Id debit: </strong><a href='https://bdcadmin.dxmb.vn/admin/dev/warningPaymentShow?debit_id=" . $item["id"] . "' >" . $item["id"] . "</a>
<strong>Lỗi: </strong><pre>số liệu cuối kỳ sai, tác động kỳ $debitDetailByCycleName->cycle_name</pre>", \config('app.telegram_warning_pay'));
                        return false;
                    }

                    if ($item['deleted_at'] != null) {
                        if ($showDetail) {
                            echo "số liệu tổng hợp sai do xóa phát sinh";
                        }
                        !$showDetail && env('APP_ENV') !== 'local' && dBug::pushNotification("<strong>Tòa: </strong><pre>$debitDetailByCycleName->bdc_building_id</pre>
<strong>Mã căn: </strong><pre>$debitDetailByCycleName->bdc_apartment_id</pre>
<strong>Mã dịch vụ: </strong><pre>$debitDetailByCycleName->bdc_apartment_service_price_id</pre>
<strong>Kỳ: </strong><pre>" . $item["cycle_name"] . "</pre>
<strong>Id debit: </strong><a href='https://bdcadmin.dxmb.vn/admin/dev/warningPaymentShow?debit_id=" . $item["id"] . "' >" . $item["id"] . "</a>
<strong>Lỗi: </strong><pre>số liệu tổng hợp sai do xóa phát sinh, tác động kỳ $debitDetailByCycleName->cycle_name</pre>", \config('app.telegram_warning_pay'));
                        return false;
                    }

//                    self::updateDebit($item['id'], false, false, false, $before_cycle_name, $after_cycle_name);
                }
            }
        } else { // không có thì tạo
            $detailApartment = ApartmentsRespository::getInfoApartmentsById($apartmentId);
            if (!isset($detailApartment->building_id)) return false;
            if ($showDetail) {
                echo "số liệu tổng hợp sai do chưa gọi tổ hợp";
            }
            !$showDetail && env('APP_ENV') !== 'local' && dBug::pushNotification("<strong>Tòa: </strong><pre>$detailApartment->building_id</pre>
<strong>Mã căn: </strong><pre>$apartmentId</pre>
<strong>Mã dịch vụ: </strong><pre>$service_price_id</pre>
<strong>Kỳ: </strong><pre>$cycle_name</pre>
<strong>Lỗi: </strong><pre>số liệu tổng hợp sai do chưa gọi tổ hợp</pre>", \config('app.telegram_warning_pay'));
            return false;
            /*self::createOrUpdateDebit(
                $detailApartment->building_id,
                $apartmentId,
                0,
                $service_price_id,
                $cycle_name,
                '',
                '',
                '[]',
                0, 0, 0, 0, 0, 0, '', 0, $totalByCycleName
            );*/
        }
        return true;
    }

    public static function getListDebitDetail($request, $buildingId)
    {
        $sql = "SELECT 
                    *
                FROM
                    (SELECT 
                         (SELECT 
                                    COALESCE(SUM(sumery - discount), 0)
                                FROM
                                    dev_dbdc.bdc_v2_debit_detail AS tb3
                                WHERE
                                    tb3.bdc_building_id = $buildingId";

        if ($request->cycle_name) {
            $cycle_name = $request->cycle_name;
            $sql .= " AND tb3.cycle_name < $cycle_name";
        }

        $sql .= " AND tb1.bdc_apartment_id = tb3.bdc_apartment_id
                                        AND tb1.bdc_apartment_service_price_id = tb3.bdc_apartment_service_price_id
                                        AND tb3.deleted_at IS NULL) - (SELECT 
                                    COALESCE(SUM(cost), 0)
                                FROM
                                    dev_dbdc.bdc_payment_details AS tb2
                                WHERE
                                    tb2.bdc_building_id = $buildingId";
        if ($request->cycle_name) {
            $cycle_name = $request->cycle_name;
            $sql .= " AND tb2.cycle_name < $cycle_name";
        }
        $sql .= " AND tb1.bdc_apartment_id = tb2.bdc_apartment_id
                                        AND tb1.bdc_apartment_service_price_id = tb2.bdc_service_id
                                        AND tb2.type = 'add'
                                        AND tb2.deleted_at IS NULL) - (SELECT 
                                    COALESCE(SUM(cost), 0)
                                FROM
                                    dev_dbdc.bdc_payment_details AS tb2
                                WHERE
                                    tb2.bdc_building_id = $buildingId";

        if ($request->cycle_name) {
            $cycle_name = $request->cycle_name;
            $sql .= " AND tb2.cycle_name < $cycle_name";
        }

        $sql .= " AND tb1.bdc_apartment_id = tb2.bdc_apartment_id
                                        AND tb1.bdc_apartment_service_price_id = tb2.bdc_service_id
                                        AND tb2.type = 'sub'
                                        AND tb2.deleted_at IS NULL) AS dau_ky,
                                (SELECT 
                                    COALESCE(SUM(cost), 0)
                                FROM
                                    dev_dbdc.bdc_payment_details AS tb2
                                WHERE
                                    tb2.bdc_building_id = $buildingId";

        if ($request->cycle_name) {
            $cycle_name = $request->cycle_name;
            $sql .= " AND tb2.cycle_name = $cycle_name";
        }

        $sql .= " AND tb1.bdc_apartment_id = tb2.bdc_apartment_id
                                        AND tb1.bdc_apartment_service_price_id = tb2.bdc_service_id
                                        AND tb2.type = 'add'
                                        AND tb2.deleted_at IS NULL) - (SELECT 
                                    COALESCE(SUM(cost), 0)
                                FROM
                                    dev_dbdc.bdc_payment_details AS tb2
                                WHERE
                                    tb2.bdc_building_id = $buildingId";

        if ($request->cycle_name) {
            $cycle_name = $request->cycle_name;
            $sql .= " AND tb2.cycle_name = $cycle_name";
        }

        $sql .= " AND tb1.bdc_apartment_id = tb2.bdc_apartment_id
                                        AND tb1.bdc_apartment_service_price_id = tb2.bdc_service_id
                                        AND tb2.type = 'sub'
                                        AND tb2.deleted_at IS NULL) AS thanh_toan,
                            tb1.*
                    FROM
                        dev_dbdc.bdc_v2_debit_detail AS tb1
                    WHERE
                        tb1.bdc_building_id = $buildingId";

        if ($request->cycle_name) {
            $cycle_name = $request->cycle_name;
            $sql .= " AND tb1.cycle_name = $cycle_name";
        }

        if ($request->bdc_apartment_id) {

            $bdc_apartment_id = $request->bdc_apartment_id;
            $sql .= " AND tb1.bdc_apartment_id = $bdc_apartment_id";

        }
        $sql .= " AND tb1.deleted_at IS NULL
                    GROUP BY tb1.bdc_apartment_id , tb1.bdc_apartment_service_price_id) AS tb_1";

        if ($request->du_no_cuoi_ky) {
            $du_no_cuoi_ky = $request->du_no_cuoi_ky;
            $sql .= " WHERE tb_1.thanh_toan = $du_no_cuoi_ky";
        }

        return DB::table(DB::raw("($sql) as sub"));
    }

    public static function getTotalDebitDetail($request, $buildingId)
    {
        $sql = "SELECT 
                        *
                    FROM
                        (SELECT 
                            (SELECT 
                                        COALESCE(SUM(sumery - discount), 0)
                                    FROM
                                        dev_dbdc.bdc_v2_debit_detail AS tb2
                                    WHERE
                                        tb2.bdc_building_id = $buildingId
                                            AND tb1.bdc_apartment_id = tb2.bdc_apartment_id";
        if ($request->cycle_name) {
            $cycle_name = $request->cycle_name;
            $sql .= " AND tb2.cycle_name < $cycle_name";
        }
        $sql .= " AND tb1.deleted_at IS NULL
                                    GROUP BY tb2.bdc_apartment_id) - (SELECT 
                                        COALESCE(SUM(cost), 0)
                                    FROM
                                        dev_dbdc.bdc_payment_details AS tb2
                                    WHERE
                                        tb2.bdc_building_id = $buildingId";
        if ($request->cycle_name) {
            $cycle_name = $request->cycle_name;
            $sql .= " AND tb2.cycle_name < $cycle_name";
        }
        $sql .= " AND tb1.bdc_apartment_id = tb2.bdc_apartment_id
                                            AND tb2.type = 'add'
                                            AND tb2.deleted_at IS NULL) - (SELECT 
                                        COALESCE(SUM(cost), 0)
                                    FROM
                                        dev_dbdc.bdc_payment_details AS tb2
                                    WHERE
                                        tb2.bdc_building_id = $buildingId";
        if ($request->cycle_name) {
            $cycle_name = $request->cycle_name;
            $sql .= " AND tb2.cycle_name < $cycle_name";
        }
        $sql .= " AND tb1.bdc_apartment_id = tb2.bdc_apartment_id
                                            AND tb2.type = 'sub'
                                            AND tb2.deleted_at IS NULL) AS dau_ky,
                                (SELECT 
                                        COALESCE(SUM(sumery - discount), 0)
                                    FROM
                                        dev_dbdc.bdc_v2_debit_detail AS tb2
                                    WHERE
                                        tb2.bdc_building_id = $buildingId
                                            AND tb1.bdc_apartment_id = tb2.bdc_apartment_id";
        if ($request->cycle_name) {
            $cycle_name = $request->cycle_name;
            $sql .= " AND tb2.cycle_name = $cycle_name";
        }
        $sql .= " AND tb1.deleted_at IS NULL
                                    GROUP BY tb2.bdc_apartment_id) AS trong_ky,
                                (SELECT 
                                        COALESCE(SUM(cost), 0)
                                    FROM
                                        dev_dbdc.bdc_payment_details AS tb2
                                    WHERE
                                        tb2.bdc_building_id = $buildingId";
        if ($request->cycle_name) {
            $cycle_name = $request->cycle_name;
            $sql .= " AND tb2.cycle_name = $cycle_name";
        }
        $sql .= " AND tb1.bdc_apartment_id = tb2.bdc_apartment_id
                                            AND tb2.type = 'add'
                                            AND tb2.deleted_at IS NULL) - (SELECT 
                                        COALESCE(SUM(cost), 0)
                                    FROM
                                        dev_dbdc.bdc_payment_details AS tb2
                                    WHERE
                                        tb2.bdc_building_id = $buildingId";
        if ($request->cycle_name) {
            $cycle_name = $request->cycle_name;
            $sql .= " AND tb2.cycle_name = $cycle_name";
        }
        $sql .= " AND tb1.bdc_apartment_id = tb2.bdc_apartment_id
                                            AND tb2.type = 'sub'
                                            AND tb2.deleted_at IS NULL) AS thanh_toan,
                                tb1.*
                        FROM
                            dev_dbdc.bdc_v2_debit_detail AS tb1
                        WHERE
                            tb1.bdc_building_id = $buildingId";
        if ($request->cycle_name) {
            $cycle_name = $request->cycle_name;
            $sql .= " AND tb1.cycle_name = $cycle_name";
        }
        if ($request->bdc_apartment_id) {

            $bdc_apartment_id = $request->bdc_apartment_id;
            $sql .= " AND tb1.bdc_apartment_id = $bdc_apartment_id";

        }
        $sql .= " AND tb1.deleted_at IS NULL
                        GROUP BY tb1.bdc_apartment_id) AS tb_1";

        if ($request->du_no_cuoi_ky) {
            $du_no_cuoi_ky = $request->du_no_cuoi_ky;
            $sql .= " WHERE tb_1.thanh_toan = $du_no_cuoi_ky";
        }

        return DB::table(DB::raw("($sql) as sub"));
    }
}
