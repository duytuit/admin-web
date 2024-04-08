<?php

namespace App\Http\Controllers\AccountingAccounts;

use App\Commons\Helper;
use App\Http\Controllers\BuildingController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BdcAccountingAccounts\AccountingAccounts;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Maatwebsite\Excel\Facades\Excel;

class AccountingAccountController extends BuildingController
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
        $check_tai_khoan_default = AccountingAccounts::where(['bdc_building_id' => $this->building_active_id, 'default' => 1])->first();
        if(!$check_tai_khoan_default){
                $list_tai_khoan = Helper::tai_khoan_ke_toan_toa_nha;
                foreach ($list_tai_khoan as $key => $value) {
                    AccountingAccounts::create([
                        'code' => $key,
                        'name' => $value,
                        'bdc_building_id' => $this->building_active_id,
                        'user_id' => Auth::user()->id,
                        'default' => 1
                    ]);
                }
        }
        $data['accounting_accounts'] = AccountingAccounts::where('bdc_building_id',$this->building_active_id)->where(function($query) use ($request) {
               if(isset($request->keyword) && $request->keyword !=null){
                    $query->where('code','like', '%'.$request->keyword.'%')
                        ->orWhere('name','like',  '%'.$request->keyword.'%');
                }
        })->orderBy('code')->paginate($data['per_page']);

        $data['meta_title'] = "Quản lý thông tin tài khoản kế toán";
        
        return view('accounting-account.index', $data);
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
            $accounting_accounts = AccountingAccounts::findOrFail($id);
        } else {
            $accounting_accounts = new AccountingAccounts();
        }

        $data['id']       = $id;
        $data['now']      = Carbon::now();
        $data['accounting_accounts'] = $accounting_accounts;

        $data['meta_title'] = "Quản lý tài khoản ngân hàng";

        return view('accounting-account.edit', $data);
    }

    /**
     * Lưu bản ghi
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function save(Request $request, $id = 0)
    {
        $input           = $request->all();
        $input['id']     = $id;
        $accounting_accounts = AccountingAccounts::findOrNew($id);
        $accounting_accounts->fill($input);
        $accounting_accounts->user_id = Auth::user()->id;
        $accounting_accounts->bdc_building_id = $this->building_active_id;
        
        $accounting_accounts->save();

        $message = [
            'error'  => 0,
            'status' => 'success',
            'msg'    => 'Đã cập nhập thông tin',
        ];

        return redirect()->route('admin.accounting.account.index')->with('message', $message);
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

        $categories = AccountingAccounts::searchBy([
            'select' => $select,
            'where'  => $where,
        ]);

        return response()->json($categories);
    }
    public function action(Request $request)
    {
        if(!in_array('admin.accounting.account.action',$this->access_router)){
            return $this->sendError_Api('liên hệ với admin để cấp quyền.');
        }
        $method = $request->input('method', '');
        if ($method == 'delete') {
            $del = $this->deleteAt($request);
            return back()->with('success', $del['msg']);
        }

        $method = $request->input('method_custom', '');

        if($method == 'tai_khoan_no_pt') {

            $status =  $this->status($request);

            return back()->with('success', $status['msg']);

        }elseif ($method == 'tai_khoan_co_pt') {

            $status =  $this->status($request);

            return back()->with('success', $status['msg']);

        }elseif ($method == 'tai_khoan_no_bao_co') {

            $status =  $this->status($request);

            return back()->with('success', $status['msg']);

        }elseif ($method == 'tai_khoan_co_bao_co') {

            $status =  $this->status($request);

            return back()->with('success', $status['msg']);

        }elseif ($method == 'tai_khoan_no_thue') {

            $status =  $this->status($request);

            return back()->with('success', $status['msg']);

        }elseif ($method == 'tai_khoan_co_thue') {

            $status =  $this->status($request);

            return back()->with('success', $status['msg']);

        }elseif ($method == 'tai_khoan_no_truoc_vat') {

            $status =  $this->status($request);

            return back()->with('success', $status['msg']);

        }elseif ($method == 'tai_khoan_co_truoc_vat') {

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
            Cache::store('redis')->forget(env('REDIS_PREFIX') . 'get_detail_accountingaccountById_' . $id);
            $list[] = (int) $id;
        }

        $number = AccountingAccounts::destroy($list);

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
        // $list_tai_khoan = AccountingAccounts::fillable;
        // $list_tai_khoan = array_diff($list_tai_khoan, [$request->method_custom]);
        $list_change = AccountingAccounts::whereIn('id', (array) $list)->get();
        foreach ($list_change as $key => $value) {
            $value->update([$request->method_custom => (int) $status,'default' => $value->default]);
        }
        $list_no_change = AccountingAccounts::whereNotIn('id', (array) $list)->get();
        foreach ($list_no_change as $key => $value) {
            $value->update([$request->method_custom => (int) !$status,'default' => $value->default]);
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
        $payment_info = AccountingAccounts::where('bdc_building_id',$this->building_active_id)->where(function($query) use ($request) {
            if(isset($request->keyword) && $request->keyword !=null){
                $query->where('code','like', '%'.$request->keyword.'%')
                      ->orWhere('name','like',  '%'.$request->keyword.'%');
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
                    'TK Nợ PT',
                    'TK Có PT',
                    'TK Nợ Báo có',
                    'TK Có Báo có',
                    'TK Nợ Thuế',
                    'TK Có Thuế',
                    'TK Nợ trước VAT',
                    'TK Có trước VAT',
                    'Ngày cập nhật',
                    'Người tạo',
                ]);
                foreach ($payment_info as $key => $value) {
                    $row++;
                    $sheet->row($row, [
                        ($key + 1),
                        $value->code,
                        $value->name,
                        $value->tai_khoan_no_pt == 1 ? 'Có' :'Không',
                        $value->tai_khoan_co_pt == 1 ? 'Có' :'Không',
                        $value->tai_khoan_no_bao_co == 1 ? 'Có' :'Không',
                        $value->tai_khoan_co_bao_co == 1 ? 'Có' :'Không',
                        $value->tai_khoan_co_thue == 1 ? 'Có' :'Không',
                        $value->tai_khoan_no_thue == 1 ? 'Có' :'Không',
                        $value->tai_khoan_co_truoc_vat == 1 ? 'Có' :'Không',
                        $value->tai_khoan_no_truoc_vat == 1 ? 'Có' :'Không',
                        $value->updated_at,
                        @$value->user->email,
                    ]);
                }
            });
        })->store('xlsx', storage_path('exports/'));
        $file     = storage_path('exports/' . $result->filename . '.' . $result->ext);
        return response()->download($file)->deleteFileAfterSend(true);
             
    }
}
