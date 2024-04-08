<?php

namespace App\Http\Controllers\VehicleCategory;

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
use App\Models\Vehicles\Vehicles;
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

    public function __construct(
        VehicleCategoryRespository $model,
        ProgressiveRepository $progressiveRepository,
        ProgressivePriceRepository $progressivePriceRepository,
        ServiceRepository $serviceRepository,
        Request $request
    ) {
        //$this->middleware('route_permision');
        $this->model = $model;
        $this->progressiveRepository = $progressiveRepository;
        $this->progressivePriceRepository = $progressivePriceRepository;
        $this->serviceRepository = $serviceRepository;
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
        return view('vehiclecategory.index', $data);
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
        ]);

        if ($bdc_price_type_id == 1) {
            $this->progressivePriceRepository->create([
                'name' => 'Phí Dịch vụ ' . $name,
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
            return redirect()->route('admin.vehicles.index', ['tab' => 'Category'])->with('success', 'Thêm danh mục thành công!');
        }
        return redirect()->route('admin.vehicles.index', ['tab' => 'Category'])->with('error', 'Thêm danh mục không thành công!');
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

        return view('vehiclecategory.edit', $data);
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

        $vehicle_cate = DB::table('bdc_vehicles_category')
            ->where('id', $id)
            ->first();

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
        ]);

        $services = DB::table('bdc_services')
            ->where('name', 'like', 'Phí dịch vụ' . ' - ' . $vehicle_cate->name . ' - ' . '%')
            ->where('bdc_building_id', $this->building_active_id)
            ->get()
            ->toArray();

        foreach ($services as $key => $service) {
            DB::table('bdc_services')
                ->where('id', $service->id)
                ->update([
                    'name' => 'Phí dịch vụ' . ' - ' . $vehicleCategory->name . ' - ' . ($key + 1),
                    'description' => 'Phí dịch vụ' . ' - ' . $vehicleCategory->name . ' - ' . ($key + 1),
                    'type' => 4, // dịch vụ phương tiện 
                    'bdc_period_id' => 1,
                    'service_group' => $service_group,
                    'bdc_building_id' => $this->building_active_id,
                    'payment_deadline' => $payment_dealine,
                    'bill_date' => $bill_date,
                    'first_time_active' => $first_time_active,
                    'ngay_chuyen_doi' => isset($request->ngay_chuyen_doi) ? $request->ngay_chuyen_doi : $vehicle_cate->ngay_chuyen_doi,
                    'code_receipt' => isset($request->code_receipt) ? $request->code_receipt : $vehicle_cate->code_receipt,
                    'status' => $status,
                    'updated_at' => \Illuminate\Support\Carbon::now()
                ]);
        }


        DB::table('bdc_progressives')
            ->where([
                'building_id' => $this->building_active_id,
                'name' => 'Phí Dịch vụ ' . $vehicle_cate->name
            ])
            ->update([
                'bdc_price_type_id' => $bdc_price_type_id,
                'name' => 'Phí Dịch vụ ' . $name,
                'updated_at' => \Illuminate\Support\Carbon::now()
            ]);

        $progressiveId = $vehicleCategory->bdc_progressive_id;

        $service_price = ApartmentServicePrice::where('name', 'like', 'Phí dịch vụ - ' . $name . '%')
            ->where('bdc_vehicle_id','>', 0)
            ->where('status', 1)
            ->orderBy('bdc_apartment_id')
            ->orderBy('created_at')
            ->get();

        $apartments = [];

        foreach ($service_price as $value) {
            if (in_array($value->bdc_apartment_id, $apartments)) {
                $apartments[$value->bdc_apartment_id][] = $value;
            } else {
                $apartments[$value->bdc_apartment_id][] = $value;
            }
        }

        DB::table('bdc_progressive_price')
            ->where([
                'progressive_id' => $progressiveId,
            ])->delete();

        if ($bdc_price_type_id == 1) {
            $bdc_progressive_price_id = DB::table('bdc_progressive_price')->insertGetId([
                'name' => 'Dịch vụ ' . $name,
                'from' => 1,
                'to' => 1,
                'price' => $price,
                'progressive_id' => $progressiveId,
                'priority_level' => 1,
                'created_at' => \Illuminate\Support\Carbon::now()
            ]);

            foreach ($services as $service) {
                DB::table('bdc_service_price_default')
                    ->where('bdc_service_id', $service->id)
                    ->update([
                        'name' => $service->name,
                        'bdc_price_type_id' => 1,
                        'price' => $price,
                        'updated_at' => \Illuminate\Support\Carbon::now()
                    ]);
            }

            foreach ($apartments as $apartment) {
                foreach ($apartment as $key => $service_price) {
                    ApartmentServicePrice::where('id', $service_price->id)
                        ->update([
                            'price' => $price,
                            'bdc_price_type_id' => 1,
                            'bdc_progressive_id' => $progressiveId,
                        ]);
                    Vehicles::where('id', $service_price->bdc_vehicle_id)
                        ->update([
                            'price' => $price,
                            'priority_level' => 1,
                            'bdc_progressive_price_id' => $bdc_progressive_price_id
                        ]);
                }
            }
        } else {
            if (!empty($progressive_price)) {
                $progressive_prices = \GuzzleHttp\json_decode($progressive_price);
                foreach ($progressive_prices as $key => $progressive_price) {
                    DB::table('bdc_progressive_price')->insert([
                        'name' => 'Dịch vụ ' . $name . '( ' . $progressive_price->from . ' - ' . $progressive_price->to . ' )',
                        'from' => $progressive_price->from,
                        'to' => $progressive_price->to,
                        'price' => $progressive_price->price,
                        'progressive_id' => $progressiveId,
                        'priority_level' => $key + 1,
                        'created_at' => \Illuminate\Support\Carbon::now()
                    ]);
                    foreach ($services as $ke => $service) {
                        if (($ke + 1) >= $progressive_price->from && ($ke + 1) <= $progressive_price->to) {
                            DB::table('bdc_service_price_default')
                                ->where('bdc_service_id', $service->id)
                                ->update([
                                    'name' => $service->name,
                                    'bdc_price_type_id' => 1,
                                    'price' => $progressive_price->price,
                                    'updated_at' => \Illuminate\Support\Carbon::now()
                                ]);
                        }
                    }
                }

                $progressive_prices = DB::table('bdc_progressive_price')
                    ->where('progressive_id', $progressiveId)
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
                                        'bdc_progressive_id' => $progressiveId,
                                    ]);
                                Vehicles::where('id', $service_price->bdc_vehicle_id)
                                    ->update([
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

        return redirect()->route('admin.vehicles.index', ['tab' => 'Category'])->with([
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

        $vehicles = Vehicles::where('vehicle_category_id', $id)
            ->count();

        if ($vehicles == 0) {
            //            $this->model->delete(['id'=>$id]);

            $bdc_vehicles_category = DB::table('bdc_vehicles_category')->find($id);

            DB::table('bdc_services')
                ->where('bdc_building_id', $this->building_active_id)
                ->where('name', 'Phí dịch vụ ' . $bdc_vehicles_category->name)
                ->delete();

            $this->model->delete(['id' => $id]);

            return redirect()->route('admin.vehicles.index', ['tab' => 'Category'])->with('success', 'Xóa danh mục thành công!');
        } else {
            return redirect()->route('admin.vehicles.index', ['tab' => 'Category'])->with('error', 'Xóa danh mục không thành công!');
        }
    }

    public function ajaxGetSelectVehicleCate(Request $request)
    {
        if ($request->search) {
            return response()->json($this->model->searchByAll(['where' => [
                ['name', 'like', '%' . $request->search . '%'],
                ['bdc_building_id', '=', $this->building_active_id]
            ]]));
        }
        return response()->json($this->model->paginate(['id', 'name']));
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
                ->whereNull('deleted_at')
                ->where('bdc_building_id', $this->building_active_id)
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

        DB::table('bdc_vehicles_category')
            ->where('id', $id)
            ->update([
                'status' => $status
            ]);

        $bdc_vehicles_category = DB::table('bdc_vehicles_category')->find($id);

        DB::table('bdc_services')
            ->where('bdc_building_id', $this->building_active_id)
            ->where('name', 'like', '%' . $bdc_vehicles_category->name . '%')
            ->update([
                'status' => $status
            ]);

        $dataResponse = [
            'success' => true,
            'message' => 'Thay đổi trạng thái thành công!'
        ];
        return response()->json($dataResponse);
    }
}
