<?php

namespace App\Repositories\Service;

use App\Helpers\dBug;
use App\Models\Apartments\Apartments;
use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Models\BdcProgressivePrice\ProgressivePrice;
use App\Models\BdcProgressives\Progressives;
use App\Models\Building\Building;
use App\Models\Service\Service;
use App\Models\Service\ServicePriceDefault;
use App\Models\VehicleCategory\VehicleCategory;
use App\Repositories\Eloquent\Repository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Webpatser\Uuid\Uuid;
use Illuminate\Database\Eloquent\Builder;
use App\Models\BdcBills\Bills;
use App\Models\BdcDebitDetail\DebitDetail;
use Illuminate\Support\Facades\Auth;

const PAGE = 10;
const NO_BUILDING = 0;
const NO_COMPANY = 0;
const BUILDING_USER = 1;
const USE_SERVICE = 1;
const NOT_USE_SERVICE = 0;
const ONE_PRICE = 1;
const MANY_PRICE = 2;
const UTILITIES = 4;
const VND = "VND";
const ONE_MONTH = 1;
const USE_STATUS = 1;
const TYPEVEHICLE = 1;
const NOTYPEVEHICLE = 0;
const FLOOR_PRICE = 2;

class ServiceRepository extends Repository
{
    const DIEN = 5;
    const NUOC = 3;
    const NUOC_NONG = 6;
    const TIEN_ICH = 7;
    const DICHVU = 2;
    const PHUONG_TIEN = 4;
    const PHI_KHAC = 0;
    function model()
    {
        return Service::class;
    }

    public function getAll()
    {
        return $this->model->paginate(PAGE);
    }

    public function getAllServiceCompany($perPage)
    {
        $company =Auth::user()->company_staff->company->id ?? 1;
        return $this->model->where('bdc_building_id', NO_BUILDING)->where('company_id', $company)->withCount('apartmentUseService')->withCount('children')->paginate($perPage);
    }

    public function getAllServiceCompanySelect($select='*',$apartment_id, $building_active_id)
    {
        $company =Auth::user()->company_staff->company->id ?? 1;
        return $this->model->with(['apartmentServicePrices' => function($q) use($apartment_id) {$q->where('bdc_apartment_id', $apartment_id);}, 'apartmentServicePrices.vehicle', 'apartmentServicePrices.apartment'])->select($select)->where('bdc_building_id', $building_active_id)->where('company_id', $company)->whereHas('apartmentServicePrices', function ($query) use ($apartment_id) { $query->where('bdc_apartment_id', '=', $apartment_id); })->get();
    }
    public function getAllServiceCompanySelect1($select='*',$apartment_id, $building_active_id)
    {
        return $this->model->with([
            'apartmentServicePrices' => function($q) use($apartment_id) {
                    $q->where('bdc_apartment_id', $apartment_id);
                    $q->where('status', 1);
                },
            'apartmentServicePrices.vehicle',
            'apartmentServicePrices.apartment'
        ])->select($select)->where('bdc_building_id', $building_active_id)
        ->whereHas('apartmentServicePrices', function ($query) use ($apartment_id) {
            $query->where('bdc_apartment_id', '=', $apartment_id);
        })->get();
    }

    public function findByApartment($request)
    {
        return $this->model->whereHas('apartmentServicePrices', function ($query) use ($request){
            $query->where('status', 1) // trạng thái hoạt động
                  ->where(['bdc_apartment_id' => $request->bdc_apartment_id]);
        })->where('status', 1)->select('id','name')->get();
    }
    public function searchByAll(array $options = [],$building_id, $type_service = null)
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
        if($type_service){
          $model = $model->where('type',$type_service);
        }
        return $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
    }
    public function getAllChoose($perPage)
    {
        $company =Auth::user()->company_staff->company->id ?? 1;
        $companyServices = $this->model->where('bdc_building_id', NO_BUILDING)->where('company_id',
            $company)->get();
        $services = $this->model->where('bdc_building_id', NO_BUILDING)->where('company_id',
            NO_COMPANY)->paginate($perPage);
        $data['companyServices'] = $companyServices->pluck('status', 'service_code')->toArray();
        $data['services'] = $services;
        return $data;
    }

    public function createServiceCompany($request)
    {
        $company =Auth::user()->company_staff->company->id ?? 1;
        if (isset($request['price']))
        {
            $request['price'] = preg_replace("/([^0-9\\.])/i", "", $request['price']);
        }
        DB::beginTransaction();
        try {
            $service = $this->model->create([
                'bdc_building_id' => NO_BUILDING,
                'bdc_period_id' => ONE_MONTH,
                'name' => $request['name'],
                'description' => $request['description'],
                'bill_date' => $request['bill_date'],
                'payment_deadline' => $request['payment_deadline'],
                'first_time_active' => $request['first_time_active'],
                'unit' => VND,
                'company_id' => $company,
                'service_code' => Uuid::generate(),
                'type' => isset($request['type']) ? $request['type'] : 0,
                'service_group' => $request['service_group']
            ]);
            if ($request['progressive_id']) {
                $progressiveId = Progressives::where('id', $request['progressive_id'])->first();
                $progressive = Progressives::create([
                    'name' => $progressiveId->name,
                    'building_id' => NO_BUILDING,
                    'company_id' => $company,
                    'bdc_price_type_id' => $request['bdc_price_type_id']
                ]);
            } else {
                $progressive = Progressives::create([
                    'name' => $service->name,
                    'building_id' => NO_BUILDING,
                    'company_id' => $company,
                    'bdc_price_type_id' => $request['bdc_price_type_id']
                ]);
            }
            if ($request['bdc_price_type_id'] == ONE_PRICE) {
                ProgressivePrice::create([
                    'name' => $service->name,
                    'from' => 0,
                    'to' => 0,
                    'price' => $request['price'],
                    'progressive_id' => $progressive->id,
                ]);
            } else {
                $progressPrice = ProgressivePrice::where('progressive_id', $request['progressive_id'])->get();
                foreach ($progressPrice as $key => $value) {
                    ProgressivePrice::create([
                        'name' => $value->name,
                        'from' => $value->from,
                        'to' => $value->to,
                        'price' => $value->price,
                        'progressive_id' => $progressive->id
                    ]);
                }
            }
            ServicePriceDefault::create([
                'bdc_building_id' => NO_BUILDING,
                'bdc_service_id' => $service->id,
                'bdc_price_type_id' => $request['bdc_price_type_id'],
                'name' => $service->name,
                'price' => isset($request['price']) ? $request['price'] : 0,
                'progressive_id' => $progressive->id,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            throw new \Exception($e->getMessage());
        }
        return $service;
    }

    public function updateServiceCompany($request, $id)
    {
        $company =Auth::user()->company_staff->company->id ?? 1;
        if (isset($request['price']))
        {
            $request['price'] = preg_replace("/([^0-9\\.])/i", "", $request['price']);
        }
        $service = $this->model->find($id);
        DB::beginTransaction();
        try {

            $service->update([
                'bdc_building_id' => NO_BUILDING,
                'bdc_period_id' => ONE_MONTH,
                'name' => $request['name'],
                'description' => $request['description'],
                'bill_date' => $request['bill_date'],
                'payment_deadline' => $request['payment_deadline'],
                'first_time_active' => $request['first_time_active'],
                'unit' => VND,
                'company_id' => $company,
                'service_code' => $service->service_code,
                'type' => isset($request['type']) ? $request['type'] : 0,
                'service_group' => $request['service_group']
            ]);
            $progressiveId = Progressives::where('id', $service->servicePriceDefault->progressive->id)->first();
            $progressive = $service->servicePriceDefault->progressive->update([
                'name' => $progressiveId->name,
                'building_id' => NO_BUILDING,
                'company_id' => $company,
                'bdc_price_type_id' => $request['bdc_price_type_id']
            ]);

            ProgressivePrice::where('progressive_id', $id)->delete();
            if ($request['bdc_price_type_id'] == ONE_PRICE) {
                ProgressivePrice::create([
                    'name' => $request['name'],
                    'from' => 0,
                    'to' => 0,
                    'price' => $request['price'],
                    'progressive_id' => $service->servicePriceDefault->progressive->id,
                ]);

            } else {
                $progressPrice = ProgressivePrice::where('progressive_id', $request['progressive_id'])->get();
                foreach ($progressPrice as $key => $value) {
                    ProgressivePrice::create([
                        'name' => $value->name,
                        'from' => $value->from,
                        'to' => $value->to,
                        'price' => $value->price,
                        'progressive_id' => $service->servicePriceDefault->progressive->id
                    ]);
                }
            }

            $service->servicePriceDefault->update([
                'bdc_building_id' => NO_BUILDING,
                'bdc_service_id' => $service->id,
                'bdc_price_type_id' => $request['bdc_price_type_id'],
                'name' => $service->name,
                'price' => isset($request['price']) ? $request['price'] : 0,
                'progressive_id' => $service->servicePriceDefault->progressive->id,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            throw new \Exception($e->getMessage());
        }
        return $service;
    }

    public function filterCompany($name,$perPage)
    {
        if ($name) {
            $company =Auth::user()->company_staff->company->id ?? 1;
            return $this->model->where('bdc_building_id', NO_BUILDING)->where('company_id', $company)->where('name',
                'like', '%'.$name.'%')->withCount('apartmentUseService')->withCount('children')->paginate($perPage);
        }
    }

    public function changeStatusCompany($request)
    {
        if ($request->status == 'Active') {
            $this->model->whereIn('id', $request->ids)->update(['status' => USE_SERVICE]);
        } elseif ($request->status == 'Inactive') {
            $this->model->whereIn('id', $request->ids)->update(['status' => NOT_USE_SERVICE]);
        } else {
            $service = $this->model->where('id',$request->id)->first();
            if ($service->status == USE_SERVICE) {
                $service->status = NOT_USE_SERVICE;
                $service->save();
            } else {
                $service->status = USE_SERVICE;
                $service->save();
            }
        }
    }

    public function findService($id)
    {
        return $this->model->where(['status' => USE_SERVICE, 'id' => $id])->first();
    }
    public function findServiceV2($id)
    {
        return $this->model->where(['id' => $id])->first();
    }

    public function postChooseCompany($ids)
    {
        $company =Auth::user()->company_staff->company->id ?? 1;
        $ids = $this->checkSaveChoose($ids);
        if (count($ids) > 0) {
            DB::beginTransaction();
            try {
                $services = $this->model->whereIn('id', $ids)->get();
                foreach ($services as $service) {
                    $serviceNew = $this->model->create([
                        'bdc_building_id' => NO_BUILDING,
                        'bdc_period_id' => $service->bdc_period_id,
                        'name' => $service->name,
                        'unit' => $service->unit,
                        'description' => $service->description,
                        'bill_date' => $service->bill_date,
                        'payment_deadline' => $service->payment_deadline,
                        'company_id' => $company,
                        'service_code' => $service->service_code,
                        'first_time_active' => $service->first_time_active,
                    ]);
                    $progressiveId = Progressives::where('id', $service->servicePriceDefault->progressive->id)->first();
                    $progressive = Progressives::create([
                        'name' => $progressiveId->name,
                        'building_id' => NO_BUILDING,
                        'company_id' => $company,
                        'bdc_price_type_id' => $service->servicePriceDefault->priceType->id,
                    ]);
                    if ($progressiveId->priceType->id == ONE_PRICE) {
                        ProgressivePrice::create([
                            'name' => $service->name,
                            'from' => 0,
                            'to' => 0,
                            'price' => $service->servicePriceDefault->price,
                            'progressive_id' => $progressive->id,
                        ]);
                    } else {
                        $progressPrice = ProgressivePrice::where('progressive_id', $progressiveId->id)->get();
                        foreach ($progressPrice as $key => $value) {
                            ProgressivePrice::create([
                                'name' => $value->name,
                                'from' => $value->from,
                                'to' => $value->to,
                                'price' => $value->price,
                                'progressive_id' => $progressive->id
                            ]);
                        }
                    }
                    ServicePriceDefault::create([
                        'bdc_building_id' => NO_BUILDING,
                        'bdc_service_id' => $serviceNew->id,
                        'bdc_price_type_id' => $service->servicePriceDefault->priceType->id,
                        'name' => $service->name,
                        'price' => isset($service->servicePriceDefault->price) ? $service->servicePriceDefault->price : 0,
                        'progressive_id' => $progressive->id,
                    ]);
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();

                throw new \Exception($e->getMessage());
            }
        }
    }

    // man hinh index service building
    public function getAllServiceBuilding($perPage,$building)
    {
        return $this->model->where('bdc_building_id', $building)->withCount('apartmentServicePrices')->orderBy('index_accounting','ASC')->paginate($perPage);
        // return collect([]);
    }
    
    // man hinh index service building
    public function getAllServiceBuilding_v2($building)
    {
        return $this->model->where('bdc_building_id', $building)->withCount('apartmentServicePrices')->get();
    }
    // man hinh choose service building
    public function getAllChooseBuilding($perPage,$building)
    {
        $company =Auth::user()->company_staff->company->id ?? 1;
        $buildingServices = $this->model->where('bdc_building_id', $building )->where('company_id', $company)->get();
        $data['buildingServices'] = $buildingServices->pluck('status', 'service_code')->toArray();
        $data['services'] = $this->model->where('bdc_building_id', NO_BUILDING)->where('company_id', $company)->where('status',USE_SERVICE)->paginate($perPage);
        return $data;

    }

    public function filterBuilding($name,$perPage,$building)
    {
        if ($name) {
            $company =Auth::user()->company_staff->company->id ?? 1;
            return $this->model->where('bdc_building_id', $building)->where('company_id',
                $company)->where('name',
                'like', '%'.$name.'%')->withCount('apartmentServicePrices')->orderBy('index_accounting','ASC')->paginate($perPage);
        }
    }

    public function getfilterById($service_id)
    {
        return $this->model->find($service_id);
    }

    public function getAllServiceBuildingApartment_by_range_date_v2($building,$get_list_apartment)
    {
        return $this->model->where(['bdc_building_id'=> $building,'status' =>1])->where('type','<>',self::PHUONG_TIEN)->whereHas('apartmentServicePrices',function($query) use ($get_list_apartment){
            if(isset($get_list_apartment)){
                $query->whereIn('bdc_apartment_id', $get_list_apartment);
            }
           
        })->get();
    }

    public function postChooseBuilding($ids,$building)
    {
        $company =Auth::user()->company_staff->company->id ?? 1;
        $ids = $this->checkSaveChooseBuilding($ids, $building);

        if (count($ids) > 0) {
            DB::beginTransaction();
            try {
                $services = $this->model->whereIn('id', $ids)->get();
                foreach ($services as $service) {
                    $serviceNew = $this->model->create([
                        'bdc_building_id' => $building,
                        'bdc_period_id' => $service->bdc_period_id,
                        'name' => $service->name,
                        'unit' => $service->unit,
                        'description' => $service->description,
                        'bill_date' => $service->bill_date,
                        'payment_deadline' => $service->payment_deadline,
                        'company_id' => $company,
                        'service_code' => $service->service_code,
                        'first_time_active' => $service->first_time_active,
                        'service_group' => $service->service_group
                    ]);

                    if($service->servicePriceDefault == null) {
                        continue;
                    }
                    if($service->servicePriceDefault->progressive == null) {
                        continue;
                    }

                    $progressiveId = Progressives::where('id', $service->servicePriceDefault->progressive->id)->first();
                    $progressive = Progressives::create([
                        'name' => $progressiveId->name,
                        'building_id' => $building,
                        'company_id' => $company,
                        'bdc_price_type_id' => $service->servicePriceDefault->priceType->id,
                    ]);

                    if ($progressiveId->priceType->id == ONE_PRICE) {
                        ProgressivePrice::create([
                            'name' => $service->name,
                            'from' => 0,
                            'to' => 0,
                            'price' => $service->servicePriceDefault->price,
                            'progressive_id' => $progressive->id,
                        ]);
                    } else {
                        $progressPrice = ProgressivePrice::where('progressive_id', $progressiveId->id)->get();
                        foreach ($progressPrice as $key => $value) {
                            ProgressivePrice::create([
                                'name' => $value->name,
                                'from' => $value->from,
                                'to' => $value->to,
                                'price' => $value->price,
                                'progressive_id' => $progressive->id
                            ]);
                        }
                    }
                    ServicePriceDefault::create([
                        'bdc_building_id' => $building,
                        'bdc_service_id' => $serviceNew->id,
                        'bdc_price_type_id' => $service->servicePriceDefault->priceType->id,
                        'name' => $service->name,
                        'price' => isset($service->servicePriceDefault->price) ? $service->servicePriceDefault->price : 0,
                        'progressive_id' => $progressive->id,
                    ]);
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();

                throw new \Exception($e->getMessage());
            }
        }
    }

    public function createServiceBuilding($request,$building)
    {
        $company =Auth::user()->company_staff->company->id ?? 1;
        if (isset($request['price']))
        {
            $request['price'] = preg_replace("/([^0-9\\.])/i", "", $request['price']);
        }
       
        $first_time_active = Carbon::parse($request['first_time_active']);
        DB::beginTransaction();
        try {
            $service = $this->model->create([
                'bdc_building_id' => $building,
                'bdc_period_id' => $request['bdc_period_id'],
                'name' => $request['name'],
                'description' => $request['description'],
                'bill_date' => $request['bdc_period_id'] == 6 ? (int)$first_time_active->day : $request['bill_date'],
                'payment_deadline' => $request['bdc_period_id'] == 6 ? (int)$first_time_active->day : $request['payment_deadline'],
                'first_time_active' => isset($request['first_time_active']) ? $first_time_active : null,
                'ngay_chuyen_doi' => $request['ngay_chuyen_doi'],
                'unit' => VND,
                'company_id' => $company,
                'service_code' => Uuid::generate(),
                'type' => isset($request['type']) ? $request['type'] : 0,
                'service_group' => $request['service_group'],
                'code_receipt' => isset($request['code_receipt']) ? $request['code_receipt'] : null,
                'user_id' => auth()->user()->id,
                'partner_id' => @$request['partner_id'],
                'price_free' => @$request['price_free'],
                'check_confirm' => @$request['check_confirm'],
                'persion_register' => @$request['persion_register'],
                'service_type' => @$request['service_type'],
                'status' => $request['type'] == 7 ? 0 : 1,
            ]);
            if ($request['progressive_id']) {
                $progressive = Progressives::where('id', $request['progressive_id'])->first();
                $progressive->bdc_service_id = $service->id;
                $progressive->save();
            }
            ServicePriceDefault::create([
                'bdc_building_id' => $building,
                'bdc_service_id' => $service->id,
                'bdc_price_type_id' => $request['bdc_price_type_id'],
                'name' => $service->name,
                'price' => isset($request['price']) ? $request['price'] : 0,
                'progressive_id' => isset($progressive->id) ? $progressive->id : 0,
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            throw new \Exception($e->getMessage());
        }
        return $service;
    }

    public function updateServiceBuilding($request, $id,$building)
    {
        $company =Auth::user()->company_staff->company->id ?? 1;
        if (isset($request['price']))
        {
            $request['price'] = preg_replace("/([^0-9\\.])/i", "", $request['price']);
        }
      
        $service = $this->model->where('id',$id)->first();
        $first_time_active = Carbon::parse($request['first_time_active']);
        DB::beginTransaction();
        try {
            $service->update([
                'bdc_building_id' => $building,
                'bdc_period_id' => $request['bdc_period_id'],
                'name' => $request['name'],
                'description' => $request['description'],
                'bill_date' => $request['bdc_period_id'] == 6 ? (int)$first_time_active->day : $request['bill_date'],
                'payment_deadline' => $request['bdc_period_id'] == 6 ? (int)$first_time_active->day : $request['payment_deadline'],
                'first_time_active' => isset($request['first_time_active']) ? $first_time_active : null,
                'ngay_chuyen_doi' => $request['ngay_chuyen_doi'],
                'unit' => VND,
                'company_id' => $company,
                'service_code' => $service->service_code,
                'type' => isset($request['type']) ? $request['type'] : 0,
                'service_group' => $request['service_group'],
                'code_receipt' => isset($request['code_receipt']) ? $request['code_receipt'] : $service->code_receipt,
                'partner_id' => @$request['partner_id'],
                'price_free' => @$request['price_free'],
                'check_confirm' => @$request['check_confirm'],
                'persion_register' => @$request['persion_register'],
                'service_type' => @$request['service_type'],
                'status' => $request['type'] == 7 ? 0 : 1
            ]);
            $service->servicePriceDefault->update([
                'bdc_building_id' => $building,
                'bdc_service_id' => $service->id,
                'bdc_price_type_id' => $request['bdc_price_type_id'],
                'name' => $service->name,
                'price' => isset($request['price']) ? $request['price'] : 0,
                'progressive_id' => $request['progressive_id']
            ]);
            if ($request['progressive_id']) {
                $progressive = Progressives::where('id', $request['progressive_id'])->first();
                $progressive->bdc_service_id = $service->id;
                $progressive->save();
            }
            if($request['bdc_price_type_id'] == 1 && $service->type == 2){
                $get_list_apartment_service = ApartmentServicePrice::where(['bdc_building_id'=>$building,'bdc_service_id'=>$service->id,'bdc_price_type_id'=>1])->get();
                if($get_list_apartment_service){
                       foreach ($get_list_apartment_service as $key => $value) {
                           $apartment = Apartments::get_detail_apartment_by_apartment_id($value->bdc_apartment_id);
                           $area = $apartment->area ? $apartment->area : 0; 
                           $value->price = $area > 0 ? $request['price'] * $area : $request['price'];
                           $value->floor_price = $request['price'];
                           $value->save();
                       }
                }
            }
            if($request['bdc_price_type_id'] == 1 && $service->type == 0 && Auth::user()->isadmin == 1){
                $get_list_apartment_service = ApartmentServicePrice::where(['bdc_building_id'=>$building,'bdc_service_id'=>$service->id,'bdc_price_type_id'=>1])->get();
                if($get_list_apartment_service){
                       foreach ($get_list_apartment_service as $key => $value) {
                           $value->price = @$service->servicePriceDefault->price;
                           $value->save();
                       }
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }
        return $service;
    }

    public function getServiceApartment($building)
    {
        $_building = Building::get_detail_building_by_building_id($building);
        return $this->model->where('bdc_building_id', $building)->where('status',
            USE_STATUS)->get();
    }

    private function checkSaveChoose($ids)
    {
        $company =Auth::user()->company_staff->company->id ?? 1;
        $companyServices = $this->model->where('bdc_building_id', NO_BUILDING)->where('company_id',
            $company)->get();
        $checkCompanyServiceUse = $companyServices->where('status', USE_SERVICE)->pluck('service_code')->toArray();
        $checkCompanyServices = $companyServices->where('status', NOT_USE_SERVICE)->pluck('service_code')->toArray();
        $services = $this->model->whereIn('id', $ids)->pluck('service_code')->toArray();
        $codeServices = array_unique(array_merge($checkCompanyServiceUse, $services));
        $servicesCheck = array_diff($codeServices, $checkCompanyServiceUse);
        $data['deleteServiceCompany'] = array_diff($checkCompanyServices, $servicesCheck);
        $data['addServiceCompany'] = array_diff($servicesCheck, $checkCompanyServices);
        $this->deleteServiceCompany($data['deleteServiceCompany']); //xoa cac service khong chon
        return $this->model->whereIn('service_code', $data['addServiceCompany'])->pluck('id')->toArray();
    }

    private function deleteServiceCompany($serviceCodes)
    {
        $company =Auth::user()->company_staff->company->id ?? 1;
        $serviceDeletes = $this->model->where('bdc_building_id', NO_BUILDING)->where('company_id',
            $company)->whereIn('service_code', $serviceCodes)->get();
        foreach ($serviceDeletes as $service) {
            DB::beginTransaction();
            try {
                $progressivePrice = $service->servicePriceDefault->progressive->progressivePrice;
                foreach ($progressivePrice as $progress) {
                    $progress->delete();
                }
                $service->servicePriceDefault->progressive->delete();
                $service->servicePriceDefault->delete();
                $service->delete();
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();

                throw new \Exception($e->getMessage());
            }
        }
    }

    public function getServiceBuildingDebitDetail()
    {
        $company =Auth::user()->company_staff->company->id ?? 1;
        $buildingServices = $this->model->where('bdc_building_id', BUILDING_USER)->where('company_id',
            $company)->where('status', USE_STATUS)->get();
        $buildingServiceCodes = $buildingServices->pluck('service_code')->toArray();
        $services = $this->model->where('bdc_building_id', NO_BUILDING)->where('company_id',
            $company)->where('status', USE_STATUS)->whereIn('service_code',
            $buildingServiceCodes)->paginate(PAGE);

        return $services;
    }

    public function getServiceOfApartment($id, $buildingId)
    {
        return $this->model->select('name', 'id')
        ->where('status', USE_STATUS)
        ->where('bdc_building_id', $buildingId)
        ->whereIn('id',$id)->get();
    }
    public function getServiceOfApartment_v4($buildingId)
    {
        return $this->model->select('name', 'id')
        ->where('bdc_building_id', $buildingId)->get();
    }

    public function getServiceOfApartment_v2($buildingId)
    {
        return $this->model->select('name', 'id')
        ->whereHas('servicePriceDefault' , function (Builder $query) {
            $query->where('status', USE_STATUS);
        })
        ->where('status', USE_STATUS)
        ->where('bdc_building_id', $buildingId)->get();
    }
    public function getServiceOfApartment_v3_log($buildingId)
    {
        return $this->model->select('name', 'id')->where('bdc_building_id', $buildingId)->get();
    }

    public function per_page($request)
    {
        $per_page = $request->input('per_page', 20);

        Cookie::queue('per_page', $per_page, 60 * 24 * 30);

        return back();
    }

    public function action($request)
    {
        $method = $request->input('method','');
        if ($method == 'per_page') {
            $this->per_page($request);
        }
        if ($method == 'delete') {
            $del = $this->deleteAt($request);
            return back()->with('success', $del['msg']);
        }
        if ($method == 'update_dateline') {
            if(!isset($request->update_dateline) && $request->update_dateline == null)
            {
                return back()->with('error',"chưa chọn hạn thanh toán!");
            }
            $ids = $this->updateAt($request);
            Bills::whereIn('id',$ids)->update(['deadline'=>Carbon::parse($request->update_dateline)]);
            return back()->with('success',"sửa ngày hạn thanh toán thành công!");
        }
        // Sửa trạng thái hóa đơn
        if ($method == 'update_status_bill') {
            if(!isset($request->update_status_bill) && $request->update_status_bill == null)
            {
                return back()->with('error',"chưa chọn trạng thái hóa đơn!");
            }
            $ids = $this->updateAt($request);
            foreach ($ids as $key => $value) {
                $_bill = Bills::find($value);
                if($_bill){
                    $_bill->update(['customer_address'=> $_bill->status, 'status'=>$request->update_status_bill]);
                }
            }
            return back()->with('success',"sửa trạng thái thành công!");
        }
        // Xóa hóa đơn
        if ($method == 'del_bill_debit') {
            $ids = $this->updateAt($request);
            Bills::whereIn('id',$ids)->delete();
            DebitDetail::whereIn('bdc_bill_id',$ids)->delete();
            return back()->with('success',"xóa hóa đơn thành công!");
        }
        // Phục hồi hóa đơn
        if ($method == 'restore_bill_debit') {
            $ids = $this->updateAt($request);
            Bills::withTrashed()->whereIn('id',$ids)->restore();
            DebitDetail::withTrashed()->whereIn('bdc_bill_id',$ids)->restore();
            return back()->with('success',"phục hồi hóa đơn thành công!");
        }
        return back();
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
            $service = Service::find($id);
            $service->user_id = auth()->user()->id;
            $service->save();
            Cache::store('redis')->forget( env('REDIS_PREFIX') . 'get_detail_bdc_serviceById_'.$id);
            $list[] = (int) $id;
        }

        $number = $this->model->destroy($list);

        $message = [
            'error'  => 0,
            'status' => 'success',
            'msg'    => "Đã xóa $number bản ghi!",
        ];

        return $message;
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
            Cache::store('redis')->forget( env('REDIS_PREFIX') . 'get_detail_bdc_serviceById_'.$id);
            $list[] = (int) $id;
        }

        return $list;
    }
    public static function activeService()
    {
        return USE_SERVICE;
    }

    public static function getOnePrice()
    {
        return ONE_PRICE;
    }

    public static function getManyPrice()
    {
        return MANY_PRICE;
    }

    public function getServiceApartmentAjax($id)
    {
        $service = $this->model->where('id',$id)->first();
        $servicePriceDefault = $service->servicePriceDefault;
        $servicePriceDefault->bdc_period_id = $service->bdc_period_id;
        $servicePriceDefault->first_time_active = $service->first_time_active;
        return $servicePriceDefault;
    }

    public function getServiceById($id)
    {
        return $this->model->where('id',$id)->first();
    }

    private function checkSaveChooseBuilding($ids, $building)
    {
        $company =Auth::user()->company_staff->company->id ?? 1;
        $buildingServices = $this->model->where('bdc_building_id', $building )->where('company_id', $company)->get();
        $checkBuildingServiceUse = $buildingServices->where('status', USE_SERVICE)->pluck('service_code')->toArray();
        $checkBuildingServices = $buildingServices->where('status', NOT_USE_SERVICE)->pluck('service_code')->toArray();
        $services = $this->model->whereIn('id', $ids)->whereNotIn('service_code',  $checkBuildingServices)->pluck('service_code')->toArray();
        $codeServices = array_unique(array_merge($checkBuildingServiceUse, $services));
        $servicesCheck = array_diff($codeServices, $checkBuildingServiceUse);
        $data['deleteServiceCompany'] = array_diff($checkBuildingServices, $servicesCheck);
        $data['addServiceCompany'] = array_diff($servicesCheck, $checkBuildingServices);
        $this->deleteServiceBuilding($data['deleteServiceCompany'], $building); //xoa cac service khong chon
        return $this->model->whereIn('service_code', $data['addServiceCompany'])->where('bdc_building_id', 0)->pluck('id')->toArray();
    }

    private function deleteServiceBuilding($serviceCodes, $building)
    {
        $company =Auth::user()->company_staff->company->id ?? 1;
        $serviceDeletes = $this->model->where('bdc_building_id', $building )->where('company_id', $company)->whereIn('service_code', $serviceCodes)->get();
        foreach ($serviceDeletes as $service) {
            DB::beginTransaction();
            try {
                $progressivePrice = $service->servicePriceDefault->progressive->progressivePrice;
                foreach ($progressivePrice as $progress) {
                    $progress->delete();
                }
                $service->servicePriceDefault->progressive->delete();
                $service->servicePriceDefault->delete();
                $service->delete();
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();

                throw new \Exception($e->getMessage());
            }
        }
    }

    public function findServiceCompany($id)
    {
        return $this->model->where('id',$id)->first();
    }

    public function findServiceBuilding($id)
    {
        return $this->model->where('id',$id)->first();
    }

    public function getServiceOfApartment2($id)
    {
        return $this->model->whereHas('servicePriceDefault.priceType' , function (Builder $query) {
            $query->where('id', '=', ONE_PRICE);
        })
            ->where('status', USE_STATUS)
            ->whereIn('id',$id)->get();
    }
    public function getServiceOfApartment3($id)
    {
        return $this->model->whereHas('servicePriceDefault.priceType' , function (Builder $query) {
            $query->where('id', '=', ONE_PRICE);
        })
            ->where('status', USE_STATUS)
            ->whereIn('id',$id);
    }

    public function changeStatusBuilding($request)
    {
        if ($request->status == 'Active') {
            $this->model->whereIn('id', $request->ids)->where('type','<>',7)->update(['status' => USE_SERVICE]);
        } elseif ($request->status == 'Inactive') {
            $this->model->whereIn('id', $request->ids)->where('type','<>',7)->update(['status' => NOT_USE_SERVICE]);

        } else {
            $service = $this->model->where('id',$request->id)->where('type','<>',7)->first();
            if($service){
                if ($service->status == USE_SERVICE) {
                    foreach ($service->apartmentServicePrices as $apartment)
                    {
                        $apartment->update(['status' => NOT_USE_SERVICE]);
                    }
                    $service->status = NOT_USE_SERVICE;
                    $service->save();
                } else {
                    $service->status = USE_SERVICE;
                    $service->save();
                }
            }
        }
    }

    public function filterBuildingId($buildingId)
    {
        return $this->model->where(['bdc_building_id' => $buildingId, 'status' => USE_STATUS]);
    }

    public function filterServiceBuildingId($id, $buildingId)
    {
        return $this->model->where(["id" => $id, "bdc_building_id" => $buildingId])->first();
    }

    public function getServiceVehicle($vehicle_category, $count)
    {
        return $this->model
            ->where('name', 'like', '%'.' - '.$vehicle_category->name.' - ' . $count)
            ->where('bdc_building_id', $vehicle_category->bdc_building_id)
            ->first();
    }

    /**
     * @param VehicleCategory $vehicle_category
     * @param int $count
     * @param ProgressivePrice $progressivePrice
     */
    public function getCheckServicePrice(VehicleCategory $vehicle_category, int $count, ProgressivePrice $progressivePrice)
    {

        $service = $this->model
            ->where('name', 'like', '%'.' - '.$vehicle_category->name.' - ' . $count)
            ->where('bdc_building_id', $vehicle_category->bdc_building_id)
            ->first();

        if (empty($service)) {
            $service = $this->model->create([
                'name'=>'Phí dịch vụ'.' - '.$vehicle_category->name.' - '.$count,
                'bdc_building_id'=>$vehicle_category->bdc_building_id,
                'bdc_period_id'=>1,
                'description'=>'Phí dịch vụ'.' - '.$vehicle_category->name.' - '.$count,
                'unit'=>'VNĐ',
                'bill_date'=>$vehicle_category->bill_date ?? 1,
                'payment_deadline'=>$vehicle_category->payment_deadline ?? 10,
                'first_time_active'=>$vehicle_category->first_time_active ?? Carbon::now(),
                'ngay_chuyen_doi'=> isset($vehicle_category->ngay_chuyen_doi) ? $vehicle_category->ngay_chuyen_doi : null,
                'code_receipt' => isset($vehicle_category->code_receipt) ? $vehicle_category->code_receipt  : null,
                'service_group'=>$vehicle_category->service_group ?? 1,
                'type'=>4,
                'status'=>1,
                'company_id'=>1
            ]);

            $service->servicePriceDefault()->create([
                'name'=>'Phí dịch vụ'.' - '.$vehicle_category->name.' - '.$count,
                'bdc_building_id'=>$vehicle_category->bdc_building_id,
                'price'=>$progressivePrice->price,
                'progressive_id'=>$vehicle_category->progressive->id,
                'bdc_price_type_id'=>1
            ]);
        }
        return $service;
    }

    public static function getInfoServiceById($id)
    {
        $keyCache = "getInfoServiceById_" . $id;
        return Cache::remember($keyCache, 10 ,function () use ($keyCache, $id) {
            $rs = Service::where([
                "id" => $id
            ])->first();
            if (!$rs) return null;
            return (object) $rs->toArray();
        });
    }

    public static function getInfoServiceByIdV2($id)
    {
        return Service::find($id);
    }
    public static function setTinhCongNo($bdc_active_id, $value)
    {
        return Cache::store('redis')->put( env('REDIS_PREFIX') . '_Tinh_Cong_No_'.$bdc_active_id ,$value);
    }

    public static function getTinhCongNo($bdc_active_id)
    {
        return Cache::store('redis')->get( env('REDIS_PREFIX') . '_Tinh_Cong_No_'.$bdc_active_id);
    }

    public static function getAllServiceByBuildingId($building_id)
    {
        return Service::where([
            "bdc_building_id" => $building_id
        ])->get();
    }
}
