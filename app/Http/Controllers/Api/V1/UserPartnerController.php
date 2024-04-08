<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\UserPartner;
use App\Http\Resources\UserPartnerResource;
use Validator;

class UserPartnerController extends Controller
{
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
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $per_page = $request->input('per_page', 10);
        $per_page = $per_page > 0 ? $per_page : 10;

        $columns = (new UserPartner)->getTableColumns();

        $unset = ['user_id', 'ub_token', 'deleted_at'];

        $allowFields = [];
        foreach ($columns as $column) {
            if (!in_array($column, $unset)) {
                $allowFields[] = $column;
            }
        }

        $select    = $this->_select($request, $allowFields);
        $condition = $this->_filters($request, $columns);
        $order_by  = $this->_sort($request, $columns);

        $user_partners = UserPartner::select($select)
            ->where('status', 1)
            ->where($condition)
            ->orderByRaw($order_by)
            ->paginate($per_page);

        $data = [
            'data'    => $user_partners,
            'partner' => route('api.v1.partners.index'),
        ];

        // return response()->json($data, 200, [], JSON_UNESCAPED_SLASHES);
        return new UserPartnerResource($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $id = (int) $request->id;
        try {
            $user_partner = UserPartner::where('id', $id)->where('status', 1)->first();

            $data = [
                'data'    => $user_partner,
                'partner' => route('api.v1.partners.index'),
            ];
            return new UserPartnerResource($data);

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
        $id    = (int) $request->id;
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

        $input = $request->all();

        if ($request->isMethod('PATCH')) {
            foreach ($rules as $key => $value) {
                if (!array_key_exists($key, $input)) {
                    unset($rules[$key]);
                }
            }
        }

        $validator = Validator::make($input, $rules, $this->messages(), $this->attributes());
        $errors    = $validator->messages();

        $partner = Partner::find($request->partner_id);
        if ($partner) {
            $errors->add('partner_id', 'Đối tác không tồn tại');
        }
        if ($errors->toArray()) {
            return response()->json($errors, 400);
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

                return response()->json($user_partner, 200);
                // return $this->show($user_partner->id);
            } else {
                $errors->add('chane_pass', 'Xác nhận mật khẩu không chính xác!');
                return response()->json($errors, 400);
            }
        }
    }
}
