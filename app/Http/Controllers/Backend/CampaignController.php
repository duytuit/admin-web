<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Controller;
use App\Models\BoCategory;
use App\Models\BoUser;
use App\Models\Campaign;
use App\Models\CampaignAssign;
use App\Models\Filter;
use App\Models\Setting;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Ixudra\Curl\Facades\Curl;
use Validator;
use Webpatser\Uuid\Uuid;

class CampaignController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->model = new Campaign();
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
            'title'         => 'Tên chiến dịch',
            'project_id'    => 'Dự án',
            'file_user_cus' => 'File nhân viên - khách hàng',
        ];
    }

    public function index(Request $request)
    {
        $data['meta_title'] = "QL Chiến dịch";
        $this->authorize('index', app(Campaign::class));

        $searches['source'] = Setting::config_get('customer-source');

        // End setting tìm kiếm

        $data['per_page'] = Cookie::get('per_page', 20);
        $data['searches'] = $searches;

        //Tìm kiếm
        $where = [];
        if (!empty($request->title)) {
            $where[] = ['title', 'Like', "%{$request->title}%"];
        }

        if (!empty($request->project_id)) {
            $where[] = ['project_id', '=', $request->project_id];
        }

        if (!empty($request->source)) {
            $where[] = ['source', '=', $request->source];
        }

        if (!empty($request->begin_date) && empty($request->end_date)) {
            $where[] = ['updated_at', '>=', date('Y-m-d H:i:s', strtotime($request->begin_date))];
        } elseif (empty($request->begin_date) && !empty($request->end_date)) {
            $where[] = ['updated_at', '<=', date_add('Y-m-d H:i:s', strtotime($request->end_date))];
        } elseif (!empty($request->begin_date) && !empty($request->end_date)) {
            $where[] = ['updated_at', '>=', date('Y-m-d H:i:s', strtotime($request->begin_date))];
            $where[] = ['updated_at', '<=', date('Y-m-d H:i:s', strtotime($request->end_date))];
        }

        if ($where) {
            $campaigns = Campaign::searchBy(['where' => $where, 'per_page' => $data['per_page']]);
        } else {
            $campaigns = Campaign::searchBy(['per_page' => $data['per_page']]);
        }

        $data['campaigns'] = $campaigns;

        //End tìm kiếm
        $data_search = [
            'title'      => '',
            'project'    => [],
            'source'     => '',
            'begin_date' => '',
            'end_date'   => '',
        ];

        if (!empty($request->title)) {
            $data_search['title'] = $request->title;
        }

        if (!empty($request->project_id)) {
            $data_search['project']['id']   = $request->project_id;
            $data_search['project']['name'] = BoCategory::where('cb_id', $request->project_id)->first()->cb_title;
        }

        if (!empty($request->source)) {
            $data_search['source'] = $request->source;
        }

        if (!empty($request->begin_date)) {
            $data_search['begin_date'] = $request->begin_date;
        }

        if (!empty($request->end_date)) {
            $data_search['end_date'] = $request->end_date;
        }

        $data['data_search'] = $data_search;

        return view('backend.campaigns.index', $data);
    }

    public function edit($id = 0)
    {
        $data['meta_title'] = "QL Chiến dịch";
        $this->authorize('view', app(Campaign::class));

        $campaign                = Campaign::findOrNew($id);
        $customers               = CampaignAssign::searchBy(['where' => ['campaign_id' => $id]]);
        $data['customer_source'] = Setting::config_get('customer-source');
        $data['campaign']        = $campaign;
        $data['customers']       = $customers;
        $data['id']              = $id;

        return view('backend.campaigns.edit_add', $data);
    }

    public function save(Request $request, $id = 0)
    {
        //phân quyền chỗ này
        $this->authorize('update', app(Campaign::class));

        //end check quyền
        $rules = [
            'title'      => 'required',
            'project_id' => 'required',
        ];

        if (!$id) {
            $rules[] = [
                'title' => 'unique:campaigns',
            ];
        }

        $validator = Validator::make($request->all(), $rules, $this->messages(), $this->attributes());
        $errors    = $validator->messages();

        if ($errors->toArray()) {
            return back()->with(['errors' => $errors])->withInput();
        }

        if (!$request->has('_validate')) {

            $file        = $request->file('file_user_cus');
            $data_import = [];

            if ($file) {
                $data_import = $this->getDataFile($file);
            }

            // edit hoặc thêm mới mà ko import file
            if (!$data_import) {
                $check = $this->saveDataImport($request, $id);
                return redirect(url('/admin/campaigns'))->with('success', 'Cập nhật thành công!');
            }

            if ($data_import['data']) {
                $check = $this->saveDataImport($request, $id, $data_import['data']);
//                dd($check);
                // Nếu có lỗi do hệ thống
                if (!$check) {
                    return back()->with('error', 'Cập nhật không thành công do phát sinh lỗi!');
                } else {
                    // Nếu danh sách import có khách hàng hoặc nhân viên bị trùng
                    if ($data_import['data']['duplicate']) {
                        $duplicate = $data_import['data']['duplicate'];

                        if ($duplicate['customer'] || $duplicate['staff']) {
                            $duplicate = [
                                'success' => 'Thêm chiến dich thành công!',
                                'data'    => $duplicate,
                            ];

                            return redirect()->route('admin.campaigns.edit', ['id' => $check->id])->with('duplicate', $duplicate);

                        } else {
                            return redirect()->route('admin.campaigns.index')->with('success', 'Cập nhật chiến dịch thành công');
                        }
                    } else {
                        // Hoàn hảo ko có gì hết :)
                        return redirect()->route('admin.campaigns.index')->with('success', 'Cập nhật chiến dịch thành công');
                    }
                }
            } else {
                // Khi có thông tin TK nhân viên không chính xác
                return back()->with('errors_user', $data_import['messages'])->withInput();
            }
        }
    }

    public function getDataFile($file)
    {
        $path = $file->getRealPath();

        $excel_data = Excel::load($path)->get();
        storage_path('upload', $file->getClientOriginalName());

        $url = [
            'name' => $file->getClientOriginalName(),
            'uuid' => (string) Uuid::generate(),
        ];

        if ($excel_data->count()) {
            $customers  = [];
            $users      = [];
            $error_user = [];

            $excel_customers = $this->unset_customer($excel_data[0]);
            $excel_staffs    = $this->unset_staff($excel_data[1]);

            foreach ($excel_customers['data'] as $key => $customer) {
                $customers[] = [
                    'cus_name'  => $customer->ten_khach_hang,
                    'cus_email' => $customer->email,
                    'cus_phone' => $customer->so_dien_thoai,
                    'source'    => strtolower($customer->nguon),
                ];
            }

            // $staff_tvc = BoUser::pluck('ub_account_tvc')->toArray();

            // foreach ($excel_staffs['data'] as $key => $value) {
            //     if (!in_array(strtoupper($value->tvc_account), $staff_tvc)) {
            //         $error_user[] = $value;
            //     } else {
            //         $tvc_account[] = strtoupper($value->tvc_account);
            //     }
            // }

            foreach ($excel_staffs['data'] as $key => $value) {
                $tvc_account[] = strtoupper($value->tvc_account);
            }
        }

        $data = [
            'messages' => null,
            'data'     => null,
        ];

        if ($error_user) {
            $messages = [
                'messages' => 'Có ' . count($error_user) . ' nhân viên sai thông tin hoặc không có trên hệ thống!',
                'data'     => $error_user,
            ];

            $data['messages'] = $messages;
        } else {
            // $excel_users = BoUser::whereIn('ub_account_tvc', $tvc_account)->get();

            // foreach ($excel_users as $user) {
            //     $users[] = [
            //         'ub_id'       => $user->ub_id,
            //         'ub_name'     => $user->ub_title,
            //         'tvc_account' => $user->ub_account_tvc,
            //     ];
            // }

            $users = $tvc_account;

            $duplicate = [
                'customer' => $excel_customers['duplicate'],
                'staff'    => $excel_staffs['duplicate'],
            ];

            $data_new = [
                'users'     => $users,
                'customers' => $customers,
                'url_file'  => $url,
                'duplicate' => $duplicate,
            ];

            $data['data'] = $data_new;
        }

        return $data;
    }

    public function saveDataImport(Request $request, $id, $import = [])
    {
        $data = $request->all();
        $this->authorize('update', app(Campaign::class));

        if ($import && !(session()->get('errors_user'))) {
            $customers      = $import['customers'];
            $users          = $import['users'];
            $url            = $import['url_file'];
            $count_customer = count($customers);
            $count_user     = count($users);
        }

        $user_id = Auth::user()->id;
        $project_name = Campaign::getProjectById($data['project_id'])['cb_title'];
        $params = [
            'file_user_cus' => !empty($url) ? $url : '',
            'user_id'       => $user_id,
            'sum_user'      => !empty($count_user) ? $count_user : 0,
            'sum_customer'  => !empty($count_customer) ? $count_customer : 0,
            'feedback'      => 0,
            'project_name'  => $project_name
        ];

        $data = array_merge($data, $params);

        $campaign = Campaign::findOrNew($id);
        $campaign->fill($data)->save();

        $time = Carbon::now();

        // Xử lý lưu thông tin bảng phân bổ khách hàng
        if ($import && !(session()->get('errors_user'))) {
            $data_new = [];
            foreach ($customers as $index => $customer) {
                $k     = $index % $count_user;
                $staff = $users[$k];

                $data_new[] = [
                    //'cb_id'          => time() + $index,
                    'campaign_id'    => $campaign->id,
                    'user_id'        => $user_id,
                    'staff_account'  => $staff,
                    'customer_name'  => $customer['cus_name'],
                    'customer_phone' => $customer['cus_phone'],
                    'customer_email' => $customer['cus_email'],
                    'source'         => $customer['source'],
                    'feedback'       => 0,
                    'created_at'     => $time,
                    'updated_at'     => $time,
                ];

            }
            //xóa thông tin phân bổ cũ nếu có
            $this->deleteAllAssign($campaign->id);

            //Lưu thông tin phân bổ khách hàng
            $check = CampaignAssign::insert($data_new);
            $check_us = [];
            foreach ($users as $ai){
                $check_us[]= $ai;
                $staff_info = BoUser::where('ub_account_tvc',$ai)->first();
                if($staff_info->u_type == 1){
                    $list_camp_staff= CampaignAssign::where('staff_account',$ai)->select('id')->get()->toArray();
                    $list_camp = array_map(function($item){ return $item['id']; }, $list_camp_staff);
                    if(!empty($staff_info->list_campassign_ids)){
                        $list_campassign_ids= json_decode($staff_info->list_campassign_ids);
                    }else{
                        $list_campassign_ids = [];
                    }
                    $list_camp= array_values(array_unique(array_merge($list_camp,$list_campassign_ids)));
                    $update = BoUser::where('ub_id',$staff_info->ub_id)->update(['list_campassign_ids' =>json_encode($list_camp)]);
                }
            }
        }

        if ((isset($check) && $check == true) || !isset($check)) {
            return $campaign;
        }

        return false;
    }

    public function deleteAllAssign($campaign_id)
    {
        $campaign_assign = CampaignAssign::where('campaign_id', $campaign_id)->delete();
        return $campaign_assign;
    }

    public function get_all_campaigns(Request $request)
    {
        $keyword = $request->input('search', '');
        if ($keyword) {
            $where[] = ['title', 'like', '%' . $keyword . '%'];
        }
        if (!empty($where)) {
            $campaigns = Campaign::where($where)->paginate(20);
        } else {
            $campaigns = Campaign::paginate(20);
        }

        return response()->json($campaigns);
    }

    // Loại những khách hàng trùng nhau và dòng trống
    public function unset_customer($customers)
    {
        $duplicate = [];
        for ($i = 0; $i < count($customers) - 1; $i++) {
            for ($j = $i + 1; $j < count($customers); $j++) {
                if ($customers[$i]['so_dien_thoai'] !== null && $customers[$i]['so_dien_thoai'] == $customers[$j]['so_dien_thoai']) {
                    $duplicate[$j] = $customers[$j];
                } elseif ($customers[$i]['email'] !== null && $customers[$i]['email'] == $customers[$j]['email']) {
                    $duplicate[$j] = $customers[$j];
                }
            }
        }

        for ($i = 0; $i < count($customers); $i++) {
            if ($customers[$i]['so_dien_thoai'] == null &&
                $customers[$i]['email'] == null &&
                $customers[$i]['ten_khach_hang'] == null) {

                unset($customers[$i]);
            }

            foreach ($duplicate as $key => $value) {
                if ($i == $key) {
                    unset($customers[$i]);
                }
            }
        }

        $data = [
            'data'      => $customers,
            'duplicate' => $duplicate,
        ];
        return $data;
    }

    // Loại những nhân viên trùng nhau và dòng trống
    public function unset_staff($staffs)
    {
        $duplicate = [];
        for ($i = 0; $i < count($staffs) - 1; $i++) {
            for ($j = $i + 1; $j < count($staffs); $j++) {
                if ($staffs[$i]['tvc_account'] == $staffs[$j]['tvc_account']) {
                    $duplicate[$j] = $staffs[$j];
                    unset($staffs[$j]);
                }
            }
        }
        for ($i = 0; $i < count($staffs); $i++) {
            if ($staffs[$i]['tvc_account'] == null) {
                unset($staffs[$i]);
            }
        }

        $data = [
            'data'      => $staffs,
            'duplicate' => $duplicate,
        ];
        return $data;
    }

    public function get_download(Request $request, $uuid = '')
    {
        if ($uuid) {
            $campaign = Campaign::where([['file_user_cus', 'LIKE', "%{$uuid}%"]])->first();
            $file     = storage_path() . "/upload/" . $campaign->file_user_cus['name'];
        } else {
            $file = storage_path() . "/downloads/" . $request->file_name;
        }

        return response()->download($file);
    }

    public function getProject(Request $request)
    {
        $keyword = $request->input('search', '');

        $projects = Curl::to('https://bo.dxmb.vn/api/category/list')
            ->withHeader('Content-MD5: BO.PCN@DXMB!@#')
            ->withData([
                "size"    => 20,
                "page"    => 1,
                'keyword' => $keyword,
                "order"   => ["cb_title" => "DESC"],
            ])
            ->asJson(true)
            ->post();

        return response()->json($projects);
    }
    public function ajax_detail_cus(Request $request)
    {
        $customers               = CampaignAssign::searchBy(['where' => ['campaign_id' => $request->assign_id]]);
        return view('backend.campaigns.sub-views.show_cus', compact('customers'));
    }
}
