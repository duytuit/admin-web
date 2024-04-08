<?php

namespace App\Http\Controllers\ProductDeposit;

use App\Http\Controllers\BuildingController;
use App\Repositories\ProductDeposit\ProductDepositRespository;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cookie;

class ProductDepositController extends BuildingController
{
    public $_productDepositRespository;
    public function __construct(
        Request $request,
        ProductDepositRespository $productDepositRespository
    ) {
        //$this->middleware('auth', ['except' => []]);
        //$this->middleware('route_permision');
        $this->_productDepositRespository = $productDepositRespository;
        Carbon::setLocale('vi');
        parent::__construct($request);
    }

    public function index(Request $request)
    {
        $data['per_page'] = $perPage = Cookie::get('per_page', 20);
        
        $data['heading']    = 'Ý kiến phản hồi';
        $data['meta_title'] = "QL Ý kiến phản hồi";

        $productDepositList = $this->_productDepositRespository->getList($perPage);

        $data_search = [
            'type' => '-1',
            'name'  => '',
            'direction' => '',
            'status' => '-1',

        ];

        if (isset($request->type) && $request->type != null) {
            $data_search['type'] = $request->type;
            $productDepositList = $productDepositList->where('type',$request->type);
        }

        if (isset($request->name) && $request->name != null) {
            $data_search['name'] = $request->name;
            $productDepositList = $productDepositList->where('name','like','%'.$request->name.'%');
        }

        if (isset($request->direction) && $request->direction != null) {
            $data_search['direction'] = $request->direction;
            $productDepositList = $productDepositList->where('direction','like','%'.$request->direction.'%');
        }
        if (isset($request->status) && $request->status != null) {
            $data_search['status'] = $request->status;
            $productDepositList = $productDepositList->where('status', $request->status);
        }

        $data['data_search'] = $data_search;
        
        $data['productDeposits'] = $productDepositList->paginate($perPage);

        return view('product-deposit.index', $data);
    }

    public function destroy(Request $request)
    {
        $productDeposit = $this->_productDepositRespository->destroy($request->id);
        if($productDeposit == 1) {
            $message = [
                'error'  => 0,
                'status' => 'success',
                'msg'    => 'Đã cập nhật trạng thái!',
            ];
            return response()->json($message);
        } else {
            $message = [
                'error'  => -1,
                'status' => 'error',
                'msg'    => 'Cập nhật trạng thái thất bại!',
            ];
            return response()->json($message);
        }
    }

    public function changeStatus(Request $request)
    {
        $productDeposit = $this->_productDepositRespository->changeStatus($request->id, $request->status);
        if($productDeposit == 1) {
            $message = [
                'error'  => 0,
                'status' => 'success',
                'msg'    => 'Đã cập nhật trạng thái!',
            ];
            return response()->json($message);
        } else {
            $message = [
                'error'  => -1,
                'status' => 'error',
                'msg'    => 'Cập nhật trạng thái thất bại!',
            ];
            return response()->json($message);
        }
    }
}
