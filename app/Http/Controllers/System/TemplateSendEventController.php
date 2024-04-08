<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\BuildingController;
use App\Http\Requests\MailTemplates\MailTemplateCreateRequest;
use App\Http\Requests\MailTemplates\MailTemplateUpdateRequest;
use App\Models\System\TemplateMail;
use App\Repositories\MailTemplates\MailTemplateRepository;
use Illuminate\Http\Request;

class TemplateSendEventController extends BuildingController
{
    protected $repository;

    public function __construct(Request $request, MailTemplateRepository $repository)
    {
        //$this->middleware('route_permision');
        $this->repository = $repository;
        parent::__construct($request);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data['meta_title'] = 'Mẫu mail gửi sự kiện';
        $data['bdc_building_id'] = $this->building_active_id;
        $data['keyword'] = $request->get('keyword');
        $data['templates'] = $this->repository->getEventTemplates($data['keyword'], $this->building_active_id);

        return view('template-send-event.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['meta_title'] = 'Mẫu mail gửi hóa đơn';
        $data['id'] = 0;
        $data['bdc_building_id'] = $this->building_active_id;

        return view('template-send-event.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(MailTemplateCreateRequest $request)
    {
        $template = $this->repository->create($request->except('_token'));

        return redirect()->action('System\TemplateSendEventController@index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $template = $this->repository->find($id);
        $data['meta_title'] = 'Mẫu mail gửi sự kiên';
        $data['id'] = $template->id;
        $data['template'] = $template;
        $data['bdc_building_id'] = $this->building_active_id;

        return view('template-send-event.create', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(MailTemplateUpdateRequest $request, $id)
    {
        $template = $this->repository->update($request->except('_token', '_method'), $id);

        return redirect()->action('System\TemplateSendEventController@index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $ids = $request->input('ids');
        $this->repository->deleteMulti(['id' => $ids]);

        $request->session()->flash('success', 'Xóa danh mục thành công');
    }

    public function ajaxDeleteMulti(Request $request)
    {
        $ids = $request->input('ids');
        $this->repository->deleteMulti($ids);
        $dataResponse = [
            'success' => true,
            'message' => 'Xóa mẫu email thành công!'
        ];
        return response()->json($dataResponse);
    }
}
