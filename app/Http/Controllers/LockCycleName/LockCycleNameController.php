<?php

namespace App\Http\Controllers\LockCycleName;

use App\Helpers\dBug;
use App\Http\Controllers\BuildingController;
use App\Models\BdcV2DebitDetail\DebitDetail;
use App\Models\LockCycleName\LockCycleName;
use App\Util\Debug\Log;
use Illuminate\Http\Request;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\BdcDebitLogs\DebitLogsRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class LockCycleNameController extends BuildingController
{
    protected $model;
    public $apartmentRepo;

    public function __construct(Request $request, ApartmentsRespository $apartmentRepo, DebitLogsRepository $model)
    {
        $this->model = $model;
        parent::__construct($request);

        $this->apartmentRepo = $apartmentRepo;
    }

    public function index(Request $request)
    {
        $data['meta_title'] = 'Quản lý khóa kỳ';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['filter'] = $request->all();
        $cycle_names = LockCycleName::where(['bdc_building_id' => $this->building_active_id])->where(function ($query) use ($request) {
            if (isset($request->cycle_name) && $request->cycle_name != null) {
                $query->where('cycle_name', $request->cycle_name);
            }
            if ($request->status != null) {
                $query->where('status', (int)$request->status);
            }
        })->orderBy('cycle_name', 'desc')->orderBy('updated_at', 'desc')->paginate($data['per_page']);
        $data['cycle_names'] = $cycle_names;
        $data['cycle'] = LockCycleName::getCycleName($this->building_active_id);
        return view('lock-cycle-name.index', $data);
    }

    public function save(Request $request)
    {
        $cycleName = $request->cycle_year . $request->cycle_month;
        $check_duplicate = LockCycleName::where(['bdc_building_id' => $this->building_active_id, 'cycle_name' => $cycleName])->first();
        if ($check_duplicate) {
            return redirect()->back()->with('warning', 'Khóa kỳ đã tồn tại');
        }
        $input = [
            'bdc_building_id' => $this->building_active_id,
            'cycle_name' => $cycleName,
            'created_by' => Auth::user()->id,
            'status' => 1,
        ];
        LockCycleName::create($input);
        return redirect()->back();
    }

    public function generate_cycle_curent(Request $request)
    {
        $log_data = [
            'created_by' => Auth::user()->id,
            "status" => $request->status,
            "building_id" => $this->building_active_id
        ];
        Log::info("lock_cycle2", $log_data);
        $get_cycle_curent = Carbon::now()->subMonth(1)->format('Ym');
        $buildings = [13, 17, 20, 28, 30, 37, 60, 61, 62, 63, 64, 65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 78, 79, 80, 81, 82, 83, 84, 85, 86, 87, 88, 89, 90, 91, 92, 93, 94, 95, 96, 97, 98, 99, 107, 108, 109];
        foreach ($buildings as $key_1 => $value_1) {
            $get_debit = DebitDetail::where('bdc_building_id', $value_1)->where('cycle_name', '>', 202301)->distinct('cycle_name')->pluck('cycle_name');
            if ($get_debit) {
                foreach ($get_debit as $key => $value) {
                    $check_duplicate = LockCycleName::where(['bdc_building_id' => $value_1, 'cycle_name' => $value])->first();
                    if (!$check_duplicate) {
                        if ($value <= $get_cycle_curent) {
                            $input = [
                                'bdc_building_id' => $value_1,
                                'cycle_name' => $value,
                                'status' => 1,
                            ];
                            LockCycleName::create($input);
                        } else {
                            $input = [
                                'bdc_building_id' => $value_1,
                                'cycle_name' => $value,
                                'status' => 0,
                            ];
                            LockCycleName::create($input);
                        }
                    } else {
                        $check_duplicate->status = 1;
                        $check_duplicate->created_by = null;
                        $check_duplicate->save();
                    }

                }
            }
        }
    }

    public function change_status(Request $request)
    {
        $log_data = [
            'created_by' => Auth::user()->id,
            "status" => $request->status,
            "building_id" => $this->building_active_id
        ];
        Log::info("lock_cycle", $log_data);
        $lock_cycle = LockCycleName::find($request->id);

        if ($request->status == 0) {
            $schedule_active = date('Y-m-d H:i', strtotime($request->schedule_active));
            $date_time_now = Carbon::now();
            $interval = $date_time_now->diffInHours($schedule_active);
            if ($interval > 48) { // giới hạn khóa kỳ không được quá 48 giờ
                $dataResponse = [
                    'success' => false,
                    'message' => "Thời gian tắt khóa kỳ không được vượt quá 48 giờ."
                ];
                return response()->json($dataResponse);
            }
        }
        if (!$lock_cycle) {
            $dataResponse = [
                'success' => false,
                'message' => 'không tìm thấy dữ liệu!'
            ];
            return response()->json($dataResponse);
        }
        $check_cycle_after = LockCycleName::where('bdc_building_id', $lock_cycle->bdc_building_id)->where('cycle_name', '>', $lock_cycle->cycle_name)->where('status', 1)->first();
        if ($check_cycle_after) {
            $dataResponse = [
                'success' => false,
                'message' => "Kỳ $check_cycle_after->cycle_name đã được khóa. Vui lòng Mở hết các kỳ liền trước để có thể mở kỳ bạn đang muốn thao tác."
            ];
            return response()->json($dataResponse);
        }

        $data = [
            'id' => $request->id,
            'status' => $request->status,
            'schedule_active' => $request->status == 1 ? null : ($request->check_status == 1 ? $schedule_active : null)
        ];
        if ($lock_cycle) {
            $lock_cycle->update($data);
        }
        $dataResponse = [
            'success' => true,
            'message' => 'Cập nhật trạng thái thành công!'
        ];
        return response()->json($dataResponse);
    }

    public function action(Request $request)
    {
        $method = $request->input('method', '');
        if ($method == 'per_page') {
            $this->per_page($request);
            return back();
        }
    }
}
