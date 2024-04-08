<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Controller;
use App\Models\Article;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class VoucherController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->model = new Voucher();

        Carbon::setLocale('vi');
    }

    /**
     * Danh sách bản ghi
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $id)
    {
        $article = Article::findOrFail($id);

        // Phân trang
        $data['per_page'] = Cookie::get('per_page', 20);

        // Tìm kiếm nâng cao
        $advance = 0;

        $data['type']     = $request->input('type', 'article');
        $data['keyword']  = $request->input('keyword', '');
        $data['phone']    = $request->input('phone', '');
        $data['email']    = $request->input('email', '');
        $data['check_in'] = $request->input('check_in', '');

        $where = [];

        $where[] = ['article_id', '=', $id];

        if ($data['check_in'] === '0') {
            $where[] = ['check_in', '=', null];
            $advance = 1;
        }

        if ($data['check_in'] === '1') {
            $where[] = ['check_in', '<>', null];
            $advance = 1;
        }

        $vouchers = Voucher::where($where);

        if ($data['keyword'] || $data['phone'] || $data['email']) {
            $advance = 1;

            $vouchers->whereHas('user', function ($query) use ($data) {
                $table = $query->getModel()->getTable();

                if ($data['keyword']) {
                    if ($table == 'bo_users') {
                        $query->where('ub_title', 'like', '%' . $data['keyword'] . '%');
                    } else {
                        $query->where('cb_name', 'like', '%' . $data['keyword'] . '%');
                    }
                }

                if ($data['phone']) {
                    if ($table == 'bo_users') {
                        $query->where('ub_phone', 'like', '%' . $data['ub_phone'] . '%');
                    } else {
                        $query->where('cb_phone', 'like', '%' . $data['cb_phone'] . '%');
                    }
                }

                if ($data['email']) {
                    if ($table == 'bo_users') {
                        $query->where('ub_email', 'like', '%' . $data['ub_email'] . '%');
                    } else {
                        $query->where('cb_email', 'like', '%' . $data['cb_email'] . '%');
                    }
                }
            });
        }

        $vouchers = $vouchers->orderByRaw('id DESC')->paginate($data['per_page']);

        $vouchers->load('user');

        $data['meta_title'] = 'KQ Khuyến mãi';
        $data['vouchers']   = $vouchers;
        $data['article']    = $article;
        $data['advance']    = $advance;

        return view('backend.vouchers.index', $data);
    }
}
