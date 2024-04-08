<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Controller;
use App\Models\Feedback;
use App\Repositories\Feedback\FeedbackRespository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class FeedbackController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        die('xx');
        $this->model = new Feedback();
    }

    /**
     * Danh sách bản ghi
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
        // Phân trang
        $data['per_page'] = Cookie::get('per_page', 20);
        $this->authorize('index', app(Feedback::class));

        // Tìm kiếm nâng cao
        $advance = 0;

        $data['keyword'] = $request->input('keyword', '');
        $data['type']    = $request->input('type', '');
        $data['rating']  = $request->input('rating', '');
        $data['status']  = $request->input('status', '');
        $data['name']    = $request->input('name', '');

        $where = [];

        if ($data['keyword']) {
            $where[] = ['title', 'like', '%' . $data['keyword'] . '%'];
            $advance = 1;
        }

        if ($data['type']) {
            $where[] = ['type', '=', $data['type']];
            $advance = 1;
        }

        if ($data['rating']) {
            $where[] = ['rating', '=', $data['rating']];
            $advance = 1;
        }

        if ($data['status'] != '') {
            $where[] = ['status', '=', $data['status']];
            $advance = 1;
        }

        $feedback = Feedback::where($where);

        if ($data['name']) {
            $advance = 1;

            $feedback->whereHas('customer', function ($query) use ($data) {
                $query->where('cb_name', 'like', '%' . $data['name'] . '%');
            });
        }

        $feedback = $feedback->orderByRaw('id DESC')->paginate($data['per_page']);

        $feedback->load('customer');

        $data['feedback'] = $feedback;

        $data['heading']    = 'Ý kiến phản hồi';
        $data['meta_title'] = "QL Ý kiến phản hồi";

        $data['types'] = [
            'user'    => 'Nhân viên',
            'product' => 'Sản phẩm',
            'service' => 'Dịch vụ',
            'other'   => 'Khác',
        ];
        $data['advance'] = $advance;

        return view('backend.feedback.index', $data);
    }

    /**
     * Xem bản ghi
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function detail(Request $request, $id = 0)
    {
        $feedback = Feedback::findOrFail($id);
        $this->authorize('view', $feedback);

        $feedback->load('comments', 'comments.user', 'comments.comments');

        $data['id']       = $id;
        $data['now']      = Carbon::now();
        $data['feedback'] = $feedback;
        $data['colors']   = ['#008a00', '#0050ef', '#6a00ff', '#a20025', '#fa6800', '#825a2c', '#6d8764'];

        $data['types'] = [
            'user'    => 'Nhân viên',
            'product' => 'Sản phẩm',
            'service' => 'Dịch vụ',
            'other'   => 'Khác',
        ];

        $data['meta_title'] = "QL Ý kiến phản hồi";

        return view('backend.feedback.detail', $data);
    }

    public function repairApartment(Request $request)
    {
        die('xx');
        // Phân trang
        $data['per_page'] = Cookie::get('per_page', 20);
        $this->authorize('index', app(Feedback::class));

        // Tìm kiếm nâng cao
        $advance = 0;

        $data['keyword'] = $request->input('keyword', '');
        $data['type']    = $request->input('type', '');
        $data['rating']  = $request->input('rating', '');
        $data['status']  = $request->input('status', '');
        $data['name']    = $request->input('name', '');

        $where = [];

        if ($data['keyword']) {
            $where[] = ['title', 'like', '%' . $data['keyword'] . '%'];
            $advance = 1;
        }

        $where[] = ['type', '=', FeedbackRespository::TYPE_REPAIR_APARTMENT];

        if ($data['rating']) {
            $where[] = ['rating', '=', $data['rating']];
            $advance = 1;
        }

        if ($data['status'] != '') {
            $where[] = ['status', '=', $data['status']];
            $advance = 1;
        }

        $feedback = Feedback::where($where);

        if ($data['name']) {
            $advance = 1;

            $feedback->whereHas('customer', function ($query) use ($data) {
                $query->where('cb_name', 'like', '%' . $data['name'] . '%');
            });
        }

        $feedback = $feedback->orderByRaw('id DESC')->paginate($data['per_page']);

        $feedback->load('customer');

        $data['feedback'] = $feedback;

        $data['heading']    = 'Quản lý sửa chữa căn hộ';
        $data['meta_title'] = "Quản lý sửa chữa căn hộ";

        $data['types'] = [
            'user'    => 'Nhân viên',
            'product' => 'Sản phẩm',
            'service' => 'Dịch vụ',
            'other'   => 'Khác',
        ];

        $data['advance'] = $advance;

        return view('backend.feedback.repair-apartment', $data);
    }
}
