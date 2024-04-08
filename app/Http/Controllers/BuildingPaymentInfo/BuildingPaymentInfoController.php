<?php

namespace App\Http\Controllers\BuildingPaymentInfo;

use App\Http\Controllers\BuildingController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PaymentInfo\PaymentInfo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\PaymentInfo\CreatePaymentRequest;
use Illuminate\Support\Facades\Cache;

class BuildingPaymentInfoController extends BuildingController
{
     /**
     * Constructor.
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /**
     * Danh sách bản ghi
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {

        // Phân trang
        $data['per_page'] = Cookie::get('per_page', 20);
        $data['keyword'] = $request->input('keyword', '');

       
        $data['payment_info'] = PaymentInfo::where('bdc_building_id',$this->building_active_id)->where(function($query) use ($request) {
               if(isset($request->keyword) && $request->keyword !=null){
                    $query->where('bank_account','like', '%'.$request->keyword.'%')
                          ->orWhere('bank_name','like',  '%'.$request->keyword.'%')
                          ->orWhere('holder_name','like',  '%'.$request->keyword.'%')
                          ->orWhere('branch','like',  '%'.$request->keyword.'%');
                }
        })->orderBy('updated_at','desc')->paginate($data['per_page']);

        $data['meta_title'] = "Quản thông tin thanh toán";
        
        return view('building.payments.index', $data);
    }

    /**
     * Sửa bản ghi
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function edit(Request $request, $id = 0)
    {

        if ($id > 0) {
            $payment_info = PaymentInfo::findOrFail($id);
        } else {
            $payment_info = new PaymentInfo();
        }

        $data['id']       = $id;
        $data['now']      = Carbon::now();
        $data['payment_info'] = $payment_info;

        $data['meta_title'] = "Quản lý tài khoản ngân hàng";

        return view('building.payments.edit', $data);
    }

    /**
     * Lưu bản ghi
     *
     * @param  CreatePaymentRequest  $request
     * @param  int  $id
     * @return Response
     */
    public function save(CreatePaymentRequest $request, $id = 0)
    {
        $input           = $request->all();
        $input['id']     = $id;
        $input['status'] = $request->input('status', 0);

        $payment_info = PaymentInfo::findOrNew($id);
        $payment_info->fill($input);
        $payment_info->user_id = Auth::user()->id;
        $payment_info->bdc_building_id = $this->building_active_id;
        Cache::store('redis')->forget( env('REDIS_PREFIX') . 'get_detail_payment_infoById_'.$this->building_active_id);
        $payment_info->save();

        $message = [
            'error'  => 0,
            'status' => 'success',
            'msg'    => 'Đã cập nhập thông tin',
        ];

        return redirect()->route('admin.building.info.index')->with('message', $message);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request)
    {
        $type    = $request->input('type', 'article');
        $keyword = $request->input('keyword', '');

        $where = [];

        if ($type) {
            $where[] = ['type', '=', $type];
        }

        if ($keyword) {
            $where[] = ['title', 'like', '%' . $keyword . '%'];
        }

        $select = ['id', 'title'];

        $where[] = ['bdc_building_id', '=', $this->building_active_id];

        $categories = PaymentInfo::searchBy([
            'select' => $select,
            'where'  => $where,
        ]);

        return response()->json($categories);
    }
    public function action(Request $request)
    {
        if(!in_array('admin.building.info.action',$this->access_router)){
            return $this->sendError_Api('liên hệ với admin để cấp quyền.');
        }
       
        $method = $request->input('method', '');
        if ($method == 'delete') {
            $del = $this->deleteAt($request);
            return back()->with('success', $del['msg']);
        }

        $method = $request->input('method_custom', '');
        if ($method == 'web_status') {

            $status =  $this->status($request);

            return back()->with('success', $status['msg']);

        } elseif ($method == 'app_status') {

            $status =  $this->status($request);

            return back()->with('success', $status['msg']);

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
            $list[] = (int) $id;
           
        }

        $number = PaymentInfo::destroy($list);

        $message = [
            'error'  => 0,
            'status' => 'success',
            'msg'    => "Đã xóa $number bản ghi!",
        ];

        return $message;
    }
    public function status($request)
    {
        $ids    = $request->input('ids', []);
        $status = $request->input('status', 1);

        // convert to array
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $list = [];
        foreach ($ids as $id) {
            $list[] = (int) $id;
        }

        PaymentInfo::whereIn('id', (array) $list)->update([$request->method_custom => (int) $status]);
        if((int)$status == 1){ // chỉ có một ngân hàng được active
            PaymentInfo::whereNotIn('id', (array) $list)->update([$request->method_custom => (int) !$status]);
        }
        $message = [
            'error'  => 0,
            'status' => 'success',
            'msg'    => 'Đã cập nhật trạng thái!',
        ];

        return $message;
    }
    public function export(Request $request)
    {
        $payment_info = PaymentInfo::where('bdc_building_id',$this->building_active_id)->where(function($query) use ($request) {
            if(isset($request->keyword) && $request->keyword !=null){
                $query->where('bank_account','like', '%'.$request->keyword.'%')
                      ->orWhere('bank_name','like',  '%'.$request->keyword.'%')
                      ->orWhere('holder_name','like',  '%'.$request->keyword.'%')
                      ->orWhere('branch','like',  '%'.$request->keyword.'%');
            }
     })->orderBy('updated_at','desc')->get();

        $result = Excel::create('danh sách tài khoản', function ($excel) use ($payment_info) {
            $excel->setTitle('danh sách');
            $excel->sheet('danh sách', function ($sheet) use ($payment_info) {
                $row = 1;
                $sheet->row($row, [
                    'STT',
                    'Mã tài khoản',
                    'Số tài khoản',
                    'Ngân hàng',
                    'Chủ tài khoản',
                    'Chi nhánh',
                    'Web',
                    'App',
                    'Ngày cập nhật',
                    'Người tạo',
                ]);
                foreach ($payment_info as $key => $value) {
                    $row++;
                    $sheet->row($row, [
                        ($key + 1),
                        $value->code,
                        $value->bank_account,
                        $value->bank_name,
                        $value->holder_name,
                        $value->branch,
                        $value->web_status == 1 ? 'Active' : 'Inactive',
                        $value->app_status == 1 ? 'Active' : 'Inactive',
                        $value->updated_at,
                        @$value->user->email,
                    ]);
                }
            });
        })->store('xlsx',storage_path('exports/'));
$file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
return response()->download($file)->deleteFileAfterSend(true);
             
    }
}
