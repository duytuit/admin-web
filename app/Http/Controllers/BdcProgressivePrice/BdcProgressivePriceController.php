<?php

namespace App\Http\Controllers\BdcProgressivePrice;

use App\Exceptions\QueueRedis;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\BdcProgressivePrice\ProgressivePriceRepository;
use App\Traits\ApiResponse;

class BdcProgressivePriceController extends Controller
{
    use ApiResponse;

    protected $model;

    public function __construct(ProgressivePriceRepository $model)
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
        QueueRedis::setItemForQueue("test_queue", "test");
        return view('progressive_price.index', [
            'data' => $this->model->paginate(),
            'meta_title'=> 'Bảng giá lũy tiến'
        ]);
    }
}
