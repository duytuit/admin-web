<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\BoCustomerResource;
use App\Models\BoCustomer;
use App\Models\CustomerGroup;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class BoCustomerController extends Controller
{
    /**
     * Constructor.
     */

    protected $user;

    public function __construct()
    {
        $this->model = new BoCustomer();
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
            'cb_name'        => 'Tên khách hàng',
            'cb_phone'       => 'Số điện thoại',
            'cd_customer_id' => 'Khách hàng',
            'cb_id_passport' => 'Số CMND/Hộ chiếu',
            'cd_rating'      => 'Điểm số',
            'project_id'     => 'Dự án',
            'import_file'    => 'File tải lên',
        ];
    }
    /**
     * Lấy danh sách khách hàng từ BO.
     *
     * Tìm kiếm
     * @param $request
     * - select: field muốn lấy (VD: id,name)
     * - cb_name: Tên khách hàng,
     * - cb_staff_id: id phụ của nhân viên được phân bổ
     * - project_id: id phụ của dự án
     * - cb_source: nguồn khách hàng (bo, sale, marketing, khac)
     * - status: trạng thái (0 or 1)
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $per_page = $request->input('per_page', 10);
        $per_page = $per_page > 0 ? $per_page : 10;

        $columns = (new BoCustomer)->getTableColumns();

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

        $bo_customers = BoCustomer::select($select)
            ->where($where)
            ->where($condition)
            ->orderByRaw($order_by)
            ->paginate($per_page);

        foreach ($bo_customers as $key => $customer) {
            $bo_customers[$key] = $this->formatKey($customer->toArray(), 'cb_', ['cb_id']);
        }

        return BoCustomerResource::collection($bo_customers);
    }

    /**
     * Lấy thông tin chi tiết của khách hàng.
     *
     * @param  int  $cb_id
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
            $customer = BoCustomer::where($where)->first();
            $customer = $this->formatKey($customer->toArray(), 'cb_', ['cb_id']);

            $data = [
                'data'            => $customer,
                'customer_source' => route('api.v1.settings.show', ['type' => 'customer-source']),
                'diaries'         => route('api.v1.bo_customers.diaries', ['id' => $id]),
            ];
            return new BoCustomerResource($data);

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
     * Thêm mới hoặc cập nhật thông tin khách hàng.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $cb_id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $id    = (int) $request->id;
        $rules = [
            'cb_name'  => 'required',
            'cb_phone' => 'required',
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

        // Kiểm tra khách hàng đã tồn tại chưa
        $customer = BoCustomer::find($id);

        $customer = BoCustomer::is_exist(['cb_phone' => $request->cb_phone, 'project_id' => $request->project_id, 'cb_id' => $customer ? $customer->cb_id : '']);
        if ($customer === true) {
            $errors->add('is_customer', 'Khách hàng này đã có trên hệ thống.');
        }

        if ($errors->toArray()) {
            return response()->json(['error' => $errors])->setStatusCode(401);
        }

        if (!$request->has('_validate')) {

            $data     = $request->only('cb_name', 'birthday', 'cb_phone', 'cb_email', 'cb_id_passport', 'cmnd_date', 'issued_by', 'city', 'district', 'address', 'cb_staff_id[]', 'project_id', 'cb_source', 'status');
            $customer = $customer ?: new BoCustomer();
            $params   = [
                'id'            => $customer ? $customer->id : 0,
                'birthday'      => date('Y-m-d', strtotime($request->birthday)),
                'cb_staff_id'   => collect($request->cb_staff_id)->implode(','),
                'cmnd_date'     => date('Y-m-d', strtotime($request->birthday)),
                'tc_created_by' => \Auth::user()->ub_id,

            ];

            if (!$id) {
                $params['cb_id'] = strtotime(date('Y-m-d H:i:s'));
            }

            $data = array_merge($data, $params);
            $customer->fill($data)->save();

            // Cập nhật lại danh sách thành viên nhóm
            CustomerGroup::updateAll();

            // return $this->show($customer->id);
            $customer = $this->formatKey($customer->toArray(), 'cb_', ['cb_id']);
            return response()->json(['data' => $customer]);
        }
    }
}
