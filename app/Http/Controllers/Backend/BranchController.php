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

class BranchController extends Controller
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->model = new Branch();
    }

    public function messages()
    {
        return [
            'required' => ':attribute không được để trống',
        ];
    }

    /**
     * Undocumented function
     * Mô tả các field validate
     */
    public function attributes()
    {
        return [
            'partner_id'     => 'Đối tác',
            'title'          => 'Tên chi nhánh',
            'address'        => 'Địa chỉ chi tiết',
            'representative' => 'Người đại diện',
            'city'           => 'Tỉnh/ Thành phố',
            'district'       => 'Quận/ Huyện',
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data['meta_title'] = "QL chinh nhánh";

        //Phân quyền
        $this->authorize('index', app(Branch::class));

        $data['partners'] = Partner::all();

        $data['per_page'] = Cookie::get('per_page', 20);

        $where = [];
        if ($request->title != null) {
            $where[] = ['title', 'Like', "%{$request->title}%"];
        }

        if ($request->status !== null) {
            $where[] = ['status', '=', (int) $request->status];
        }

        if ($request->partner_id !== null) {
            $where[] = ['partner_id', '=', $request->partner_id];

            if ($request->branch_id !== null) {
                $where[] = ['id', '=', $request->branch_id];
            }
        }

        if ($request->hotline !== null) {
            $where[] = ['hotline', 'LIKE', "%{$request->hotline}%"];
        }

        if ($where) {
            $data['branches'] = Branch::searchBy(['where' => $where, 'per_page' => $data['per_page']]);
        } else {
            $data['branches'] = Branch::searchBy(['per_page' => $data['per_page']]);
        }

        $data['data_search'] = [
            'title'      => '',
            'status'     => '',
            'partner_id' => '',
            'branch'     => [],
            'hotline'    => '',
        ];

        if (!empty($request->title)) {
            $data['data_search']['title'] = $request->title;
        }

        if (!empty($request->status)) {
            $data['data_search']['status'] = $request->status;
        }

        if (!empty($request->partner_id)) {
            $data['data_search']['partner_id'] = $request->partner_id;

            if (!empty($request->branch_id)) {
                $data['data_search']['branch']['id']    = $request->branch_id;
                $data['data_search']['branch']['title'] = Branch::find($request->branch_id)->title;
            }
        }

        if (!empty($request->hotline)) {
            $data['data_search']['hotline'] = $request->hotline;
        }

        return view('backend.branches.index', $data);
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

        $branch = $id ? Branch::findOrFail($id) : new Branch();
        $this->authorize('view', $branch);

        $data['branch']   = $branch;
        $data['partners'] = Partner::all();
        $data['cities']   = City::all();
        $data['id']       = $id;
        return view('backend.branches.edit_add', $data);
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
        $branch = $id ? Branch::findOrFail($id) : new Branch();
        $this->authorize('update', $branch);

        $rules = [
            'partner_id'     => 'required',
            'title'          => 'required',
            'address'        => 'required',
            'city'           => 'required',
            'district'       => 'required',
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
            $partner        = Partner::findOrFail($request->partner_id);
            $params         = [
                'id'           => $id,
                'user_id'      => 1,
                'user_name'    => 'Admin',
                'partner_name' => $partner->name,
            ];
            $data = array_merge($data, $params);
            $branch->fill($data);
            $branch->save();

            return redirect(url('/admin/branches'))->with('success', 'Cập nhật đối tác thành công!');
        }
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @return void
     */
    public function search_partner_branch(Request $request)
    {
        $options = [];

        $options['select'] = ['id', 'title'];

        $keyword = $request->input('search', '');

        $options['where'][] = ['partner_id', '=', $request->partner_id];
        if ($keyword) {
            $options['where'][] = ['title', 'like', '%' . $keyword . '%'];
        }

        $branches = Branch::searchBy($options);

        return response()->json($branches);
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

}
