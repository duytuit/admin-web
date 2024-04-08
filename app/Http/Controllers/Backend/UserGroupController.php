<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Controller;
use App\Models\BoUserGroup;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;

class UserGroupController extends Controller
{
    /**
     * Khởi tạo
     */
    public function __construct()
    {
        $this->model = new BoUserGroup();
        Carbon::setLocale('vi');
    }

    /**
     * Danh sách bản ghi
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
        $this->authorize('index', app(BoUserGroup::class));
        // Phân trang
        $data['per_page'] = Cookie::get('per_page', 20);

        // Tìm kiếm nâng cao
        $data['keyword'] = $request->input('keyword', '');
        $data['code']    = $request->input('code', '');
        $data['status']  = $request->input('status', '');

        $where = [];

        if ($data['keyword']) {
            $where[] = ['gb_title', 'like', '%' . $data['keyword'] . '%'];
        }

        if ($data['code']) {
            $where[] = ['gb_code', '=', $data['code']];
        }

        if ($data['status'] != '') {
            $where[] = ['ub_status', '=', $data['status']];
        }

        // Phòng ban
        $data['groups'] = BoUserGroup::searchBy([
            'where'    => $where,
            'per_page' => $data['per_page'],
            'order_by' => 'gb_title ASC',
        ]);

        $data['meta_title'] = "QL Phòng ban";

        return view('backend.user-groups.index', $data);
    }
}
