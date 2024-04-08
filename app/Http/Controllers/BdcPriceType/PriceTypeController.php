<?php

namespace App\Http\Controllers\BdcPriceType;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\PriceType\PriceTypeRequest;
use App\Models\BdcPriceType\PriceType;
use App\Repositories\BdcPriceType\PriceTypeRepository;
use App\Traits\ApiResponse;

class PriceTypeController extends Controller
{
    use ApiResponse;

    protected $model;

    const CREATE_PRICE_FAILURE = 203;

    public function __construct(PriceTypeRepository $model)
    {
        $this->model = $model;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return view('pricetype.index', [
            'data' => $this->model->paginate(),
            'meta_title'=> 'Price Type'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pricetype.create', ['meta_title'=> 'Tạo bảng giá']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PriceTypeRequest $request)
    {
        $input = $request->only(['name']);
        $this->model->create($input);
        return redirect('admin/pricetype')->with('success', 'Thêm bảng giá thành công.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $priceType = PriceType::find($id);
        return view('pricetype.edit', ['meta_title'=> 'Sửa bảng giá', 'item' => $priceType]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(PriceTypeRequest $request, $id)
    {
        $input = $request->only(['name']);
        $this->model->update($input, $id);
        return redirect('admin/pricetype')->with('success', 'Sửa bảng giá thành công.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $input = ['id' => $id];
        $rs = $this->model->delete($input);
        if($rs) {
            return $this->responseSuccess([], ['Xóa bảng giá thành công!'] );
        } else {
            return $this->responseError(['Xóa bảng giá thất bại!'], self::CREATE_PRICE_FAILURE );
        }
    }
}
