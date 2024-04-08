<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Controller;
use App\Models\BoUser;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\UserPartner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleUserController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->model = new RoleUser();
    }

    /**
     * Phân quyền
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function edit(Request $request, $id = 0)
    {
        // Nhóm quyền
        $role = Role::findOrFail($id);
        $role->load('users', 'users.group');

        $data['id']         = $id;
        $data['role']       = $role;
        $data['heading']    = 'Nhóm quyền';
        $data['meta_title'] = "QL Nhóm quyền";

        return view('backend.role-users.edit', $data);
    }

    public function add(Request $request, $id = 0)
    {
        $this->authorize('approve', app(RoleUser::class));
        $user_id   = $request->input('user_id');
        $user_type = $request->input('user_type');
        $tap       = $request->input('tap');

        RoleUser::where('role_id', $id)
            ->where('user_id', $user_id)
            ->where('user_type', $user_type)
            ->delete();

        RoleUser::create([
            'role_id'   => $id,
            'user_id'   => $user_id,
            'user_type' => $user_type,
        ]);
    }

    public function search(Request $request)
    {
        $keyword   = $request->input('keyword', '');
        $user_type = $request->input('user_type', 'user');

        $users = [];

        if ($user_type == 'user') {
            $select = DB::raw("id, 'user' AS user_type, ub_title AS full_name, ub_account_tvc AS user_name, ub_phone AS phone, ub_email AS email");

            $users = BoUser::select($select)
                ->orWhere('ub_account_tvc', 'like', '%' . $keyword . '%')
                ->orWhere('ub_title', 'like', '%' . $keyword . '%')
                ->orWhere('ub_phone', 'like', '%' . $keyword . '%')
                ->orWhere('ub_email', 'like', '%' . $keyword . '%')
                ->paginate(10);
        }

        if ($user_type == 'partner') {
            $select = DB::raw("id, 'partner' AS user_type, full_name,'' AS user_name, phone, email");

            $users = UserPartner::select($select)
                ->orWhere('full_name', 'like', '%' . $keyword . '%')
                ->orWhere('phone', 'like', '%' . $keyword . '%')
                ->orWhere('email', 'like', '%' . $keyword . '%')
                ->paginate(10);
        }

        return response()->json($users);
    }

    /**
     * Thực hiện tác vụ
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function action(Request $request, $id = 0)
    {
        $method = $request->input('method', '');

        if ($method == 'status') {
            return $this->status($request, $id);
        } elseif ($method == 'per_page') {
            return $this->per_page($request);
        }

        return back();
    }

    /**
     * Phân quyền
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function status(Request $request, $id = 0)
    {
        $ids    = $request->input('ids', []);
        $status = $request->input('status', 0);

        // chuyển sang kiểu array
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $list  = [];
        $input = [];
        foreach ($ids as $user_id) {
            $list[] = (int) $user_id;

            $input[] = [
                'role_id' => $id,
                'user_id' => (int) $user_id,
            ];
        }

        $count = count($list);

        if ($count && ($status != '')) {
            // Xóa quyền
            DB::table('role_users')->whereIn('user_id', $list)->delete();

            // Cấp quyền
            if ($status) {
                DB::table('role_users')->insert($input);
            }
        }

        $message = [
            'error'  => 0,
            'status' => 'success',
            'msg'    => "Đã cấp quyền {$count} thành viên",
        ];

        return back()->with('message', $message);
    }
}
