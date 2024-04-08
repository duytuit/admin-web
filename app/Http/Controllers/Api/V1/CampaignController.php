<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\CampaignResource;
use App\Models\BoCategory;
use App\Models\Campaign;
use App\Models\Setting;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Validator;

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
    /**
     * lấy thông tin chiến dịch.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $per_page = $request->input('per_page', 10);
        $per_page = $per_page > 0 ? $per_page : 10;

        $columns = (new Campaign)->getTableColumns();

        $unset = ['user_id', 'deleted_at'];

        $allowFields = [];
        foreach ($columns as $column) {
            if (!in_array($column, $unset)) {
                $allowFields[] = $column;
            }
        }

        $select    = $this->_select($request, $allowFields);
        $condition = $this->_filters($request, $columns);
        $order_by  = $this->_sort($request, $columns);

        $campaign = Campaign::select($select)
            ->where($condition)
            ->orderByRaw($order_by)
            ->paginate($per_page);

        return CampaignResource::collection($campaign);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $id = (int) $request->id;
        try {
            $campaign = Campaign::findOrfail($id);

            $data = [
                'data'    => $campaign,
                'setting' => $this->getSetting('customer-source'),
            ];
            return new CampaignResource($data);

        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'errors' => [
                    [
                        'code'   => 11001,
                        'title'  => 'Record not found',
                        'detail' => 'Record ID ' . $id . ' for ' . str_replace('App\\', '', $exception->getModel()) . ' not found',
                    ],
                ],
            ])->setStatusCode(400);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $id = (int) $request->id;
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

        $input = $request->all();
        if ($request->isMethod('PATCH')) {
            foreach ($rules as $key => $value) {
                if (!array_key_exists($key, $input)) {
                    unset($rules[$key]);
                }
            }
        }

        $validator = Validator::make($request->all(), $rules, $this->messages(), $this->attributes());
        $errors    = $validator->messages();

        $project = BoCategory::findById($request->project_id);
        if (!$project) {
            $errors->add('project_id', 'Dự án không tồn tại');
        }

        if ($errors->toArray()) {
            return response()->json($errors)->setStatusCode(400);
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
            } else {
                $check = $this->saveDataImport($request, $id, $data_import['data']);
            }

            if ($check) {
                return response()->json($check, 200);
            } else {
                return response()->json([
                    'errors' => [
                        [
                            'code'   => 11001,
                            'title'  => 'System not found',
                            'detail' => 'Record ID ' . $id . ' for ' . str_replace('App\\', '', $exception->getModel()) . ' not found',
                        ],
                    ],
                ])->setStatusCode(400);
            }
        }
    }

    public function getSetting($setting_name)
    {
        $setting = Setting::config_get($setting_name);
        return $setting;
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
                ];
            }

            $staff_tvc = BoUser::pluck('ub_account_tvc')->toArray();

            foreach ($excel_staffs['data'] as $key => $value) {
                if (!in_array(strtoupper($value->tvc_account), $staff_tvc)) {
                    $error_user[] = $value;
                } else {
                    $tvc_account[] = strtoupper($value->tvc_account);
                }
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
            $excel_users = BoUser::whereIn('ub_account_tvc', $tvc_account)->get();

            foreach ($excel_users as $user) {
                $users[] = [
                    'ub_id'       => $user->ub_id,
                    'ub_name'     => $user->ub_title,
                    'tvc_account' => $user->ub_account_tvc,
                ];
            }

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

        if ($import && !(session()->get('errors_user'))) {
            $customers      = $import['customers'];
            $users          = $import['users'];
            $url            = $import['url_file'];
            $count_customer = count($customers);
            $count_user     = count($users);
        }

        $user_id = Auth::user()->uid;

        $params = [
            'file_user_cus' => !empty($url) ? $url : '',
            'user_id'       => $user_id,
            'sum_user'      => !empty($count_user) ? $count_user : 0,
            'sum_customer'  => !empty($count_customer) ? $count_customer : 0,
        ];

        $data = array_merge($data, $params);

        $campaign = Campaign::findOrNew($id);
        $campaign->fill($data)->save();

        $time = Carbon::now();

        // Xử lý lưu thông tin bảng NV-KH-CD
        if ($import && !(session()->get('errors_user'))) {
            $data_new = [];
            foreach ($customers as $index => $customer) {
                $k     = $index % $count_user;
                $staff = $users[$k];

                $data_new[] = [
                    //'cb_id'          => time() + $index,
                    'campaign_id'    => $campaign->id,
                    'user_id'        => $user_id,
                    'staff_id'       => $staff['ub_id'],
                    'staff_name'     => $staff['ub_name'],
                    'customer_name'  => $customer['cus_name'],
                    'customer_phone' => $customer['cus_phone'],
                    'customer_email' => $customer['cus_email'],
                    'created_at'     => $time,
                    'updated_at'     => $time,
                ];
            }

            //xóa thông tin phân bổ cũ nếu có
            $this->deleteAllAssign($campaign->id);

            //Lưu thông tin phân bổ khách hàng
            $check = CampaignAssign::insert($data_new);
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
                    unset($customers[$j]);
                } elseif ($customers[$i]['email'] !== null && $customers[$i]['email'] == $customers[$j]['email']) {
                    $duplicate[$j] = $customers[$j];
                    unset($customers[$j]);
                }
            }
        }

        for ($i = 0; $i < count($customers); $i++) {
            if ($customers[$i]['so_dien_thoai'] == null &&
                $customers[$i]['email'] == null &&
                $customers[$i]['ten_khach_hang'] == null) {

                unset($customers[$i]);
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
}
