<?php

namespace App\Http\Controllers\Service\V2;

use App\Http\Controllers\BuildingController;
use App\Http\Requests\ApartmentServicePrice\ApartmentServicePriceRequest;
use App\Models\Vehicles\Vehicles;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\BdcApartmentServicePrice\ApartmentServicePriceRepository;
use App\Repositories\Service\ServiceRepository;
use App\Repositories\Vehicles\VehiclesRespository;
use App\Util\Debug\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Maatwebsite\Excel\Facades\Excel;

class ServiceApartmentController extends BuildingController
{
    public $serviceRepo;
    public $apartmentServiceRepo;
    public $apartmentRepo;
    public $vehicleRepo;

    public function __construct(
        Request $request,
        ServiceRepository $serviceRepo,
        ApartmentServicePriceRepository $apartmentServiceRepo,
        ApartmentsRespository $apartmentRepo,
        VehiclesRespository $vehicleRepo
    ) {
        parent::__construct($request);
        //$this->middleware('route_permision');
        $this->serviceRepo = $serviceRepo;
        $this->apartmentServiceRepo = $apartmentServiceRepo;
        $this->apartmentRepo = $apartmentRepo;
        $this->vehicleRepo = $vehicleRepo;
    }

    public function index(Request $request)
    {
        $data['meta_title'] = 'Quản lý dịch vụ căn hộ';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['apartmentServices'] = $this->apartmentServiceRepo->filterApartmentServicePriceByAdmin($this->building_active_id,$request)->paginate($data['per_page']);
        $data['filter'] = $request->all();
        return view('service.v2.apartment.index', $data);
    }

    public function create()
    {
        $meta_title = 'Thêm mới dịch vụ căn hộ';
        $services = $this->serviceRepo->getServiceApartment($this->building_active_id);
        $apartments = $this->apartmentRepo->getApartmentOfBuilding($this->building_active_id);
        return view('service.v2.apartment.create', compact(['meta_title', 'services', 'apartments']));
    }

    public function store(ApartmentServicePriceRequest $request)
    {
        if($request->bdc_service_id && $request->bdc_apartment_id){
           $apartmentservice = $this->apartmentServiceRepo->GetApartmentService($request->bdc_service_id,$request->bdc_apartment_id,$this->building_active_id);
            if (in_array($request->bdc_apartment_id, $apartmentservice)){
               $dataResponse = [
                    'success' => false,
                    'message' => 'Căn hộ đã được đăng ký dịch vụ này',
                    'href' => route('admin.v2.service.apartment.index')
                ];
                return response()->json($dataResponse);
            }
        }
        $apartment = $this->apartmentServiceRepo->createServiceApartment($request->all(),$this->building_active_id);
        $dataResponse = [
            'success' => true,
            'message' => 'Thêm dịch vụ mới thành công',
            'href' => route('admin.v2.service.apartment.index')
        ];
        return response()->json($dataResponse);
    }
   
    public function edit($id)
    {
        $meta_title = 'Sửa dịch vụ căn hộ';
        $services = $this->serviceRepo->getServiceApartment($this->building_active_id);
        $apartmentService = $this->apartmentServiceRepo->findApartmentServicePrice($id);
        return view('service.v2.apartment.edit', compact(['meta_title', 'apartmentService', 'services']));
    }

    public function update(ApartmentServicePriceRequest $request,$id)
    {
        $apartment = $this->apartmentServiceRepo->updateServiceApartment($request->all(),$id, $this->building_active_id);
        $dataResponse = [
            'success' => true,
            'message' => 'Sửa dịch vụ mới thành công',
            'href' => route('admin.v2.service.apartment.index')
        ];
        return response()->json($dataResponse);
    }

    public function destroy($id)
    {
        $apartmentService = $this->apartmentServiceRepo->findApartmentServicePrice($id);
        $apartmentService->updated_by = auth()->user()->id;
        $apartmentService->save();
        $apartmentService->delete();
        return redirect()->route('admin.v2.service.apartment.index')->with('success', 'Xóa dịch vụ thành công.');
    }

    public function getVehicleApartment(Request $request)
    {
        $vehicles = $this->vehicleRepo->getVehicleApartment($request->id);
        return response()->json($vehicles);
    }

    public function getServiceApartmentAjax(Request $request)
    {
        $service = $this->serviceRepo->getServiceApartmentAjax($request->id);
        return response()->json($service);
    }
    public function getServiceApartmentAjaxV2(Request $request)
    {
        $apartment_service = $this->apartmentServiceRepo->findByApartment_v3($request->apartment_id);  
        if($apartment_service->count() > 0){
            $view = view('service.v2.apartment.select_service',compact('apartment_service'))->render();
            $dataResponse = [
                'success' => true,
                'message' => 'Thành công!',
                'data' =>$apartment_service
            ];
            return response()->json($dataResponse);
        }
        $dataResponse = [
            'success' => false,
            'message' => 'Thất bại!',
            'data' => null
        ];
        return response()->json($dataResponse);
    }
    public function action(Request $request)
    {
       return $this->apartmentServiceRepo->action($request);
    }
    public function changeStatus(Request $request)
    {
        $this->apartmentServiceRepo->changeStatusApartmentV2($request['id']);
        $dataResponse = [
            'success' => true,
            'message' => 'Cập nhật trạng thái thành công!'
        ];
        return response()->json($dataResponse);
    }
    public function check_type_electric_water(Request $request)
    {
        $count = $this->apartmentServiceRepo->checkTypeElectricWater($request->apartment_id,$request->service_id);
        $dataResponse = [
            'success' => true,
            'count' => $count,
            'apartmentId' =>$request->apartment_id,
            'service_id' =>$request->service_id
        ];
        return response()->json($dataResponse);
    }

    public function export(Request $request){
       $apartmentService = $this->apartmentServiceRepo->filterApartmentServicePriceByAdmin($this->building_active_id,$request)->get();
        try {
            $result = Excel::create('Dịch vụ căn hộ', function ($excel) use ($apartmentService) {
                        $excel->setTitle('Dịch vụ căn hộ');
                        $excel->sheet('Dịch vụ căn hộ', function ($sheet) use ($apartmentService) {
                            $row = 1;
                            $sheet->row($row, [
                                'STT',
                                'ID',
                                'Mã căn hộ',
                                'Tên căn hộ',
                                'Mã dịch vụ',
                                'Tên dịch vụ',
                                'giá',
                                'Ngày bắt đầu sử dụng',
                                'Ngày kết thúc sử dụng',
                                'Ngày tính phí tiếp theo',
                                'Trạng thái',
                                'Người tạo'
                            ]);
                            foreach ($apartmentService as $key => $value) {
                                $row++;
                                $sheet->row($row, [
                                    ($key + 1),
                                    $value->id,
                                    asset(@$value->apartment->code) ? @$value->apartment->code : "",
                                    asset(@$value->apartment->name) ? @$value->apartment->name : "",
                                    $value->bdc_service_id,
                                    @$value->vehicle ? $value->name.' - '.@$value->vehicle->number : $value->name,
                                    $value->price ? number_format($value->price) : '',
                                    $value->first_time_active ? date('d/m/Y', strtotime(@$value->first_time_active)): null,
                                    $value->finish ? date('d/m/Y', strtotime(@$value->finish)): null,
                                    date('d/m/Y', strtotime(@$value->last_time_pay)),
                                    $value->status == 1 ? 'active' : 'inactive',
                                    asset(@$value->pubUser->email) ? @$value->pubUser->email : "",
                                ]);
                            }
                        });
                })->store('xlsx',storage_path('exports/'));
                ob_end_clean();
            $file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
            return response()->download($file)->deleteFileAfterSend(true);
             
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
    public function ajaxGetServiceApartment(Request $request)
    {
        if ($request->search || $request->apartment_id) {
            $where[] = ['name', 'like', '%' . $request->search . '%'];
            if ($request->apartment_id) {
                $where[] = ['bdc_apartment_id', '=',@$request->apartment_id];
            }
            $result = $this->apartmentServiceRepo->findServiceAparmtent(['where' => $where],$this->building_active_id);
            if($result){
                foreach ($result as $key => $item) {
                    $result[$key]->vehicle_name = '';
                    if($item->bdc_vehicle_id > 0){
                        $vehicle = Vehicles::get_detail_vehicle_by_id($item->bdc_vehicle_id);
                        $result[$key]->vehicle_name = $vehicle ? $vehicle->number : '';
                    }

                }
            }
            return response()->json(['data'=>$result]);
        }
        $result = $this->apartmentServiceRepo->findServiceAparmtent(['select' => ['id', 'name','bdc_vehicle_id']],$this->building_active_id);
        if($result){
            foreach ($result as $key => $item) {
                $result[$key]->vehicle_name = '';
                if($item->bdc_vehicle_id > 0){
                    $vehicle = Vehicles::get_detail_vehicle_by_id($item->bdc_vehicle_id);
                    $result[$key]->vehicle_name = $vehicle ? $vehicle->number : '';
                }

            }
        }
        return response()->json(['data'=>$result]);
    }
}
