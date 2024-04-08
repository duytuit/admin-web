<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Controller;
use App\Models\Branch;
use App\Models\Partner;
use App\Models\UserPartner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Validator;

class UserPartnerController extends Controller
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->model = new UserPartner();
    }

    /**
     *  Undocumented function
     *  Custom thông báo lỗi
     */
    public function messages()
    {
        return [
            'required' => ':attribute không được để trống',
            'unique'   => ':attribute đã tồn tại',
            'same'     => ':attribute không chính xác',
        ];
    }

    /**
     * Undocumented function
     * @return void
     */
    public function attributes()
    {
        return [
            'email'          => 'Địa chỉ email',
            'full_name'      => 'Họ tên',
            'phone'          => 'Số điện thoại',
            'partner_id'     => 'Đối tác',
            'check_password' => 'Xác nhận mật khẩu',
        ];
    }

    /**
     * Display a listing of the resource.
     *@param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data['meta_title'] = "QL tài khoản đối tác";
        $this->authorize('index', app(Userpartner::class));

        $searches['fields'] = [
            'email' => "Email",
            'phone' => "Số điện thoại",
        ];
        $data['search'] = $searches;

        $data['per_page'] = Cookie::get('per_page', 20);
        //Tìm kiếm
        $where = [];
        if (!empty($request->full_name)) {
            $where[] = ['full_name', 'LIKE', "%{$request->full_name}%"];
        }

        if ($request->status !== null) {
            $where[] = ['status', '=', (int) $request->status];
        }

        if ($request->partner_id !== null) {
            $where[] = ['partner_id', '=', (int) $request->partner_id];

            if ($request->branch_id !== null) {
                $where[] = ['id', '=', $request->branch_id];
            }
        }

        if (!empty($request->field !== null)) {
            $where[] = [$request->field, 'LIKE', "%{$request->partner_search}%"];
        }

        if ($where) {
            $data['user_partners'] = UserPartner::searchBy(['where' => $where, 'per_page' => $data['per_page']]);
        } else {
            $data['user_partners'] = UserPartner::searchBy(['per_page' => $data['per_page']]);
        }
        // End tìm kiếm

        $data['partners'] = Partner::all();

        // Trả dữ liêu tìm kiếm cho view
        $data_search = [
            'full_name'      => '',
            'status'         => '',
            'partner_id'     => '',
            'branch_id'      => '',
            'field'          => '',
            'partner_search' => '',
        ];

        $data['data_search'] = !empty($request->data_search) ? $request->data_search : $data_search;

        $data['data_search']['full_name'] = $request->full_name;

        if ($request->status != null) {
            $data['data_search']['status'] = $request->status;
        }

        if (!empty($request->partner_id)) {
            $data['data_search']['partner_id'] = $request->partner_id;

            if (!empty($request->branch_id)) {
                $data['data_search']['branch']['id']    = $request->branch_id;
                $data['data_search']['branch']['title'] = Branch::find($request->branch_id)->title;
            }
        }

        if (!empty($request->field)) {
            $data['data_search']['field'] = (string) $request->field;
        }

        if (!empty($request->partner_search)) {
            $data['data_search']['partner_search'] = (string) $request->partner_search;
        }

        return view('backend.user_partners.index', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['meta_title'] = "QL đối tác";

        $data['user_partner'] = $id ? UserPartner::findOrFail($id) : new UserPartner();
        $this->authorize('view', $data['user_partner']);

        $data['partners'] = Partner::searchBy();
        $data['id']       = $id;
        return view('backend.user_partners.edit_add', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request, $id = 0)
    {
        $this->authorize('view', app(UserPartner::class));
        $rules = [
            'email'      => 'required|email|unique:user_partners,email,' . $id,
            'full_name'  => 'required',
            'phone'      => 'required',
            'partner_id' => 'required',
        ];

        if ($id == 0) {
            $rules['password']       = 'required';
            $rules['check_password'] = 'required|same:password';
        }

        $validator = Validator::make($request->all(), $rules, $this->messages(), $this->attributes());
        $errors    = $validator->messages();

        if ($errors->toArray()) {
            return back()->with(['errors' => $errors])->withInput();
        }

        if (!$request->has('_validate')) {
            $data           = $request->all();
            $data['status'] = $request->input('status', 0);

            if ($request->password === $request->check_password) {
                unset($data['check_password']);
                if (!$request->password) {
                    unset($data['password']);
                }
                $params = [
                    'id'           => $id,
                    'user_id'      => 1,
                    'user_name'    => 'Admin',
                    'password'     => Hash::make($request->password),
                    'partner_name' => Partner::find($request->partner_id)->name,
                    'branch_name'  => Branch::find($request->branch_id) ? Branch::find($request->branch_id)->title : '',
                ];

                $data = array_merge($data, $params);

                $user_partner = $id ? UserPartner::findOrFail($id) : new UserPartner();
                $user_partner->fill($data);
                $user_partner->save();

                return redirect(url('/admin/user-partners'))->with('success', 'Cập nhật thành công!');
            } else {
                return back()->with('danger', 'Xác nhận mật khẩu không chính xác!');
            }
        }
    }
}
