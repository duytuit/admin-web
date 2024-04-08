<?php

namespace App\Http\Controllers\BuildingHandbookType;

use App\Http\Controllers\BuildingController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Validator;
use App\Http\Requests\BuildingHandbookType\BuildingHandbookTypeRequest;
use App\Repositories\BuildingHandbookType\BuildingHandbookTypeRepository;
use App\Commons\Helper;

class BuildingHandbookTypeController extends BuildingController
{
    private $model;
    /**
     * Constructor.
     */
    public function __construct( BuildingHandbookTypeRepository $model, Request $request )
    {
        //$this->middleware('route_permision');
        $this->model = $model;
        parent::__construct($request);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $data['meta_title'] = 'Cẩm nang tòa nhà';

        $data['filter'] = $request->all();
        $data['handbook_types'] = $this->model->myPaginate($data['filter']);
        $data['keyword'] = $request->input('keyword', '');

        return view('building-handbook-type.index', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BuildingHandbookTypeRequest $request, $id = 0)
    {
        $data = $request->except('_token');
        $data['bdc_building_id'] = $this->building_active_id;
        $this->model->create($data);

        return redirect( route('admin.building-handbook.type.index') )->with('success', 'Thêm mới kiểu cẩm nang thành công!');
    }

    public function update(BuildingHandbookTypeRequest $request, $id = 0)
    {
        $data = $request->except('_token');
        $this->model->update($data, $id);

        return redirect( route('admin.building-handbook.type.index') )->with('success', 'Cập nhật kiểu cẩm nang thành công!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\BuildingHandbookType  $buildingHandbook
     * @return \Illuminate\Http\Response
     */
    public function edit($id = 0)
    {
        $data['meta_title'] = 'Cẩm nang tòa nhà';
        $data['id'] = $id;
        $data['type_companys'] = Helper::template_emails();

        $asset = $this->model->find($id);

        if ( $id == 0 ) {
            // create
            return view('building-handbook-type.edit', $data);
        }

        if ( $asset ) {
            $data['bdh_type'] = $asset;

            return view('building-handbook-type.edit', $data);
        }

        return redirect()->back()->with('error', 'không tìm thấy kiểu cẩm nang');
    }

    public function delete(Request $request)
    {
        $id = $request->input('ids');
        $this->model->delete(['id' => $id]);

        $request->session()->flash('success', 'Xóa kiểu cẩm nang thành công');
    }
}
