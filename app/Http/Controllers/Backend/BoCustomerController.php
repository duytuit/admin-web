<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Controller;
use App\Models\BoCategory;
use App\Models\BoCustomer;
use App\Models\BoUser;
use App\Models\Branch;
use App\Models\Campaign;
use App\Models\CustomerDiary;
use App\Models\CustomerGroup;
use App\Models\Filter;
use App\Models\Setting;
use DB;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Validator;
use Webpatser\Uuid\Uuid;
use willvincent\Rateable\Rateable;

class BoCustomerController extends Controller
{
    use Rateable;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->model = new BoCustomer();
        $this->user  = \Auth::user();
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
            'import_file'    => 'File tải lên',
        ];
    }

    public function index(Request $request)
    {
        $data['meta_title'] = "QL Khách Hàng";
        $this->authorize('index', app(BoCustomer::class));

        // setting tìm kiếm
        $searches['field'] = [
            'cb_phone' => "Số điện thoại",
            'cb_email' => "Email",
        ];
        $searches['customer-source'] = Setting::config_get('customer-source');

        $data['searches'] = $searches;
        // End setting tìm kiếm

        $data['per_page'] = Cookie::get('per_page', 20);

        //Tìm kiếm
        $where = [];
        if (!empty($request->cb_name)) {
            $where[] = ['cb_name', 'Like', "%{$request->cb_name}%"];
        }

        if (!empty($request->field)) {
            $where[] = [$request->field, 'LIKE', "%{$request->partner_search}%"];
        }

        if (!empty($request->cb_staff_id)) {
            $where[] = ['cb_staff_id', 'LIKE', "%{$request->cb_staff_id}%"];
        }

        if (!empty($request->project_id)) {
            $where[] = ['project_id', '=', $request->project_id];
        }

        if (!empty($request->cb_source)) {
            $where[] = ['cb_source', '=', $request->cb_source];
        }

        if ($request->status !== null) {
            $where[] = ['status', '=', $request->status];
        }

        if ($where) {
            $bo_customers = BoCustomer::searchBy(['where' => $where, 'per_page' => $data['per_page']]);
        } else {
            $bo_customers = BoCustomer::searchBy(['per_page' => $data['per_page']]);
        }
        $bo_customers->load('user');

        foreach ($bo_customers as $key => $customer) {
            $bo_customers[$key]['cb_staff'] = $customer->bo_users();
        }

        $data['bo_customers'] = $bo_customers;

        //End tìm kiếm
        $data_search = [
            'cb_name'        => '',
            'status'         => '',
            'field'          => '',
            'partner_search' => '',
            'project_id'     => '',
            'cb_source'      => '',
            'cb_staff_id'    => '',
        ];

        $data['data_search'] = $request->data_search ?: $data_search;

        $data['data_search']['cb_name'] = $request->cb_name;

        if ($request->status != null) {
            $data['data_search']['status'] = $request->status;
        }

        if (!empty($request->project_id)) {
            $data['data_search']['project']['id']   = $request->project_id;
            $data['data_search']['project']['name'] = BoCategory::where('cb_id', $request->project_id)->first()->cb_title;
        }

        if (!empty($request->cb_staff_id)) {
            $data['data_search']['cb_staff']['id']   = $request->cb_staff_id;
            $data['data_search']['cb_staff']['name'] = BoUser::where('ub_id', $request->cb_staff_id)->first()->ub_title;
        }

        if (!empty($request->cb_source)) {
            $data['data_search']['cb_source'] = $request->cb_source;
        }

        if (!empty($request->field)) {
            $data['data_search']['field'] = $request->field;

            if (!empty($request->partner_search)) {
                $data['data_search']['partner_search'] = $request->partner_search;
            }
        }

        if (!empty($request->partner_search) && empty($request->field)) {
            $data['data_search']['partner_search'] = '';
        }

        return view('backend.bo-customers.index', $data);
    }

    public function edit($cb_id)
    {
        $data['meta_title'] = "QL Khách Hàng";

        if ($cb_id > 0) {
            $customer = BoCustomer::findById($cb_id);
        } else {
            $customer = new BoCustomer;
        }
        $this->authorize('view', $customer);

        $data['customer_source'] = Setting::config_get('customer-source');
        $data['bo_customer']     = $customer;
        $data['diaries']         = $customer->diaries;
        $data['id']              = $cb_id;

        return view('backend.bo-customers.edit_add', $data);
    }

    public function save(Request $request, $cb_id = 0)
    {
        //phân quyền chỗ này
        $customer = $cb_id ? BoCustomer::findById($cb_id) : new BoCustomer();
        $this->authorize('update', $customer);

        //end check quyền
        $rules = [
            'cb_name'  => 'required',
            'cb_phone' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages(), $this->attributes());
        $errors    = $validator->messages();

        // Kiểm tra khách hàng đã tồn tại chưa
        $check_customer = BoCustomer::is_exist(['cb_phone' => $request->cb_phone, 'project_id' => $request->project_id, 'cb_id' => $cb_id]);
        if ($check_customer === true) {
            $errors->add('is_customer', 'Khách hàng này đã có trên hệ thống.');
        }
        if ($errors->toArray()) {
            return back()->with(['errors' => $errors])->withInput();
        }

        if (!$request->has('_validate')) {

            $data = $request->all();

            $params = [
                'id'            => $customer ? $customer->id : 0,
                'birthday'      => date('Y-m-d', strtotime($request->birthday)),
                'cb_staff_id'   => collect($request->cb_staff_id)->implode(','),
                'cmnd_date'     => date('Y-m-d', strtotime($request->birthday)),
                'tc_created_by' => \Auth::user()->ub_id,

            ];

            if ($cb_id == 0) {
                $params['cb_id']       = strtotime(date('Y-m-d H:i:s'));
                $params['cb_password'] = Hash::make('123456');
            }

            $data = array_merge($data, $params);
            $customer->fill($data);
            $customer->save();

            // Cập nhật lại danh sách thành viên nhóm
            CustomerGroup::updateAll();

            return redirect(url('/admin/bo-customers'))->with('success', 'Cập nhật khách hàng thành công!');
        }
    }

    public function validator_add_diary(Request $request)
    {
        $rules = [
            'cd_customer_id' => 'required',
            'project_id'     => 'required',
            'cd_rating'      => 'required',
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages(), $this->attributes());
        $errors    = $validator->messages();

        if ($errors->toArray()) {
            return response()->json(['error_branches' => $errors]);
        }
    }

    public function edit_add_diary(Request $request)
    {
        $diary = new CustomerDiary;
        $this->authorize('update', $diary);

        if (!$request->has('_validate')) {
            $data = $request->diary;

            if (!empty($data['cd_id'])) {
                $diary = CustomerDiary::findBy([['cd_id', $data['cd_id']]])->first();
                if (!$diary) {
                    return abort('404');
                }
                $diary->fill($data);
                $diary->save();
            } else {
                $params = [
                    'cd_id'      => strtotime(date('d-m-Y H:i:s')),
                    'cd_user_id' => \Auth::user()->uid,
                ];

                $data = array_merge($data, $params);

                // Lưu thông tin nhật ký
                $diary->fill($data);
                $diary->save();
            }

            if ($diary->campaign_id) {
                $campaign     = Campaign::findOrFail($diary->campaign_id);
                $count_status = CustomerDiary::where('campaign_id', $campaign->id)->where('status', 1)->count();
                $params       = [
                    'status' => $count_status,
                ];
                $campaign->fill($params);
                $campaign->save();
            }

            return redirect('/admin/bo-customers/edit/' . $data['cd_customer_id'] . $request->hashtag)->with('success', 'Cập nhật nhật ký thành công!');
        }
    }

    /**
     * Lấy all danh sách bo_user
     *
     * @param Request $request
     * @return void
     */
    public function getUserByGroup(Request $request)
    {
        $keyword = $request->input('search', '');
        $users   = DB::table('bo_users as u')->select('u.ub_id', 'u.ub_title', 'u.group_ids', 'g.gb_title')->join('bo_user_groups as g', 'u.group_ids', '=', 'g.gb_id');
        if ($keyword) {
            $users = $users->where('ub_title', 'like', '%' . $keyword . '%')->orWhere('gb_title', 'like', '%' . $keyword . '%');
        }
        $users = $users->paginate(20);

        return response()->json($users);
    }

    public function ajax_get_all_project(Request $request)
    {
        $keyword = $request->input('search', '');
        if ($keyword) {
            $where[] = ['cb_title', 'like', '%' . $keyword . '%'];
        }
        if (!empty($where)) {
            $categories = BoCategory::where($where)->paginate(20);
        } else {
            $categories = BoCategory::paginate(20);
        }

        return response()->json($categories);
    }

    public function get_all_branch(Request $request)
    {
        $keyword = $request->input('search', '');
        if ($keyword) {
            $where[] = ['title', 'like', '%' . $keyword . '%'];
        }
        if (!empty($where)) {
            $branches = Branch::where($where)->paginate(20);
        } else {
            $branches = Branch::paginate(20);
        }

        return response()->json($branches);
    }

    public function ajax_edit_diary(Request $request)
    {
        $diary       = CustomerDiary::findBy([['cd_id', '=', $request->diary_id]])->first();
        $customer_id = $request->customer_id;
        $projects    = BoCategory::all();
        return view('backend.bo-customers.sub-views.edit_diary', compact('diary', 'customer_id', 'projects'));
    }

    public function ajax_edit_diary_call(Request $request)
    {
        $customer = BoCustomer::findOrFail($request->cus_id);
        $filters = Filter::getAll();
        $customer->load('diaries');

        return view('backend.bo-customers.sub-views.edit_diary_call',compact('customer','filters'));
    }

    public function get_download(Request $request, $uuid = '')
    {
        if ($uuid) {
            $customer = BoCustomer::where([['files', 'LIKE', "%{$uuid}%"]])->first();
            $file     = storage_path() . "/upload/" . $customer->files['name'];
        } else {
            $file = storage_path() . "/downloads/" . $request->file_name;
        }

        return response()->download($file);
    }

    public function validator_upload_file(Request $request)
    {
        $rules = [
            'import_file' => 'required',
            'project_id'  => 'required',
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages(), $this->attributes());
        $errors    = $validator->messages();

        if ($errors->toArray()) {
            return response()->json(['error_upload' => $errors]);
        }
    }

    public function import(Request $request)
    {
        $this->authorize('update', app(BoCustomer::class));

        $validator = Validator::make($request->all(), $this->messages(), $this->attributes());
        $errors    = $validator->messages();

        if ($errors->toArray()) {
            return response()->json(['error_upload' => $errors]);
        }

        if (!$request->has('_validate')) {
            $file = $request->file('import_file');
            $path = $file->getRealPath();

            $data = Excel::load($path)->get();
            storage_path('upload', $file->getClientOriginalName());

            $url = [
                'name' => $file->getClientOriginalName(),
                'uuid' => (string) Uuid::generate(),
            ];

            if ($data->count()) {
                $customers = [];
                foreach ($data as $key => $value) {
                    $customers[] = [
                        'cb_id'          => strtotime(date('d-m-Y H:i:s')) + $key,
                        'project_id'     => $request->project_id,
                        'cb_source'      => $request->cb_source,
                        'tc_created_by'  => 1536638938,
                        'cb_staff_id'    => collect($request->cb_staff_id)->implode(','),
                        'cb_name'        => $value->ho_ten,
                        'cb_email'       => $value->email,
                        'cb_phone'       => $value->sdt,
                        'birthday'       => date('Y-m-d', strtotime($value->ngay_sinh)),
                        'cb_id_passport' => $value->cmnd_ho_chieu,
                        'cmnd_date'      => date('Y-m-d', strtotime($value->ngay_cap)),
                        'issued_by'      => $value->noi_cap,
                        'address'        => $value->dia_chi_lien_he,
                        'files'          => json_encode($url),
                        'cb_password'    => Hash::make('123456'),
                        'created_at'     => date('Y-m-d H:i:s'),
                        'updated_at'     => date('Y-m-d H:i:s'),
                    ];
                }

                // Kiểm tra xem có khách hàng nào đã có trên hệ thống không?
                if (!empty($customers)) {
                    $err_customer = [];

                    $bo_customers = BoCustomer::all();

                    foreach ($customers as $customer) {
                        foreach ($bo_customers as $value) {
                            if ($customer['cb_id'] != $value->cb_id) {
                                if ($value->cb_phone == $customer['cb_phone'] && $value->project_id == $customer['project_id']) {
                                    $err_customer[] = $customer;
                                }
                            }
                        }
                    }

                    if (empty($err_customer)) {
                        BoCustomer::insert($customers);
                        return redirect(url('/admin/bo-customers'))->with('success', 'Thêm thành công ' . count($customers) . ' khách hàng.');
                    } else {
                        $errors->add('err_customer', 'Có khách hàng đã tồn tại trên hệ thống.');
                        return response()->json(['error_upload' => $errors, 'error_customer' => $err_customer]);
                    }
                }
            }
        }
    }

    public function exportCustomer(Request $request)
    {
        $this->authorize('export', app(BoCustomer::class));

        $where = [];
        if (!empty($request->cb_name)) {
            $where[] = ['cb_name', 'Like', "%{$request->cb_name}%"];
        }

        if (!empty($request->field)) {
            $where[] = [$request->field, 'LIKE', "%{$request->partner_search}%"];
        }

        if (!empty($request->cb_staff_id)) {
            $where[] = ['cb_staff_id', 'LIKE', "%{$request->cb_staff_id}%"];
        }

        if (!empty($request->project_id)) {
            $where[] = ['project_id', '=', $request->project_id];
        }

        if (!empty($request->cb_source)) {
            $where[] = ['cb_source', '=', $request->cb_source];
        }

        if ($request->status !== null) {
            $where[] = ['status', '=', $request->status];
        }

        $bo_customers = BoCustomer::where($where)->get();

        try {
            $result = Excel::create('Danh sách khách hàng', function ($excel) use ($bo_customers) {
                $excel->setTitle('Danh sách khách hàng');
                $excel->sheet('Danh sách khách hàng', function ($sheet) use ($bo_customers) {
                    $new_customers = [];
                    foreach ($bo_customers as $key => $bo_customer) {
                        $new_customers[] = [
                            'STT'               => $key + 1,
                            'Họ tên Khách hàng' => $bo_customer->cb_name,
                            'SĐT'               => $bo_customer->cb_phone,
                            'Email'             => $bo_customer->cb_email,
                            'Dự án'             => $bo_customer->bo_category ? $bo_customer->bo_category->cb_title : '',
                            'Trạng thái'        => $bo_customer->status == 1 ? 'Quan tâm' : 'Không quan tâm',
                        ];
                    }
                    if ($new_customers) {
                        $sheet->fromArray($new_customers);
                    }
                });
            })->store('xlsx',storage_path('exports/'));
$file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
return response()->download($file)->deleteFileAfterSend(true);
             
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function assigned_customer(Request $request)
    {
        $this->authorize('assign', app(BoCustomer::class));

        $customer = BoCustomer::findById($request->cus_cb_id);

        $param = [
            'cb_staff_id' => collect($request->cb_staff_id)->implode(','),
        ];
        $customer->fill($param);
        $customer->save();

        return back()->with('success', 'Phân bổ thành công!');
    }
    public function confirm_diary_call(Request $request)
    {
        $user_id = \Auth::user()->uid;
        $this->authorize('update', app(CustomerDiary::class));
        $customer = BoCustomer::Check_phonenumber($request->cb_phone);
        $bocus = new BoCustomer();
        $bocus_diaru = new CustomerDiary();
//
        $data=[
            'cb_name' => $request->cb_name,
            'cb_phone' => $request->cb_phone,
            'cb_email' => $request->cb_email,
            'cmnd' => $request->cmnd,
            'address' => $request->address,
            'status' => $request->feedback,
            'project_id' => $request->project_id,
        ];

        $diary=[
            'status' => $request->feedback,
            'project_id' => $request->project_id,
            'cd_description' => $request->cd_description,
            'cd_rating' => $request->cd_rating,
            'filters' => $request->filters,
            'campaign_assign_id' => $request->campaign_assign_id,
            'cd_id' => $customer->cb_id,
            'cd_customer_id' => $customer->cb_id,
            'cd_user_id' => $user_id,
        ];
//        dd($diary);
        $bocus->where('cb_phone',$request->cb_phone)->update($data);

        $bocus_diaru->fill($diary)->save();
        /*$customer = BoCustomer::findById($request->cus_cb_id);

        $param = [
            'cb_staff_id' => collect($request->cb_staff_id)->implode(','),
        ];
        $customer->fill($param);
        $customer->save();*/

        return back()->with('success', 'Cập nhật thông tin khách hàng thành công');
    }


}
