<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Controller;
use App\Models\Article;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Excel;

class EventController extends Controller
{
    /**
     * @var App\Models\Article
     */
    protected $article;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->model = new Event();
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
        $data['check_in'] = $request->input('check_in', '');

        $where = [];

        $where[] = ['article_id', '=', $id];

        if ($data['keyword']) {
            //$where[] = ['title', 'like', '%' . $data['keyword'] . '%'];
        }

        if ($data['check_in'] === '0') {
            $where[] = ['check_in', '=', null];
            $advance = 1;
        }

        if ($data['check_in'] === '1') {
            $where[] = ['check_in', '<>', null];
            $advance = 1;
        }

        $events = Event::searchBy([
            'where'    => $where,
            'per_page' => $data['per_page'],
        ]);

        $events->load('user');

        $data['meta_title'] = 'KQ Sự kiện';
        $data['events']     = $events;
        $data['article']    = $article;
        $data['advance']    = $advance;

        return view('backend.events.index', $data);
    }

    public function export(Request $request)
    {
        $where = [];

        $where[] = ['article_id', '=', $request->id];
        $article = Article::findOrFail($request->id);

        if ($request['keyword']) {
            $where[] = ['title', 'like', '%' . $data['keyword'] . '%'];
        }

        if ($request->check_in === '0') {
            $where[] = ['check_in', '=', null];
            $advance = 1;
        }

        if ($request->check_in === '1') {
            $where[] = ['check_in', '<>', null];
            $advance = 1;
        }

        $events = Event::where($where)->get();

        $events->load('user');

        try {
            $result = Excel::create('Danh_sach_kh_tham_gia_su_kien', function ($excel) use ($events, $article) {
                $excel->setTitle('Danh sách khách hàng');
                $excel->sheet('Danh sách khách hàng', function ($sheet) use ($events, $article) {
                    $new_customers = [];
                    foreach ($events as $key => $event) {
                        $user = $event->user;
                        
                        $new_customers[] = [
                            'STT'               => $key + 1,
                            'Họ tên Khách hàng' => $event->user_type == 'customer'? $user->ub_title : $user->cb_name,
                            'TK tavico'         => $event->user_type == 'customer'? $user->ub_account_tvc : '',
                            'SĐT'               => $event->user_type == 'customer'? $user->ub_phone : $user->cb_phone,
                            'Email'             => $event->user_type == 'customer'? $user->ub_email : $user->cb_email,
                            'Check in'          => $event->check_in ? date('d-m-Y H:i', strtotime($event->check_in)) : '',
                        ];
                    }
                    $sheet->setAutoSize(true);

                    // data of excel
                    if ($new_customers) {
                        $sheet->fromArray($new_customers);
                    }
                    // add header
                    $sheet->prependRow(1, ["Danh sách khách hàng tham gia sự kiện `{$article->title}`"]);
                    $sheet->mergeCells("A1:F1");
                    $sheet->cell('A1', function($cell) {
                        // change header color
                        $cell->setFontColor('#000000')
                            ->setFontWeight('bold')
                            ->setFontSize(10)
                            ->setAlignment('center')
                            ->setValignment('center');
                    });
                });
            })->store('xlsx',storage_path('exports/'));
$file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
return response()->download($file)->deleteFileAfterSend(true);
             
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
