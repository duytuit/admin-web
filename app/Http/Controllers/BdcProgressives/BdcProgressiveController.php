<?php

namespace App\Http\Controllers\BdcProgressives;

use App\Http\Controllers\BuildingController;
use App\Models\Service\Service;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Progressives\ImportExcelRequest;
use App\Http\Requests\Progressives\ProgressiveRequest;
use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Models\BdcBills\Bills;
use App\Models\BdcPriceType\PriceType;
use App\Models\BdcProgressivePrice\ProgressivePrice;
use App\Models\BdcProgressives\Progressives;
use App\Models\Building\Building;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\BdcBills\BillRepository;
use App\Repositories\BdcApartmentServicePrice\ApartmentServicePriceRepository;
use App\Repositories\BdcDebitDetail\DebitDetailRepository;
use App\Repositories\BdcDebitLogs\DebitLogsRepository;
use App\Repositories\BdcProgressive\ProgressiveRepository;
use App\Repositories\Building\BuildingRepository;
use App\Repositories\CronJobManager\CronJobManagerRepository;
use App\Repositories\Customers\CustomersRespository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Repositories\Service\ServiceRepository;
use App\Traits\ApiResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cookie;
use Maatwebsite\Excel\Facades\Excel;
use App\Repositories\Config\ConfigRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BdcProgressiveController extends BuildingController
{
    use ApiResponse;

    protected $model;

    const CREATE_PRICE_FAILURE = 203;

    public function __construct(Request $request, ProgressiveRepository $model, DebitDetailRepository $debitDetail)
    {
        //$this->middleware('auth', ['except'=>[]]);
        //$this->middleware('route_permision');
        $this->model = $model;
        $this->debitDetail = $debitDetail;
        Carbon::setLocale('vi');
        parent::__construct($request);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $data['meta_title'] = 'Bảng giá';
        $data['per_page'] = Cookie::get('per_page', 20);
        $data['filter'] = $request->all();
        $progressive = $this->model->findByBuildingId($this->building_active_id)->where(function ($query) use($request){
            if(isset($request->bdc_service_id) && $request->bdc_service_id!=null){
                $query->where('bdc_service_id',$request->bdc_service_id);
            }
        })->paginate($data['per_page']);
        $data['service'] = Service::where('bdc_building_id',$this->building_active_id)->where('status',1)->select(['name','id'])->get();
        $data['progressive'] = $progressive;
        $data['count_display'] = count($progressive);
        return view('progressive.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(PriceType $modelPriceType)
    {
        $service = Service::where('bdc_building_id',$this->building_active_id)->where('status',1)->pluck('name','id')->toArray();
        $progressives = $modelPriceType::pluck('name', 'id');
        return view('progressive.create', ['meta_title' => 'Tạo bảng giá', 'progressives' => $progressives,'service' => $service]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProgressiveRequest $request, ProgressivePrice $progressivePrice, BuildingRepository $buildingRepository)
    {
        $input = $request->all();

        \DB::beginTransaction();
        try {
            $building = Building::find($this->building_active_id);
            $this->model->addProgressive($building->id, $building->company_id, $input, $progressivePrice);
        } catch (\Exception $e) {
            \DB::rollBack();
            throw new \Exception("register ERROR: " . $e->getMessage(), 1);
        }
        \DB::commit();

        return redirect('admin/progressive')->with('success', 'Thêm bảng giá thành công.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit(PriceType $modelPriceType, $id)
    {
        $progressive = Progressives::find($id);
        $progressivePrice = $progressive->progressivePrice()->get();
        $service = Service::where('bdc_building_id',$this->building_active_id)->where('status',1)->pluck('name','id')->toArray();
        $progressives = $modelPriceType::pluck('name', 'id');
        $selectedRole = $progressive->bdc_price_type_id;
        return view('progressive.edit', [
            'meta_title' => 'Sửa bảng giá',
            'item' => $progressive,
            'progressivePrices' => $progressivePrice,
            'progressives' => $progressives,
            'selectedRole' => $selectedRole,
            'service' => $service
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update($id, ProgressiveRequest $request, ProgressivePrice $progressivePrice, BuildingRepository $buildingRepository)
    {
        $input = $request->all();
        \DB::beginTransaction();
        try {
            $building = Building::find($this->building_active_id);
            $this->model->updateProgressive($id, $building->id, $building->company_id, $input, $progressivePrice);
            Cache::store('redis')->forget(env('REDIS_PREFIX') . 'get_detail_progressive_price_by_progressive_id' . $id);
        } catch (\Exception $e) {
            \DB::rollBack();
            throw new \Exception("register ERROR: " . $e->getMessage(), 1);
        }
        \DB::commit();

        return redirect('admin/progressive')->with('success', 'Sửa bảng giá thành công.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $rs = $this->model->delete(['id' => $id]);
        if ($rs) {
            return $this->responseSuccess([], ['Xóa bảng giá thành công!']);
        } else {
            return $this->responseError(['Xóa bảng giá thất bại!'], self::CREATE_PRICE_FAILURE);
        }
    }

    public function importExcel()
    {
        //dd($this->building_active_id);
        $data['meta_title'] = 'Import Excel Điện Nước';
        return view('progressive.import_excel', $data);
    }

    public function importExcelPhiDauKy()
    {
        //dd($this->building_active_id);
        $data['meta_title'] = 'Import Excel phí dịch vụ';
        return view('progressive.import_phi_dau_ky', $data);
    }

    public function importServiceApartment()
    {
        $data['meta_title'] = 'Import Excel';
        return view('progressive.import_service_apartment', $data);
    }

    public function importFileExcelPost(
        ImportExcelRequest              $request,
        CronJobManagerRepository        $cronJobManager,
        ApartmentServicePriceRepository $apartmentServicePrice,
        CustomersRespository            $customer,
        ServiceRepository               $service,
        ApartmentsRespository           $apartmentRepository,
        DebitLogsRepository             $debitLogs)
    {
        if (!$request->file('file_import')) {
            return redirect('admin/progressive/import-excel')->with('danger', 'Không có file tải lên.');
        }
        $importDienNuoc = $this->debitDetail->importFileDienNuoc(
            $request,
            $cronJobManager,
            $this->building_active_id,
            $apartmentServicePrice,
            $customer,
            $service,
            $apartmentRepository,
            $debitLogs);
        if ($importDienNuoc) {
            return redirect('admin/progressive/import-excel')->with('success', 'Import file thành công.');
        }
        return redirect('admin/progressive/import-excel')->with('danger', 'Import file không thành công.');
    }

    public function importFileExcelPhiDauKyPost(
        ImportExcelRequest              $request,
        CronJobManagerRepository        $cronJobManager,
        ApartmentServicePriceRepository $apartmentServicePrice,
        CustomersRespository            $customer,
        ServiceRepository               $service,
        ApartmentsRespository           $apartmentRepository,
        DebitLogsRepository             $debitLogs)
    {
        if (!$request->file('file_import')) {
            return redirect('admin/progressive/import-phi-dau-ky')->with('danger', 'Không có file tải lên.');
        }
        $importPhiDauKy = $this->debitDetail->importFileDauKy(
            $request,
            $cronJobManager,
            $this->building_active_id,
            $apartmentServicePrice,
            $customer,
            $service,
            $apartmentRepository,
            $debitLogs);
        if ($importPhiDauKy) {
            return redirect('admin/progressive/import-phi-dau-ky')->with('success', 'Import file thành công.');
        }
        return redirect('admin/progressive/import-phi-dau-ky')->with('danger', 'Import file không thành công.');
    }

    public function import_excel_service_post(
        ImportExcelRequest              $request,
        CronJobManagerRepository        $cronJobManager,
        ApartmentServicePriceRepository $apartmentServicePrice,
        CustomersRespository            $customer,
        ServiceRepository               $service,
        ApartmentsRespository           $apartmentRepository,
        BillRepository                  $bill,
        DebitDetailRepository           $debitDetail,
        ConfigRepository                $config,
        DebitLogsRepository             $debitLogs)
    {
        $file = $request->file('file_import');

        if (!$request->file('file_import')) {
            return redirect('admin/progressive/import-cong-no')->with('danger', 'Không có file tải lên.');
        }

        // param: serviceApartmentId,from_date,to_date,cycle_name,deadline
        $path = $file->getRealPath();

        $excel_data = Excel::load($path)->get();
        if ($excel_data->count()) {
            $ids = null;
            foreach ($excel_data as $content) {
                // check is number
                if (!empty($content->ma_dich_vu) && preg_match('/\d/', $content->ma_dich_vu) !== 1) { // không phải là kiểu số hoặc nhỏ hơn 0
                    $new_content = $content->toArray();
                    $new_content['error'] = $content->ma_dich_vu . '| không phải là kiểu số nguyên';
                    array_push($data_list_error, $new_content);
                    continue;
                }
                $ids[] = $content->ma_dich_vu;
            }
        }

        if (count($ids) > 0) {
            $get_service_apartment = ApartmentServicePrice::whereIn('id', $ids)->where(['status' => 1, 'bdc_price_type_id' => 1])->orderBy('bdc_apartment_id')->get();
            $apartmentId = null;
            //dd($get_service_apartment);
            DB::beginTransaction();
            $year = substr($request->cycle_name, 0, 4);
            $month = sprintf("%'.01d", substr($request->cycle_name, 4, strlen($request->cycle_name)));
            $chuky = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            try {
                foreach ($get_service_apartment as $key => $value) {
                    $vehicle_number = $value->vehicle;
                    if ($vehicle_number != null) {
                        $check_status_vehicle = $value->vehicle()->where(['bdc_apartment_id' => $value->bdc_apartment_id, 'status' => 1])->first();
                        if (!$check_status_vehicle) {
                            continue;
                        }
                        $value->name = $vehicle_number->number;
                    }
                    if ($apartmentId != $value->bdc_apartment_id) {
                        $apartmentId = $value->bdc_apartment_id;

                        $checkDuplicateBillCycleName = DebitDetailRepository::findServiceCheckFromDate($value->bdc_service_id, $value->bdc_apartment_id, $value->id, $request->from_date);
                        if ($checkDuplicateBillCycleName) {
                            $debitLogs->create([
                                'bdc_building_id' => $value->bdc_building_id,
                                'bdc_apartment_id' => $value->bdc_apartment_id,
                                'bdc_service_id' => $value->bdc_service_id,
                                'key' => "debitprocess:cron",
                                'cycle_name' => $request->cycle_name,
                                'input' => json_encode($value),
                                'data' => "",
                                'message' => "Thời gian tính" . $request->from_date . '->' . $request->to_date,
                                'status' => 110
                            ]);
                            continue;
                        }
                        // Tìm hóa đơn của tháng hiện tại đã tạo hay chưa
                        $get_bill = $bill->findBuildingApartmentIdV2($value->bdc_building_id, $value->bdc_apartment_id, $request->cycle_name);

                        if ($get_bill) {   // thi tao debit
                            // Tạo công nợ
                            $dateUsing = Carbon::parse($request->to_date)->diffInDays(Carbon::parse($request->from_date));

                            $sumeryPrice = ($value->price / $chuky) * $dateUsing;
                            $debitDetailMaxVersion = $debitDetail->findByBuildingApartmentServiceId(
                                $value->bdc_building_id,
                                $value->bdc_apartment_id,
                                $value->bdc_service_id);
                            $previousOwed = 0;
                            if ($debitDetailMaxVersion) {
                                $previousOwed = $debitDetailMaxVersion->previous_owed;
                            }
                            $onePrice = $value->price / $chuky;
                            $debitDetail->create([
                                'bdc_building_id' => $value->bdc_building_id,
                                'bdc_bill_id' => $get_bill->id,
                                'bdc_apartment_id' => $value->bdc_apartment_id,
                                'bdc_service_id' => $value->bdc_service_id,
                                'bdc_apartment_service_price_id' => $value->id,
                                'title' => $value->name,
                                'from_date' => $request->from_date,
                                'to_date' => $request->to_date,
                                'detail' => 'test',
                                'version' => 0,
                                'sumery' => $sumeryPrice,
                                'new_sumery' => $sumeryPrice,
                                'previous_owed' => $previousOwed,
                                'paid' => 0,
                                'is_free' => 0,
                                'cycle_name' => $request->cycle_name,
                                'price' => $onePrice,
                                'quantity' => $dateUsing,
                                'bdc_price_type_id' => $value->bdc_price_type_id,
                                'price_current' => $value->price,
                            ]);
                            $debitDetailByBillId = $debitDetail->findMaxVersionByBillId($get_bill->id);
                            $sumary = array_sum(array_column($debitDetailByBillId, 'sumery'));
                            $get_bill->cost = $sumary;
                            $get_bill->save();
                            // Cập nhật last_time_pay
                            $apartmentServicePrice->update(['last_time_pay' => $request->to_date], $value->id);
                        } else {
                            $sum_paid = $get_service_apartment->where('bdc_apartment_id')->sum('price');
                            $customer = CustomersRespository::findApartmentIdV2($value->bdc_apartment_id, 0);
                            $pubUserProfile = $customer ? PublicUsersProfileRespository::getInfoUserById($customer->user_info_id) : null;

                            $billResult = $bill->create([
                                'bdc_apartment_id' => $value->bdc_apartment_id,
                                'bdc_building_id' => $value->bdc_building_id,
                                'bill_code' => $bill->autoIncrementBillCode($config, $value->bdc_building_id),
                                'cost' => $sum_paid,
                                'cost_free' => 0,
                                'customer_name' => $pubUserProfile->full_name,
                                'customer_address' => $pubUserProfile->address == null ? "" : $pubUserProfile->address,
                                'deadline' => $request->deadline,
                                'provider_address' => 'Banking',
                                'is_vat' => 0,
                                'status' => $bill::WAIT_FOR_CONFIRM,
                                'notify' => 0,
                                'cycle_name' => $request->cycle_name,
                                'user_id' => auth()->user()->id,
                            ]);

                            $dateUsing = Carbon::parse($request->to_date)->diffInDays(Carbon::parse($request->from_date));

                            $sumeryPrice = ($value->price / $chuky) * $dateUsing;
                            $debitDetailMaxVersion = $debitDetail->findByBuildingApartmentServiceId(
                                $value->bdc_building_id,
                                $value->bdc_apartment_id,
                                $value->bdc_service_id);
                            $previousOwed = 0;
                            if ($debitDetailMaxVersion) {
                                $previousOwed = $debitDetailMaxVersion->previous_owed;
                            }
                            $onePrice = $value->price / $chuky;
                            $debitDetail->create([
                                'bdc_building_id' => $value->bdc_building_id,
                                'bdc_bill_id' => $billResult->id,
                                'bdc_apartment_id' => $value->bdc_apartment_id,
                                'bdc_service_id' => $value->bdc_service_id,
                                'bdc_apartment_service_price_id' => $value->id,
                                'title' => $value->name,
                                'from_date' => $request->from_date,
                                'to_date' => $request->to_date,
                                'detail' => 'test',
                                'version' => 0,
                                'sumery' => $sumeryPrice,
                                'new_sumery' => $sumeryPrice,
                                'previous_owed' => $previousOwed,
                                'paid' => 0,
                                'is_free' => 0,
                                'cycle_name' => $request->cycle_name,
                                'price' => $onePrice,
                                'quantity' => $dateUsing,
                                'bdc_price_type_id' => $value->bdc_price_type_id,
                                'price_current' => $value->price,
                            ]);
                            // Cập nhật last_time_pay
                            $apartmentServicePrice->update(['last_time_pay' => $request->to_date], $value->id);
                        }

                    } else {
                        $checkDuplicateBillCycleName = DebitDetailRepository::findServiceCheckFromDate($value->bdc_service_id, $value->bdc_apartment_id, $value->id, $request->from_date);
                        if ($checkDuplicateBillCycleName) {
                            $debitLogs->create([
                                'bdc_building_id' => $value->bdc_building_id,
                                'bdc_apartment_id' => $value->bdc_apartment_id,
                                'bdc_service_id' => $value->bdc_service_id,
                                'key' => "debitprocess:cron",
                                'cycle_name' => $request->cycle_name,
                                'input' => json_encode($value),
                                'data' => "",
                                'message' => "Thời gian tính" . $request->from_date . '->' . $request->to_date,
                                'status' => 110
                            ]);
                            continue;
                        }
                        // Tìm hóa đơn của tháng hiện tại đã tạo hay chưa
                        $get_bill = $bill->findBuildingApartmentIdV2($value->bdc_building_id, $value->bdc_apartment_id, $request->cycle_name);

                        if ($get_bill) {   // thi tao debit
                            // Tạo công nợ
                            $dateUsing = Carbon::parse($request->to_date)->diffInDays(Carbon::parse($request->from_date));

                            $sumeryPrice = ($value->price / $chuky) * $dateUsing;
                            $debitDetailMaxVersion = $debitDetail->findByBuildingApartmentServiceId(
                                $value->bdc_building_id,
                                $value->bdc_apartment_id,
                                $value->bdc_service_id);
                            $previousOwed = 0;
                            if ($debitDetailMaxVersion) {
                                $previousOwed = $debitDetailMaxVersion->previous_owed;
                            }
                            $onePrice = $value->price / $chuky;
                            $debitDetail->create([
                                'bdc_building_id' => $value->bdc_building_id,
                                'bdc_bill_id' => $get_bill->id,
                                'bdc_apartment_id' => $value->bdc_apartment_id,
                                'bdc_service_id' => $value->bdc_service_id,
                                'bdc_apartment_service_price_id' => $value->id,
                                'title' => $value->name,
                                'from_date' => $request->from_date,
                                'to_date' => $request->to_date,
                                'detail' => 'test',
                                'version' => 0,
                                'sumery' => $sumeryPrice,
                                'new_sumery' => $sumeryPrice,
                                'previous_owed' => $previousOwed,
                                'paid' => 0,
                                'is_free' => 0,
                                'cycle_name' => $request->cycle_name,
                                'price' => $onePrice,
                                'quantity' => $dateUsing,
                                'bdc_price_type_id' => $value->bdc_price_type_id,
                                'price_current' => $value->price,
                            ]);
                            $debitDetailByBillId = $debitDetail->findMaxVersionByBillId($get_bill->id);
                            $sumary = array_sum(array_column($debitDetailByBillId, 'sumery'));
                            $get_bill->cost = $sumary;
                            $get_bill->save();
                            // Cập nhật last_time_pay
                            $apartmentServicePrice->update(['last_time_pay' => $request->to_date], $value->id);
                        } else {
                            $sum_paid = $get_service_apartment->where('bdc_apartment_id')->sum('price');
                            $customer = CustomersRespository::findApartmentIdV2($value->bdc_apartment_id, 0);
                            $pubUserProfile = $customer ? PublicUsersProfileRespository::getInfoUserById($customer->user_info_id) : null;

                            $billResult = $bill->create([
                                'bdc_apartment_id' => $value->bdc_apartment_id,
                                'bdc_building_id' => $value->bdc_building_id,
                                'bill_code' => $bill->autoIncrementBillCode($config, $value->bdc_building_id),
                                'cost' => $sum_paid,
                                'cost_free' => 0,
                                'customer_name' => $pubUserProfile->full_name,
                                'customer_address' => $pubUserProfile->address == null ? "" : $pubUserProfile->address,
                                'deadline' => $request->deadline,
                                'provider_address' => 'Banking',
                                'is_vat' => 0,
                                'status' => $bill::WAIT_FOR_CONFIRM,
                                'notify' => 0,
                                'cycle_name' => $request->cycle_name,
                                'user_id' => auth()->user()->id,
                            ]);

                            $dateUsing = Carbon::parse($request->to_date)->diffInDays(Carbon::parse($request->from_date));

                            $sumeryPrice = ($value->price / $chuky) * $dateUsing;
                            $debitDetailMaxVersion = $debitDetail->findByBuildingApartmentServiceId(
                                $value->bdc_building_id,
                                $value->bdc_apartment_id,
                                $value->bdc_service_id);
                            $previousOwed = 0;
                            if ($debitDetailMaxVersion) {
                                $previousOwed = $debitDetailMaxVersion->previous_owed;
                            }
                            $onePrice = $value->price / $chuky;
                            $debitDetail->create([
                                'bdc_building_id' => $value->bdc_building_id,
                                'bdc_bill_id' => $billResult->id,
                                'bdc_apartment_id' => $value->bdc_apartment_id,
                                'bdc_service_id' => $value->bdc_service_id,
                                'bdc_apartment_service_price_id' => $value->id,
                                'title' => $value->name,
                                'from_date' => $request->from_date,
                                'to_date' => $request->to_date,
                                'detail' => 'test',
                                'version' => 0,
                                'sumery' => $sumeryPrice,
                                'new_sumery' => $sumeryPrice,
                                'previous_owed' => $previousOwed,
                                'paid' => 0,
                                'is_free' => 0,
                                'cycle_name' => $request->cycle_name,
                                'price' => $onePrice,
                                'quantity' => $dateUsing,
                                'bdc_price_type_id' => $value->bdc_price_type_id,
                                'price_current' => $value->price,
                            ]);
                            // Cập nhật last_time_pay
                            $apartmentServicePrice->update(['last_time_pay' => $request->to_date], $value->id);
                        }
                    }
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                dd($e->getMessage());

            }
            return redirect('admin/progressive/import-cong-no')->with('success', 'Import file thành công.');

        }
    }


    public function download()
    {
        $file = public_path() . '/downloads/dien_nuoc_template.xlsx';
        return response()->download($file);
    }

    public function downloadphidauky()
    {
        $file = public_path() . '/downloads/phi_dau_ky_template.xlsx';
        return response()->download($file);
    }

    public function download_tool_template()
    {
        $file = storage_path() . '/downloads/tool_template.xlsx';
        return response()->download($file);
    }
}
