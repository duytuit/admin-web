<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Controller;
use App\Models\City;
use App\Models\District;
use App\Models\Exchange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Validator;

class ExchangeController extends Controller
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->model = new Exchange();
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
            'name'     => 'Tên địa điểm giao dịch',
            'city'     => 'Tỉnh/ Thành phố',
            'district' => 'Quận/ Huyện',
            'address'  => 'Địa chỉ chi tiết',
            'hotline'  => 'Hotline',
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data['meta_title'] = "QL điểm giao dịch";
        $this->authorize('index', app(Exchange::class));

        $data['per_page'] = Cookie::get('per_page', 20);

        //Tìm kiếm
        $where = [];
        if (!empty($request->name)) {
            $where[] = ['name', 'LIKE', "%{$request->name}%"];
        }

        if ($request->status !== null) {
            $where[] = ['status', '=', $request->status];
        }

        if ($request->city !== null) {
            $where[] = ['city', '=', $request->city];
        }

        if ($request->district !== null) {
            $where[] = ['district', '=', $request->district];
        }

        if ($where) {
            $data['exchanges'] = Exchange::searchBy(['where' => $where, 'per_page' => $data['per_page']]);
        } else {
            $data['exchanges'] = Exchange::searchBy(['per_page' => $data['per_page']]);
        }
        $data['exchanges']->load('user');
        $data['exchanges']->load('city_code');
        $data['exchanges']->load('district_code');
        //End tìm kiếm
        $data_search = [
            'name'     => '',
            'status'   => '',
            'city'     => [],
            'district' => [],
        ];

        if ($request->name !== null) {
            $data_search['name'] = $request->name;
        }

        if ($request->status !== null) {
            $data_search['status'] = $request->status;
        }

        if ($request->city !== null) {
            $data_search['city']['code'] = $request->city;
            $data_search['city']['name'] = City::where('code', $request->city)->first()->name;

            if ($request->district !== null) {
                $data_search['district']['code'] = $request->district;
                $data_search['district']['name'] = District::where('code', $request->district)->first()->name;
            }
        }

        $data['data_search'] = $data_search;
//        dd($data);

        return view('backend.exchanges.index', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($cb_id)
    {
        $data['meta_title'] = "QL địa điểm giao dịch";
        $this->authorize('view', app(Exchange::class));

        $data['exchange'] = $cb_id ? Exchange::findById($cb_id) : new Exchange();
        $data['id']       = $cb_id;

        return view('backend.exchanges.edit_add', $data);
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
        $this->authorize('update', app(Exchange::class));

        $rules = [
            'hotline'  => 'required',
            'name'     => 'required',
            'city'     => 'required',
            'district' => 'required',
            'address'  => 'required',
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages(), $this->attributes());
        $errors    = $validator->messages();

        if ($errors->toArray()) {
            return back()->with(['errors' => $errors])->withInput();
        }

        if (!$request->has('_validate')) {
            $data            = $request->all();
            $data['status']  = $request->input('status', 0);
            $data['user_id'] = \Auth::user()->uid;

            if (!$id) {
                $params = [
                    'cb_id' => strtotime(date('Y-m-d H:i:s')),
                ];

                $data = array_merge($data, $params);
            }

            $exchange = $id ? Exchange::findById($id) : new Exchange;

            $exchange->fill($data);
            $exchange->save();

            return redirect(url('/admin/exchanges'))->with('success', 'Cập nhật thông tin điểm giao dịch thành công!');
        }
    }
}
