<?php

namespace App\Repositories\BdcApartmentServicePrice;

use App\Helpers\dBug;
use Carbon\Carbon;
use App\Models\Service\Service;
use Illuminate\Support\Facades\Auth;
use App\Models\Apartments\Apartments;
use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Repositories\Eloquent\Repository;
use Illuminate\Database\Eloquent\Builder;
use const App\Repositories\Service\MANY_PRICE;
use App\Repositories\Service\ServiceRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

const ONE_PRICE = 1; // Đơn giá
const MULTI_PRICE = 2; // Lũy tiến
const FIRST_PRICE = 3; // phí dịch vụ
const FLOOR_PRICE = 2; // sàn nhà
const USE_STATUS = 1;
const USE_SERVICE = 1;
const NOT_USE_SERVICE = 0;


class ApartmentServicePriceRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\BdcApartmentServicePrice\ApartmentServicePrice::class;
    }

    public function findById($apartmentId)
    {
        return $this->model->whereHas('service', function ($query) {
            $query->where('status', '=', ServiceRepository::activeService());
        })
        ->where(['id' => $apartmentId])->first();
    }

    public function findByApartment($apartmentId)
    {
        return $this->model->whereHas('service', function ($query) {
            $query->where('status', '=', ServiceRepository::activeService());
        })
        ->where(['id' => $apartmentId])->get();
    }

    public function findByApartment_v2($apartmentId)
    {
        return $this->model->whereHas('service', function ($query) {
            $query->where('status', '=', 1);
            $query->orderBy('index_accounting');
        })
        ->where(['id' => $apartmentId,'status'=>1])->get();
    }

    public function findByApartment_v3($apartmentId)
    {
        return $this->model->whereHas('service', function ($query) {
            $query->where('status', '=', 1);
        })
        ->where(['bdc_apartment_id' => $apartmentId,'status'=>1])->get();
    }

    public function findBuildingId($buildingId, $serviceIds)
    {
        return $this->model
            ->whereHas('service', function ($query) {
                $query->where('status', '=', ServiceRepository::activeService());
            })
            ->where(['bdc_building_id' => $buildingId, 'bdc_price_type_id' => ONE_PRICE, 'status' => USE_SERVICE])
            ->whereIn('bdc_service_id', $serviceIds)
            ->orderBy('bdc_apartment_id');
    }

    public function findBuildingIdByGroupApartment($buildingId, $serviceIds,$apartmentIds)
    {
        return $this->model
            ->whereHas('service', function ($query) {
                $query->where('status', '=', ServiceRepository::activeService());
            })
            ->where(['bdc_building_id' => $buildingId, 'bdc_price_type_id' => ONE_PRICE, 'status' => USE_SERVICE])
            ->whereIn('bdc_service_id', $serviceIds)
            ->whereIn('bdc_apartment_id', $apartmentIds)
            ->orderBy('bdc_apartment_id');
    }

    public function findBuildingApartmentServiceId($buildingId, $apartmentId, $serviceId)
    {
        return $this->model->where([
            'bdc_building_id' => $buildingId, 'bdc_apartment_id' => $apartmentId, 'bdc_service_id' => $serviceId
        ])->first();
    }

    public function findApartmentUsingDienNuoc($buildingId, $apartmentId, $serviceId)
    {
        return $this->model->where([
            'bdc_building_id' => $buildingId,
            'bdc_apartment_id' => $apartmentId,
            'bdc_service_id' => $serviceId,
            'bdc_price_type_id' => MULTI_PRICE
        ])->first();
    }

    public function findBuildingIdV2($buildingId, $perPage)
    {
        return $this->model
        ->whereHas('service', function($query) {
            $query->where('status', '=', ServiceRepository::activeService());
        })
        ->where(['bdc_building_id' => $buildingId])
        ->orderBy('bdc_apartment_id')->paginate($perPage);
    }

    public function getAll($perPage)
    {
        return $this->model->paginate($perPage);
    }

    public function createServiceApartment($request, $building)
    {
        if (isset($request['price']))
        {
            $request['price'] = preg_replace("/([^0-9\\.])/i", "", $request['price']);
        }
        $service = Service::where('id', $request['bdc_service_id'])->first();
        $request['bdc_price_type_id'] = $service->servicePriceDefault->priceType->id;
        if ($request['bdc_price_type_id'] == MANY_PRICE) {
            $request['bdc_progressive_id'] = $service->servicePriceDefault->progressive->id;
        }
        if ($service->type == FLOOR_PRICE) {
            $apartment = Apartments::where('id', $request['bdc_apartment_id'])->first();
            $request['price'] = $request['floor_price'] * $apartment->area;
        }
        if($service->bdc_period_id == 6){ // tính theo chu kỳ 1 năm
            $current = Carbon::now();
            $get_cycle_year = Carbon::parse($service->first_time_active);
            $getDate = "{$current->year}-{$get_cycle_year->month}-{$get_cycle_year->day}";
            $last_time_pay = Carbon::parse($getDate)->addYear();
        }
        $serviceApart = $this->model->create([
            'bdc_service_id' => $request['bdc_service_id'],
            'bdc_price_type_id' => isset($request['bdc_price_type_id']) ? $request['bdc_price_type_id'] : ONE_PRICE,
            'bdc_apartment_id' => $request['bdc_apartment_id'],
            'name' => $service->name,
            'price' => isset($request['price']) ? $request['price'] : 0,
            'floor_price' => isset($request['floor_price']) ? $request['floor_price'] : 0,
            'first_time_active' => Carbon::parse($request['first_time_active']) ,
            'last_time_pay' => $service->bdc_period_id == 6 ? $last_time_pay : Carbon::parse($request['first_time_active']),
            'bdc_progressive_id' => isset($request['bdc_progressive_id']) ? $request['bdc_progressive_id'] : 0,
            'bdc_vehicle_id' => isset($request['bdc_vehicle_id']) ? $request['bdc_vehicle_id'] : 0,
            'bdc_building_id' => $building,
            'description' => $request['description'],
            'finish' => $request['finish'] ? Carbon::parse($request['finish']) : null,
            'user_id' => Auth::id(),
        ]);
        if(@$serviceApart->service->type == ServiceRepository::DIEN || @$serviceApart->service->type == ServiceRepository::NUOC){
            $this->model->where('id','<>', $serviceApart->id)->where(['bdc_price_type_id'=>MULTI_PRICE,'bdc_apartment_id'=>$serviceApart->bdc_apartment_id])->whereHas('service',function($query) use($serviceApart){
                $query->where('type',ServiceRepository::DIEN)
                      ->orWhere('type',ServiceRepository::NUOC);
            })->update(['status'=>0]);
        }
       
    }

    public function findApartmentServicePrice($id)
    {
        return $this->model->where('id', $id)->first();
    }
    public static function findApartmentServicePriceByApartment($apartmentId, $type)
    {
        return ApartmentServicePrice::where('bdc_apartment_id', $apartmentId)
        ->whereHas('service', function($query) use($type){
                if($type == 0){ // điện
                    $query->where('type',ServiceRepository::DIEN);
                }
                if($type == 1){ // nước
                    $query->where('type',ServiceRepository::NUOC);
                }
                if($type == 2){ // nước
                    $query->where('type',ServiceRepository::NUOC_NONG);
                }
                $query->where('status',1);
        })
        ->where(['bdc_price_type_id'=> 2,'status'=>1])->first();
    }

    public function filterApartmentServicePrice($buildingId, $name, $perPage,$request)
    {
        $filter = $this->model
        ->whereHas('service', function (Builder $query) use ($name, $buildingId) {
            $query->where('name', 'like', '%'.$name.'%')->where(['bdc_building_id' => $buildingId]);
        })
        ->whereHas('service', function($query) {
            $query->where('status', '=', ServiceRepository::activeService());
        })
        ->orWhereHas('apartment', function (Builder $query) use ($name, $buildingId) {
            $query->where('name', 'like', '%'.$name.'%')->where(['building_id' => $buildingId]);
        })
        ->where(function($query) use ($request){
            if(isset($request->bdc_price_type_id) && $request->bdc_price_type_id != null){
                $query->where('bdc_price_type_id', $request->bdc_price_type_id);
            }
        })
        ->where(['bdc_building_id' => $buildingId])->orderBy('updated_at','desc')->paginate($perPage);
        return $filter;
    }

    public function filterApartmentServicePriceByAdmin($buildingId,$request)
    {
        $filter = $this->model
                ->withTrashed()
                ->where(function($query) use ($request){
                    if(isset($request->ngay_tinh_phi) && $request->ngay_tinh_phi != null){
                        $ngay_tinh_phi = Carbon::parse($request->ngay_tinh_phi);
                        $query->whereDate('last_time_pay', $ngay_tinh_phi);
                    }
                    if(isset($request->bdc_price_type_id) && $request->bdc_price_type_id != null){
                        $query->where('bdc_price_type_id', $request->bdc_price_type_id);
                    }
                    if(isset($request->status) && $request->status != null){
                        $query->where('status', $request->status);
                    }
                })
                ->where(function($query) use ($request, $buildingId){
                    $query->whereHas('service', function (Builder $query) use ($request, $buildingId) {
                        if(isset($request->name) && $request->name != null){
                            $query->where('name', 'like', '%'.$request->name.'%')
                                  ->orWhere('id',$request->name);
                        }
                    })
                    ->orWhereHas('apartment', function (Builder $query) use ($request, $buildingId) {
                        $query->where('name', $request->name)->where(['building_id' => $buildingId]);
                    });
                })
                ->where(['bdc_building_id' => $buildingId])->orderBy('deleted_at')->orderBy('updated_at','desc');
        return $filter;
    }

    public function updateServiceApartment($request, $id, $building)
    {
        if (isset($request['price']))
        {
            $request['price'] = preg_replace("/([^0-9\\.])/i", "", $request['price']);
        }
        $apartmentService = $this->model->where('id', $id)->first();
        $service = Service::where('id', $request['bdc_service_id'])->first();

        $request['bdc_price_type_id'] = $service->servicePriceDefault->priceType->id;
        if ($request['bdc_price_type_id'] == MANY_PRICE) {
            $request['bdc_progressive_id'] = $service->servicePriceDefault->progressive->id;
        }
        if ($service->type == FLOOR_PRICE) {
            $apartment = Apartments::where('id', $request['bdc_apartment_id'])->first();
            $request['price'] = $request['floor_price'] * $apartment->area;
        }
        if($service->bdc_period_id == 6){ // tính theo chu kỳ 1 năm
            $current = Carbon::now();
            $get_cycle_year = Carbon::parse($service->first_time_active);
            $getDate = "{$current->year}-{$get_cycle_year->month}-{$get_cycle_year->day}";
            $last_time_pay = Carbon::parse($getDate)->addYear();
        }
        $apartmentService->update([
            'bdc_service_id' => $request['bdc_service_id'],
            'bdc_price_type_id' => isset($request['bdc_price_type_id']) ? $request['bdc_price_type_id'] : ONE_PRICE,
            'bdc_apartment_id' => $request['bdc_apartment_id'],
            'name' => $service->name,
            'price' => isset($request['price']) ? $request['price'] : 0,
            'floor_price' => isset($request['floor_price']) ? $request['floor_price'] : 0,
            'first_time_active' => Carbon::parse($request['first_time_active']),
            'last_time_pay' =>  $service->bdc_period_id == 6 ? $last_time_pay : Carbon::parse($request['last_time_pay']),
            'bdc_progressive_id' => isset($request['bdc_progressive_id']) ? $request['bdc_progressive_id'] : $apartmentService->bdc_progressive_id,
            'bdc_vehicle_id' => isset($request['bdc_vehicle_id']) ? $request['bdc_vehicle_id'] : 0,
            'bdc_building_id' => $building,
            'description' => $request['description'],
            'finish' => $request['finish'] ? Carbon::parse($request['finish']) : null,
            'updated_by' => auth()->user()->id,
        ]);
    }

    public function getServiceApartment($building)
    {
        $serviceApartment = $this->model->where('bdc_building_id',
            $building)->get()->pluck('bdc_service_id')->toArray();
        return array_values(array_unique($serviceApartment));
    }

    public function getServiceApartmentInactive($apartmentId,$start_date)
    {
       return $this->model->where('bdc_apartment_id',$apartmentId)->where('bdc_vehicle_id','>',0)->whereHas('vehicle',function($query) use($start_date){
                $query->where('status',0);
                $query->whereDate('finish','>', Carbon::parse($start_date)->format('Y-m-d'));
       })->get();
    }

    public function findAllIdApartmentUseService($building)
    {
        $apartments = $this->model->with('service')->where('bdc_building_id', $building)->whereHas('service',
            function (Builder $query) {
                $query->where('status', '=', USE_STATUS);
            })->get()->pluck('bdc_apartment_id')->toArray();
        return array_values(array_unique($apartments));
    }

    public function changeStatusApartment($request)
    {
        $service = $this->model->where('id', $request)->first();
        $service->updated_by = auth()->user()->id;
        if ($service->status == USE_SERVICE) {
            $service->status = NOT_USE_SERVICE;
            $service->save();
        } else {
            $service->status = USE_SERVICE;
            $service->save();
        }
    }
    public function changeStatusApartmentV2($id)
    {
        $serviceApart = $this->model->where('id', $id)->first();
        $serviceApart->updated_by = auth()->user()->id;
        if ($serviceApart->status == USE_SERVICE) {
            $serviceApart->status = NOT_USE_SERVICE;
            $serviceApart->save();
        } else {
            $serviceApart->status = USE_SERVICE;
            $serviceApart->save();
            $service = Service::get_detail_bdc_service_by_bdc_service_id($serviceApart->bdc_service_id);
            $this->model->where('id','<>', $id)->where(['bdc_price_type_id'=>MULTI_PRICE,'bdc_apartment_id'=>$serviceApart->bdc_apartment_id])->whereHas('service',function($query) use($service){
                $query->where('type',$service->type);
            })->update(['status'=>0]);
        }
    }
    public function checkTypeElectricWater($apartmentId,$serviceId)
    {
       $service = Service::get_detail_bdc_service_by_bdc_service_id($serviceId);
       if($service && ($service->type == ServiceRepository::DIEN || $service->type == ServiceRepository::NUOC)){
            return $this->model->where(['bdc_price_type_id'=>MULTI_PRICE,'bdc_apartment_id'=>$apartmentId])->whereHas('service',function($query) use($service){
                $query->where('type',$service->type);
            })->count();
       }
       return 0;
      
    }
    public function action($request)
    {
        $method = $request->input('method', '');
        if ($method == 'delete') {
            $del = $this->deleteAt($request);
            return back()->with('success',$del['msg']);
        }
            if ($method == 'update_first_time_pay') {
                if(!isset($request->update_time_pay) && $request->update_time_pay ==null)
                {
                    return back()->with('error',"chưa chọn ngày tính phí!");
                }
                    $ids = $this->updateAt($request);
                    $this->model->whereIn('id',$ids)->update(['first_time_active'=> Carbon::parse($request->update_time_pay)]);
                    return back()->with('success',"sửa ngày bắt đầu tính phí thành công!");
            }
            if ($method == 'update_last_time_pay') {
                if(!isset($request->update_time_pay) && $request->update_time_pay ==null)
                {
                    return back()->with('error',"chưa chọn ngày tính phí!");
                }
                    $ids = $this->updateAt($request);
                    $this->model->whereIn('id',$ids)->update(['last_time_pay'=>Carbon::parse($request->update_time_pay)]);
                    return back()->with('success',"sửa ngày tính phí tiếp theo thành công!");
            }
            if ($method == 'update_price_type') {
                if(!isset($request->price_type) && $request->price_type ==null)
                {
                    return back()->with('error',"chưa chọn loại giá!");
                }
                    $ids = $this->updateAt($request);
                    $this->model->whereIn('id',$ids)->update(['bdc_price_type_id'=>$request->price_type]);
                    return back()->with('success',"sửa loại giá thành công!");
            }
            if ($method == 'restore_delete') {

                $ids = $this->updateAt($request);
                $this->model->withTrashed()->whereIn('id',$ids)->restore();

                return back()->with('success',"Phục hồi thành công!");
            }
            if ($method == 'update_status') {

                $ids = $this->updateAt($request);
                $this->model->whereIn('id',$ids)->update(['status'=>$request->status]);

                return back()->with('success',"Phục hồi thành công!");
            }
        return back();
    }
    public function updateAt($request)
    {
        $ids = $request->input('ids', []);

        // chuyển sang kiểu array
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $list = [];
        foreach ($ids as $id) {
            $list[] = (int) $id;
        }

        return $list;
    }
    public function deleteAt($request)
    {
        $ids = $request->input('ids', []);

        // chuyển sang kiểu array
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $list = [];
        foreach ($ids as $id) {
            $list[] = (int) $id;
        }
        $this->model->whereIn('id',$list)->update(['updated_by'=>auth()->user()->id]);
        $number = $this->model->destroy($list);
        $message = [
            'error'  => 0,
            'status' => 'success',
            'msg'    => "Đã xóa $number bản ghi!",
        ];

        return $message;
    }
    public function filterServiceIds($serviceIds, $buildingId, $apartmentId)
    {
        return $this->model->where(['bdc_building_id' => $buildingId, 'bdc_apartment_id' => $apartmentId, 'bdc_price_type_id' => ONE_PRICE])
            ->whereNotIn('bdc_service_id', $serviceIds)->get();
    }

    public function filterApartmentId($buildingId, $apartmentId)
    {
        return $this->model->whereHas('service',function($query){
            $query->where('status',USE_STATUS);
        })->where(['bdc_building_id' => $buildingId, 'bdc_apartment_id' => $apartmentId, 'bdc_price_type_id' => ONE_PRICE, 'status'=>1])->get();
    }

    public function filterBuildingId($buildingId)
    {
        return $this->model->where(['bdc_building_id' => $buildingId]);
    }

    public function getMultiPrice()
    {
        return MULTI_PRICE;
    }
    public function getDataExport($buildingId, $name,$request)
    {
        $filter = $this->model
        ->whereHas('service', function (Builder $query) use ($name, $buildingId) {
            $query->where('name', 'like', '%'.$name.'%')->where(['bdc_building_id' => $buildingId]);
        })
        ->whereHas('service', function($query) {
            $query->where('status', '=', ServiceRepository::activeService());
        })
        ->orWhereHas('apartment', function (Builder $query) use ($name, $buildingId) {
            $query->where('name', 'like', '%'.$name.'%')->where(['building_id' => $buildingId]);
        })
        ->where(function($query) use ($request){
            if(isset($request->bdc_price_type_id) && $request->bdc_price_type_id != null){
                $query->where('bdc_price_type_id', $request->bdc_price_type_id);
            }
        })
        ->where(['bdc_building_id' => $buildingId])->get();
        return $filter;
    }
     public function unsetApartmentService($data_apartmentservice)
    {
        $duplicate=[];
        $array_data_apartmentservice=[];
        for ($i = 0; $i < count($data_apartmentservice); $i++) {
            $apartmentservice_data=[
                        'bdc_service_id' => $data_apartmentservice[$i]['bdc_service_id'],
                        'code' => $data_apartmentservice[$i]['code'],
                        'bdc_price_type_id' => $data_apartmentservice[$i]['bdc_price_type_id'],
                        'bdc_apartment_id' =>$data_apartmentservice[$i]['bdc_apartment_id'],
                        'name' =>$data_apartmentservice[$i]['name'],
                        'price' =>$data_apartmentservice[$i]['price'],
                        'first_time_active' =>$data_apartmentservice[$i]['first_time_active'],
                        'last_time_pay' => $data_apartmentservice[$i]['last_time_pay'],
                        'bdc_vehicle_id' =>$data_apartmentservice[$i]['bdc_vehicle_id'],
                        'bdc_building_id' =>$data_apartmentservice[$i]['bdc_building_id'],
                        'bdc_progressive_id' =>$data_apartmentservice[$i]['bdc_progressive_id'],
                        'description' => $data_apartmentservice[$i]['description'],
                        'floor_price' => $data_apartmentservice[$i]['floor_price'],
                        'status' => $data_apartmentservice[$i]['status'],
                        'user_id' => $data_apartmentservice[$i]['user_id'],                 
            ];
            array_push($array_data_apartmentservice, $apartmentservice_data);
            for ($j = $i + 1; $j < count($data_apartmentservice); $j++) {
                if ($data_apartmentservice[$i]['bdc_service_id'] == $data_apartmentservice[$j]['bdc_service_id'] && $data_apartmentservice[$i]['bdc_apartment_id'] == $data_apartmentservice[$j]['bdc_apartment_id'] && $data_apartmentservice[$i]['bdc_building_id'] == $data_apartmentservice[$j]['bdc_building_id']) {
                    $duplicate_data=[
                        'bdc_service_id'   => $data_apartmentservice[$j]['bdc_service_id'],
                        'bdc_apartment_id' => $data_apartmentservice[$j]['bdc_apartment_id'],
                        'bdc_building_id'  => $data_apartmentservice[$j]['bdc_building_id'],
                        'code' => $data_apartmentservice[$j]['code'],
                        'floor_price' =>  $data_apartmentservice[$j]['floor_price'],
                        'price' => $data_apartmentservice[$j]['price'],
                        'bdc_price_type_id' => $data_apartmentservice[$j]['bdc_price_type_id'],
                        'first_time_active' => $data_apartmentservice[$j]['first_time_active'],
                        'last_time_pay' =>  $data_apartmentservice[$j]['last_time_pay'],
                    ];
                    array_push($duplicate, $duplicate_data);
                    unset($array_data_apartmentservice[$i]);
                }
            }
           
        }
        $data = [
            'data' => $array_data_apartmentservice,
            'duplicate' => $duplicate,
        ];

        return $data;
    }
    public function ApartmentServiceExelData($data_apartmentservice)
    {
      
        $has_ap=[];$fail_ap=[];$new_ap=[];
        foreach ($data_apartmentservice['data'] as $key => $value) {
              $check_apartmentservice = $this->GetApartmentService($value['bdc_service_id'],$value['bdc_apartment_id'],$value['bdc_building_id']);
            if (!in_array($value['bdc_apartment_id'], $check_apartmentservice)) {
                if($value['bdc_service_id'] && $value['bdc_apartment_id'] && $value['bdc_building_id']) {
                    $new_ap[] = [
                        'bdc_service_id' => $value['bdc_service_id'],
                        'code' => $value['code'],
                        'bdc_price_type_id' => $value['bdc_price_type_id'],
                        'bdc_apartment_id' => $value['bdc_apartment_id'],
                        'name' => $value['name'],
                        'price' => $value['price'],
                        'first_time_active' => $value['first_time_active'],
                        'last_time_pay' => $value['last_time_pay'],
                        'bdc_vehicle_id' => $value['bdc_vehicle_id'],
                        'bdc_building_id' => $value['bdc_building_id'],
                        'bdc_progressive_id' => $value['bdc_progressive_id'],
                        'description' => $value['description'],
                        'floor_price' => $value['floor_price'],
                        'status' => $value['status'],
                        'user_id' => $value['user_id'],    
                    ];
                }else{
                    $fail_ap[] = [
                        'bdc_service_id'   =>  $value['bdc_service_id'],
                        'bdc_apartment_id' =>  $value['bdc_apartment_id'],
                        'bdc_building_id'  =>  $value['bdc_building_id'],
                    ];
                }
            } else {
                $has_ap[] =[
                        'bdc_service_id'   =>  $value['bdc_service_id'],
                        'bdc_apartment_id' =>  $value['bdc_apartment_id'],
                        'bdc_building_id'  =>  $value['bdc_building_id'],
                ];
            }
        }

        
        $data = [
            'data' => $new_ap,
            'duplicate' =>$data_apartmentservice['duplicate'],
        ];

        return $data;

    }
    public function GetApartmentService($service_id,$apartment_id,$building_id)
    {
        return $this->model::where(['bdc_service_id'=>$service_id,'bdc_apartment_id'=>$apartment_id,'bdc_building_id'=>$building_id])->pluck('bdc_apartment_id')->toArray();
    }

    public function updateLastTimePay($fromDate, $apartmentServicePriceId)
    {
        return $this->model->where('id', $apartmentServicePriceId)->update(['last_time_pay' => $fromDate]);
    }

    public static function getServicePriceByServiceId($service_id)
    {
        return ApartmentServicePrice::where(['bdc_service_id'=>$service_id])->get();
    }

    public static function getServicePriceByServiceIdV2($request)
    {
        return ApartmentServicePrice::where(function($query) use($request){
            if (isset($request->service) && $request->service != null) {
                $query->where('bdc_service_id', $request->service);
            }
            if (isset($request->type_service) && $request->type_service != null) {
                $query->whereHas('service', function ($query) use ($request) {
                    $query->where('type', $request->type_service);
                });
            }
        })->get();
    }

    public static function getServicePriceByServiceIdAll($request,$buildingId)
    {
        return ApartmentServicePrice::where(function($query) use($request,$buildingId){
            $query->where('bdc_building_id', $buildingId);
            if (isset($request->service) && $request->service != null) {
                $query->where('bdc_service_id', $request->service);
            }
            if (isset($request->type_service) && $request->type_service != null) {
                $query->whereHas('service', function ($query) use ($request) {
                    $query->where('type', $request->type_service);
                });
            }
        })->orderBy('bdc_service_id')->pluck('id')->toArray();
    }

    public static function getInfoServiceApartmentById($id)
    {
        $keyCache = "getInfoServiceApartmentById_" . $id;
        return Cache::remember($keyCache, 10 ,function () use ($keyCache, $id) {
            $rs = DB::table('bdc_apartment_service_price')->find($id);
            if (!$rs) return null;
            return (object) $rs;
        });
    }
    public static function getInfoServiceApartmentByVehicle($vehicleId)
    {
        $rs = Cache::store('redis')->get(env('REDIS_PREFIX') . 'get_detail_byVehicleId_'.$vehicleId);
 
        if($rs){
             return $rs;
        }
        $rs = ApartmentServicePrice::where('bdc_vehicle_id',$vehicleId)->first(); // lấy ra thông tin dự án
        if(!$rs){
             return false;
        }
         Cache::store('redis')->put(env('REDIS_PREFIX') . 'get_detail_byVehicleId_' . $vehicleId, $rs,60*60);
         return $rs;
    }
    public static function findApartmentServicePrice_v2($id)
    {
        return ApartmentServicePrice::find($id);
    }
    public function findServiceAparmtent(array $options = [],$building_id)
    {
        $default = [
            'select'   => '*',
            'where'    => [],
            'order_by' => 'id DESC',
            'per_page' => 20,
        ];

        $options = array_merge($default, $options);

        extract($options);

        $model = $this->model->select($options['select']);

        if ($options['where']) {
            $model = $model->where($options['where']);
        }
        $model = $model->where('bdc_building_id',$building_id);
        $model = $model->where('status',1);
        return $model->orderByRaw($options['order_by'])->get();
    }
}
