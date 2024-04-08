<?php

namespace App\Http\Controllers\Backend;

use App\Models\CampaignAssign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use willvincent\Rateable\Rateable;

class SaleController extends Controller
{
    use Rateable;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->model = new CampaignAssign();
    }

    public function index(Request $request)
    {
        // Phân trang
        $data['per_page'] = Cookie::get('per_page', 20);

        // Tìm kiếm nâng cao
        $advance = 0;

        $data['name']     = $request->input('name', '');
        $data['phone']    = $request->input('phone', '');
        $data['email']    = $request->input('email', '');
        $data['campaign'] = $request->input('campaign', '');

        $where = [];

        if ($data['name']) {
            $where[] = ['customer_name', 'like', '%' . $data['name'] . '%'];
            $advance = 1;
        }

        if ($data['email']) {
            $where[] = ['customer_email', 'like', '%' . $data['email'] . '%'];
            $advance = 1;
        }

        if ($data['phone']) {
            $where[] = ['customer_phone', 'like', '%' . $data['phone'] . '%'];
            $advance = 1;
        }

        $user = \Auth::user();
        if ($user->username == 'ADMIN@DXMB') {
            $hasRole = true;
        } else {
            $hasRole = false;
        }

        if (!$hasRole) {
            $where[]  = ['staff_id', '=', $user->uid];
        }

        $customers = CampaignAssign::searchBy([
            'where'    => $where,
            'per_page' => $data['per_page'],
        ]);

        $customers->load('campaign', 'staff');

        $data['customers'] = $customers;

        $data['heading']    = 'Khách hàng';
        $data['meta_title'] = "NVKD > Khách hàng";

        $data['advance'] = $advance;

        return view('backend.sales.index', $data);
    }

    public function add(Request $request, $id)
    {
        $data['id'] = $id;

        $customer = CampaignAssign::findOrFail($id);

        $customer->load('campaign', 'user', 'staff');

        $data['heading']    = 'Khách hàng';
        $data['meta_title'] = "NVKD > Khách hàng";
        $data['customer']   = $customer;

        return view('backend.sales.add', $data);
    }

    public function save(Request $request)
    {
        # code...
    }

    public function diary(Request $request, $customer_id)
    {
        $data = [];
        return view('backend.sales.diary', $data);
    }

    /** */
    public function assign(Request $request)
    {
        // Phân trang
        $data['per_page'] = Cookie::get('per_page', 20);

        // Tìm kiếm nâng cao
        $advance = 0;

        $data['name']     = $request->input('name', '');
        $data['phone']    = $request->input('phone', '');
        $data['email']    = $request->input('email', '');
        $data['campaign'] = $request->input('campaign', '');

        $where = [];

        if ($data['name']) {
            $where[] = ['customer_name', 'like', '%' . $data['name'] . '%'];
            $advance = 1;
        }

        if ($data['email']) {
            $where[] = ['customer_email', 'like', '%' . $data['email'] . '%'];
            $advance = 1;
        }

        if ($data['phone']) {
            $where[] = ['customer_phone', 'like', '%' . $data['phone'] . '%'];
            $advance = 1;
        }

        $user = \Auth::user();
        if ($user->username == 'ADMIN@DXMB') {
            $hasRole = true;
        } else {
            $hasRole = false;
        }

        if (!$hasRole) {
            $where[]  = ['staff_id', '=', $user->uid];
        }

        $customers = CampaignAssign::searchBy([
            'where'    => $where,
            'per_page' => $data['per_page'],
        ]);

        $customers->load('campaign', 'staff');

        $data['customers'] = $customers;

        $data['heading']    = 'Phân bổ';
        $data['meta_title'] = "NVKD > Phân bổ";

        $data['advance'] = $advance;

        return view('backend.sales.assign', $data);
    }
}
