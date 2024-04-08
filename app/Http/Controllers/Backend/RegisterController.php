<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Controller;
use App\Models\Apartments\Apartments;
use App\Models\Post;
use App\Models\PostRegister;
use App\Models\PublicUser\UserInfo as PublicUserUserInfo;
use App\Models\PublicUser\V2\UserInfo;
use App\Models\PublicUser\V2\UserInfoApartment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Maatwebsite\Excel\Facades\Excel;
use Validator;

class RegisterController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->model = new PostRegister();

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
        $post = Post::findOrFail($id);

        $data = [];

        $registers = $this->getRegisters($request, $data);
        
        //dd($registers);

        $data['meta_title'] = ($data['type'] == 'event') ? 'KQ Sự kiện ' : 'KQ Khuyến mãi';
        $data['heading']    = ($data['type'] == 'event') ? 'Sự kiện ' : 'Khuyến mãi';
        $data['registers']  = $registers;
        $data['post']       = $post;

        return view('backend.registers.index', $data);
    }

    protected function getRegisters($request, &$data)
    {
        $id = $request->id;

        // Phân trang
        $data['per_page'] = Cookie::get('per_page', 20);

        // Tìm kiếm nâng cao
        $data['advance'] = 0;

        $data['type']     = $request->input('type', 'event');
        $data['keyword']  = $request->input('keyword', '');
        $data['phone']    = $request->input('phone', '');
        $data['email']    = $request->input('email', '');
        $data['check_in'] = $request->input('check_in', '');

        $where = [];

        $where[] = ['post_id', '=', $id];

        if ($data['check_in'] === '0') {
            $where[]         = ['check_in', '=', null];
            $data['advance'] = 1;
        }

        if ($data['check_in'] === '1') {
            $where[]         = ['check_in', '<>', null];
            $data['advance'] = 1;
        }

        $registers = PostRegister::where($where)->with('pubUserinfo.bdcCustomers.bdcApartment');

        if ($data['keyword'] || $data['phone'] || $data['email']) {
            $data['advance'] = 1;

            $registers->where(function ($query) use ($data) {
                $query->whereHas('pubUserinfo', function ($query) use ($data) {
                    if ($data['keyword']) {
                        $query->where('display_name', 'like', '%' . $data['keyword'] . '%');
                    }
                    if ($data['phone']) {
                        $query->where('phone', 'like', '%' . $data['phone'] . '%');
                    }
                    if ($data['email']) {
                        $query->where('email', 'like', '%' . $data['email'] . '%');
                    }
                });
            });
        }

        $registers = $registers->orderByRaw('id DESC')->paginate($data['per_page']);

        return $registers;
    }

    public function validationCode(Request $request)
    {

    }
    public function checkInRegister(Request $request)
    {
        $rules = [
            'code' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        $errors    = $validator->messages();

        $code = trim($request->code);
        $code = strtoupper($code);

        $register = PostRegister::where('code', $code)->first();

        if (!$register) {
            $errors->add('code_none', 'Mã không chính xác. Vui lòng kiểm tra lại hoặc nhập mã khác.');

        } elseif ($register && $register->check_in) {
            $errors->add('code_used', 'Mã đã được sử dụng. Vui lòng kiểm tra lại hoặc nhập mã khác.');
        }

        if ($errors->toArray()) {
            return response()->json(['error_code' => $errors]);
        }

        if (!$request->has('_validate')) {
            $code = trim($request->code);
            $code = strtoupper($code);

            $register           = PostRegister::where('code', $code)->first();
            $register->check_in = Carbon::now();
            $register->save();

            return response()->json(['msg' => 'Check in thành công']);
        }
    }

    public function export(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        $data = [];

        $registers = $this->getRegisters($request, $data);

        try {
            $time = date('Y-m-d_H-i-s');
            $result = Excel::create('Danh-sach-khach-hang_' . $time, function ($excel) use ($registers, $post) {
                $excel->setTitle('Danh sách khách hàng');
                $excel->sheet('Danh sách khách hàng', function ($sheet) use ($registers, $post) {
                    $list = [];
                    foreach ($registers as $key => $item) {
                        $apartment=null;
                        if($item->new == 1){
                            $user_info_1 = UserInfo::where('user_id',$item->user_id)->first();
                            $apartment = $user_info_1 ? UserInfoApartment::getApartmentByUserInfo($user_info_1->id): null;
                        }else{
                            $user_info_1 = PublicUserUserInfo::find($item->user_id);
                            $apartment = @$item->pubUserinfo->bdcCustomers;
                        }
                        $apartment_name='';
                        if($apartment){
                            foreach ($apartment as $key => $apartment) {
                                if($item->new == 1){
                                    $_apartment = Apartments::get_detail_apartment_by_apartment_id($apartment->apartment_id);
                                    $apartment_name .= $_apartment ? ' '.$_apartment->name : '';
                                }else{
                                    $apartment_name .= ' '.$apartment->bdcApartment->name??'';
                                }
                            }
                        }
                        $list[] = [
                            'STT'       => $key + 1,
                            'Họ tên'    => @$user_info_1->display_name??@$user_info_1->full_name?? 'không rõ' ,
                            'SĐT'       => @$user_info_1->phone??@$user_info_1->phone_contact?? 'không rõ' ,
                            'Email'     => @$user_info_1->email??@$user_info_1->email_contact?? 'không rõ' ,
                            'Căn hộ'    => $apartment_name,
                            'Đăng ký'   => $item->updated_at,
                            'Check in'  => $item->check_in
                        ];
                    }

                    $sheet->setAutoSize(true);

                    // data of excel
                    if ($list) {
                        $sheet->fromArray($list);
                    }

                    // add header
                    $sheet->prependRow(1, ["Danh sách khách hàng tham gia sự kiện `{$post->title}`"]);

                    $sheet->mergeCells("A1:F1");
                    $sheet->cell('A1', function ($cell) {
                        $cell->setFontWeight('bold')->setAlignment('center');
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
