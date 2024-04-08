<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\CustomerGroupResource;
use App\Models\CustomerGroup;
use Illuminate\Http\Request;
use Validator;

class CustomerGroupController extends Controller
{
    public function __construct()
    {
        $this->model = new CustomerGroup();
        // $user = \Auth::user();
    }

    /**
     * Undocumented function
     * Mô tả các field validate
     */
    public function attributes()
    {
        return [
            'name' => 'Tên nhóm khách hàng',
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

        $columns = (new CustomerGroup)->getTableColumns();

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

        $groups = CustomerGroup::select($select)
            ->where('status', 1)
            ->where($condition)
            ->orderByRaw($order_by)
            ->paginate($per_page);

        return CustomerGroupResource::collection($groups);
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
        $group = CustomerGroup::where('id', $id)->where('status', 1)->first();

        $data = [
            'data'            => $group,
            'customer_source' => route('api.v1.settings.show', ['type' => 'customer-source']),
        ];
        return new CustomerGroupResource($data);
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
        //phân quyền chỗ này

        //end check quyền
        $rules = [
            'name' => 'required',
        ];

        $input = $request->all();

        if ($request->isMethod('PATCH')) {
            foreach ($rules as $key => $value) {
                if (!array_key_exists($key, $input)) {
                    unset($rules[$key]);
                }
            }
        }

        $validator = Validator::make($input, $rules, [], $this->attributes());
        $errors    = $validator->messages();

        if ($errors->toArray()) {
            return response()->json($errors)->setStatusCode(400);
        }

        if (!$request->has('_validate')) {
            $id = (int) $request->id;
            $data['status'] = $request->input('status', 0);
            $params         = [
                'user_id'   => 1536638938,
                'name'      => $request->name,
                'criterion' => [],
            ];

            if (($request->address['city'] !== null) || ($request->address['district'] !== null)) {
                $params['criterion']['address'] = $request->address;
            }

            if (($request->birthday['from'] !== null) || ($request->birthday['to'] !== null)) {
                $params['criterion']['birthday'] = $request->birthday;
            }

            if (($request->status !== null)) {
                $params['criterion']['status'] = $request->status;
            }

            if (($request->cb_source !== null)) {
                $params['criterion']['cb_source'] = $request->cb_source;
            }

            if (($request->project !== null)) {
                $params['criterion']['project'] = $request->project;
            }

            $group = $id ? CustomerGroup::find($id) : new CustomerGroup;

            if ($id) {
                $params['cb_id'] = strtotime(date('d-m-Y H:i:s'));
            }

            $group->fill($params);
            $group->save();

            // xóa thành viên cũ
            $group->remove_customer();

            // add thành viên mới
            $group->add_customer();

            // $group = $this->show($group->id);

            return CustomreGroupResource::collection($group);
        }
    }
}
