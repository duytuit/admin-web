<?php

namespace App\Http\Controllers\BdcReceipts;

use App\Http\Controllers\BuildingController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\BdcReceipts\ReceiptRepository;

class ReceiptsController extends BuildingController
{
    protected $model;

    public function __construct(Request $request, ReceiptRepository $model)
    {
       // $this->middleware('auth', ['except' => []]);
        //$this->middleware('route_permision');
        $this->model = $model;
        Carbon::setLocale('vi');
        parent::__construct($request);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return view('receipts.index', [
            'data' => $this->model->paginate(),
            'meta_title' => 'Bảng giá lũy tiến'
        ]);
    }
}
