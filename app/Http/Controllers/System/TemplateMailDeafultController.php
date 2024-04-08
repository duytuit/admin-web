<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\BuildingController;
use App\Http\Requests\MailTemplates\MailTemplateCreateRequest;
use App\Repositories\MailTemplates\MailTemplateRepository;
use Illuminate\Http\Request;

class TemplateMailDeafultController extends BuildingController
{
    protected $repository;

    public function __construct(Request $request, MailTemplateRepository $repository)
    {
        //$this->middleware('route_permision');
        $this->repository = $repository;
        parent::__construct($request);
    }

    public function index()
    {
        $data['meta_title'] = 'Mẫu mail mặc định';
        $data['bdc_building_id'] = $this->building_active_id;
        $data['template'] = $this->repository->getDefaultTemplate($this->building_active_id);

        return view('template-send-default.index', $data);
    }

    public function store(MailTemplateCreateRequest $request)
    {
        $request->merge([
            'type' => 3,
            'bdc_building_id' => $this->building_active_id
        ]);
        if($request->has('id')) {
            $this->repository->find($request->id)->update($request->except(['_token', 'id']));
        } else {
            $this->repository->create($request->except(['_token']));
        }

        return redirect()->route('admin.system.template_send_default.index')->with('success', 'Cập nhật mail mặc định thành công');
    }
}
