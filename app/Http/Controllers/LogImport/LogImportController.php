<?php

namespace App\Http\Controllers\LogImport;

use App\Commons\Api;
use Illuminate\Http\Request;
use App\Http\Controllers\BuildingController;
use App\Traits\ApiResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cookie;

class LogImportController extends BuildingController
{
    use ApiResponse;

    public function __construct(
        Request $request
    ) {
        parent::__construct($request);
        //$this->middleware('route_permision');
    }
    /**
     * Danh sách bản ghi
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
        $data['meta_title'] = 'Customers';
        $data['per_page'] = Cookie::get('per_page',10);
        $limit =  $data['per_page'] ?  $data['per_page'] : 10;
        $page = isset($request->page) ? $request->page : 1;
        $data['per_page'] = $limit;
        $data['data_search'] = $request->all();
        $data['type'] = $request->input('type',0);
        $request->request->add(['building_id' => $this->building_active_id]);
        $request->request->add(['type' => $data['type']]);
        $request->request->add(['limit' => $limit]);

        $logImport = Api::GET('admin/getListImportLog',$request->all());
        if(@$logImport->data){
            $_logImport = new LengthAwarePaginator($logImport->data->data, $logImport->data->count, $limit, $page,  ['path' => route('admin.log.import.index')]);
            $data['logImport'] = $_logImport;
        }

        return view('log-import.index', $data);
    }
    public function action(Request $request)
    {
        $method = $request->input('method','');
        if ($method == 'per_page') {
            $this->per_page($request);
            return back();
        }
    }
}
