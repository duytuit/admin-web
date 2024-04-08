<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\BuildingController;
use App\Http\Requests\Setting\ConfigSendMailRequest;
use App\Repositories\MailTemplates\MailTemplateRepository;
use App\Repositories\SettingSendMails\SettingSendMailRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Arr;

class ConfigSendEventController extends BuildingController
{
    protected $repository;
    protected $mailTemplateRepository;
    protected $settingSendMail;

    public function __construct(Request $request, SettingSendMailRepository $repository, MailTemplateRepository $mailTemplateRepository)
    {
        //$this->middleware('route_permision');
        $this->repository = $repository;
        $this->mailTemplateRepository = $mailTemplateRepository;
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
        $data['config_send_mails'] = $this->repository->getSetingEvent($this->building_active_id);

        return view('config-send-event.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['setting'] = $this->repository->makeModel();
        $data['meta_title'] = 'Thiết lập mail sự kiện';
        $data['id'] = 0;
        $data['bdc_building_id'] = $this->building_active_id;
        $data['templates'] = $this->mailTemplateRepository->getEventTemplates(null, $data['bdc_building_id'])->except($this->repository->getSetingEvent($this->building_active_id)->pluck('mail_template_id')->toArray());
        $data['remainStatus'] = Arr::except(config('send_mail.event'), $this->repository->getSetingEvent($this->building_active_id)->pluck('status')->toArray());

        return view('config-send-event.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ConfigSendMailRequest $request)
    {
        $data['config_send_mail'] = $this->repository->create($request->except('_token'));

        return redirect()->action('System\ConfigSendEventController@index');
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
        $setting = $this->repository->find($id);
        $data['meta_title'] = 'Mẫu mail gửi sự kiên';
        $data['id'] = $setting->id;
        $data['setting'] = $setting;
        $data['bdc_building_id'] = $this->building_active_id;
        $data['templates'] = $this->mailTemplateRepository->getEventTemplates(null, $data['bdc_building_id']);
        $data['remainStatus'] = config('send_mail.event');

        return view('config-send-event.create', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ConfigSendMailRequest $request, $id)
    {
        $setting = $this->repository->update($request->except('_token', '_method'), $id);

        return redirect()->action('System\ConfigSendEventController@index');
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
