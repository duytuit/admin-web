<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Controller;
use App\Http\Requests\RoleRequest;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;

class RoleController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->model = new Role();
    }

    /**
     * Danh sách bản ghi
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
        $this->authorize('index', app(Role::class));
        // Phân trang
        $data['per_page'] = Cookie::get('per_page', 20);

        $data['keyword'] = $request->input('keyword', '');

        $where = [];

        if ($data['keyword']) {
            $where[] = ['name', 'like', '%' . $data['keyword'] . '%'];
        }

        $data['roles'] = Role::searchBy([
            'where'    => $where,
            'per_page' => $data['per_page'],
        ]);

        $data['heading']    = 'Nhóm quyền';
        $data['meta_title'] = "QL Nhóm quyền";

        return view('backend.roles.index', $data);
    }

    /**
     * Sửa bản ghi
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function edit(Request $request, $id = 0)
    {
        $this->authorize('view', app(Role::class));
        if ($id > 0) {
            $role = Role::findOrFail($id);
        } else {
            $role = new Role();
        }

        $data['id']   = $id;
        $data['now']  = Carbon::now();
        $data['role'] = $role;
        $data['list'] = Config::get('role');

        $data['heading']    = 'Nhóm quyền';
        $data['meta_title'] = "QL Nhóm quyền";

        return view('backend.roles.edit', $data);
    }

    /**
     * Lưu bản ghi
     *
     * @param  RoleRequest  $request
     * @param  int  $id
     * @return Response
     */
    public function save(RoleRequest $request, $id = 0)
    {
        $this->authorize('update', app(Role::class));
        $input = $request->all();

        $input['id']   = $id;
        foreach ($request->permissions as $value) {
            $permissions[$value] = true;
        }
        $input['permissions'] = $permissions;

        $role = Role::findOrNew($id);
        $role->fill($input)->save();

        $message = [
            'error'  => 0,
            'status' => 'success',
            'msg'    => 'Đã cập nhập thông tin',
        ];

        return redirect()->route('admin.roles.index')->with('message', $message);
    }
}
