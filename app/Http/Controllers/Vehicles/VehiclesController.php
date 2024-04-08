<?php

namespace App\Http\Controllers\Vehicles;

use App\Commons\ApiResponse;
use App\Commons\Util\Debug\Log;
use App\Http\Controllers\BuildingController;
use App\Http\Requests\Vehicles\VehiclesRequest;
use App\Models\BdcProgressivePrice\ProgressivePrice;
use App\Models\VehicleCategory\VehicleCategory;
use App\Models\Vehicles\Vehicles;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\BdcApartmentServicePrice\ApartmentServicePriceRepository;
use App\Repositories\BdcProgressivePrice\ProgressivePriceRepository;
use App\Repositories\Service\ServiceRepository;
use App\Repositories\VehicleCards\VehicleCardsRespository;
use App\Repositories\VehicleCategory\VehicleCategoryRespository;
use App\Repositories\Vehicles\VehiclesRespository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Apartments\Apartments;
use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Models\PublicUser\Users;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use App\Repositories\Building\BuildingPlaceRepository;
use App\Models\Service\ServicePriceDefault;

class VehiclesController extends BuildingController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private $model;
    private $modelApartment;
    private $modelCategory;
    private $modelBuildingPlace;
    private $modelService;
    private $modelProgressivePrice;
    private $modelVehicleCard;
    private $modelApartmentServicePrice;


    public function __construct(
        VehiclesRespository $model,
        ApartmentsRespository $modelApartment,
        VehicleCategoryRespository $modelCategory,
        Request $request,
        BuildingPlaceRepository $modelBuildingPlace,
        ServiceRepository $modelService,
        ProgressivePriceRepository $modelProgressivePrice,
        VehicleCardsRespository $modelVehicleCard,
        ApartmentServicePriceRepository $modelApartmentServicePrice
    ) {
        //$this->middleware('route_permision');
        $this->model = $model;
        $this->modelCategory = $modelCategory;
        $this->modelService = $modelService;
        $this->modelProgressivePrice = $modelProgressivePrice;
        $this->modelVehicleCard = $modelVehicleCard;
        $this->modelApartmentServicePrice = $modelApartmentServicePrice;
        $this->modelApartment = $modelApartment;
        $this->modelBuildingPlace = $modelBuildingPlace;
        parent::__construct($request);
    }
    public function index(Request $request)
    {
        $data['meta_title'] = 'Vehicles';
        $data['per_page'] = Cookie::get('per_page', 20);
        $vehicles = $this->model->searchVehicle($this->building_active_id, $request, [], $data['per_page']);

        $data['tab'] = $request->get('tab', '');

        $data['data_search'] = $request->all();
      
        $data['data_search']['keyword'] = $request->keyword;
        if ($request->cate) {
            $data['data_search']['cate'] = $this->modelCategory->getOne('id', $request->cate);
        }
        $data['vehicles'] = $vehicles;
        $data['display_count'] = count($vehicles);
        $data['data_vhc'] = Session::get('data_vhc');
        $data['data_error'] = Session::get('error');
        $data['data_success'] = Session::get('success');

        //category vehicle
        $vehiclecate = $this->modelCategory->searchByAll(['where' => [
            ['name', 'like', '%' . $request->keyword_cate . '%'],
            ['bdc_building_id', '=', $this->building_active_id]
        ], 'per_page' => $data['per_page']]);
        $data['keyword_cate'] = $request->keyword_cate;
        $data['vehiclecates'] = $vehiclecate;
        $data['display_count_cate'] = count($vehiclecate);
        if (isset($data['data_search']['apartment'])) {
           
            $data['get_apartment'] = $this->modelApartment->findById($data['data_search']['apartment']);
        }
        if (isset($data['data_search']['place_id'])) {
            $data['get_place_building'] = $this->modelBuildingPlace->findById($data['data_search']['place_id']);
        }
        $vehicleCateActive = DB::table('bdc_vehicles_category')
            ->where('bdc_building_id', $this->building_active_id)
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->get();
        $data['vehicleCateActive'] = $vehicleCateActive;

        return view('vehicles.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(VehiclesRequest $request)
    {
        $data['meta_title'] = 'Add Vehicles';
        //        $data =$request->only(['name', 'bdc_apartment_id', 'number', 'description','vehicle_category_id']);
        //        $insert = $this->model->create($data);

        $name = $request->get('name');
        $bdc_apartment_id = $request->get('bdc_apartment_id');
        $vehicle_category_id = $request->get('vehicle_category_id');
        $number = $request->get('number');
        $code = $request->get('code');
        $description = $request->get('description');
        $first_time_active = $request->get('first_time_active');
        $priority_level = $request->get('priority_level', 1);
        $priority_price = $request->get('priority_price');
        $bdc_progressive_price_id = $request->get('progressive_price_id');
        $status = $request->get('status', 'on') === 'on';
        $bdc_progressive_id = $request->get('bdc_progressive_id');

        $tab_current = $request->get('tab_current');

        $progressive_price = $this->modelProgressivePrice->find($bdc_progressive_price_id);

        $priority_level = $progressive_price->priority_level ?? null;
        $priority_price = $progressive_price->price ?? null;
        $bdc_progressive_id = $progressive_price->progressive_id ?? null;

        /**
         * @var Vehicles $vehicle
         */

        $vehicle = Vehicles::create([
            'name' => $name,
            'bdc_apartment_id' => $bdc_apartment_id,
            'number' => $number,
            'description' => $description,
            'vehicle_category_id' => $vehicle_category_id,
            'bdc_progressive_price_id' => $bdc_progressive_price_id,
            'first_time_active' => $first_time_active ? carbon::parse($first_time_active) : carbon::now(),
            'finish' => $request->finish ? carbon::parse($request->finish) : null,
            'status' => $status,
            'priority_level' => $priority_level,
            'price' => $priority_price,
            'user_id' => auth()->user()->id
        ]);

        if (!empty($code)) {
            $vehicle->vehicleCard()->create([
                'code' => $code,
                'status' => $status,
                'description' => $description,
            ]);
        }

        $vehicle_category = $this->modelCategory->find($vehicle_category_id);

        $number_vehicle_apartment = $this->model->countVehicleByApartmentAndCate($bdc_apartment_id, $vehicle_category_id);

        $service = $this->modelService->getServiceVehicle($vehicle_category, $number_vehicle_apartment);

        $vehicles_all = Vehicles::where('bdc_apartment_id', $bdc_apartment_id)
            ->where('vehicle_category_id', $vehicle_category_id)
            ->get();

        $vehicles = Vehicles::where('bdc_apartment_id', $bdc_apartment_id)
            ->where('vehicle_category_id', $vehicle_category_id)
            ->where('priority_level', '>=', $priority_level)
            ->where('id', '<>', $vehicle->id)
            ->orderBy('priority_level', 'ASC')
            ->get();

        $indexCurrent = count($vehicles_all) - count($vehicles);

        $progressive_prices = DB::table('bdc_progressive_price')
            ->where('progressive_id', $bdc_progressive_id)
            ->orderBy('priority_level', 'ASC')
            ->get();

        foreach ($vehicles as $index => $veh) {
            foreach ($progressive_prices as $progressive_price) {
                if (($indexCurrent + $index + 1) >= $progressive_price->from && ($indexCurrent + $index + 1) <= $progressive_price->to && $progressive_price->from !== 0) {
                    Vehicles::where('id', $veh->id)
                        ->update([
                            'price' => $progressive_price->price,
                            'priority_level' => $progressive_price->priority_level,
                            'bdc_progressive_price_id' => $progressive_price->id,
                            'user_id' => auth()->user()->id
                        ]);
                    ApartmentServicePrice::where('bdc_vehicle_id', $veh->id)
                        ->update([
                            'price' => $progressive_price->price,
                            'user_id' => auth()->user()->id
                        ]);
                }
            }
        }
        if (!isset($service)) {
            return redirect()->route('admin.vehicles.index')->with(['error' => 'Thêm phương tiện không thành công!', 'data_vhc' => 'Thêm phương tiện không thành công!']);
        }
        $this->modelApartmentServicePrice->create([
            'bdc_service_id' => $service->id,
            'bdc_price_type_id' => 1,
            'bdc_apartment_id' => $bdc_apartment_id,
            'name' => $service->name,
            'price' => $priority_price,
            'first_time_active' =>  $first_time_active ? carbon::parse($first_time_active) : carbon::now(),
            'last_time_pay' =>  $first_time_active ? carbon::parse($first_time_active) : carbon::now(),
            'finish' => $request->finish ? carbon::parse($request->finish) : null,
            'bdc_vehicle_id' => $vehicle->id,
            'bdc_building_id' => $this->building_active_id,
            'bdc_progressive_id' => $bdc_progressive_id,
            'user_id' => auth()->user()->id
        ]);

        if (!empty($vehicle)) {
            if (!empty($tab_current) && $tab_current == 'apartment') {
                return redirect()->route('admin.apartments.edit', ['id' => $bdc_apartment_id])->with(['success' => 'Thêm phương tiện thành công!', 'data_vhc' => 'Thêm phương tiện thành công!']);
            }
            return redirect()->route('admin.vehicles.index')->with(['success' => 'Thêm phương tiện thành công!', 'data_vhc' => 'Thêm phương tiện thành công!']);
        }
        if (!empty($tab_current) && $tab_current == 'apartment') {
            return redirect()->route('admin.apartments.edit', ['id' => $bdc_apartment_id])->with(['error' => 'Thêm phương tiện không thành công!', 'data_vhc' => 'Thêm phương tiện không thành công!']);
        }
        return redirect()->route('admin.vehicles.index')->with(['error' => 'Thêm phương tiện không thành công!', 'data_vhc' => 'Thêm phương tiện không thành công!']);
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['meta_title'] = 'edit Vehicles';
        $vehicle = $this->model->getOne('id', $id);
        $data['vehiclecate'] = $vehicle->bdcVehiclesCategory;
        $data['bdcApartment'] = $vehicle->bdcApartment;
        $data['vehicle'] = $vehicle;
        $data['id'] = $id;
        $apartment_service_vehicle = ApartmentServicePrice::where('bdc_vehicle_id', $id)
            ->first();

        if (!empty($apartment_service_vehicle)) {

            $data['apartment_service_vehicle'] = $apartment_service_vehicle;

            $progressive_prices = DB::table('bdc_progressive_price')
                ->where('progressive_id', $apartment_service_vehicle->bdc_progressive_id)
                ->select('name', 'id', 'price', 'priority_level')
                ->get();

            $data['progressive_prices'] = $progressive_prices;
        }
        $vehicleCateActive = VehicleCategory::where('bdc_building_id', $this->building_active_id)
        ->where('status', 1)
        ->get();
        $data['vehicleCateActive'] = $vehicleCateActive;
        return view('vehicles.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(VehiclesRequest $request, $id)
    {
        try {

            $name = $request->get('name');
            $bdc_apartment_id = $request->get('bdc_apartment_id');
            $vehicle_category_id = $request->get('vehicle_category_id');
            $number = $request->get('number');
            $code = $request->get('code');
            $description = $request->get('description');
            $first_time_active = $request->get('first_time_active');
            $priority_level = $request->get('priority_level', 1);
            $priority_price = $request->get('priority_price');
            $bdc_progressive_price_id = $request->get('progressive_price_id');
            $status = $request->get('status', 'on') === 'on';
            $bdc_progressive_id = $request->get('bdc_progressive_id');

            Vehicles::where('id', $id)
                ->update([
                    'name' => $name,
                    'number' => $number,
                    'description' => $description,
                    'bdc_progressive_price_id' => $bdc_progressive_price_id,
                    'first_time_active' => $first_time_active ? carbon::parse($first_time_active) : null,
                    'finish' => $request->finish ? carbon::parse($request->finish) : null,
                    'status' => $status,
                    'priority_level' => $priority_level,
                    'price' => $priority_price,
                    'updated_at' => \Illuminate\Support\Carbon::now(),
                    'vehicle_category_id' => $vehicle_category_id,
                    'user_id' => auth()->user()->id
                ]);

            $vehicle_current = Vehicles::find($id);

            $get_count_vehicle_apartment_all_status = $this->model->vehicle_apartment_all_status($bdc_apartment_id, $vehicle_category_id);
            $progressive_prices = @$vehicle_current->bdcVehiclesCategory->progressive->progressivePrice;
        
        
        
            foreach ($get_count_vehicle_apartment_all_status as $key => $vehicle) {
                // kiểm tra xem căn hộ đã gắn vào dịch vụ chưa
                $apartment_vehicle = $vehicle->apartmentServicePrices_v2;
    
                if ($apartment_vehicle) {
                    foreach ($progressive_prices as $progressive_price) {
    
                        if ($vehicle->status == 1 && ($key + 1 >= $progressive_price->from && $key + 1 <= $progressive_price->to) || $progressive_price->from == 0) {
                            ApartmentServicePrice::where('bdc_vehicle_id', $vehicle->id)
                                ->update([
                                    'price' => $progressive_price->price,
                                    'user_id' => auth()->user()->id
                                ]);
                            Vehicles::where('id', $vehicle->id)
                                ->update([
                                    'price' => $progressive_price->price,
                                    'priority_level' => $progressive_price->priority_level,
                                    'bdc_progressive_price_id' => $progressive_price->id,
                                    'user_id' => auth()->user()->id
                                ]);
                        }
                    }
                }
            }
        

            if (!empty($code)) {
                DB::table('bdc_vehicle_cards')
                    ->where('bdc_vehicle_id', $id)
                    ->update([
                        'code' => $code,
                        'status' => $status,
                        'description' => $description,
                        'updated_at' => \Illuminate\Support\Carbon::now()
                    ]);
            }

            ApartmentServicePrice::where('bdc_vehicle_id', $id)
                ->update([
                    'price' => $priority_price,
                    'first_time_active' => $first_time_active,
                    'last_time_pay' => $first_time_active,
                    'finish' => $request->finish ? carbon::parse($request->finish) : null,
                    'bdc_progressive_id' => $bdc_progressive_id,
                    'updated_at' => \Illuminate\Support\Carbon::now(),
                    'user_id' => auth()->user()->id
                ]);

            //        $this->model->update($data,$id,'id');

            DB::commit();
            return redirect()->route('admin.vehicles.index')->with('success', 'Cập nhật phương tiện thành công!');
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()->route('admin.vehicles.index')->with('success', 'Cập nhật phương tiện khong thành công!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $check_destroy = ApartmentServicePrice::join('bdc_debit_detail', 'bdc_debit_detail.bdc_apartment_service_price_id', '=', 'bdc_apartment_service_price.id')
            ->whereNull('bdc_debit_detail.deleted_at')
            ->whereNull('bdc_apartment_service_price.deleted_at')
            ->where('bdc_apartment_service_price.bdc_vehicle_id', $id)->count();
        if ($check_destroy > 0) { // có phát sinh phí dịch vụ nên không cho xóa
            return redirect()->route('admin.vehicles.index')->with('error', 'Không được xóa. Phương tiện này đã có phát sinh phí!');
        }
        try {

            $vehicle_delete = $this->model->findById($id);

            $vehicle_delete->delete();

            ApartmentServicePrice::where('bdc_vehicle_id', $id)->delete();

            DB::table('bdc_vehicle_cards')
                ->where('bdc_vehicle_id', $id)
                ->update([
                    'status' => 0
                ]);

            $vehicles = Vehicles::where('bdc_apartment_id', $vehicle_delete->bdc_apartment_id)
                ->where('vehicle_category_id', $vehicle_delete->vehicle_category_id)
                ->orderBy('created_at')
                ->get();

            $vehicle_category = DB::table('bdc_vehicles_category')->where('id', $vehicle_delete->vehicle_category_id)->first();

            $progressive = DB::table('bdc_progressives')
                ->find($vehicle_category->bdc_progressive_id);

            if (!empty($progressive)) {
                $progressive_prices = DB::table('bdc_progressive_price')
                    ->where('progressive_id', $progressive->id)
                    ->get();

                foreach ($vehicles as $key => $vehicle) {
                    foreach ($progressive_prices as $progressive_price) {
                        if (($key + 1 >= $progressive_price->from && $key + 1 <= $progressive_price->to) || $progressive_price->from == 0) {
                            ApartmentServicePrice::where('bdc_vehicle_id', $vehicle->id)
                                ->update([
                                    'price' => $progressive_price->price,
                                    'user_id' => auth()->user()->id
                                ]);
                            Vehicles::where('id', $vehicle->id)
                                ->update([
                                    'price' => $progressive_price->price,
                                    'priority_level' => $progressive_price->priority_level,
                                    'bdc_progressive_price_id' => $progressive_price->id,
                                    'user_id' => auth()->user()->id
                                ]);
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('admin.vehicles.index')->with('success', 'Xóa phương tiện thành công!');
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()->route('admin.vehicles.index')->with(['error' => 'Thêm phương tiện không thành công!', 'data_vhc' => 'Thêm phương tiện không thành công!']);
        }
    }

    public function saveVehicleApartment(Request $request)
    {
        $data = [
            'name' => $request->vc_name,
            'bdc_apartment_id' => $request->bdc_apartment_id,
            'number' => $request->number,
            'description' => $request->description,
            'vehicle_category_id' => $request->vehicle_category_id,
        ];
        $insert = $this->model->create($data);
        if ($insert) {
            return redirect()->route('admin.apartments.edit', ['id' => $request->bdc_apartment_id])->with(['success' => 'Thêm phương tiện thành công!', 'data_vhc' => 'Thêm phương tiện thành công!']);
        }
        return redirect()->route('admin.apartments.edit', ['id' => $request->bdc_apartment_id])->with(['error' => 'Thêm phương tiện không thành công!', 'data_vhc' => 'Thêm phương tiện không thành công!']);
    }

    public function destroyVehicleApartment($id)
    {
        $this->model->delete(['id' => $id]);
        return back()->with('success', 'Xóa phương tiện thành công!');
    }

    public function indexImport()
    {

        $data['meta_title'] = 'import file vehicles';
        $data['messages'] = json_decode(Session::get('messages'), true);
        //        dd($data['messages']);
        $data['error_data'] = Session::get('error_data');

        return view('vehicles.import', $data);
    }
    public function importFileApartment(Request $request)
    {
        $data_error = [];
        $data_success = [];
        if (!$request->file('file_import')) {
            return redirect()->route('admin.vehicles.index')->with('error', 'Không có file tải lên');
        }
        $data['data_import'] = $this->model->getDataFile($request->file('file_import'));

        if ($data['data_import']['data']['vehicles']) {
            foreach ($data['data_import']['data']['data_vh'] as $key => $vh) {
                $place_id = $this->modelBuildingPlace->searchByName($this->building_active_id, $vh['building_place'])->id ?? null;
                $apt_id = $this->modelApartment->findByNamev2($vh['apartment_name'], $this->building_active_id, $place_id) ?? '';
                if ($apt_id) {
                    $data['data_import']['data']['vehicles'][$key] = array_merge($data['data_import']['data']['vehicles'][$key], ['bdc_apartment_id' => isset($apt_id->id) ? $apt_id->id : 0]);
                    unset($data['data_import']['data']['vehicles'][$key]['building_place']);
                    $data_success[] = $vh;
                } else {
                    $data_error[] = $vh;
                    unset($data['data_import']['data']['vehicles'][$key]);
                }
            }
            if (!empty($data_error)) {
                $data['data_import']['messages'][] = ['messages' => 'Lỗi không có căn hộ trên hệ thống', 'data' => $data_error];
            }
            if (!empty($data_success)) {
                $data['data_import']['messages'][] = ['messages' => 'Có bản ghi được cập nhật trên hệ thống', 'data' => $data_success];
            }

            $check = $this->model->insert($data['data_import']['data']['vehicles']);
            if ((isset($check) && $check == false) || !isset($check)) {
                return redirect()->route('admin.vehicles.index_import')->with(['error' => 'Import file không thành công', 'messages' => json_encode($data['data_import']['messages'])]);
            }
            return redirect()->route('admin.vehicles.index_import')->with(['success' => 'Import file thành công', 'messages' => json_encode($data['data_import']['messages'])]);
        }
        return redirect()->route('admin.vehicles.index_import')->with(['success' => 'Import file thành công, không có dữ liệu được thêm', 'messages' => json_encode($data['data_import']['messages'])]);
    }
    public function download()
    {
        $file = public_path() . '/downloads/phuong_tien_import.xlsx';

        return response()->download($file);
    }
    public function ajaxCheckNumber(Request $request)
    {
        $check = $this->model->checkNumberid($request->type, $this->building_active_id, $request->id);
        if ($check) {
            return response()->json(['message' => 'Biển số xe đã tồn tại trên hệ thống, vui lòng kiểm tra lại!', 'status' => 1]);
        }
        return response()->json(['message' => '', 'status' => 0]);
    }
    public function action(Request $request)
    {
        return $this->model->action($request);
    }

    public function checkNumberVehicle(Request $request)
    {
        $number = $request->get('number');
        $cate_vehicle = $request->get('cate_vehicle');
        $apartment_id = $request->get('apartment_id');
        $building_id = $this->building_active_id;
        if ($building_id && $number && $cate_vehicle && $apartment_id) {
            $vehicke = Vehicles::join('bdc_vehicles_category', 'bdc_vehicles_category.id', '=', 'bdc_vehicles.vehicle_category_id')
                ->join('bdc_apartments', 'bdc_apartments.id', '=', 'bdc_vehicles.bdc_apartment_id')
                ->when($number, function ($query) use ($building_id, $number) {
                    return $query->where(['bdc_building_id' => $building_id, 'number' => $number]);
                })->whereNull('bdc_apartments.deleted_at')->where('bdc_vehicles.status', 1)->where('bdc_vehicles.vehicle_category_id', $cate_vehicle)->count();
        }
        return ApiResponse::responseSuccess([
            'data' => [
                'count' => $vehicke ?? 0
            ]
        ]);
    }

    public function getPriceVehicle(Request $request)
    {
        $apartment_id = $request->get('apartment_id');
        $vehicle_category_id = $request->get('vehicle_category_id');

        $number_vehicle_apartment = $this->model->countVehicleByApartmentAndCate($apartment_id, $vehicle_category_id);

        $number_current = $number_vehicle_apartment + 1;

        $vehicle_categories = $this->modelCategory
            ->find($vehicle_category_id);

        if ($vehicle_categories->progressive->bdc_price_type_id == 1) {
            $progressivePrice = $vehicle_categories->progressive->progressivePrice->first();
        } else {
            $progressivePrice = $vehicle_categories->progressive->progressivePrice
                ->where('from', '<=', $number_current)
                ->where('to', '>=', $number_current)
                ->last();
        }

        $service = $this->modelService->getCheckServicePrice($vehicle_categories, $number_current, $progressivePrice);

        $progressive_prices = $vehicle_categories->progressive->progressivePrice->toArray();

        $data = [
            'progressive_prices' => $progressive_prices,
            'vehicle_categories' => $vehicle_categories,
            'progressivePrice' => $progressivePrice
        ];

        return ApiResponse::responseSuccess([
            'data' => $data
        ]);

    }

    public function status(Request $request)
    {
        $id = $request->get('id');
        $status = $request->get('status');

        $vehicle_current = $this->model->find($id);

        $vehicle_current->status = $status;
        $progressive_prices = @$vehicle_current->bdcVehiclesCategory->progressive->progressivePrice;
        if ($status == 0) {
            $vehicle_current->price = null;
            $vehicle_current->priority_level = null;
        } else {
            $vehicke = Vehicles::join('bdc_vehicles_category', 'bdc_vehicles_category.id', '=', 'bdc_vehicles.vehicle_category_id')
                ->join('bdc_apartments', 'bdc_apartments.id', '=', 'bdc_vehicles.bdc_apartment_id')
                ->where(['bdc_building_id' => @$vehicle_current->bdcVehiclesCategory->bdc_building_id, 'number' => $vehicle_current->number])
                ->whereNull('bdc_apartments.deleted_at')->where('bdc_vehicles.status', 1)->where('bdc_vehicles.vehicle_category_id', $vehicle_current->vehicle_category_id)->first();
            if ($vehicke && $vehicke->id != $vehicle_current->id) {
                $dataResponse = [
                    'success' => false,
                    'message' => 'Biển số này đã được kích hoạt từ căn hộ khác!'
                ];
                return response()->json($dataResponse);
            }
           
        }
        $vehicle_current->save();
        $get_count_vehicle_apartment_all_status = $this->model->vehicle_apartment_all_status($vehicle_current->bdc_apartment_id, $vehicle_current->vehicle_category_id);

        foreach ($get_count_vehicle_apartment_all_status as $key => $vehicle) {
            // kiểm tra xem căn hộ đã gắn vào dịch vụ chưa
            $apartment_vehicle = $vehicle->apartmentServicePrices_v2;

            if ($apartment_vehicle) {
                foreach ($progressive_prices as $progressive_price) {

                    if ($vehicle->status == 1 && ($key + 1 >= $progressive_price->from && $key + 1 <= $progressive_price->to) || $progressive_price->from == 0) {
                        ApartmentServicePrice::where('bdc_vehicle_id', $vehicle->id)
                            ->update([
                                'price' => $progressive_price->price,
                                'user_id' => auth()->user()->id
                            ]);
                        Vehicles::where('id', $vehicle->id)
                            ->update([
                                'price' => $progressive_price->price,
                                'priority_level' => $progressive_price->priority_level,
                                'bdc_progressive_price_id' => $progressive_price->id,
                                'user_id' => auth()->user()->id
                            ]);
                    }
                }
            }
        }

        if($progressive_prices->count() == 1 && $status ==1){
            $vehicle_current->price = $progressive_prices[0]->price;
            $vehicle_current->priority_level = $progressive_prices[0]->priority_level;
        }
        $vehicle_current->user_id = auth()->user()->id;
        $vehicle_current->save();
       

        DB::table('bdc_vehicle_cards')
            ->where('bdc_vehicle_id', $id)
            ->update([
                'status' => $status
            ]);
        ApartmentServicePrice::where('bdc_vehicle_id', $id)
            ->update([
                'status' => $status,
                'user_id' => auth()->user()->id
            ]);
        $dataResponse = [
            'success' => true,
            'message' => 'Thay đổi trạng thái thành công!'
        ];
        return response()->json($dataResponse);
    }

    public function export(Request $request)
    {
        set_time_limit(0);
        $data = [];
        $data['per_page'] = 20000;
        $vehicles = $this->model->exportVehicle($this->building_active_id, $request, [], $data['per_page']);

        $data['vehicles'] = $vehicles;

        try {
            $result = Excel::create('Danh_sach_phuong_tien' . date('d-m-Y-H-i-s', time()), function ($excel) use ($data) {
                $excel->setTitle('Danh sách');
                $excel->sheet('Danh sách', function ($sheet) use ($data) {
                    $result = [];
                    foreach ($data['vehicles'] as $key => $v) {
                        $category = VehicleCategory::get_detail_vehicles_category_by_id($v->vehicle_category_id);
                        $apartment = Apartments::get_detail_apartment_by_apartment_id($v->bdc_apartment_id);
                        $user = Users::get_detail_user_by_user_id($v->updated_by);
                        $result[] = [
                            'STT' => $key + 1,
                            'Tên phương tiện' => $v->name,
                            'Biển số xe' => $v->number,
                            'Loại phương tiện' => $category->name ?? '',
                            'Mức ưu tiên' => $v->priority_level,
                            'Phí dịch vụ (tháng)' => $v->price,
                            'Ngày bắt đầu tính phí' => @$v->first_time_active ? date('d-m-Y', strtotime(@$v->first_time_active)) : '',
                            'Ngày kết tính phí' => @$v->finish ? date('d-m-Y', strtotime(@$v->finish)) : '',
                            'Mã thẻ xe' => @$v->bdcVehicleCard->code ?? '',
                            'Ngày vào' =>  date('d-m-Y H:i:s', strtotime($v->created_at)),
                            'Trạng thái' => $v->status == 1 ? 'đang hoạt động' : 'chưa hoạt động',
                            'Tên căn hộ' => $apartment->name ?? '',
                            'Mô tả' => $v->description,
                            'Người cập nhật' => @$user->email,
                            'ID' => @$v->id,
                        ];
                    }
                    $sheet->setAutoSize(true);
                    if ($result) {
                        $sheet->fromArray($result);
                    }
                    $sheet->cell('A1:K1', function ($cell) {
                        // change header color
                        $cell->setFontColor('#000000')
                            ->setBackground('#cecece')
                            ->setFontWeight('bold')
                            ->setFontSize(10)
                            ->setAlignment('center')
                            ->setValignment('center');
                    });
                });
            })->store('xlsx',storage_path('exports/'));
            $file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
            return response()->download($file)->deleteFileAfterSend(true);
             
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function importExcel(Request $request)
    {
        $file = $request->file('file_import');
        $path = $file->getRealPath();

        $excel_data = Excel::load($path)->get();
        storage_path('upload', $file->getClientOriginalName());
        $building_id = $this->building_active_id;
        $data_list = array();
        $check_duplicate_list = array();

        if ($excel_data->count()) {

            foreach ($excel_data as $content) {
                $name = trim($content->ten_phuong_tien);
                $number = trim($content->bien_so);
                $code = trim($content->the_xe);
                $description = $content->ghi_chu;
                $first_time_active = $content->ngay_bat_dau_tinh_phi;
                $bdc_apartment_code = trim($content->ten_can_ho);
                $vehicle_category_id = $content->ma_danh_muc_phuong_tien;
                $finish = $content->ngay_ket_thuc_tinh_phi;

                    try {
                        if (empty($name)) {
                            $new_content = $content->toArray();
                            $new_content['error'] = 'không có tên phương tiện';
                            array_push($check_duplicate_list, $new_content);
                            continue;
                        }
                        if (empty($number)) {
                            $new_content = $content->toArray();
                            $new_content['error'] = 'không có biển số xe';
                            array_push($check_duplicate_list, $new_content);
                            continue;
                        }
                        $vehicle = Vehicles::join('bdc_vehicles_category', 'bdc_vehicles_category.id', '=', 'bdc_vehicles.vehicle_category_id')
                            ->join('bdc_apartments', 'bdc_apartments.id', '=', 'bdc_vehicles.bdc_apartment_id')
                            ->when($number, function ($query) use ($building_id, $number) {
                                return $query->where(['bdc_building_id' => $building_id, 'number' => $number]);
                            })
                            ->whereNull('bdc_apartments.deleted_at')
                            ->where('bdc_vehicles.status', 1)
                            ->where('bdc_vehicles.vehicle_category_id', $vehicle_category_id)
                            ->count();
                        if ($vehicle > 0) {
                            $new_content = $content->toArray();
                            $new_content['error'] = 'trùng biển số xe';
                            array_push($check_duplicate_list, $new_content);
                            continue;
                        }

                        $bdc_apartment = DB::table('bdc_apartments')
                            ->where('name', $bdc_apartment_code)
                            ->where('building_id', $this->building_active_id)->whereNull('bdc_apartments.deleted_at')
                            ->first();
                       
                        if (empty($bdc_apartment)) {
                            $new_content = $content->toArray();
                            $new_content['error'] = 'không có mã căn hộ này';
                            array_push($check_duplicate_list, $new_content);
                            continue;
                        }

                        $vehicle_category = DB::table('bdc_vehicles_category')
                            ->where('bdc_building_id', $this->building_active_id)
                            ->where('id', $vehicle_category_id)
                            ->where('status', 1)
                            ->whereNull('deleted_at')
                            ->first();

                        if (empty($vehicle_category)) {
                            $new_content = $content->toArray();
                            $new_content['error'] = 'không có mã danh mục xe này';
                            array_push($check_duplicate_list, $new_content);
                            continue;
                        }
                        if (empty($first_time_active) || !strtotime($first_time_active)) {
                            // Display valid date message
                            $new_content = $content->toArray();
                            $new_content['error'] = $first_time_active . '| ngày tính không đúng định dạng dd/mm/yyyy';
                            array_push($check_duplicate_list, $new_content);
                            continue;
                        }

                        $progressive = DB::table('bdc_progressives')
                            ->where('building_id', $this->building_active_id)
                            ->where('name', 'Phí dịch vụ ' . $vehicle_category->name)
                            ->orderBy('id', 'ASC')
                            ->first();

                        if (empty($progressive)) {
                            $new_content = $content->toArray();
                            $new_content['error'] = 'không có bảng giá của dang mục xe này';
                            array_push($check_duplicate_list, $new_content);
                            continue;
                        }

                        $number_vehicle_apartment = $this->model->countVehicleByApartmentAndCate($bdc_apartment->id, $vehicle_category->id);
                        $number_current = $number_vehicle_apartment + 1;

                        $vehicle_categories = $this->modelCategory
                            ->find($vehicle_category_id);

                        if ($vehicle_categories->progressive->bdc_price_type_id == 1) {
                            $progressivePrice = $vehicle_categories->progressive->progressivePrice->first();
                        } else {
                            $progressivePrice = $vehicle_categories->progressive->progressivePrice
                                ->where('from', '<=', $number_current)
                                ->where('to', '>=', $number_current)
                                ->last();
                        }

                        if (empty($progressivePrice)) {
                            $new_content = $content->toArray();
                            $new_content['error'] = 'không có bảng giá lũy tiến';
                            array_push($check_duplicate_list, $new_content);
                            continue;
                        }

                        $service = $this->modelService->getCheckServicePrice($vehicle_categories, $number_current, $progressivePrice);

                        $data_vehicle = [
                            'name' => $name,
                            'bdc_apartment_id' => $bdc_apartment->id,
                            'number' => $number,
                            'description' => $description,
                            'vehicle_category_id' => $vehicle_category_id,
                            'bdc_progressive_price_id' => $progressivePrice->id,
                            'first_time_active' => $first_time_active ? Carbon::parse($first_time_active) : Carbon::now(),
                            'finish' => $finish ? carbon::parse($finish) : null,
                            'status' => 1,
                            'priority_level' => $progressivePrice->priority_level,
                            'price' => $progressivePrice->price,
                            'created_at' => \Illuminate\Support\Carbon::now()
                        ];

                        /**
                         * @var Vehicles $vehicle
                         */
                        $vehicle = $this->model->create($data_vehicle);


                        $data_card = [
                            'bdc_vehicle_id' => $vehicle->id,
                            'code' => $code,
                            'status' => 1,
                            'description' => $description,
                            'created_at' => \Illuminate\Support\Carbon::now()
                        ];

                        if (!empty($vehicle->id)) {
                            $vehicle->vehicleCard()->create($data_card);
                        }

                        $data_service = [
                            'bdc_service_id' => $service->id,
                            'bdc_price_type_id' => 1,
                            'bdc_apartment_id' => $bdc_apartment->id,
                            'name' => $service->name,
                            'price' => $progressivePrice->price,
                            'first_time_active' => $first_time_active ? Carbon::parse($first_time_active) : Carbon::now(),
                            'finish' => $finish ? carbon::parse($finish) : null,
                            'last_time_pay' => $first_time_active ? Carbon::parse($first_time_active) : Carbon::now(),
                            'bdc_vehicle_id' => $vehicle->id,
                            'bdc_building_id' => $this->building_active_id,
                            'bdc_progressive_id' => $progressive->id,
                            'user_id' => auth()->user()->id,
                        ];
                        $this->modelApartmentServicePrice->create($data_service);
                        DB::commit();
                    } catch (\Exception $exception) {
                        DB::rollBack();
                    }
            }

            if ($check_duplicate_list) {
                $result = Excel::create('kết quả import phương tiện', function ($excel) use ($check_duplicate_list) {
                    $excel->setTitle('Danh sách');
                    $excel->sheet('Danh sách', function ($sheet) use ($check_duplicate_list) {
                        $row = 1;
                        $sheet->row($row, [
                            'ten_phuong_tien',
                            'bien_so',
                            'ma_danh_muc_phuong_tien',
                            'ngay_bat_dau_tinh_phi',
                            'the_xe',
                            'ten_can_ho',
                            'ghi_chu',
                            'error'
                        ]);
                        foreach ($check_duplicate_list as $key => $v) {
                            $row++;
                            $sheet->row($row, [
                                isset($v['ten_phuong_tien']) ? $v['ten_phuong_tien'] : null,
                                isset($v['bien_so']) ? $v['bien_so'] : null,
                                isset($v['ma_danh_muc_phuong_tien']) ? $v['ma_danh_muc_phuong_tien'] : null,
                                isset($v['ngay_bat_dau_tinh_phi']) ?  date('d/m/Y', strtotime($v['ngay_bat_dau_tinh_phi'])) : '',
                                isset($v['the_xe']) ? $v['the_xe'] : null,
                                isset($v['ten_can_ho']) ? $v['ten_can_ho'] : null,
                                isset($v['ghi_chu']) ? $v['ghi_chu'] : null,
                                $v['error']
                            ]);
                        }
                    });
                })->store('xlsx',storage_path('exports/'));
                ob_end_clean();
$file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
return response()->download($file)->deleteFileAfterSend(true);
             
            }
        }
        return redirect()->route('admin.vehicles.index')->with(['success' => 'Import phương tiện thành công!', 'data_vhc' => 'Thêm phương tiện thành công!']);
    }
    public function report_export(Request $request) // báo cáo tổng hộp phương tiện
    {
        $building_id = $this->building_active_id;
        $vehicles = Vehicles::whereHas('bdcVehiclesCategory', function ($query) use ($request, $building_id) {
            $query->where('bdc_building_id', $building_id);
            $query->whereNull('bdc_service_id');
        })
            ->where(function ($query) use ($request) {
                if ($request->apartment) {
                    $query->where('bdc_apartment_id', $request->apartment);
                }
                if ($request->keyword) {
                    $query->where('number', 'like', '%' . $request->keyword . '%');
                }
                if ($request->cate) {
                    $query->where('vehicle_category_id', $request->cate);
                }
            })->where('status', 1)->groupBy('bdc_apartment_id')->get();
        $vehicles_cate = VehicleCategory::where(['bdc_building_id' => $building_id, 'status' => 1])->whereNull('bdc_service_id')->get();
        try {
            $result = Excel::create('báo cáo tổng hợp phương tiện_' . date('d-m-Y-H-i-s', time()), function ($excel) use ($vehicles, $vehicles_cate, $building_id) {
                $excel->setTitle('Danh sách');
                $excel->sheet('Danh sách', function ($sheet) use ($vehicles, $vehicles_cate, $building_id) {
                    $result = [];
                    foreach ($vehicles as $key => $v) {
                        $data_1 = [
                            'STT' => $key + 1,
                            'Căn hộ' => @$v->bdcApartment->name,
                        ];
                        $data_2 = null;
                        foreach ($vehicles_cate as $key_1 => $value) {
                            $data_2[$value->name] = 0;
                            if ($v->vehicle_category_id == $value->id) {
                                $data_2[$value->name] = Vehicles::whereHas('bdcVehiclesCategory', function ($query) use ($building_id) {
                                    $query->where('bdc_building_id', $building_id);
                                    $query->whereNull('bdc_service_id');
                                })
                                    ->where(['vehicle_category_id' => $v->vehicle_category_id, 'bdc_apartment_id' => $v->bdc_apartment_id, 'status' => 1])->count();
                            }
                        }
                        $result[] = array_merge($data_1, $data_2);
                    }
                    $sheet->setAutoSize(true);
                    if ($result) {
                        $sheet->fromArray($result);
                    }
                });
            })->store('xlsx',storage_path('exports/'));
$file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
return response()->download($file)->deleteFileAfterSend(true);
             
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
