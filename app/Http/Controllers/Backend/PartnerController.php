<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Controller;
use App\Models\Branch;
use App\Models\City;
use App\Models\District;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Validator;

class PartnerController extends Controller
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->model = new Partner();
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
            'name'           => 'Tên đối tác',
            'company_name'   => 'Tên công ty',
            'city'           => 'Tỉnh/ Thành phố',
            'district'       => 'Quận/ Huyện',
            'address'        => 'Địa chỉ chi tiết',
            'representative' => 'Người đại diện',
            'partner_id'     => 'Đối tác',
            'title'          => 'Tên Chi nhánh',
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data['meta_title'] = "QL đối tác";
        $this->authorize('index', app(Partner::class));

        $searches           = [
            'company_name' => "Công ty",
            'hotline'      => "Hotline",
            'user_name'    => "Người tạo",
        ];
        $data['searches'] = $searches;

        $data['per_page'] = Cookie::get('per_page', 20);

        //Tìm kiếm
        $where = [];
        if (!empty($request->name)) {
            $where[] = ['name', 'LIKE', "%{$request->name}%"];
        }

        if ($request->status !== null) {
            $where[] = ['status', '=', $request->status];
        }

        if (!empty($request->field)) {
            $where[] = [$request->field, 'LIKE', "%{$request->partner_search}%"];
        }

        if ($where) {
            $data['partners'] = Partner::searchBy(['where' => $where, 'per_page' => $data['per_page']]);
        } else {
            $data['partners'] = Partner::searchBy(['per_page' => $data['per_page']]);
        }
        //End tìm kiếm
        $data_search = [
            'name'           => '',
            'status'         => '',
            'field'          => '',
            'partner_search' => '',
        ];

        $data['data_search'] = $request->data_search ?: $data_search;

        $data['data_search']['name'] = $request->name;

        if ($request->data_search['status'] !== null) {
            $data['data_search']['status'] = (int) $request->data_search['status'];
        }

        if ($request->data_search['field'] !== null) {
            $data['data_search']['field'] = $request->data_search['field'];

            if ($request->data_search['partner_search'] !== null) {
                $data['data_search']['partner_search'] = $request->data_search['partner_search'];
            }
        }

        if (!empty($request->data_search['partner_search']) && empty($request->data_search['field'])) {
            $data['data_search']['partner_search'] = '';
        }

        return view('backend.partners.index', $data);
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

        $partner = $id ? Partner::findOrFail($id) : new Partner();
        $this->authorize('view', $partner);

        $data['partner'] = $partner;
        $data['branches'] = $data['partner']->branches()->paginate(20);
        $data['cities']   = City::all();

        return view('backend.partners.edit_add', $data);
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
        $partner = $id ? Partner::findOrFail($id) : new Partner;
        $this->authorize('update', $partner);
        $rules = [
            'name'           => 'required',
            'company_name'   => 'required',
            'city'           => 'required',
            'district'       => 'required',
            'address'        => 'required',
            'representative' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages(), $this->attributes());
        $errors    = $validator->messages();

        if ($errors->toArray()) {
            return back()->with(['errors' => $errors])->withInput();
        }

        if (!$request->has('_validate')) {
            $data           = $request->all();
            $data['status'] = $request->input('status', 0);

            $params = [
                'id'        => $id,
                'user_id'   => 1,
                'user_name' => 'Admin',
            ];

            $data = array_merge($data, $params);

            $partner->fill($data);
            $partner->save();

            return redirect(url('/admin/partners'))->with('success', 'Cập nhật đối tác thành công!');
        }
    }

    public function validator_add_branch(Request $request)
    {
        $rules = [
            'partner_id'     => 'required',
            'title'          => 'required',
            'address'        => 'required',
            'representative' => 'required',
            'district'       => 'required',
            'city'           => 'required',
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages(), $this->attributes());
        $errors    = $validator->messages();

        if ($errors->toArray()) {
            return response()->json(['error_branches' => $errors]);
        }
    }

    public function add_branch(Request $request)
    {
        $branch = new Branch;
        $this->authorize('update', $branch);
        $data   = $request->branch;
        $params = [
            'id'           => 0,
            'user_id'      => 1,
            'user_name'    => 'Admin',
            'partner_name' => $this->model->find($request->branch['partner_id'])->name,
        ];

        $data   = array_merge($data, $params);
        
        $branch->fill($data);
        $branch->save();

        return redirect('/admin/partners/edit/' . $request->branch['partner_id'] . $request->hashtag)->with('success', 'Thêm mới chi nhánh thành công!');
    }

    public function ajax_address(Request $request)
    {
        $where[] = ['city_code', '=', $request->city];
        $keyword = $request->input('search', '');
        if ($keyword) {
            $where[] = ['name', 'like', '%' . $keyword . '%'];
        }
        $district = District::where($where)->get();

        return response()->json($district);
    }

    public function ajax_show_branch(Request $request)
    {
        $partner  = $this->model->find($request->partner_id);
        $branches = $partner->branches()->paginate(20);

        return view('backend.partners.sub-views.branch', ['branches' => $branches]);
    }
}
