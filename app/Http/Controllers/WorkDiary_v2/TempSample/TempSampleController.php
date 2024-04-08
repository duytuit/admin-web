<?php

namespace App\Http\Controllers\WorkDiary_v2\TempSample;

use Illuminate\Http\Request;
use App\Http\Controllers\BuildingController;
use Illuminate\Support\Facades\Cookie;
use Validator;
use Carbon\Carbon;
use GuzzleHttp\Client;
use App\Commons\Helper;
use App\Http\Requests\WorkDiaryV2\TempSample\TempSampleRequest;
use Modules\Tasks\Repositories\SubTaskTemplate\SubTaskTemplateRespository;
use Modules\Tasks\Repositories\SubTaskTemplateInfo\SubTaskTemplateInfoRespository;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Filter\SubTaskTemplateFilter;

class TempSampleController extends BuildingController
{

    protected $_subTaskTemplateRespository;
    protected $_subTaskTemplateInfoRespository;

    public function __construct(
        Request $request,
        SubTaskTemplateRespository $subTaskTemplateRespository,
        SubTaskTemplateInfoRespository $subTaskTemplateInfoRespository
    )
    {
          ////$this->middleware('route_permision');
          $this->_subTaskTemplateRespository = $subTaskTemplateRespository;
          $this->_subTaskTemplateInfoRespository = $subTaskTemplateInfoRespository;
          parent::__construct($request);
    }
    public function subTaskTemplate(Request $request,$building_id){
        $limit = isset($request->limit) ? $request->limit : 10;
        $page = isset($request->page) ? $request->page : 1;

        $subTaskTemplates = $this->_subTaskTemplateRespository->filterByBuildingId($building_id);
        $subTaskTemplates = SubTaskTemplateFilter::index($subTaskTemplates, $request);
        $offSet = ($page * $limit) - $limit;
        $itemsForCurrentPage = array_slice($subTaskTemplates->toArray(), $offSet, $limit, true);
        $_subTaskTemplates = new LengthAwarePaginator($itemsForCurrentPage, count($subTaskTemplates), $limit, $page, []);
        $paging = [
            'total' => $_subTaskTemplates->total(),
            'currentPage' => $_subTaskTemplates->count(),
            'lastPage' => $_subTaskTemplates->lastPage(),
        ];
        $_subTaskTemplatesList = $_subTaskTemplates->values()->toArray();
        if($_subTaskTemplates){
            $result = ['data'=>$this->object_to_array($_subTaskTemplates),'page'=>$paging];
        }else{
            $result = ['data'=>[],'page'=>$paging];
        }
        return $result;
    }
    public function search_tempsample(Request $request)
    {
        try {
            $data['meta_title'] = 'Quản lý công việc';
        
            $_headers = [
                  'Authorization' =>'Bearer '.Helper::getToken(\Auth::user()->id),
                  'Content-Type' => 'application/json'
            ];
            $data['filter_workdiary'] = $request->all();
            $array_search='';
            foreach ($request->all() as $key => $value) {
                $param='&'.$key.'='.(string)$value;
                $array_search.=$param;
            }
    
            $_client = new \GuzzleHttp\Client();
            // TempSample
            
            //$responseTempSample = $_client->request('Get',env('APP_URL').'/api/admin/v1/sub-task-template?building_id='.$this->building_active_id.$array_search,['headers' => $_headers]);
            $result_TempSample = $this->subTaskTemplate($request,$this->building_active_id);

            $data['data']['subtemps'] = $data['subtemps'] = $result_TempSample;

            $data['data']['tasks'] = $data['tasks'] = $this->getWorkDiary()['tasks'];
            $data['data']['tasks_no_shift'] = $data['tasks_no_shift'] = $this->getWorkDiary()['tasks_no_shift'];
            $data['data']['total_page_tasks'] = $data['total_page_tasks'] = $this->getWorkDiary()['total_page_tasks'];
    
            $data['data']['shifts'] = $data['shifts'] = $this->getWorkDiary()['shifts'];
            $data['data']['categorys'] = $data['categorys'] = $this->getWorkDiary()['categorys'];
            $data['data']['departments'] = $data['departments'] = $this->getWorkDiary()['departments'];
            $data['data']['relateds'] = $data['relateds'] = $this->getWorkDiary()['relateds'];
            $data['data']['user_infos'] = $data['user_infos'] = $this->getWorkDiary()['user_infos'];

            $this->setWorkDiary($data['data']);
    
            $data['active_department'] = $request->bdc_department_id??null;
    
            $data['status_worktask'] =  Helper::status_worktask();
    
            $data['pagination'] =  $this->getPagination();
           
            return view('work-diary-v2.index',$data);
        } catch (\Exception $e) {
            return redirect()->back()->with(['error' => (string)$e->getMessage()]);
        }
       
    }
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'department_ids' => 'required',
            'sub_task_template_infos' => 'required'
        ]);
         if ($validator->fails()) {
             return response()->json(['errors' => $validator->errors()], 404);
         }
        $tempsample['building_id']=$this->building_active_id;
        $tempsample['department_ids'] = json_encode($request->department_ids);
        $tempsample['sub_task_template_infos'] = json_encode($request->sub_task_template_infos);
        $tempsample['title']=$request->title;

        $_headers = [
              'Authorization' =>'Bearer '.Helper::getToken(\Auth::user()->id)
           ];
        $_client = new \GuzzleHttp\Client(['headers' => $_headers]);

        $responseTempSample = $_client->request('POST',env('APP_URL').'/api/admin/v1/sub-task-template/stt-infos/add',[
            'json' => $tempsample
        ]);
        $result_TempSample = json_decode((string) $responseTempSample->getBody(), true);
        if($result_TempSample['success']==false){
            $responseData = [
            'success' => false,
            'message' => (string)$result_TempSample['message']
            ];
        }else{
             $responseData = [
            'success' => true,
            'message' => 'Cập nhập thành công!'
             ];
        }

        return response()->json($responseData);
    }
    public function edit($id = 0)
    {
        $array[]  = (int)$id;
        $result_sub_task_template = $this->_subTaskTemplateInfoRespository->findColumns(['sub_task_template_id' => $array])->get()->toArray();

        $responseData = [
            'success' => true,
            'message' => 'thành công!',
            'data' => $result_sub_task_template,
        ];

        return response()->json($responseData);
    }
    public function update(TempSampleRequest $request, $id = 0)
    {
        $data = $request->except('_token');
        $data['id']= $id;
        $_headers = [
              'Authorization' =>'Bearer '.Helper::getToken(\Auth::user()->id)
           ];
        $_client = new \GuzzleHttp\Client(['headers' => $_headers]);
        $data['sub_task_template_infos'] = json_encode($request->sub_task_template_infos);
        $responseTempSample = $_client->request('PUT',env('APP_URL').'/api/admin/v1/sub-task-template/stt-infos/update',[
            'json' => $data
        ]);
        $result_TempSample = json_decode((string) $responseTempSample->getBody(), true);
        if($result_TempSample['success']==false){
            $responseData = [
            'success' => false,
            'message' => (string)$result_TempSample['message']
            ];
        }else{
             $responseData = [
            'success' => true,
            'message' => 'Cập nhập thành công!'
             ];
        }
        

        return response()->json($responseData);
    }
    public function delete(Request $request)
    {
        $id = (int)$request->input('ids')[0];
        $_headers = [
              'Authorization' =>'Bearer '.Helper::getToken(\Auth::user()->id)
           ];
        $_client = new \GuzzleHttp\Client(['headers' => $_headers]);
        $responseTempSample = $_client->request('delete',env('APP_URL').'/api/admin/v1/sub-task-template/delete?id='.$id);
        
        $result_TempSample = json_decode((string) $responseTempSample->getBody(), true);
        if($result_TempSample['success']==false){
            $request->session()->flash('errors', (string)$result_TempSample['message']);
        }else{
            $request->session()->flash('success', 'Xóa thành công!');
        }
        
    }
    public function ajaxGetSelecttasktemplate(Request $request)
    {
        $data = $request->except('_token');
        $array_search='';
        foreach ($request->all() as $key => $value) {
            $param='&'.$key.'='.(string)$value;
            $array_search.=$param;
        }
         // sub-task-template
        //  $responseTask_template = $_client->request('Get',env('APP_URL').'/api/admin/v1/sub-task-template?building_id='.$this->building_active_id.$array_search,[
        //      'headers' => $_headers
        //      ]);

        $result_Task_template = $this->subTaskTemplate($request,$this->building_active_id);

        
        $responseData = [
        'success' => true,
        'message' => 'Lấy dữ liệu thành công!',
        'data' => $result_Task_template['data']
        ];
        
        return response()->json($responseData);
    }
}
