<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Controller;
use App\Models\BoCustomer;
use App\Models\BoUser;
use App\Models\CustomerGroup;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Validator;

class CustomerGroupController extends Controller
{
    /**
     * Constructor.
     */
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

    public function index(Request $request)
    {
        $data['meta_title'] = "QL Khách Hàng";

        $this->authorize('index', app(CustomerGroup::class));

        // End setting tìm kiếm
        $per_page         = Cookie::get('per_page', 20);
        $data['per_page'] = $per_page;

        // Tìm kiếm
        $wheres = [];
        if (!empty($request->name)) {
            $wheres[] = ['name', 'Like', "%{$request->name}%"];
        }

        if (!empty($request->user_id)) {
            $wheres[] = ['user_id', '=', $request->user_id];
        }

        if ($request->status !== null) {
            $wheres[] = ['status', '=', $request->status];
        }

        if ($wheres) {
            $groups = CustomerGroup::searchBy(['wheres' => $wheres, 'per_page' => $per_page]);
        } else {
            $groups = CustomerGroup::searchBy(['per_page' => $data['per_page']]);
        }
        $groups->load('user');
        //End tìm kiếm
        $data_search = [
            'name'    => '',
            'status'  => '',
            'user_id' => '',
        ];

        $data['data_search'] = $request->data_search ?: $data_search;

        $data['data_search']['name'] = $request->name;

        if ($request->status !== null) {
            $data['data_search']['status'] = $request->status;
        }

        if (!empty($request->user_id)) {
            $data['data_search']['user']['id']   = $request->user_id;
            $data['data_search']['user']['name'] = BoUser::where('ub_id', $request->user_id)->first()->ub_title;
        }

        $data['groups'] = $groups;

        return view('backend.customer-groups.index', $data);
    }

    public function edit($cb_id)
    {
        $data['meta_title'] = "QL Nhóm khách Hàng";
        $this->authorize('view', app(CustomerGroup::class));

        if ($cb_id > 0) {
            $group = CustomerGroup::findById($cb_id);
        } else {
            $group = new CustomerGroup;
        }


        $data['customer_source'] = Setting::config_get('customer-source');
        $data['group']           = $group;
        $data['id']              = $cb_id;

        return view('backend.customer-groups.edit_add', $data);
    }

    public function save(Request $request, $cb_id = 0)
    {
        //phân quyền chỗ này
        $this->authorize('update', app(CustomerGroup::class));

        //end check quyền
        $rules = [
            'name' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules, [], $this->attributes());
        $errors    = $validator->messages();

        if ($errors->toArray()) {
            return back()->with(['errors' => $errors])->withInput();
        }

        if (!$request->has('_validate')) {
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

            $group = $cb_id ? CustomerGroup::findById($cb_id) : new CustomerGroup;

            if ($cb_id == 0) {
                $params['cb_id'] = strtotime(date('d-m-Y H:i:s'));
            }

            $group->fill($params);
            $group->save();


            // xóa thành viên cũ
            $group->remove_customer();

            // add thành viên mới
            $group->add_customer();
            $data_cri = $group->criterion;
            $count = $group->getCustomer()->count();
            $data_cri+= ['count'=>$count];
            CustomerGroup::where('id',$group->id)->update(['criterion'=>json_encode($data_cri)]);

            return redirect(url('/admin/customer-groups'))->with('success', 'Thêm nhóm khách hàng thành công!');
        }
    }

    public function delete_customer(Request $request)
    {
        $customer_id = $request->input('ids', []);
        $this->authorize('delete.customer', app(CustomerGroup::class));

        // chuyển sang kiểu array
        if (!is_array($customer_id)) {
            $customer_id = [$customer_id];
        }

        $group = $request->input('group', 0);

        foreach ($customer_id as $id) {
            $customer = BoCustomer::findById($id);
            $group_id = $customer->group_id;

            foreach ($group_id as $key => $value) {
                if ($group == $value) {
                    unset($group_id[$key]);
                }
            }
            $param = [
                'group_id' => $group_id,
            ];
            $customer->fill($param);
            $customer->save();
        }

        $group = CustomerGroup::find($group)->cb_id;

        $message = [
            'error'  => 0,
            'status' => 'success',
            'msg'    => "Đã xóa " . count($customer_id) . " khách hàng thành công!",
        ];

        if ($request->ajax()) {
            return response()->json($message);
        } else {
            return redirect('/admin/customer-groups/edit/' . $group . $request->hashtag_group)->with('message', $message);
        }
    }

}
