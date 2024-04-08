<?php

namespace App\Http\Controllers\BdcElectricMeter;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Validator;
use App\Models\BdcElectricMeter\ElectricMeter;
use App\Commons\Helper;
use App\Commons\Util\Debug\Log;
use App\Exceptions\QueueRedis;
use App\Helpers\Files;
use App\Models\Apartments\Apartments;
use Dotenv\Regex\Success;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\BuildingController;
use App\Models\Apartments\V2\UserApartments;
use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Models\BdcProgressivePrice\ProgressivePrice;
use App\Models\Building\BuildingPlace;
use App\Models\PublicUser\Users;
use App\Models\Service\Service;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\BdcApartmentServicePrice\ApartmentServicePriceRepository;
use App\Repositories\BdcDebitDetail\DebitDetailRepository;
use App\Repositories\BdcDebitLogs\DebitLogsRepository;
use App\Repositories\BdcLockCyclename\BdcLockCyclenameRepository;
use App\Repositories\BdcV2DebitDetail\DebitDetailRepository as BdcV2DebitDetailDebitDetailRepository;
use App\Repositories\Building\BuildingPlaceRepository;
use App\Repositories\CronJobManager\CronJobManagerRepository;
use App\Repositories\Customers\CustomersRespository;
use App\Repositories\ElectricMeter\ElectricMeterRespository;
use App\Repositories\Service\ServiceRepository;
use App\Util\Debug\Log as DebugLog;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;
use \PHPExcel;
use \PHPExcel_IOFactory; 


class ElectricMeterController extends BuildingController
{
    use ApiResponse;

    private $model;
    private $serviceRepo;
    public $debitDetailRepo;
    private $_electricMeterRespository;
    public $apartmentRepo;
    private $modelBuildingPlace;
    private $_serviceApartment;

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct(
        Request                         $request,
        DebitDetailRepository           $debitDetailRepo,
        ElectricMeterRespository        $electricMeterRespository,
        ApartmentsRespository           $apartmentRepo,
        BuildingPlaceRepository         $modelBuildingPlace,
        ApartmentServicePriceRepository $apartmentServicePriceRepository
    )
    {
        $this->_electricMeterRespository = $electricMeterRespository;
        $this->_serviceApartment = $apartmentServicePriceRepository;
        $this->debitDetailRepo = $debitDetailRepo;
        $this->apartmentRepo = $apartmentRepo;
        $this->modelBuildingPlace = $modelBuildingPlace;
        parent::__construct($request);
        //$this->middleware('route_permision');
    }

    public function index(Request $request)
    {
        $data['meta_title'] = 'Danh sách ghi chỉ số điện nước';

        $data['per_page'] = Cookie::get('per_page', 20);

        $data['paymentDeadlineBuilding'] = Carbon::now()->addDays(3)->toDateString();

        $input = $request->all();

        $input['type'] = $request->input('type', 1); // 0:điện, 1;nước ,2: nước nóng

        $data['filter'] = $input;
        //Duong add filter floor
        $cycle_names = $this->_electricMeterRespository->getCycleName($this->building_active_id);
        
        //$data['floors'] = Apartments::select('floor')->where('building_id',$this->building_active_id)->distinct()->orderBy('floor', 'asc')->get()->toArray();

        $data['cycle_names'] = $cycle_names;

        $data['data_search']['floor'] = $request->floor;

        $data['floors'] = $this->apartmentRepo->getApartmentFloor();

        $data['chose_cycle_name'] = $request->cycle_name ? $request->cycle_name : ($cycle_names ? null : null); //$cycle_names[0]
        //$data['chose_cycle_name'] = $request->cycle_name ? $request->cycle_name : ($cycle_names ? $cycle_names[0] : null);

        $input['cycle_name'] = $data['chose_cycle_name']; // tháng chốt số

        if (isset($data['filter']['bdc_apartment_id'])) {
            $data['get_apartment'] = $this->apartmentRepo->findById_v2($this->building_active_id, $data['filter']['bdc_apartment_id']);
        }
        if (isset($data['filter']['ip_place_id'])) {
            $data['get_place_building'] = $this->modelBuildingPlace->findById($data['filter']['ip_place_id']);
        }

        $get_electric_apart = $this->_electricMeterRespository->getListAll($input, $this->building_active_id)->paginate($data['per_page']);
        $data['electric_meters'] = $get_electric_apart;
        return view('electric-meter.index', $data);
    }

    public function indexImport()
    {
        $data['meta_title'] = 'import file electric meter';
        $file = '/downloads/import_electric_meter.xlsx';
        $data['file'] = $file;
        return view('electric-meter.import', $data);
    }

    public function download()
    {
        $file = public_path() . '/downloads/import_electric_meter.xlsx';
        return response()->download($file);
    }

    public function countApartmentByCycleNameAndType(Request $request)
    {
        $count = $this->_electricMeterRespository->countApartmentUseByTypeAndCycleName($this->building_active_id, $request->cycle_name_handle, $request->type_handle);
        return response()->json(['sussces' => true, 'count' => $count], 200);
    }

    public function getDetail(Request $request)
    {
        $electric_meter = ElectricMeter::find($request->id);
        $electric_meter->apartment_name = @$electric_meter->apartment->name;
        return response()->json(['sussces' => true, 'electric_meter' => $electric_meter, 'id' => $request->id], 200);
    }

    public function next(Request $request)
    {
        $next = ElectricMeter::where('id', '>', $request->id)->where('bdc_building_id', $this->building_active_id)->orderBy('id', 'asc')->first();
        if ($next) {
            $next->apartment_name = @$next->apartment->name;
            return response()->json(['sussces' => true, 'electric_meter' => $next, 'id' => $request->id], 200);
        }

    }

    public function previous(Request $request)
    {
        $previous = ElectricMeter::where('id', '<', $request->id)->where('bdc_building_id', $this->building_active_id)->orderBy('id', 'desc')->first();

        if ($previous) {
            $previous->apartment_name = $previous ? @$previous->apartment->name : null;
            return response()->json(['sussces' => true, 'electric_meter' => $previous, 'id' => $request->id], 200);
        }

    }

    public function save(Request $request)
    {
        $electric_meter = ElectricMeter::find($request->id);
        if ($electric_meter) {
            $checkCycleName = $this->_electricMeterRespository->getLastCycleName($this->building_active_id);
            // if($checkCycleName != $electric_meter->month_create){
            //     return response()->json(['sussces'=>false,'msg'=>'Chỉ được sửa chỉ số cuối ở tháng cuối chốt số'],200);
            // }
            if ((int)$request->after_number < $electric_meter->before_number) {
                return response()->json(['sussces' => false, 'msg' => 'Chỉ số cuối không được nhỏ hơn chỉ số đầu'], 200);
            }
            $electric_meter->after_number = $request->after_number;
            $electric_meter->save();
            $electric_meter->apartment_name = @$electric_meter->apartment->name;

            $get_before_number = ElectricMeter::where(['bdc_building_id' => $electric_meter->bdc_building_id, 'bdc_apartment_id' => $electric_meter->bdc_apartment_id, 'type' => $electric_meter->type])
                ->whereDate('date_update', '>', Carbon::parse($electric_meter->date_update)->format('Y-m-d'))->orderBy('date_update', 'asc')
                ->first();

            if ($get_before_number) {
                $get_before_number->before_number = $request->after_number;
                $get_before_number->save();
            }

            return response()->json(['sussces' => true, 'electric_meter' => $electric_meter, 'id' => $request->id, 'msg' => 'thành công'], 200);
        }

        return response()->json(['sussces' => false, 'msg' => 'không tìm thấy'], 200);
    }

    public function edit(Request $request)
    {
        $count = $this->_electricMeterRespository->countApartmentUseByTypeAndCycleName($this->building_active_id, $request->cycle_name_handle, $request->type_handle);
        return response()->json(['sussces' => true, 'count' => $count], 200);
    }

    public function removeImage(Request $request)
    {
        if(\Auth::user()->isadmin == 1){
            $electric_meter = ElectricMeter::find($request->id);
            if($electric_meter){
                $electric_meter->images = null;
                $electric_meter->save();
                return response()->json(['sussces' => true,'msg' => 'Xóa ảnh thành công'], 200);
            }
            return response()->json(['sussces' => false, 'msg' => 'không tìm thấy'], 200);
        }
        return response()->json(['sussces' => false, 'msg' => 'không có quyền'], 200);
    }

    public function handle_electric_water(Request                  $request,
                                          CronJobManagerRepository $cronJobManager,
                                          CustomersRespository     $customer,
                                          DebitLogsRepository      $debitLogs)
    {
        $cycleName = $request->cycle_year . $request->cycle_month;
        $action = Helper::getAction();
        if ($action) {
            $check_lock_cycle = BdcLockCyclenameRepository::checkLock($this->building_active_id, $cycleName, $action);
            if ($check_lock_cycle) {
                return redirect()->route('admin.v2.debit.debitLogs')->with('warning', "Kỳ $cycleName đã được khóa.");
            }
        }
        $buildingId = $this->building_active_id;
        $Ids = (object)$request->ids;
        $cronJob = $cronJobManager->findSignatureBuildingId('dienuocdebitprocess_v2:cron', $this->building_active_id)->first();
        if ($cronJob) {
            return redirect()->route('admin.v2.debit.debitLogs')->with('warning', 'Công nợ đang được thiết lập');
        }
        if (!$cronJob) {   // nếu không có thì tạo mới với status = -1
            $cronJob = $cronJobManager->create([
                'building_id' => $this->building_active_id,
                'user_id' => auth()->user()->id,
                'signature' => 'dienuocdebitprocess_v2:cron',
                'status' => -1,
                'data' => json_encode($request->all()),
                'deadline' => $request['deadline'] ? Carbon::parse($request['deadline'])->format('Y-m-d') : Carbon::now()->format('Y-m-d'),
                'type' => 1
            ]);
        }
        $count = 0;
        $type = array_values($request->ids);
        if (!$request->cycle_name_handle_electric && !$request->cycle_name_handle_meter && !$request->cycle_name_handle_meter_hot) {
            return redirect()->route('admin.v2.debit.debitLogs')->with('warning', 'Chưa chọn kỳ điện nước');
        }
        $cycle_name_electric_water = null;
        if ($request->cycle_name_handle_electric) {
            $cycle_name_electric_water[] = $request->cycle_name_handle_electric;
        }
        if ($request->cycle_name_handle_meter) {
            $cycle_name_electric_water[] = $request->cycle_name_handle_meter;
        }
        if ($request->cycle_name_handle_meter_hot) {
            $cycle_name_electric_water[] = $request->cycle_name_handle_meter_hot;
        }
        if ($request->nhom_can_ho) {
            $apartmentIds = $this->apartmentRepo->findByGroup($this->building_active_id, $request->nhom_can_ho);
            if (!$apartmentIds) {
                return redirect()->route('admin.v2.debit.debitLogs')->with('warning', 'Không có căn hộ nào trong nhóm này.');
            }
            $electric_meters = ElectricMeter::where(['bdc_building_id' => $this->building_active_id])->whereIn('bdc_apartment_id', $apartmentIds)->whereIn('month_create', $cycle_name_electric_water)->whereIn('type', $type)->get();
        } else if ($request->can_ho) {
            $apartmentIds = $request->can_ho;
            if (!$apartmentIds) {
                return redirect()->route('admin.v2.debit.debitLogs')->with('warning', 'Không có căn hộ nào trong nhóm này.');
            }
            $electric_meters = ElectricMeter::where(['bdc_building_id' => $this->building_active_id])->whereIn('bdc_apartment_id', $apartmentIds)->whereIn('month_create', $cycle_name_electric_water)->whereIn('type', $type)->get();

        } else {
            $electric_meters = ElectricMeter::where(['bdc_building_id' => $this->building_active_id])->whereIn('month_create', $cycle_name_electric_water)->whereIn('type', $type)->get();
        }

        if ($electric_meters->count() == 0) {
            return redirect()->route('admin.v2.debit.debitLogs')->with('warning', 'Không tìm thấy dữ liệu điện nước.');
        }
        foreach ($electric_meters as $value) {
            QueueRedis::setItemForQueue('add_queue_apartment_service_dien_nuoc_price_v2_' . $buildingId, $value);
            $count++;
        }

        if ($count > 0) {
            $cronJobManager->update(['status' => 0], $cronJob->id);
        } else {
            $cronJobManager->delete(['id' => $cronJob->id]);
        }

        return redirect()->route('admin.v2.debit.debitLogs')->with('success', 'Thiết lập công nợ thành công.');
    }

    public function import_save(Request $request)
    {
        set_time_limit(0);
        $file = $request->file('file_import');

        if (!$file) return redirect()->route('admin.electricMeter.import')->with('warning', 'Chưa có file upload!');

        $path = $file->getRealPath();

        $excel_data = Excel::load($path)->get();

        storage_path('upload', $file->getClientOriginalName());

        $buildingId = $this->building_active_id;

        $cycleName = $request->cycle_year . $request->cycle_month;
        $action = Helper::getAction();
        if ($action) {
            $check_lock_cycle = BdcLockCyclenameRepository::checkLock($this->building_active_id, $cycleName, $action);
            if ($check_lock_cycle) {
                return redirect()->route('admin.v2.debit.debitLogs')->with('warning', "Kỳ $cycleName đã được khóa.");
            }
        }
        $data_list_error = array();

        if ($excel_data->count()) {

            foreach ($excel_data as $content) {
                if (empty($content->ma_can_ho) ||
                    empty($content->chi_so_cuoi) ||
                    empty($content->ngay_chot_so)
                ) {
                    $new_content = $content->toArray();
                    $new_content['message'] = 'hãy kiểm tra lại các trường yêu cầu bắt buộc';
                    array_push($data_list_error, $new_content);
                    continue;
                }

                // check is number

                if (preg_match('/\d/', $content->chi_so_cuoi) !== 1) { // không phải là kiểu số hoặc nhỏ hơn 0
                    $new_content = $content->toArray();
                    $new_content['message'] = $content->chi_so_cuoi . '| không phải là kiểu số nguyên';
                    array_push($data_list_error, $new_content);
                    continue;
                }

                // check is number

                if (preg_match('/\d/', $content->loai_dich_vu) !== 1) { // không phải là kiểu số hoặc nhỏ hơn 0
                    $new_content = $content->toArray();
                    $new_content['message'] = $content->loai_dich_vu . '| không phải là kiểu số nguyên';
                    array_push($data_list_error, $new_content);
                    continue;
                }

                if (!strtotime($content->ngay_chot_so)) {
                    $new_content = $content->toArray();
                    $new_content['message'] = $content->ngay_chot_so . '| ngày chốt số không đúng định dạng dd/mm/yyyy';
                    array_push($data_list_error, $new_content);
                    continue;
                }

                // tìm căn hộ

                $apartment = Apartments::where(['building_id' => $buildingId, 'code' => $content->ma_can_ho])->first();


                if (!$apartment) {
                    $new_content = $content->toArray();
                    $new_content['message'] = $content->ma_can_ho . '| căn hộ không có trên hệ thống';
                    array_push($data_list_error, $new_content);
                    continue;
                }

                try {
                    DB::beginTransaction();

                    $get_before_number = ElectricMeter::where(['bdc_building_id' => $buildingId, 'bdc_apartment_id' => $apartment->id, 'type' => $content->loai_dich_vu])
                        ->whereDate('date_update', '>', Carbon::parse($content->ngay_chot_so)->format('Y-m-d'))->orderBy('date_update', 'asc')
                        ->first();
                    if ($get_before_number) {
                        $get_before_number->before_number = $content->chi_so_cuoi;
                        $get_before_number->save();
                    }

                    $get_after_number = ElectricMeter::where(['bdc_building_id' => $buildingId, 'bdc_apartment_id' => $apartment->id, 'type' => $content->loai_dich_vu])
                        ->whereDate('date_update', '<', Carbon::parse($content->ngay_chot_so)->format('Y-m-d'))->orderBy('date_update', 'desc')
                        ->first();

                    if ($get_after_number && $get_after_number->month_create != $cycleName && ($get_after_number->after_number > $content->chi_so_cuoi)) {
                        $new_content = $content->toArray();
                        $new_content['message'] = $content->ma_can_ho . 'chỉ số đầu' . $get_after_number->after_number . '>' . $content->chi_so_cuoi;
                        array_push($data_list_error, $new_content);
                        DB::commit();
                        continue;
                    }

                    $electric_water = ElectricMeter::where(['bdc_building_id' => $buildingId, 'bdc_apartment_id' => $apartment->id, 'month_create' => $cycleName, 'type' => $content->loai_dich_vu])->first();

                    $before_number = $get_after_number && $get_after_number->month_create != $cycleName ? $get_after_number->after_number : ($electric_water ? $electric_water->before_number : 0);

                    $result = ElectricMeter::updateOrCreate(['bdc_building_id' => $buildingId, 'bdc_apartment_id' => $apartment->id, 'month_create' => $cycleName, 'type' => $content->loai_dich_vu], [
                        'after_number' => $content->chi_so_cuoi,
                        'before_number' => $before_number ?? 0,
                        'type_action' => 0,
                        'status' => 0,
                        'date_update' => Carbon::parse($content->ngay_chot_so),
                        'type' => $content->loai_dich_vu,
                        'created_by' => auth()->user()->id,
                    ]);
                    if ($result) {
                        $new_content = $content->toArray();
                        $new_content['message'] = 'thêm mới thành công';
                        array_push($data_list_error, $new_content);
                    }

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    $new_content = $content->toArray();
                    $new_content['message'] = $e->getMessage();
                    array_push($data_list_error, $new_content);
                    continue;
                }
            }
        }

        if ($data_list_error) {
            $result = Excel::create('Kết quả Import', function ($excel) use ($data_list_error) {

                $excel->setTitle('Kết quả Import');
                $excel->sheet('Kết quả Import', function ($sheet) use ($data_list_error) {
                    $row = 1;
                    $sheet->row($row, [
                        'mã căn hộ(*)',
                        'chỉ số cuối(*)',
                        'ngày chôt số(*)',
                        'loại dịch vụ(*)',
                        'Message'
                    ]);

                    foreach ($data_list_error as $key => $value) {
                        $row++;
                        $sheet->row($row, [
                            isset($value['ma_can_ho']) ? $value['ma_can_ho'] : '',
                            isset($value['chi_so_cuoi']) ? $value['chi_so_cuoi'] : '',
                            isset($value['ngay_chot_so']) ? $value['ngay_chot_so'] : '',
                            isset($value['loai_dich_vu']) ? $value['loai_dich_vu'] : '',
                            $value['message'],
                        ]);
                        if (isset($value['message']) && $value['message'] == 'thêm mới thành công') {
                            $sheet->cells('E' . $row, function ($cells) {
                                $cells->setBackground('#23fa43');
                            });
                        }
                        if (isset($value['message']) && $value['message'] != 'thêm mới thành công') {
                            $sheet->cells('E' . $row, function ($cells) {
                                $cells->setBackground('#FC2F03');
                            });
                        }
                    }
                });
            })->store('xlsx', storage_path('exports/'));
            ob_end_clean();
            $file = storage_path('exports/' . $result->filename . '.' . $result->ext);
            return response()->download($file)->deleteFileAfterSend(true);

        }
    }

    public function getAll(Request $request)
    {
        $buildingId = $this->building_active_id;
        return Apartments::leftJoin('bdc_electric_meter', function ($join) {
            $join->on('bdc_apartments.id', '=', 'bdc_electric_meter.bdc_apartment_id');
        })
            ->leftJoin('pub_users', function ($join) {
                $join->on('pub_users.id', '=', 'bdc_electric_meter.user_id');
            })
            ->where(function ($query) use ($request, $buildingId) {
                if (isset($buildingId) && $buildingId != null) {
                    $query->where('bdc_apartments.building_id', $buildingId);
                }
                if (isset($request->cycle_name) && $request->cycle_name != null) {
                    $query->where('bdc_electric_meter.cycle_name', $request->cycle_name);
                }
                if (isset($request->type) && $request->type != null) {
                    $query->where('bdc_electric_meter.type', $request->type);
                }
                if (isset($request->bdc_apartment_id) && $request->bdc_apartment_id != null) {
                    $query->where('bdc_electric_meter.bdc_apartment_id', $request->bdc_apartment_id);
                }
            })
            ->whereNull('bdc_electric_meter.deleted_at')
            ->select('bdc_apartments.name', 'bdc_apartments.code', 'pub_users.email', 'bdc_electric_meter.*')->orderBy('created_at', 'desc');
    }

    public function action(Request $request)
    {
        $method = $request->input('method', '');
        if ($method == 'per_page') {
            $this->per_page($request);
            return back();
        }
        if ($method == 'delete') {
            $number = ElectricMeter::whereIn('id', $request->ids)->where('status', 0)->delete();
            return back()->with('success', 'Đã xóa ' . count($request->ids) . ' bản ghi!');
        }
        if ($method == 'download_image' && $request->ids) {
            $directory = storage_path() . '/upload';
            if (!is_dir($directory)) {
                mkdir(storage_path() . '/upload');
            }
            $zipname = storage_path() . '/upload/file_image_electric_water.zip';
            $zip = new ZipArchive;
            $zip->open($zipname, ZipArchive::CREATE);
            $path_list = [];
            $electric_water = ElectricMeter::whereIn('id', $request->ids)->get();
            //dd($electric_water);
            if ($electric_water) {
                foreach ($electric_water as $key => $value) {
                    if ($value->images) {
                        $apartment = Apartments::get_detail_apartment_by_apartment_id($value->bdc_apartment_id);
                        if ($value->type == 0) {
                            $img = 'electric_' . $key . '_' . $value->month_create . '_' . $apartment->name;
                        }
                        if ($value->type == 1) {
                            $img = 'water_' . $key . '_' . $value->month_create . '_' . $apartment->name;
                        }
                        if ($value->type == 2) {
                            $img = 'water_hot_' . $key . '_' . $value->month_create . '_' . $apartment->name;
                        }

                        $path = storage_path('upload/' . $img . '.png');
                        $path_list[] = storage_path('upload/' . $img . '.png');
                        // Function to write image into file
                        file_put_contents($path, file_get_contents($value->images));
                        $zip->addFile(storage_path() . '/upload/' . $img . '.png', $img . '.png');
                    }

                }
            }
            //dd($path_list);
            if (count($path_list) > 0) {
                $zip->close();
                foreach ($path_list as $key => $value) {
                    unlink($value);
                }
                $file = storage_path('upload/file_image_electric_water.zip');
                return response()->download($file)->deleteFileAfterSend(true);

            }

        }

    }

    public function delete(Request $request)
    {
        return redirect()->back();
    }
    //DUongremove old function 
     /*public function export(Request $request)
    {
        $input = $request->all();

        $input['type'] = $request->input('type', 0); // 0:điện, 1;nước

        $data['filter'] = $input;

        $cycle_names = $this->_electricMeterRespository->getCycleName($this->building_active_id);

        $data['cycle_names'] = $cycle_names;

        $data['data_search']['floor'] = $request->floor;

        $data['floors'] = $this->apartmentRepo->getApartmentFloor();

        $data['chose_cycle_name'] = $request->cycle_name ? $request->cycle_name : ($cycle_names ? null : null); //$cycle_names[0]
        //$data['chose_cycle_name'] = $request->cycle_name ? $request->cycle_name : ($cycle_names ? $cycle_names[0] : null);

        $input['cycle_name'] = $data['chose_cycle_name']; // tháng chốt số

        $electric_meters = $this->_electricMeterRespository->getListAll($input, $this->building_active_id)->get();

        $result = Excel::create('Danh sách ghi số điện nước', function ($excel) use ($electric_meters) {
            $excel->setTitle('Danh sách căn hộ');
            $excel->sheet('Danh sách', function ($sheet) use ($electric_meters) {
                $row = 1;
                $sheet->row($row, [
                    'STT',
                    'Tên căn hộ',
                    'Mã căn hộ',
                    'Tòa',
                    'Dịch vụ',
                    'Chỉ số đầu',
                    'Chỉ số cuối',
                    'Tiêu thụ',
                    'Tháng chốt',
                    'Ngày chốt',
                    'Người chốt',
                    'Ảnh'
                ]);

                foreach ($electric_meters as $key => $value) {
                    if ($value->type_action == 1) {
                        continue;
                    }
                    $row++;
                    $apartment = Apartments::get_detail_apartment_by_apartment_id($value->bdc_apartment_id);
                    $service_apartment = $this->_serviceApartment->findApartmentServicePriceByApartment($value->bdc_apartment_id, $value->type);
                    $user = Users::find($value->created_by);
                    $buildingPlace = BuildingPlace::get_detail_bulding_place_by_bulding_place_id($apartment->building_place_id);

                    $sheet->row($row, [
                        ($key + 1),
                        $apartment->name,
                        $apartment->code,
                        $buildingPlace->name,
                        @$service_apartment->name,
                        $value->before_number,
                        $value->after_number,
                        $value->after_number - $value->before_number,
                        $value->month_create,
                        $value->date_update,
                        $user ? @$user->email : '',
                        $value->images
                    ]);
                }
            });
        })->store('xlsx', storage_path('exports/'));
        $file = storage_path('exports/' . $result->filename . '.' . $result->ext);
        return response()->download($file)->deleteFileAfterSend(true);

    }*/
    public function export(Request $request)
{
    $input = $request->all();

    $input['type'] = $request->input('type', 0); // 0:điện, 1;nước

    $data['filter'] = $input;

    $cycle_names = $this->_electricMeterRespository->getCycleName($this->building_active_id);

    $data['cycle_names'] = $cycle_names;

    $data['data_search']['floor'] = $request->floor;

    $data['floors'] = $this->apartmentRepo->getApartmentFloor();

    $data['chose_cycle_name'] = $request->cycle_name ? $request->cycle_name : ($cycle_names ? null : null);

    $input['cycle_name'] = $data['chose_cycle_name'];

    $electric_meters = $this->_electricMeterRespository->getListAll($input, $this->building_active_id)->get();

    // Khởi tạo PHPExcel
    $excel = new PHPExcel();
    $excel->getProperties()
        ->setCreator("Your Name")
        ->setLastModifiedBy("Your Name")
        ->setTitle("Danh sách ghi số điện nước")
        ->setSubject("Danh sách ghi số điện nước")
        ->setDescription("Danh sách ghi số điện nước")
        ->setKeywords("excel phpoffice phpexcel")
        ->setCategory("Export");

    // Tạo trang tính mới
    $excel->setActiveSheetIndex(0);
    $sheet = $excel->getActiveSheet();

    // Ghi tiêu đề vào tệp Excel
    $row = 1;
    $sheet->setCellValue('A'.$row, 'STT');
    $sheet->setCellValue('B'.$row, 'Tên căn hộ');
    $sheet->setCellValue('C'.$row, 'Mã căn hộ');
    $sheet->setCellValue('D'.$row, 'Tòa');
    $sheet->setCellValue('E'.$row, 'Dịch vụ');
    $sheet->setCellValue('F'.$row, 'Chỉ số đầu');
    $sheet->setCellValue('G'.$row, 'Chỉ số cuối');
    $sheet->setCellValue('H'.$row, 'Tiêu thụ');
    $sheet->setCellValue('I'.$row, 'Tháng chốt');
    $sheet->setCellValue('J'.$row, 'Ngày chốt');
    $sheet->setCellValue('K'.$row, 'Người chốt');
    $sheet->setCellValue('L'.$row, 'Ảnh');

    // Ghi dữ liệu vào tệp Excel
    foreach ($electric_meters as $key => $value) {
        if ($value->type_action == 1) {
            continue;
        }
        $row++;
        $apartment = Apartments::get_detail_apartment_by_apartment_id($value->bdc_apartment_id);
        $service_apartment = $this->_serviceApartment->findApartmentServicePriceByApartment($value->bdc_apartment_id, $value->type);
        $user = Users::find($value->created_by);
        $buildingPlace = BuildingPlace::get_detail_bulding_place_by_bulding_place_id($apartment->building_place_id);

        $sheet->setCellValue('A'.$row, $key + 1);
        $sheet->setCellValue('B'.$row, $apartment->name);
        $sheet->setCellValue('C'.$row, $apartment->code);
        $sheet->setCellValue('D'.$row, $buildingPlace->name);
        $sheet->setCellValue('E'.$row, @$service_apartment->name);
        $sheet->setCellValue('F'.$row, $value->before_number);
        $sheet->setCellValue('G'.$row, $value->after_number);
        $sheet->setCellValue('H'.$row, $value->after_number - $value->before_number);
        $sheet->setCellValue('I'.$row, $value->month_create);
        $sheet->setCellValue('J'.$row, $value->date_update);
        $sheet->setCellValue('K'.$row, $user ? @$user->email : '');
        $sheet->setCellValue('L'.$row, $value->images);
    }

    // Lưu và xuất tệp Excel
    $excelWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
    ob_end_clean();
    $file = storage_path('exports/Danh sách ghi số điện nước.xlsx');
    $excelWriter->save($file);

    return response()->download($file)->deleteFileAfterSend(true);
}
}