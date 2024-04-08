<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\PartnerResource;
use App\Models\Partner;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class PartnerController extends Controller
{
    protected $model;

    /**
     * Constructor.
     */
    protected $user;

    public function __construct()
    {
        $this->model = new Partner();
        $this->user  = Auth::user();
    }

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
     * Lấy danh sách đối tác
     *
     * @param Request $request
     * - (string) name: Tên đối tác
     * - (int) status: trạng thái (0, 1),
     * - (string) field: ['company_name', 'hotline', 'user_name']
     * - (string) partner_search: Nôi dung tìm kiếm theo field
     * @return \Illuminate\Http\Response $partners
     */
    public function index(Request $request)
    {
        $per_page = $request->input('per_page', 10);
        $per_page = $per_page > 0 ? $per_page : 10;

        $columns = (new Partner)->getTableColumns();

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
        // if ($this->user->app_id) {
        //     $where[] = ['app_id', '=', $this->user->app_id];
        // }

        $partners = Partner::select($select)
            ->where($where)
            ->where($condition)
            ->orderByRaw($order_by)
            ->paginate($per_page);

        return PartnerResource::collection($partners);
    }

    /**
     * Lấy thông tin chi tiết đối tác.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $id    = (int) $request->id;
        $where = [
            ['status', '=', 1],
            ['id', '=', $id],
        ];
        if ($this->user->app_id) {
            $where[] = ['app_id', '=', $this->user->app_id];
        }
        try {
            $partner  = Partner::where($where)->first();
            $branches = route('api.v1.partners.branches', ['id' => $id]);
            $cities   = route('api.v1.address.city');

            $data = [
                'data'     => $partner ?: [],
                'branches' => $branches,
                'city'     => $cities,
            ];
            return new PartnerResource($data);
            // return response()->json($data, 200);

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
     * Lưu thông tin đối tác khi thêm mới/ chỉnh sửa.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id = 0 nếu là thêm mới
     * @param array $data : Thông tin đối tác
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $id    = (int) $request->id;
        $rules = [
            'name'           => 'required',
            'company_name'   => 'required',
            'city'           => 'required',
            'district'       => 'required',
            'address'        => 'required',
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
            return response()->json($errors);
        }

        $partner = $id ? Partner::findOrfail($id) : new Partner;

        if (!$request->has('_validate')) {
            $data = $request->only('name', 'company_name', 'city', 'district', 'address', 'representative', 'info', 'logo', 'hotline', 'status');

            $data['status'] = $request->input('status', 0);

            $params = [
                'id'        => $id,
                'user_id'   => $this->user->id,
                'user_name' => $this->user->name,
                'app_id'    => $this->user->app_id,
            ];

            $data    = array_merge($data, $params);
            $updated = $partner->fill($data)->save();

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

    /**
     * Lấy danh sách chi nhánh theo đối tác
     *
     * @param Request $request
     * @return response
     */
    public function branch_by_partner($id)
    {
        $partner  = Partner::findOrFail($id);
        $branches = $partner->branches;

        return response()->json($branches);
    }
}
