<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Controller;
use App\Models\BoCustomer;
use App\Models\BoUser;
use App\Models\Campaign;
use App\Models\CampaignAssign;
use App\Models\CustomerDiary;
use App\Models\Filter;
use App\Models\Setting;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Validator;
use willvincent\Rateable\Rateable;

class CampaignAssignController extends Controller
{
    use Rateable;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->model = new CampaignAssign();
        // $user = \Auth::user();
    }

    /**
     * Undocumented function
     * Mô tả các lỗi validate
     */
    public function messages()
    {
        return [
            'required' => ':attribute không được để trống',
            'unique'   => ':attribute đã tồn tại',
        ];
    }

    /**
     * Undocumented function
     * Mô tả các field validate
     */
    public function attributes()
    {
        return [
            'cb_name'        => 'Tên khách hàng',
            'cb_phone'       => 'Số điện thoại',
            'cd_customer_id' => 'Khách hàng',
            'cd_rating'      => 'Điểm số',
            'project_id'     => 'Dự án',
        ];
    }

    public function index(Request $request)
    {
        $data['meta_title'] = "Khách hàng phân bổ";
        $this->authorize('index', app(CampaignAssign::class));

        $data['per_page'] = Cookie::get('per_page', 20);
        $data['bo_users'] = new BoUser();

        //Tìm kiếm
        // $where = [
        //     ['staff_id', '=', \Auth::user()->ub_id],
        // ];

        $where = [];
        if (!empty($request->customer_name)) {
            $where[] = ['customer_name', 'Like', "%{$request->customer_name}%"];
        }

        if (!empty($request->customer_email)) {
            $where[] = ['customer_email', 'LIKE', "%{$request->customer_email}%"];
        }

        if ($request->customer_phone !== null) {
            $where[] = ['customer_phone', 'LIKE', "%{$request->customer_phone}%"];
        }

        if (!empty($request->campaign_id)) {
            $where[] = ['campaign_id', '=', $request->campaign_id];
        }

        if (!empty($request->staff_name)) {
            $where[] = ['staff_account', 'Like', "%$request->staff_name%"];
        }
        if (isset($request->feedback)) {
            $where[] = ['feedback', '=', $request->feedback];
        }
        $where[] = ['role', '=', 1];
        $user = Auth::user();
        if ($user->username == 'ADMIN@DXMB') {
            $hasRole = true;
        } else {
            $hasRole = false;
        }
//        dd($user);
//        if (!$hasRole) {
//            $where[] = ['staff_id', '=', $user->uid];
//        }

        if ($where) {

            $assigns = CampaignAssign::searchBy(['where' => $where, 'per_page' => $data['per_page']]);
        } else {
            $assigns = CampaignAssign::searchBy(['per_page' => $data['per_page']]);
        }

        $data['assigns'] = $assigns;
        //End tìm kiếm
        $data_search = [
            'customer_name'  => '',
            'customer_email' => '',
            'customer_phone' => '',
            'staff_name'     => '',
            'campaign'       => [],
        ];
        if (!empty($request->customer_name)) {
            $data_search['customer_name'] = $request->customer_name;
        }

        if (!empty($request->customer_email)) {
            $data_search['customer_email'] = $request->customer_email;
        }

        if ($request->customer_phone !== null) {
            $data_search['customer_phone'] = $request->customer_phone;
        }

        if (!empty($request->staff_name)) {
            $data_search['staff_name'] = $request->staff_name;
        }

        if (!empty($request->campaign_id)) {
            $data_search['campaign']['id']   = $request->cb_staff_id;
            $data_search['campaign']['name'] = Campaign::find($request->campaign_id)->title;
        }
        if (isset($request->feedback)) {
            $data_search['feedback'] = $request->feedback;
        }

        $data['data_search'] = $data_search;

        return view('backend.campaign-assigns.index', $data);
    }

    public function index_ctv(Request $request)
    {
        $data['meta_title'] = "Khách Hàng Phân bổ CTV";
        $this->authorize('index_ctv_kh', app(CampaignAssign::class));

        $data['per_page'] = Cookie::get('per_page', 20);

        //Tìm kiếm
        // $where = [
        //     ['staff_id', '=', \Auth::user()->ub_id],
        // ];

        $where = [];
        if (!empty($request->customer_name)) {
            $where[] = ['customer_name', 'Like', "%{$request->customer_name}%"];
        }

        if (!empty($request->customer_email)) {
            $where[] = ['customer_email', 'LIKE', "%{$request->customer_email}%"];
        }

        if ($request->customer_phone !== null) {
            $where[] = ['customer_phone', 'LIKE', "%{$request->customer_phone}%"];
        }

        if (!empty($request->campaign_id)) {
            $where[] = ['campaign_id', '=', $request->campaign_id];
        }

        if (!empty($request->staff_name)) {
            $where[] = ['staff_account', 'Like', "%$request->staff_name%"];
        }
        if (isset($request->feedback)) {
            $where[] = ['feedback', '=', $request->feedback];
        }

        $user = Auth::user();
//        dd($user);
        if ($user->username == 'ADMIN@DXMB') {
            $hasRole = true;
        } else {
            $hasRole = false;
        }

        if ($user->u_type == 1) {
            $list_camp = json_decode($user->list_campassign_ids);
            if($list_camp){
                $where[] = [
                    function ($query) use ($list_camp) {
                        $query->whereIn('id', $list_camp);
                    },
                ];
            }else{
                $where[] = ['id', '=', ''];
            }
        }
        $where[] = ['role', '=', 0];

        if ($where) {
            $assigns = CampaignAssign::searchBy(['where' => $where, 'per_page' => $data['per_page']]);
        } else {
            $assigns = CampaignAssign::searchBy(['where' => $where,'per_page' => $data['per_page']]);
        }
//        dd($assigns);
        $data['assigns'] = $assigns;

        //End tìm kiếm
        $data_search = [
            'customer_name'  => '',
            'customer_email' => '',
            'customer_phone' => '',
            'staff_name'     => '',
            'campaign'       => [],
//            'feedback'       => '',
        ];

        if (!empty($request->customer_name)) {
            $data_search['customer_name'] = $request->customer_name;
        }

        if (!empty($request->customer_email)) {
            $data_search['customer_email'] = $request->customer_email;
        }

        if ($request->customer_phone !== null) {
            $data_search['customer_phone'] = $request->customer_phone;
        }

        if (!empty($request->staff_name)) {
            $data_search['staff_name'] = $request->staff_name;
        }

        if (!empty($request->campaign_id)) {
            $data_search['campaign']['id']   = $request->cb_staff_id;
            $data_search['campaign']['name'] = Campaign::find($request->campaign_id)->title;
        }
        if (isset($request->feedback)) {
            $data_search['feedback'] = $request->feedback;
        }

        $data['data_search'] = $data_search;

        return view('backend.campaign-assigns.index_ctv', $data);
    }

    public function edit_add_diary(Request $request, $id = 0)
    {
        $this->authorize('update', app(CustomerDiary::class));
        $data['meta_title'] = "Phản hồi";

        if ($id > 0) {
            $assign_customer = CampaignAssign::findOrFail($id);
        } else {
            $assign_customer  = new CampaignAssign;
        }

        $filters = Filter::getAll();

        $data['filters']         = $filters;
        $data['assign_customer'] = $assign_customer;

        return view('backend.campaign-assigns.edit_add', $data);
    }

    public function confirmAssign(Request $request)
    {

        $this->authorize('update', app(CustomerDiary::class));
        $rules = [

        ];

        $validator = Validator::make($request->all(), $rules, $this->messages(), $this->attributes());
        $errors    = $validator->messages();
        if ($errors->toArray()) {
            return back()->with(['errors' => $errors])->withInput();
        }

        if (!$request->has('_validate')) {

            $user_id = \Auth::user()->uid;
            $user_edit = BoUser::where('ub_id',$user_id)->select('ub_title')->first();
            $assign        = CampaignAssign::findOrFail($request->assign_id);
            $logs_data = $assign->logs;
            $diary = array_pop($logs_data);
            $customer = new BoCustomer;
            $campaign_assign = new CampaignAssign();
//            $check_cus = BoCustomer::getusers($request->customer_email,$request->customer_phone);
            $check_cus = BoCustomer::Check_phonenumber($request->customer_phone);

            $diary_m = new CustomerDiary;
            if($check_cus){
                $customer = $check_cus;

                $data = $request->only('status', 'project_id', 'cd_description', 'filters');
                $params = [
                    'cd_id'              => strtotime(date('d-m-Y H:i:s')),
                    'cd_user_id'         => $user_id,
                    'cd_customer_id'     => $customer->cb_id,
                    'cd_rating'     => empty($request->cd_rating) ? 0 : $request->cd_rating,
                    'campaign_id'        => $assign->campaign->id,
                    'campaign_assign_id' => $assign->id,
                    'status'             => (int) $customer->status,
                    'project_id'         => empty($request->project_id) ? 0 : $request->project_id,
                    'role' => 3
                ];

                $data = array_merge($data, $params);
                unset($data['assign_id']);
                // Lưu thông tin nhật ký
                $diary_m->fill($data)->save();
            }else{
                $data_customer = [
                    'cb_id'         => time(),
                    'project_id'    => $request->project_id,
                    'tc_created_by' => $user_id,
                    'cb_name'       => $request->customer_name,
                    'cb_email'      => $request->customer_email,
                    'cb_phone'      => $request->customer_phone,
                    'cb_staff_id'   => $user_id,
                    'cb_source'   => $assign->source,
                    'status'        => $request->status ? $request->status : 0,
                ];

                $customer->fill($data_customer)->save();
                $logs_ar = $assign->logs?$assign->logs:[];
                $diary = array_pop($logs_ar);

                $data_diary = [
                    'customer_name'  => $request->customer_name??$diary['content']['customer_name']??$assign->customer_name,
                    'customer_phone' => $request->customer_phone??$diary['content']['customer_phone']??$assign->customer_phone,
                    'customer_email' => $request->customer_email??$diary['content']['customer_email']??$assign->customer_email,
                    'rating' => $request->cd_rating ?$request->cd_rating: 0,
                    'status' => (int)$diary['content']['status'],
                    'project_id' => $request->project_id,
                    'description' => $request->cd_description,
                    'filters' => $request->filters,
                    'user_name' => $request->user_name?$request->user_name:$user_edit->ub_title,
                ];

                $logs = $assign->logs;

                $logs[] = [
                    'edit_by' => $request->user_name?$request->user_name:$user_edit->ub_title,
                    'edit_at' => Carbon::now(),
                    'approve' => 0,
                    'content' => $data_diary,
                    'role' => 2
                ];

                $campaign_assign->where('id',$request->assign_id)->update(['logs'=>json_encode($logs)]);

                $datalogs = array();
                foreach ($logs as $l){
                    if($l['content']['status'] == 4 || $l['content']['status'] == 3 || $l['content']['status'] == 2 || $l['content']['status'] == 0){
                        $status_ar = 0;
                    }else{
                        $status_ar = 1;
                    }
                    $adddary = [
                        'project_id' => $l['content']['project_id'],
                        'cd_description' => $l['content']['description'],
                        'filters' => $l['content']['filters'],
                        'cd_id' => strtotime(date('d-m-Y H:i:s')),
                        'cd_user_id'         => $user_id,
                        'cd_customer_id'     => $customer->cb_id,
                        'cd_rating'     => $l['content']['rating'],
                        'campaign_id'        => $assign->campaign->id,
                        'campaign_assign_id' => $assign->id,
                        'status' => $status_ar,
                        'role' => $l['role']
                    ];
                    // Lưu thông tin nhật ký
                    $diary_m->fill($adddary)->save();
                }
            }
            return redirect()->route('admin.campaign_assign.index')->with('success', 'Cập nhật nhật ký thành công!');
        }
    }

    public function confirmAssignCTV(Request $request)
    {

        $rules = [

        ];

        $validator = Validator::make($request->all(), $rules, $this->messages(), $this->attributes());
        $errors    = $validator->messages();
        if ($errors->toArray()) {
            return back()->with(['errors' => $errors])->withInput();
        }

        if (!$request->has('_validate')) {

            $user_id = \Auth::user()->uid;

            $assign        = CampaignAssign::findOrFail($request->assign_id);

            $customer = new BoCustomer;
            $campaign_assign = new CampaignAssign();
            $logs_ar = $assign->logs?$assign->logs:[];
            $diary = array_pop($logs_ar);

            $data = [
                'customer_name'  => $request->customer_name??$diary['content']['customer_name']??$assign->customer_name,
                'customer_phone' => $request->customer_phone??$diary['content']['customer_phone']??$assign->customer_phone,
                'customer_email' => $request->customer_email??$diary['content']['customer_email']??$assign->customer_email,
                'rating' => $request->cd_rating ?: $assign->cd_rating,
                'status' => (int) $request->feedback,
                'project_id' => $request->project_id,
                'description' => $request->cd_description,
                'filters' => $request->filters,
                'user_name' => $request->user_name,
            ];
            $logs = $assign->logs;
            if($request->feedback == 4 || $request->feedback == 3 || $request->feedback == 2){
                $logs[] = [
                    'edit_by' => $request->user_name,
                    'edit_at' => Carbon::now(),
                    'approve' => 0,
                    'content' => $data,
                    'role' => 0
                ];
                $campaign_assign->where('id',$request->assign_id)->update(['logs'=>json_encode($logs),'role'=>0,'feedback'=>$request->feedback]);
            }else{
                $logs[] = [
                    'edit_by' => $request->user_name,
                    'edit_at' => Carbon::now(),
                    'approve' => 0,
                    'content' => $data,
                    'role' => 1
                ];
                $campaign_assign->where('id',$request->assign_id)->update(['logs'=>json_encode($logs),'check_diary'=>1,'role'=>1,'feedback'=>$request->feedback]);
            }

            return redirect()->route('admin.campaign_assign.index_ctv')->with('success', 'Cập nhật nhật ký thành công!');
        }
    }

    public function ajax_detail_diary(Request $request)
    {
        $assign  = CampaignAssign::findOrFail($request->assign_id);
        $filters = Filter::getAll();
        $diary_list = CustomerDiary::getAllDiary($request->assign_id);

        return view('backend.campaign-assigns.sub-views.edit_diary', compact('assign', 'filters','diary_list'));
    }
    public function ajax_detail_diary_ctv(Request $request)
    {
        $assign  = CampaignAssign::findOrFail($request->assign_id);
        $assign->load('campaign');
        $filters = Filter::getAll();
        $diary_list = CustomerDiary::getAllDiary($request->assign_id);

        return view('backend.campaign-assigns.sub-views.edit_diary_call_ctv', compact('assign', 'filters','diary_list'));
    }
    public function ajax_show_campain_insert(Request $request)
    {
        if($request->campain_id){
            $id = $request->campain_id;
        }else{
            $id = 0;
        }
        $campaign                = Campaign::findOrNew($id);
        $customers               = CampaignAssign::searchBy(['where' => ['campaign_id' => $id]]);
        $data['customer_source'] = Setting::config_get('customer-source');
        $data['campaign']        = $campaign;
        $data['customers']       = $customers;
        $data['id']              = $id;

        return view('backend.campaign-assigns.sub-views.import_file_campain', $data);
    }
    public function ajax_detail_diary_fast(Request $request)
    {
//        $assign  = CampaignAssign::findOrFail($request->assign_id);
        $filters = Filter::getAll();
        $diary_list = CustomerDiary::getAllDiary($request->assign_id);

        $user_id = \Auth::user()->uid;
        $user_edit = BoUser::where('ub_id',$user_id)->select('ub_title')->first();
        $assign        = CampaignAssign::findOrFail($request->assign_id);
        $logs_data = $assign->logs;
        $diary = array_pop($logs_data);
        $customer = new BoCustomer;
        $campaign_assign = new CampaignAssign();
//            $check_cus = BoCustomer::getusers($request->customer_email,$request->customer_phone);
        $check_cus = BoCustomer::Check_phonenumber($diary['content']['customer_phone']);
        $diary_m = new CustomerDiary;
        if($check_cus){
            $customer = $check_cus;

            $data = $request->only('status', 'project_id', 'cd_description', 'filters');
            if($diary['content']['status'] == 4 || $diary['content']['status'] == 3 || $diary['content']['status'] == 2 || $diary['content']['status'] == 0){
                $status_ar = 0;
            }else{
                $status_ar = 1;
            }
            $params = [
                'cd_id'              => strtotime(date('d-m-Y H:i:s')),
                'cd_user_id'         => $user_id,
                'cd_customer_id'     => $customer->cb_id,
                'cd_rating'     => $status_ar,
                'campaign_id'        => $assign->campaign->id,
                'campaign_assign_id' => $assign->id,
                'status'             => (int) $customer->status,
                'project_id'         => $diary['content']['project_id'],
                'role' => 3
            ];

            $data = array_merge($data, $params);
            unset($data['assign_id']);
            // Lưu thông tin nhật ký
            $diary_m->fill($data)->save();
        }else{
            $data_customer = [
                'cb_id'         => time(),
                'project_id'    => $diary['content']['project_id'],
                'tc_created_by' => $user_id,
                'cb_name'       => $diary['content']['customer_name'],
                'cb_email'      => $diary['content']['customer_email'],
                'cb_phone'      => $diary['content']['customer_phone'],
                'cb_staff_id'   => $user_id,
                'cb_source'   => $assign->source,
                'status'        => $diary['content']['status']?$diary['content']['status']:0,
            ];

            $customer->fill($data_customer)->save();
            $data_diary = [
                'customer_name'  => $diary['content']['customer_name'] ?: $assign->customer_name,
                'customer_phone' => $diary['content']['customer_phone']  ?: $assign->customer_phone,
                'customer_email' =>  $diary['content']['customer_email']  ?: $assign->customer_email,
                'rating' => $diary['content']['status'] ?$diary['content']['status']: 0,
                'status' => (int)$diary['content']['status'],
                'project_id' => $diary['content']['project_id'],
                'description' => $diary['content']['description'],
                'filters' => $diary['content']['filters'],
                'user_name' => $request->user_name?$request->user_name:$user_edit->ub_title,
            ];

            $logs = $assign->logs;

            $logs[] = [
                'edit_by' => $request->user_name?$request->user_name:$user_edit->ub_title,
                'edit_at' => Carbon::now(),
                'approve' => 0,
                'content' => $data_diary,
                'role' => 2
            ];

            $campaign_assign->where('id',$request->assign_id)->update(['logs'=>json_encode($logs)]);

            $datalogs = array();
            foreach ($logs as $l){
                if($l['content']['status'] == 4 || $l['content']['status'] == 3 || $l['content']['status'] == 2 || $l['content']['status'] == 0){
                    $status_ar = 0;
                }else{
                    $status_ar = 1;
                }
                $adddary = [
                    'project_id' => $l['content']['project_id'],
                    'cd_description' => $l['content']['description'],
                    'filters' => $l['content']['filters'],
                    'cd_id' => strtotime(date('d-m-Y H:i:s')),
                    'cd_user_id'         => $user_id,
                    'cd_customer_id'     => $customer->cb_id,
                    'cd_rating'     => $l['content']['rating'],
                    'campaign_id'        => $assign->campaign->id,
                    'campaign_assign_id' => $assign->id,
                    'status' => $status_ar,
                    'role' => $l['role']
                ];

                $datalogs[]= $diary_m->fill($adddary)->save();
            }
        }

        return view('backend.campaign-assigns.sub-views.edit_diary', compact('assign', 'filters','diary_list'));
    }
    public function save_add_diary(Request $request)
    {
        $rules = [
            'rating' => 'required',
        ];
        $id = $request->id;

        $validator = Validator::make($request->all(), $rules, $this->messages(), $this->attributes());
        $errors    = $validator->messages();

        $campaign_assign = CampaignAssign::find($id);
        if (!$campaign_assign) {
            $errors->add('assign_id', 'Khách hàng phản hồi không chính xác!');
        }
        // return $errors;
        /*if ($errors->toArray()) {
            return respone()->json(['mesager' => $errors]);
        }*/

        if (!$request->has('_validate')) {
            $data = $request->all();
            // return $data;
            $chech_log = empty($campaign_assign->logs) ? true : false;

            $params = [
                'customer_name'  => $request->customer_name ?: $campaign_assign->customer_name,
                'customer_phone' => $request->customer_phone ?: $campaign_assign->customer_phone,
                'customer_email' => $request->customer_email ?: $campaign_assign->customer_email,
            ];

            $data = array_merge($data, $params);

            $logs = $campaign_assign->logs;

            $logs[] = [
                'edit_by' => $request->user_name,
                'edit_at' => Carbon::now(),
                'approve' => 0,
                'content' => $data,
            ];

            $input = [
                'logs'        => $logs,
                'feedback'    => $request->feedback,
                'check_diary' => 1,
            ];

            if ($request->project_id != $campaign_assign->campaign->project_id) {
                $input['feedback'] = -1;
            }
            $update = $campaign_assign->fill($input)->save();

            // Cập nhật số lượng phản hồi khách hàng của chiến dịch
            if ($update) {
                if ($chech_log) {
                    $campaign = Campaign::where('id', $campaign_assign->campaign->id)->increment('feedback', $request->feedback);
                }
                $msg = "Thành công";
            } else {
                $msg = "Không cập nhật được nhật ký";
            }

            $res = [
                'status' => true,
                'msg'    => $msg,
            ];

            return redirect()->route('admin.campaign_assign.index')->with('success', $msg);
        }
    }

}
