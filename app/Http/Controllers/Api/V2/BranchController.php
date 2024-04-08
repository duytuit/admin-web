<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use App\Models\Partner;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class BranchController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->model = new Branch();
        $this->user  = Auth::user();
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
     * Lấy danh sách chi nhánh.
     *
     * @param $title : tên chi nhánh
     * @param $status :0 or 1
     * @param $partner_id :id đối tác
     * @param $hotline
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $per_page = $request->input('per_page', 10);
        $per_page = $per_page > 0 ? $per_page : 10;

        $columns = (new Branch)->getTableColumns();

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

        $where = [['status', '=', 1]];
        if ($this->user->app_id) {
            $where[] = ['app_id', '=', $this->user->app_id];
        }

        $branches = Branch::select($select)
            ->where($where)
            ->where($condition)
            ->orderByRaw($order_by)
            ->paginate($per_page);

        $data = [
            'status'  => true,
            'code'    => 200,
            'data'    => $branches,
            'partner' => route('api.v1.partners.index'),
        ];

        // return response()->json($data, 200, [], JSON_UNESCAPED_SLASHES);
        return new BranchResource($data);
    }

    /**
     * Lấy thông tin chi tiết chi nhánh.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $id = (int) $request->id;

        $where = [
            ['status', '=', 1],
            ['id', '=', $id],
        ];
        if ($this->user->app_id) {
            $where[] = ['app_id', '=', $this->user->app_id];
        }

        try {
            $branch   = Branch::where($where)->first();
            $partners = route('api.v1.partners.index');
            $cities   = route('api.v1.address.city');

            $data = [
                'data'     => $branch ?: [],
                'partners' => $partners,
                'city'     => $cities,
            ];
            return new BranchResource($data);
            // return response()->json($data, 200, [], JSON_UNESCAPED_SLASHES);

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
     *Cập nhật thông tin chi nhánh.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $id    = (int) $request->id;
        $rules = [
            'partner_id'     => 'required',
            'title'          => 'required',
            'address'        => 'required',
            'city'           => 'required',
            'district'       => 'required',
            'representative' => 'required',
        ];

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

        if ($errors->toArray()) {
            return response()->json($errors, 402);
        }

        if (!$request->has('_validate')) {

            $data           = $request->only('partner_id', 'title', 'city', 'district', 'address', 'representative', 'info', 'hotline', 'status');
            $data['status'] = $request->input('status', 0);
            $partner        = Partner::findOrFail($request->partner_id);
            $params         = [
                'id'           => $id,
                'user_id'      => 1,
                'user_name'    => 'Admin',
                'partner_name' => $partner->name,
                'app_id'       => $this->user->app_id,
            ];
            $data    = array_merge($data, $params);
            $branch  = $id ? Branch::findOrFail($id) : new Branch();
            $updated = $branch->fill($data)->save();

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
