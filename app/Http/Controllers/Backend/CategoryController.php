<?php

namespace App\Http\Controllers\Backend;

use App\Commons\Helper;
use App\Http\Controllers\Backend\Controller;
use App\Http\Controllers\BuildingController;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Models\UrlAlias;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Maatwebsite\Excel\Facades\Excel;

class CategoryController extends BuildingController
{
    /**
     * Constructor.
     */
    public function __construct(Request $request)
    {
        $this->model = new Category();
        parent::__construct($request);
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

        // Tìm kiếm nâng cao
        $advance = 0;

        $data['type']    = $request->input('type', 'article');
        $data['status']  = $request->input('status', '');
        $data['keyword'] = $request->input('keyword', '');


        $where = [];

        if ($data['keyword']) {
            $where[] = ['title', 'like', '%' . $data['keyword'] . '%'];
            $advance = 1;
        }

        if ($data['type']) {
            $where[] = ['type', '=', $data['type']];
        }

        if ($data['status'] != '') {
            $where[] = ['status', '=', $data['status']];
            $advance = 1;
        }

        if($data['type'] !='article' && $data['type'] != 'event'){
            $where[] = ['bdc_building_id', '=', $this->building_active_id];
        }

        $data['categories'] = Category::searchBy([
            'where'    => $where,
            'per_page' => $data['per_page'],
        ]);
        $data['heading']    = Category::types[$data['type']];

        $data['meta_title'] = "QL {$data['heading']} > Danh mục";

        $data['advance'] = $advance;

        //dd($data['categories']->toArray());
        return view('backend.categories.index', $data);
    }
    public function indexEvent(Request $request)
    {

        // Phân trang
        $data['per_page'] = Cookie::get('per_page', 20);

        // Tìm kiếm nâng cao
        $advance = 0;
       
        $data['type']    = $request->input('type', 'event');
        $data['status']  = $request->input('status', '');
        $data['keyword'] = $request->input('keyword', '');


        $where = [];

        if ($data['keyword']) {
            $where[] = ['title', 'like', '%' . $data['keyword'] . '%'];
            $advance = 1;
        }

        if ($data['type']) {
            $where[] = ['type', '=', $data['type']];
        }

        if ($data['status'] != '') {
            $where[] = ['status', '=', $data['status']];
            $advance = 1;
        }
        if($data['type'] !='article' && $data['type'] != 'event'){
            $where[] = ['bdc_building_id', '=', $this->building_active_id];
        }
        $data['categories'] = Category::searchBy([
            'where'    => $where,
            'per_page' => $data['per_page'],
        ]);
        $data['heading']    = Category::types[$data['type']];

        $data['meta_title'] = "QL {$data['heading']} > Danh mục";

        $data['advance'] = $advance;

        return view('backend.categories.index', $data);
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
        // Phân quyền
        $data['type'] = $request->input('type', 'article');

        if ($id > 0) {
            $category = Category::findOrFail($id);
        } else {
            $category = new Category();
        }

        $data['id']       = $id;
        $data['now']      = Carbon::now();
        $data['category'] = $category;

        $data['heading']    = Category::types[$data['type']];
        $data['typeService']    = Helper::loai_phi_dich_vu;

        $data['meta_title'] = "QL {$data['heading']} > Danh mục";

        return view('backend.categories.edit', $data);
    }

    /**
     * Lưu bản ghi
     *
     * @param  CategoryRequest  $request
     * @param  int  $id
     * @return Response
     */
    public function save(CategoryRequest $request, $id = 0)
    {
        $type = $request->input('type', 'article');

        $input           = $request->all();
        $input['id']     = $id;
        $input['type']   = $type;
        $input['status'] = $request->input('status', 0);

        $category = Category::findOrNew($id);
        $category->fill($input);
        $category->user_id = Auth::user()->id;
       
        if($type !='article' && $type != 'event'){
            $category->bdc_building_id = $this->building_active_id;
        }

        $category->config = @$request->title ? str_slug($request->title,'_') : null;

        if($type != 'receipt' && $category->default != 1){
            $category->config = $request->config ? str_slug($request->config,'_') : null;
        }
       
        
        if($type == 'service'){
            if(empty($request->category) && $request->category == null){
                $message = [
                    'error'  => 0,
                    'status' => 'warning',
                    'msg'    => 'chưa chọn loại dịch vụ.',
                ];
        
                return redirect()->back()->with('message', $message);
            }
            
            $category->category = (int)$request->category;
        }
        $category->save();

        // url alias
        if (empty($category->alias)) {
            $slug = str_slug($category->title);
        } else {
            $slug = $request->alias;
        }

        // save alias
        if ($slug) {
            $uri = 'categories/' . $category->id;
            $url = UrlAlias::saveAlias($uri, $slug, '');

            $category->url_id = $url->id;
            $category->alias  = $url->alias;
            if($type !='article' && $type != 'event'){
                $category->bdc_building_id = $this->building_active_id;
            }
            if($type == 'service'){
              
                if(empty($request->category) && $request->category == null){
                    $message = [
                        'error'  => 0,
                        'status' => 'warning',
                        'msg'    => 'chưa chọn loại dịch vụ.',
                    ];
            
                    return redirect()->back()->with('message', $message);
                }
                $category->category = (int)$request->category;
            }
            $category->save();
        }

        $message = [
            'error'  => 0,
            'status' => 'success',
            'msg'    => 'Đã cập nhập thông tin',
        ];

        return redirect()->route('admin.categories.index', ['type' => $type])->with('message', $message);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request)
    {
        $type    = $request->input('type', 'article');
        $keyword = $request->input('keyword', '');

        $where = [];

        if ($type) {
            $where[] = ['type', '=', $type];
        }

        if ($keyword) {
            $where[] = ['title', 'like', '%' . $keyword . '%'];
        }

        $select = ['id', 'title'];
        if($type !='article' && $type != 'event'){
            $where[] = ['bdc_building_id', '=', $this->building_active_id];
        }

        $categories = Category::searchBy([
            'select' => $select,
            'where'  => $where,
        ]);

        return response()->json($categories);
    }
    public function action(Request $request)
    {
        $method = $request->input('method', '');
        if ($method == 'delete') {
            $del = $this->deleteAt($request);
            return back()->with('success', $del['msg']);
        } elseif ($method == 'status') {
            $status =  $this->status($request);
            return back()->with('success', $status['msg']);
        }elseif ($method == 'per_page') {
            return $this->per_page($request);
        }
        return back();
    }
    public function deleteAt($request)
    {
        $ids = $request->input('ids', []);

        // chuyển sang kiểu array
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $list = [];
        foreach ($ids as $id) {
            $id = (int)$id;
            $category = Category::find($id);
            if($category->default != 1){
                $category->delete();
                $list[]=$id;
            }
        }

        $message = [
            'error'  => 0,
            'status' => 'success',
            'msg'    => 'Đã xóa '.count($list).' bản ghi!',
        ];

        return $message;
    }
    public function status($request)
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

        Category::whereIn('id', (array) $list)->update(['status' => (int) $status]);

        $message = [
            'error'  => 0,
            'status' => 'success',
            'msg'    => 'Đã cập nhật trạng thái!',
        ];

        return $message;
    }
    public function export(Request $request)
    {
         $data['type']    = $request->input('type', 'article');
         $data['status']  = $request->input('status', '');
         $data['keyword'] = $request->input('keyword', '');
         $where = [];
         if ($data['keyword']) {
             $where[] = ['title', 'like', '%' . $data['keyword'] . '%'];
         }
 
         if ($data['type']) {
             $where[] = ['type', '=', $data['type']];
         }
 
         if ($data['status'] != '') {
             $where[] = ['status', '=', $data['status']];
         }
 
         $where[] = ['bdc_building_id', '=', $this->building_active_id];
 
         $categories = Category::searchBy([
             'where'    => $where,
         ]);

        $result = Excel::create('danh sách danh mục', function ($excel) use ($categories) {
            $excel->setTitle('danh sách danh mục');
            $excel->sheet('danh sách', function ($sheet) use ($categories) {
                $row = 1;
                $sheet->row($row, [
                    'STT',
                    'ID',
                    'Tên danh mục',
                    'Loại',
                    'Tòa nhà',
                    'Mô tả',
                    'Ngày tạo',
                    'Người tạo',
                ]);
                foreach ($categories as $key => $value) {
                    $row++;
                    $sheet->row($row, [
                        ($key + 1),
                        $value->id,
                        $value->title,
                        $value->type == 'service' ? Helper::loai_phi_dich_vu[$value->category] : $value->type,
                        @$value->building->name,
                        $value->content ? strip_tags($value->content) : null,
                        $value->created_at,
                        @$value->user->email,
                    ]);
                }
            });
        })->store('xlsx',storage_path('exports/'));
        $file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
        return response()->download($file)->deleteFileAfterSend(true);
             
    }
}
