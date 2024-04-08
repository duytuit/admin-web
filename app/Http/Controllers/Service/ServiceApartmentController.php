<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\BuildingController;
use App\Http\Requests\ApartmentServicePrice\ApartmentServicePriceRequest;
use App\Models\DepartmentStaff\DepartmentStaff;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\BdcApartmentServicePrice\ApartmentServicePriceRepository;
use App\Repositories\Service\ServiceRepository;
use App\Repositories\Vehicles\VehiclesRespository;
use App\Util\Debug\Log;
use App\Models\Building\Building;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

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
        return view('service.apartment.index', $data);
    }

    public function create()
    {
        $meta_title = 'Thêm mới dịch vụ căn hộ';
        $services = $this->serviceRepo->getServiceApartment($this->building_active_id);
        $apartments = $this->apartmentRepo->getApartmentOfBuilding($this->building_active_id);
        return view('service.apartment.create', compact(['meta_title', 'services', 'apartments']));
    }

    public function store(ApartmentServicePriceRequest $request)
    {
        if($request->bdc_service_id && $request->bdc_apartment_id){
           $apartmentservice = $this->apartmentServiceRepo->GetApartmentService($request->bdc_service_id,$request->bdc_apartment_id,$this->building_active_id);
            if (in_array($request->bdc_apartment_id, $apartmentservice)){
               $dataResponse = [
                    'success' => false,
                    'message' => 'Căn hộ đã được đăng ký dịch vụ này',
                    'href' => route('admin.service.apartment.index')
                ];
                return response()->json($dataResponse);
            }
        }
        $apartment = $this->apartmentServiceRepo->createServiceApartment($request->all(),$this->building_active_id);
        $dataResponse = [
            'success' => true,
            'message' => 'Thêm dịch vụ mới thành công',
            'href' => route('admin.service.apartment.index')
        ];
        return response()->json($dataResponse);
    }
    public function ajaxGetSelectService(Request $request)
    {
        if ($request->search) {
            $where[] = ['name', 'like', '%' . $request->search . '%'];
            return response()->json($this->serviceRepo->searchByAll(['where' => $where], $this->building_active_id,$request->type_service));
        }
        return response()->json($this->serviceRepo->searchByAll(['select' => ['id', 'name']], $this->building_active_id,$request->type_service));
    }
    public function edit($id)
    {
        $meta_title = 'Sửa dịch vụ căn hộ';
        $services = $this->serviceRepo->getServiceApartment($this->building_active_id);
        $apartmentService = $this->apartmentServiceRepo->findApartmentServicePrice($id);
        return view('service.apartment.edit', compact(['meta_title', 'apartmentService', 'services']));
    }
    public function ajaxGetSelectBuildings(Request $request)
    {
       
        $buildings = Building::where('company_id', $request->company_id)->where('status', 1)->where('status_apartment',1)->get();
        return json_encode($buildings);
        //return response()->json(Building::where('company_id',$request->company_id))->get() ;
    }
    public function ajaxGetSelectInspecter(Request $request)
    {
        $sql = "select
        x.name
        From
        bdc_department_staff as a
        join (
        select
            DISTINCT (display_name) as name,
            pub_user_id
        From
            pub_user_profile
        group by
            display_name) as x on
        a.pub_user_id = x.pub_user_id
        where
        bdc_department_id = ".$request->id."";
        $rs= DB::select(DB::raw($sql));
        return json_encode($rs);
        //return response()->json(Building::where('company_id',$request->company_id))->get() ;
    }
    public function ajaxGetSelectBuildingsOff(Request $request)
    {
        try{
        $sql= "update bdc_building set status_apartment = 0 where id =".$request->building."";
        DB::update($sql); 
        DB::insert("INSERT INTO buildingcare.logs
        ( user_id, logs_type, content, created_at, updated_at, NameUS)
        VALUES( $request->user_id , 'LOCKBUILDING', 'Locked: $request->building', now() , now() , NULL);");
        }
        catch(\Exception $e)
        {
            return response()->json($e->getMessage());
        }
        //return json_encode("Done");
        return redirect()->away('https://bdcadmin.s-tech.info/admin/v2/building-lock');
    }
    public function ajaxGetSelectBuildingsOn(Request $request)
    {
        try{
        $sql= "update bdc_building set status_apartment = 1 where id =".$request->building."";
        DB::update($sql); 
        DB::insert("INSERT INTO buildingcare.logs
        ( user_id, logs_type, content, created_at, updated_at, NameUS)
        VALUES( $request->user_id , 'UNLOCKBUILDING', 'UnLocked: $request->building', now() , now() , NULL);");
        }
        catch(\Exception $e)
        {
            return response()->json($e->getMessage());
        }
        return redirect()->away('https://bdcadmin.s-tech.info/admin/v2/building-lock');
       // return json_encode($buildings);
      //  return response()->json($buildings);
    }

    public function update(ApartmentServicePriceRequest $request,$id)
    {
        $apartment = $this->apartmentServiceRepo->updateServiceApartment($request->all(),$id, $this->building_active_id);
        $dataResponse = [
            'success' => true,
            'message' => 'Sửa dịch vụ mới thành công',
            'href' => route('admin.service.apartment.index')
        ];
        return response()->json($dataResponse);
    }

    public function destroy($id)
    {
        $apartmentService = $this->apartmentServiceRepo->findApartmentServicePrice($id);
        $apartmentService->updated_by = auth()->user()->id;
        $apartmentService->save();
        $apartmentService->delete();
        return redirect()->route('admin.service.apartment.index')->with('success', 'Xóa dịch vụ thành công.');
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
    public function action(Request $request)
    {
       return $this->apartmentServiceRepo->action($request);
    }
    public function changeStatus(Request $request)
    {
        $this->apartmentServiceRepo->changeStatusApartment($request['id']);
        $dataResponse = [
            'success' => true,
            'message' => 'Cập nhật trạng thái thành công!'
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
$file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
return response()->download($file)->deleteFileAfterSend(true);
             
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
