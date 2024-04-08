<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\ExchangeResource;
use App\Models\City;
use App\Models\District;
use App\Models\Exchange;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use JWTAuth;
use Validator;

class ExchangeController extends Controller
{
    /**
     * Constructor.
     */
    protected $user;

    public function __construct()
    {
        $this->model = new Exchange();
        $this->user  = Auth::user();
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
            'name'     => 'Tên địa điểm giao dịch',
            'city'     => 'Tỉnh/ Thành phố',
            'district' => 'Quận/ Huyện',
            'address'  => 'Địa chỉ chi tiết',
            'hotline'  => 'Hotline',
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user     = JWTAuth::toUser($request->token);
        $per_page = $request->input('per_page', 10);
        $per_page = $per_page > 0 ? $per_page : 10;

        $columns = (new Exchange)->getTableColumns();

        $unset = ['user_id', 'deleted_at'];

        $allowFields = [];
        foreach ($columns as $column) {
            if (!in_array($column, $unset)) {
                $allowFields[] = $column;
            }
        }
        $where = [['status', '=', 1]];
        if ($user->app_id) {
            $where[] = ['app_id', '=', $this->user->app_id];
        }

        $select    = $this->_select($request, $allowFields);
        $condition = $this->_filters($request, $columns);
        $order_by  = $this->_sort($request, $columns);

        $exchanges = Exchange::select($select)
            ->where($where)
            ->where($condition)
            ->orderByRaw($order_by)
            ->paginate($per_page);

        return ExchangeResource::collection($exchanges);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $id = (int) $request->id;

        $where = [
            ['id', '=', $id],
            ['status', '=', 1],
        ];
        if ($this->user->app_id) {
            $where[] = ['app_id', '=', $this->user->app_id];
        }

        try {
            $exchange = Exchange::where($where)->first();
            return new ExchangeResource($exchange);

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
        $rules = [
            'hotline'  => 'required',
            'name'     => 'required',
            'city'     => 'required',
            'district' => 'required',
            'address'  => 'required',
        ];

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

        $city = City::where('code', $request->city)->get();
        if (!$city) {
            $errors->add('city', 'Tỉnh/Thành phố không tồn tại');

        }
        $district = District::where('code', $request->district)->get();
        if (!$district) {
            $errors->add('district', 'Quận/Huyện không tồn tại');
        }

        if ($errors->toArray()) {
            return response()->json($errors);
        }

        if (!$request->has('_validate')) {
            $data = $request->only('hotline', 'name', 'city', 'district', 'address', 'status');
            $id   = (int) $request->id;

            $data['status'] = $request->input('status', 0);

            if (!$id) {
                $params = [
                    'cb_id'   => strtotime(date('Y-m-d H:i:s')),
                    'user_id' => $this->user->id,
                    'app_id'  => $this->user->app_id,
                ];

                $data = array_merge($data, $params);
            }

            $exchange = $id ? Exchange::findById($id) : new Exchange;

            $updated = $exchange->fill($data)->save();

            if ($updated) {
                return response()->json([
                    'success' => true,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Xin lỗi! Cập nhật đối tác thất bại.',
                ], 500);
            }
        }
    }
}
