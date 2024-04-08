<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Controller;
use App\Http\Controllers\BuildingController;
use App\Models\Apartments\Apartments;
use App\Models\Apartments\V2\UserApartments;
use App\Models\PollOption;
use App\Models\Post;
use App\Models\PostPoll;
use App\Models\PublicUser\V2\User;
use App\Models\PublicUser\V2\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Maatwebsite\Excel\Facades\Excel;
use Validator;

class PollOptionController extends BuildingController
{
    /**
     * Constructor.
     */
    public function __construct(Request $request)
    {
        $this->model = new PollOption();
        parent::__construct($request);
    }

    /**
     * Undocumented function
     * Mô tả các lỗi validate
     */
    public function messages()
    {
        return [
            'required' => ':attribute không được để trống',
            'unique'   => ':attribute đã tồn tại',
        ];
    }

    /**
     * Undocumented function
     * Mô tả các field validate
     */
    public function attributes()
    {
        return [
            'name'           => 'Tên đối tác',
            'company_name'   => 'Tên công ty',
            'city'           => 'Tỉnh/ Thành phố',
            'district'       => 'Quận/ Huyện',
            'address'        => 'Địa chỉ chi tiết',
            'representative' => 'Người đại diện',
            'partner_id'     => 'Đối tác',
            'title'          => 'Tên Chi nhánh',
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data['meta_title'] = "QL bình chọn";

        $data['per_page'] = Cookie::get('per_page', 20);

        //Tìm kiếm
        $where = [];
        if (!empty($request->title)) {
            $where[] = ['title', 'LIKE', "%{$request->title}%"];
        }
        $poll_options = PollOption::searchByHas(['where' => $where, 'per_page' => $data['per_page']],'post',$this->building_active_id);
        $poll_options->load('post');
        //End tìm kiếm
        $data_search = [
            'title' => '',
        ];

        $data['data_search'] = $request->data_search ?: $data_search;

        if ($request->title) {
            $data['data_search']['title'] = $request->title;
        }

        foreach ($poll_options as $index => $poll_option) {
            $key_poll_option = [];
            foreach ($poll_option->options as $key => $item) {
                $key_poll_option[$key] = [
                    'total'      => PostPoll::where('poll_id',$poll_option->id)->where('poll_key','like','%'.$key.'%')->count(),
                    'poll_title' => $item,
                ];
            }
            $poll_options[$index]['total_poll'] = $key_poll_option;
        }
        $data['poll_options'] = $poll_options;

        return view('backend.poll_options.index', $data);
    }

    public function postPoll(Request $request,$id)
    {
        $data['meta_title'] = "Danh sách bình chọn";
        $data['per_page'] = Cookie::get('per_page', 20);
        $data['buildingId'] = $this->building_active_id;
        $data['filter'] = $request->all();
        $postPoll = PostPoll::where('poll_id',$id)->where(function ($query) use ($request){
            if(isset($request->apartment_name) && $request->apartment_name != null){
                $apartment = Apartments::where('name','like','%'.$request->apartment_name.'%')->where('building_id',$this->building_active_id)->first();
                if($apartment){
                    $user_apartment =  UserApartments::where('apartment_id',$apartment->id)->pluck('user_info_id')->toArray();
                    if(count($user_apartment) > 0){
                        $user_info = UserInfo::whereIn('id',$user_apartment)->pluck('user_id')->toArray();
                        if(count($user_info) > 0){
                            $query->whereIn('user_id', $user_info);
                        }
                    }
                }
            }
           
        })->paginate($data['per_page']);
        $data['postPoll'] = $postPoll;
        $data['id'] = $id;
        return view('backend.poll_options.list_post_poll', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id = 0)
    {
        $data['meta_title'] = "QL bình chọn";

        $poll_option = $id ? PollOption::findOrFail($id) : new PollOption();

//        $this->authorize('view', $poll_option);

        $data['poll_option'] = $poll_option;
        $data['post'] = Post::where('id',$poll_option->post_id)->where('bdc_building_id',$this->building_active_id)->get();
        $data['id']          = $id;
        if(count($data['post']) > 0){
            return view('backend.poll_options.edit_add', $data);
        }
        return redirect()->route('admin.posts.index')->with(['warning' => 'Thông tin bài viết chưa được cập nhật.']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request, $id = 0)
    {
        $poll_option = $id ? PollOption::findOrFail($id) : new PollOption();

        $rules = [
            'title' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages(), $this->attributes());
        $errors    = $validator->messages();

        if ($errors->toArray()) {
            return back()->with(['errors' => $errors])->withInput();
        }

        if (!$request->has('_validate')) {
            $input = $request->all();

            // Xử lý câu hỏi thăm dò (poll_options)
            foreach ($input['options'] as $index => $item) {
                if (!$item) {
                    unset($input['options'][$index]);
                }
            }

            if (count($input['options']) == 0) {
                $errors->add('options', 'Cần ít nhất 1 câu trả lời cho câu hỏi');
                return back()->with(['errors' => $errors])->withInput();
            }

            $poll_options = $input['options'];
            $polls        = [];

            if ($poll_options) {
                foreach ($poll_options as $key => $value) {
                    $k         = "poll_" . $key;
                    $polls[$k] = $value;
                }
                $input['options'] = $polls;
            }

            if ($input['maximum'] < 1 && $input['maximum'] >= count($polls)) {
                $errors->add('maximum', 'Số câu tả lời tối đa phải nằm trong khoảng 1->' . count($polls));
                return back()->with(['errors' => $errors])->withInput();
            }
            if ($input['post_id'] <= 0) {
                $errors->add('post_id', 'Bạn phải chọn bài viết liên quan');
                return back()->with(['errors' => $errors])->withInput();
            }

            $user  = \Auth::user();
            $param = [
                'user_id'   => $user->id,
                'user_type' => $user->getTable() == 'user_partners' ? 'partner' : 'user',
            ];

            $param = array_merge($input, $param);

            $poll_option->fill($param)->save();
            $poll_op = $poll_option->where('post_id',$input['post_id'])->get();
            Post::where('id',$input['post_id'])->update(['poll_options' => '["'.$poll_op[0]->id.'"]']);
            return redirect()->back()->with('success', 'Cập nhật câu hỏi bình chọn thành công!');
        }
    }

    public function getAll(Request $request)
    {
        $keyword = $request->input('search', '');
        if ($keyword) {
            $where[] = ['title', 'like', '%' . $keyword . '%'];
        }
        if (!empty($where)) {
            $poll_options = PollOption::where($where)->whereNull('post_id')->paginate(20);
        } else {
            $poll_options = PollOption::whereNull('post_id')->paginate(20);
        }

        return response()->json($poll_options);
    }
    public function getAllPosts(Request $request)
    {
        $keyword = $request->input('search', '');
        if ($keyword) {
            $where[] = ['title', 'like', '%' . $keyword . '%'];
        }
        if (!empty($where)) {
            $poll_options = Post::where($where)->paginate(20);
        } else {
            $poll_options = Post::paginate(20);
        }
        return response()->json($poll_options);
    }
    public function deleteAt(Request $request)
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
    public function per_page($request)
    {
        $per_page = $request->input('per_page', 20);

        Cookie::queue('per_page', $per_page, 60 * 24 * 30);

        return back();
    }
    public function action(Request $request)
    {
        $method = $request->input('method', '');
        if ($method == 'delete') {
            return $this->deleteAt($request);
        } elseif ($method == 'status') {
            return $this->status($request);
        } elseif ($method == 'per_page') {
            return $this->per_page($request);
        }
        return back();
    }
    public function export(Request $request,$id)
    {
        $postPoll = PostPoll::where('poll_id',$id)->where(function ($query) use ($request){
            if(isset($request->apartment_name) && $request->apartment_name != null){
                $apartment = Apartments::where('name','like','%'.$request->apartment_name.'%')->where('building_id',$this->building_active_id)->first();
                if($apartment){
                    $user_apartment =  UserApartments::where('apartment_id',$apartment->id)->pluck('user_info_id')->toArray();
                    if(count($user_apartment) > 0){
                        $user_info = UserInfo::whereIn('id',$user_apartment)->pluck('user_id')->toArray();
                        if(count($user_info) > 0){
                            $query->whereIn('user_id', $user_info);
                        }
                    }
                }
            }
           
        })->get();

        $result = Excel::create('Kết quả Import', function ($excel) use ($postPoll) {

            $excel->setTitle('Kết quả Import');
            $excel->sheet('Kết quả Import', function ($sheet) use ($postPoll) {
                $row = 1;
                $sheet->row($row, [
                    'STT',
                    'Người bình chọn',
                    'Phương án bình chọn',
                    'Điện thoại',
                    'Email',
                    'Căn hộ',
                    'Ngày tạo',
                ]);

                foreach ($postPoll as $key => $value) {
                    $row++;
                    $user_info = UserInfo::where('user_id',$value->user_id)->first();
                    $user_apartment =$user_info ? UserApartments::where(['user_info_id'=> $user_info->id,'building_id'=>$this->building_active_id])->first():'';
                    $apartment =$user_apartment ? Apartments::get_detail_apartment_by_apartment_id($user_apartment->apartment_id): '';
                    $PollOption = PollOption::find($value->poll_id);
                    $choose = '';
                    if($PollOption){
                       if($value->poll_key){
                           $poll_key = json_decode($value->poll_key);
                           $i=0;
                           $count = count($PollOption->options);
                           foreach ($PollOption->options as $key_1 => $item_1) {
                               foreach ($poll_key as $item_2) {
                                   if($item_2 == $key_1){
                                       $i++;
                                       $choose.= $item_1 . ($i < $count-1 ? ',': '');
                                   }
                               }
                              
                           }
                       }
                    }
                    $sheet->row($row, [
                        ($key +1),
                        @$user_info->full_name,
                        $choose,
                        @$user_info->phone_contact,
                        @$user_info->email_contact,
                        @$apartment->name,
                        @$value->updated_at
                    ]);
                }
            });
        })->store('xlsx',storage_path('exports/'));
        ob_end_clean();
        $file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
        return response()->download($file)->deleteFileAfterSend(true);
    }
}
