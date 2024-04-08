<?php

namespace App\Http\Controllers\VehicleCategory\V2;

use App\Commons\ApiResponse;
use App\Http\Controllers\BuildingController;
use App\Http\Requests\VehicleCategory\VehicleCategoryRequest;
use App\Repositories\BdcProgressive\ProgressiveRepository;
use App\Repositories\BdcProgressivePrice\ProgressivePriceRepository;
use App\Repositories\Service\ServiceRepository;
use App\Repositories\VehicleCategory\VehicleCategoryRespository;
use App\Repositories\Vehicles\VehiclesRespository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Models\Service\ServicePriceDefault;
use App\Models\VehicleCategory\VehicleCategory;
use App\Models\Vehicles\Vehicles;
use App\Repositories\BdcApartmentServicePrice\ApartmentServicePriceRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

class VehicleCategoryController extends BuildingController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private $model;
    private $progressiveRepository;
    private $progressivePriceRepository;
    private $serviceRepository;
    private $apartmentServiceRepo;

    public function __construct(
        VehicleCategoryRespository $model,
        ProgressiveRepository $progressiveRepository,
        ProgressivePriceRepository $progressivePriceRepository,
        ServiceRepository $serviceRepository,
        ApartmentServicePriceRepository $apartmentServiceRepo,
        Request $request
    ) {
        //$this->middleware('route_permision');
        $this->model = $model;
        $this->progressiveRepository = $progressiveRepository;
        $this->progressivePriceRepository = $progressivePriceRepository;
        $this->serviceRepository = $serviceRepository;
        $this->apartmentServiceRepo = $apartmentServiceRepo;
        parent::__construct($request);
    }

    public function index(Request $request)
    {
        $data['meta_title'] = 'Vehicle Category';
        $data['per_page'] = Cookie::get('per_page', 20);
        $vehiclecate = $this->model->searchByAll(['where' => [['name', 'like', '%' . $request->keyword . '%']], 'per_page' => $data['per_page']]);

        $data['keyword'] = $request->keyword;
        $data['vehiclecates'] = $vehiclecate;
        $data['vehiclecates'] = $vehiclecate;
        $data['display_count'] = count($vehiclecate);
        return view('vehiclecategory.v2.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(VehicleCategoryRequest $request)
    {

        $name = $request->get('name');
        $description = $request->get('description');
        $type_price = $request->get('type-price');
        $price = $request->get('price');
        $progressive_price = $request->get('progressive_price');
        $first_time_active = $request->get('first_time_active');
        $payment_dealine = $request->get('payment_dealine');
        $type = $request->get('type');
        $status = $request->get('status', 'on') === 'on';
        $service_group = $request->get('service_group');
        $bdc_price_type_id = $request->get('bdc_price_type_id');
        $bill_date = $request->get('bill_date');


        $progressive = $this->progressiveRepository->create([
            'building_id' => $this->building_active_id,
            'bdc_price_type_id' => $bdc_price_type_id,
            'company_id' => 1,
            'name' => 'Phí dịch vụ ' . $name,
        ]);

        $service = $this->serviceRepository->create([
            'name' => 'Phí dịch vụ ' . $name,
            'bdc_building_id' => $this->building_active_id,
            'bdc_period_id' => 1,
            'description' => 'Phí dịch vụ ' . $name,
            'unit' => 'VNĐ',
            'bill_date' => $bill_date,
            'payment_deadline' => $payment_dealine,
            'first_time_active' => $first_time_active,
            'ngay_chuyen_doi' => isset($request->ngay_chuyen_doi) ? $request->ngay_chuyen_doi : null,
            'code_receipt' => isset($request->code_receipt) ? $request->code_receipt : null,
            'service_group' => $service_group,
            'type' => 4, // loại dịch vụ phương tiện
            'status' => 1, // trạng thái hoạt động
            'company_id' => 1, // công ty
            'user_id' => auth()->user()->id,
        ]);

        ServicePriceDefault::create([
            'name' => 'Phí dịch vụ ' . $name,
            'bdc_service_id' => $service->id,
            'bdc_building_id' => $this->building_active_id,
            'price' => 0,
            'progressive_id' => 0,
            'bdc_price_type_id' => 1
        ]);

        $vehicle_category = $this->model->create([
            'name' => $name,
            'description' => $description,
            'status' => $status,
            'first_time_active' => $first_time_active,
            'ngay_chuyen_doi' => isset($request->ngay_chuyen_doi) ? $request->ngay_chuyen_doi : null,
            'bdc_price_type_id' => $bdc_price_type_id,
            'bdc_building_id' => $this->building_active_id,
            'bdc_progressive_id' => $progressive->id,
            'payment_deadline' => $payment_dealine,
            'bill_date' => $bill_date,
            'service_group' => $service_group,
            'code_receipt' => isset($request->code_receipt) ? $request->code_receipt : null,
            'bdc_service_id' => $service->id,
            'user_id' => auth()->user()->id,
            'type' => $request->type
        ]);

        if ($bdc_price_type_id == 1) {
            $this->progressivePriceRepository->create([
                'name' => 'Phí dịch vụ ' . $name,
                'from' => 1,
                'to' => 1,
                'price' => $price,
                'progressive_id' => $progressive->id,
                'priority_level' => 1,
            ]);
        } else {
            if (!empty($progressive_price)) {
                $progressive_prices = \GuzzleHttp\json_decode($progressive_price);
                foreach ($progressive_prices as $key => $progressive_price) {
                    $this->progressivePriceRepository->create([
                        'name' => 'Phí dịch vụ ' . $name . '( ' . $progressive_price->from . ' - ' . $progressive_price->to . ' )',
                        'from' => $progressive_price->from,
                        'to' => $progressive_price->to,
                        'price' => $progressive_price->price,
                        'progressive_id' => $progressive->id,
                        'priority_level' => $key + 1,
                    ]);
                }
            }
        }

        if ($vehicle_category) {
            return redirect()->route('admin.v2.vehicles.index', ['tab' => 'Category'])->with('success', 'Thêm danh mục thành công!');
        }
        return redirect()->route('admin.v2.vehicles.index', ['tab' => 'Category'])->with('error', 'Thêm danh mục không thành công!');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function edit(int $id)
    {
        $data['meta_title'] = 'Edit Vehicle Category';
        $vehicle_category = $this->model->getOne('id', $id);

        $progressive = $this->progressiveRepository->find($vehicle_category->bdc_progressive_id);

        if (!empty($progressive)) {
            $data['progressive'] = $progressive;

            $progressive_prices = $this->progressivePriceRepository
                ->findByProgressiveId($progressive->id);

            $data['progressive_prices'] = $progressive_prices;
        }

        $data['vehiclecate'] = $vehicle_category;

        return view('vehiclecategory.v2.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(VehicleCategoryRequest $request, $id)
    {
        //        $this->model->update(['name'=>$request->name],$id,'id');

        $name = $request->get('name');
        $description = $request->get('description');
        $type_price = $request->get('type-price');
        $price = $request->get('price');
        $progressive_price = $request->get('progressive_price');
        $first_time_active = $request->get('first_time_active');
        $payment_dealine = $request->get('payment_dealine');
        $type = $request->get('type');
        $status = $request->get('status', 'on') === 'on';
        $service_group = $request->get('service_group');
        $bdc_price_type_id = $request->get('bdc_price_type_id');
        $bill_date = $request->get('bill_date');

        $vehicle_cate = VehicleCategory::find($id);

        $vehicleCategory = $this->model->updateOrCreate([
            'id' => $id
        ], [
            'name' => $name,
            'description' => $description,
            'status' => $status,
            'first_time_active' => $first_time_active,
            'ngay_chuyen_doi' => isset($request->ngay_chuyen_doi) ? $request->ngay_chuyen_doi : $vehicle_cate->ngay_chuyen_doi,
            'bdc_price_type_id' => $bdc_price_type_id,
            'payment_deadline' => $payment_dealine,
            'bill_date' => $bill_date,
            'code_receipt' => isset($request->code_receipt) ? $request->code_receipt : $vehicle_cate->code_receipt,
            'type' => $request->type
        ]);

        $this->serviceRepository->update([
            'name' => 'Phí dịch vụ ' . $name,
            'bdc_building_id' => $this->building_active_id,
            'bdc_period_id' => 1,
            'description' => 'Phí dịch vụ ' . $name,
            'unit' => 'VNĐ',
            'bill_date' => $bill_date,
            'payment_deadline' => $payment_dealine,
            'first_time_active' => $first_time_active,
            'ngay_chuyen_doi' => isset($request->ngay_chuyen_doi) ? $request->ngay_chuyen_doi : null,
            'code_receipt' => isset($request->code_receipt) ? $request->code_receipt : null,
            'service_group' => $service_group,
            'type' => 4, // loại dịch vụ phương tiện
            'status' => 1, // trạng thái hoạt động
            'company_id' => 1, // công ty
            'user_id' => auth()->user()->id,
        ], $vehicleCategory->bdc_service_id);


        DB::table('bdc_progressives')
            ->where([
                'building_id' => $this->building_active_id,
                'name' => 'Phí dịch vụ ' . $vehicle_cate->name
            ])
            ->update([
                'bdc_price_type_id' => $bdc_price_type_id,
                'name' => 'Phí dịch vụ ' . $name,
                'updated_at' => \Illuminate\Support\Carbon::now()
            ]);

        DB::table('bdc_progressive_price')
            ->where([
                'progressive_id' => $vehicleCategory->bdc_progressive_id,
            ])->delete();

        $_apartmentServicePrice = ApartmentServicePrice::where(['bdc_building_id' => $this->building_active_id, 'bdc_service_id' => $vehicleCategory->bdc_service_id, 'status' => 1])
            ->orderBy('bdc_apartment_id')
            ->orderBy('created_at')->get();
        $apartments = [];

        foreach ($_apartmentServicePrice as $value) {
            if (in_array($value->bdc_apartment_id, $apartments)) {
                $apartments[$value->bdc_apartment_id][] = $value;
            } else {
                $apartments[$value->bdc_apartment_id][] = $value;
            }
        }

        if ($bdc_price_type_id == 1) {
            $bdc_progressive_price = $this->progressivePriceRepository->create([
                'name' => 'Phí dịch vụ ' . $name,
                'from' => 1,
                'to' => 1,
                'price' => $price,
                'progressive_id' => $vehicleCategory->bdc_progressive_id,
                'priority_level' => 1,
            ]);

            ServicePriceDefault::where('bdc_service_id', $vehicleCategory->bdc_service_id)->update([
                'name' => 'Phí dịch vụ ' . $name,
                'price' => $price,
            ]);

            foreach ($_apartmentServicePrice as $key => $value) {
                $value->price = $price;
                $value->save();
                $_vehicle =  Vehicles::find($value->bdc_vehicle_id);
                if($_vehicle){
                    $_vehicle->price = $price;
                    $_vehicle->priority_level = 1;
                    $_vehicle->bdc_progressive_price_id = $bdc_progressive_price->id;
                    $_vehicle->save();
                }
            }
        } else {
            if (!empty($progressive_price)) {
                $progressive_prices = \GuzzleHttp\json_decode($progressive_price);
                foreach ($progressive_prices as $key => $progressive_price) {
                    $this->progressivePriceRepository->create([
                        'name' => 'Phí dịch vụ ' . $name . '( ' . $progressive_price->from . ' - ' . $progressive_price->to . ' )',
                        'from' => $progressive_price->from,
                        'to' => $progressive_price->to,
                        'price' => $progressive_price->price,
                        'progressive_id' => $vehicleCategory->bdc_progressive_id,
                        'priority_level' => $key + 1,
                    ]);
                }

                $progressive_prices = DB::table('bdc_progressive_price')
                    ->where('progressive_id', $vehicleCategory->bdc_progressive_id)
                    ->orderBy('price', 'ASC')
                    ->get();

                foreach ($progressive_prices as $progressive_price) {
                    foreach ($apartments as $apartment) {
                        foreach ($apartment as $idx => $service_price) {
                            if ($idx + 1 >= $progressive_price->from && $idx + 1 <= $progressive_price->to) {

                                ApartmentServicePrice::where('id', $service_price->id)
                                    ->update([
                                        'price' => $progressive_price->price,
                                        'bdc_price_type_id' => 1,
                                    ]);

                                $detailVehicles = Vehicles::find($service_price->bdc_vehicle_id);
                                $detailVehicles && $detailVehicles->update([
                                    'price' => $progressive_price->price,
                                    'priority_level' => $progressive_price->priority_level,
                                    'bdc_progressive_price_id' => $progressive_price->id
                                ]);
                            }
                        }
                    }
                }
            }
        }

        return redirect()->route('admin.v2.vehicles.index', ['tab' => 'Category'])->with([
            'success' => 'Cập nhật danh mục thành công!',
            'tab' => 'Category'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {

        $vehicles =  Vehicles::where('vehicle_category_id', $id)->count();

        if ($vehicles == 0) {

            $bdc_vehicles_category = VehicleCategory::find($id);

            DB::table('bdc_services')->find($bdc_vehicles_category->bdc_service_id)->delete();

            $this->model->delete(['id' => $id]);

            return redirect()->route('admin.v2.vehicles.index', ['tab' => 'Category'])->with('success', 'Xóa danh mục thành công!');
        } else {
            return redirect()->route('admin.v2.vehicles.index', ['tab' => 'Category'])->with('error', 'Xóa danh mục không thành công!');
        }
    }

    public function ajaxGetSelectVehicleCate(Request $request)
    {
        if ($request->search) {
            return response()->json($this->model->searchByAll_v2(['where' => [
                ['name', 'like', '%' . $request->search . '%'],
                ['bdc_building_id', '=', $this->building_active_id]
            ]]));
        }
        return response()->json($this->model->searchByAll_v2(['where' => [
            ['bdc_building_id', '=', $this->building_active_id]
        ]]));
    }

    public function checkVehicleNameCategory(Request $request)
    {
        $name = $request->get('name');
        $id = $request->get('id');

        if (empty($id)) {
            $vehicles_category = DB::table('bdc_vehicles_category')
                ->where('name', $name)
                ->where('bdc_building_id', $this->building_active_id)
                ->whereNull('deleted_at')
                ->count();
        } else {
            $vehicles_category = DB::table('bdc_vehicles_category')
                ->where('name', $name)
                ->whereNotIn('id', [$id])
                ->where('bdc_building_id', $this->building_active_id)
                ->whereNull('deleted_at')
                ->count();
        }

        return ApiResponse::responseSuccess([
            'data' => [
                'count' => $vehicles_category
            ]
        ]);
    }

    public function status(Request $request)
    {
        $id = $request->get('id');
        $status = $request->get('status');

        $bdc_vehicles_category = VehicleCategory::find($id);
        $bdc_vehicles_category->status = $status;
        $bdc_vehicles_category->save();

        $bdc_services = DB::table('bdc_services')->find($bdc_vehicles_category->bdc_service_id);
        if(!$bdc_services){
            $dataResponse = [
                'success' => false,
                'message' => 'Thay đổi trạng thái thất bại!'
            ];
            return response()->json($dataResponse);
        }
        $bdc_services->update([
                'status' => $status
            ]);

        $dataResponse = [
            'success' => true,
            'message' => 'Thay đổi trạng thái thành công!'
        ];
        return response()->json($dataResponse);
    }
}
