<?php

namespace App\Http\Controllers\Backend;

use App\Models\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use App\Http\Controllers\BuildingController as BaseController;
// use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Cookie;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Model tương ứng Controller CRUD
     *
     */
    protected $model;

    /**
     * Thực hiện tác vụ
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function action(Request $request)
    {
        $method = $request->input('method', '');
        if ($method == 'delete') {
            return $this->delete($request);
        } elseif ($method == 'status') {
            return $this->status($request);
        } elseif ($method == 'per_page') {
            return $this->per_page($request);
        } elseif ($method == 'assigned_staff') {
            return $this->assigned_staff($request);
        }
        return back();
    }

    /**
     * Xóa bản ghi
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $ids = $request->input('ids', []);

        // chuyển sang kiểu array
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $list = [];
        foreach ($ids as $id) {
            $list[] = (int) $id;
        }

        $number = $this->model->destroy($list);

        $message = [
            'error'  => 0,
            'status' => 'success',
            'msg'    => "Đã xóa $number bản ghi!",
        ];

        if ($request->ajax()) {
            return response()->json($message);
        } else {
            return back()->with('message', $message);
        }
    }

    /**
     * Thay đổi trạng thái
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function status(Request $request)
    {
        $ids    = $request->input('ids', []);
        $status = $request->input('status', 1);

        // convert to array
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $list = [];
        foreach ($ids as $id) {
            $list[] = (int) $id;
        }

        $this->model->whereIn('id', (array) $list)->update(['status' => (int) $status]);

        $message = [
            'error'  => 0,
            'status' => 'success',
            'msg'    => 'Đã cập nhật trạng thái!',
        ];

        if ($request->ajax()) {
            return response()->json($message);
        } else {
            return back()->with('message', $message);
        }
    }

    /**
     * Phân trang
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function per_page(Request $request)
    {
        $per_page = $request->input('per_page', 20);

        Cookie::queue('per_page', $per_page, 60 * 24 * 30);

        return back();
    }

    /**
     * Thay đổi trạng thái
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function assigned_staff(Request $request)
    {
        $ids      = $request->input('ids', []);
        $staff_id = $request->input('cb_staff_ids', []);

        // convert to array
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $list = [];
        foreach ($ids as $id) {
            $list[] = (int) $id;
        }

        $this->model->whereIn('id', (array) $list)->update(['cb_staff_id' => collect($staff_id)->implode(',')]);

        $message = [
            'error'  => 0,
            'status' => 'success',
            'msg'    => 'Đã phân bổ khách hàng thành công!',
        ];

        if ($request->ajax()) {
            return response()->json($message);
        } else {
            return back()->with('message', $message);
        }
    }

}