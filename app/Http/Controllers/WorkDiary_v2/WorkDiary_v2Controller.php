<?php

namespace App\Http\Controllers\WorkDiary_v2;

use App\Filter\SubTaskTemplateFilter;
use App\Filter\TaskCategoryFilter;
use App\Filter\TaskFilter;
use App\Filter\MaintenaceAssetFilter;
use App\Models\V3\TaskCategory;
use App\Services\SendTelegram;
use App\Util\Debug\Log;
use Illuminate\Http\Request;
use App\Http\Controllers\BuildingController;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Modules\Tasks\Repositories\SubTaskTemplate\SubTaskTemplateRespository;
use Modules\Tasks\Repositories\Task\TaskRespository;
use Modules\Tasks\Repositories\TaskCategory\TaskCategoryRespository;
use Modules\Tasks\Repositories\WorkShift\WorkShiftRespository;
use Modules\Assets\Repositories\MaintenanceAsset\MaintenanceAssetRespository;
use Validator;
use Carbon\Carbon;
use GuzzleHttp\Client;
use App\Http\Requests\WorkDiaryV2\WorkDiaryV2Request;
use App\Commons\Helper;
use App\Traits\ApiResponse;
use App\Repositories\Feedback\FeedbackRespository;
use App\Repositories\Apartments\ApartmentsRespository;
use Illuminate\Support\Facades\Auth;
use App\Commons\Api;
use App\Repositories\Department\DepartmentRepository;
use App\Models\PublicUser\UserInfo;
use App\Models\DepartmentStaff\DepartmentStaff;
use App\Models\Department\Department;
use Illuminate\Support\Facades\DB;
use Excel;

class WorkDiary_v2Controller extends BuildingController
{
    use ApiResponse;
    private $modelApartment;
    private $modelFeedback;
    protected $departmentRepository;

    protected $_workShiftRespository;
    protected $_taskCategoryRepository;
    protected $_taskRespository;
    protected $_subTaskTemplateRespository;
    protected $_maintenanceAssetRespository;
    protected $permission_task_user;

    public function __construct(
        Request $request,
        FeedbackRespository $modelFeedback,
        DepartmentRepository $departmentRepository,
        ApartmentsRespository $modelApartment,
        WorkShiftRespository $workShiftRespository,
        TaskCategoryRespository $taskCategoryRespository,
        SubTaskTemplateRespository $subTaskTemplateRespository,
        TaskRespository $taskRespository,
        MaintenanceAssetRespository $maintenanceAssetRespository
    )
    {
        $this->modelApartment = $modelApartment;
        $this->modelFeedback = $modelFeedback;
        $this->departmentRepository = $departmentRepository;
        $this->_workShiftRespository = $workShiftRespository;
        $this->_taskCategoryRepository = $taskCategoryRespository;
        $this->_taskRespository = $taskRespository;
        $this->_subTaskTemplateRespository = $subTaskTemplateRespository;
        $this->_maintenanceAssetRespository = $maintenanceAssetRespository;
        parent::__construct($request);
        $this->permission_task_user = $this->permissionByTask();
        \View::share('permission_task_user', json_encode($this->permission_task_user));

    }
   
    public function index(Request $request)
    {
        SendTelegram::sendTelegramMessage("vao day k 80 ");
        $data['meta_title'] = 'Quản lý công việc';
        $data['filter'] = $request->all();
        $request->request->add(['building_id' => $this->building_active_id]);
        // if(isset($data['filter']['asset_category_id'])){
        //     $data['_asset_category'] = AssetCategory::find($data['filter']['asset_category_id']);
        // }
        // if(isset($data['filter']['office_id'])){
        //     $data['_office_asset'] = AssetArea::find($data['filter']['office_id']);
        // }
        // if(isset($data['filter']['department_id'])){
        //     $data['_department_asset'] = Department::find($data['filter']['department_id']);
        // }
        $array_search='';
        $i=0;
        foreach ($request->all() as $key => $value) {
            if($i == 0){
                $param='?'.$key.'='.(string)$value;
            }else{
                $param='&'.$key.'='.(string)$value;
            }
            $i++;
            $array_search.=$param;
        }
        $data['array_search'] = $array_search;
        $data['building_id'] = $this->building_active_id;
        $data['status_task_html'] = json_encode(Helper::status_task_html);
        $data['request_task'] = json_encode(Helper::request_task);
        $data['TaskCategory'] = json_encode(TaskCategory::where('building_id',$this->building_active_id)->get());
        $user_per = $this->filter_array_user($this->permission_task_user,Auth::user()->id);
        $data['get_permission_by_user'] = json_encode($user_per);
        $data['type_manager'] = 0;
        if(@$user_per->permission){
            foreach ($user_per->permission as $index => $item) {
                if($item->type_manager == 1 || $item->type == 1){
                    $data['type_manager'] = 1;
                    break;
                }
            }
        }
        Cache::store('redis')->forget( env('REDIS_PREFIX') . 'get_getCateByBuilding_'.$this->building_active_id);

        SendTelegram::sendTelegramMessage("vao day k 122 ");
        return view('work-diary-v2.index',$data);


    }
    public function filter_no_shift_task($items){
        return array_filter($items, function($item){
            if($item['work_shift_id'] == null || $item['work_shift_id'] == 0){
                return true;
            }
        });
    }
    public function get_user_building(Request $request){
        $permission_task_user =  collect($this->permission_task_user);
        if ($request->search) {
            return response()->json( ['data' =>$permission_task_user->firstWhere('full_name','like', '%' . $request->search . '%')] );
        }
        return response()->json(['data' =>$permission_task_user]);
    }
    public function detail(Request $request)
    {
        $data['meta_title'] = 'Chi tiết công việc';
        $request->request->add(['building_id' => $this->building_active_id]);
        $array_search='';
        $i=0;
        foreach ($request->all() as $key => $value) {
            if($i == 0){
                $param='?'.$key.'='.(string)$value;
            }else{
                $param='&'.$key.'='.(string)$value;
            }
            $i++;
            $array_search.=$param;
        }
        $data['array_search'] = $array_search;
        $data['id'] = $request->id;
        $data['building_id'] = $this->building_active_id;
        $data['TaskCategory'] = json_encode(TaskCategory::where('building_id',$this->building_active_id)->get());
        $data['get_permission_by_user'] = json_encode($this->filter_array_user($this->permission_task_user,Auth::user()->id));
        $data['priority_task'] = json_encode(Helper::priority_task);
        $data['status_task'] = json_encode(Helper::status_task);
        $data['status_history_task'] = json_encode(Helper::status_history_task);
        if($request->id){
            $request->request->add(['id' => $request->id]);
            $task = Api::GET('admin/task/getDetailTask',$request->all());
            if($task->status == true){
                $data['task_detail'] = json_encode(@$task->data);
                $data['task'] = @$task->data->task;
            }else{
                return view('work-diary-v2.detail-task',$data)->with('warning',$task->mess);
            }
        }
        return view('work-diary-v2.detail-task',$data);
    }
    public function create(Request $request)
    {
        $data['meta_title'] = 'Quản lý công việc';
        $request->request->add(['building_id' => $this->building_active_id]);
        $array_search='';
        $i=0;
        foreach ($request->all() as $key => $value) {
            if($i == 0){
                $param='?'.$key.'='.(string)$value;
            }else{
                $param='&'.$key.'='.(string)$value;
            }
            $i++;
            $array_search.=$param;
        }
        $data['array_search'] = $array_search;
        $data['id'] = $request->id;
        $data['building_id'] = $this->building_active_id;
        $data['TaskCategory'] = TaskCategory::where('building_id',$this->building_active_id)->where('status',1)->get();
        $_user =$this->filter_array_user($this->permission_task_user,Auth::user()->id);
        $check_edit= true;
        if(@$_user->permission){
            foreach (@$_user->permission as $item) {
                if($item->type_manager == 1){
                    $check_edit =false;
                }
                if($item->type == 1){
                    $check_edit =false;
                }
            }
        }

        $data['get_permission_by_user'] = json_encode($_user);
        $data['departments'] = $this->departmentRepository->myPaginate1('', $this->building_active_id);
        if($request->id){
            $request->request->add(['id' => $request->id]);
            $task = Api::GET('admin/task/getDetailTask',$request->all());
            if($task->status == true){
                $data['task_detail'] = json_encode(@$task->data);
                $data['task'] = @$task->data->task;
                $array_user = explode('user_', @$task->data->task->create_by);
                if(count($array_user) > 0){
                    if($check_edit==true && $array_user[1] != $_user->user_id){
                        return redirect()->route('admin.work-diary-v2.index')->with('warning', 'Bạn không có quyền sửa công việc.');
                    }
                }
                if(@$task->data->task->status == 0 || @$task->data->task->status ==1 ){
                    return redirect()->route('admin.work-diary-v2.index')->with('warning', 'Không tìm thấy công việc.');
                }
            }else{
                return view('work-diary-v2.create-edit',$data)->with('warning',$task->mess);
            }
        }
        if($request->task_schedule_id){
            $request->request->add(['id' => $request->task_schedule_id]);
            $schedule = Api::GET('admin/task/getDetailTaskSchedule',$request->all());
            if($schedule->status == true){
                $data['schedule_detail'] = json_encode(@$schedule->data);
                $data['id_task_schedule'] = @$schedule->data->id;
            }else{
                return view('work-diary-v2.create-edit',$data)->with('warning',$schedule->mess);
            }
        }
        return view('work-diary-v2.create-edit',$data);
    }
    public function filter_array_user($arr,$value){
       return current(array_filter($arr, function($v, $k) use($value) {
            return $v->pub_user_id == $value;
        }, ARRAY_FILTER_USE_BOTH));
    }
    public function getAllUserByDepartment($arr,$value){
        return  array_filter($arr, function($v, $k) use($value) {
            return stripos($v->pub_user_id,$value);
        });
    }
    public function show(Request $request)
    {
        $data['meta_title'] = 'Quản lý công việc';
        $_headers = [
            'Authorization' =>'Bearer '.Helper::getToken(\Auth::user()->id)
        ];
        $_client = new \GuzzleHttp\Client(['headers' => $_headers]);
        // show user
        $responseUser = $_client->request('Get',env('APP_URL').'/api/admin/v1/admin/show?user_id='.$request->user_id);

        $result_User = json_decode((string) $responseUser->getBody(), true);

        $responseData = [
            'success' => true,
            'message' => 'Thêm mới thành công!',
            'data' => $result_User['data']
        ];

        return response()->json($responseData);
    }
    public function showtask(Request $request, $id = 0)
    {
        $data['meta_title'] = 'Quản lý công việc';

        $result_ShowTask = $this->_taskRespository->filterByIdWeb($id);

        // create by
        $result_CreateBy = UserInfo::withTrashed()->where(['pub_user_id'=> $result_ShowTask['data']['created_by'],'bdc_building_id'=>$this->building_active_id,'type'=>2])->first();
        if(!$result_CreateBy){
            $responseData = [
                'success' => false,
                'message' => 'Người dùng này chưa cập nhật vào bộ phận!',
            ];
            return response()->json($responseData);
        }
        $result_ShowTask['data']['user_name_create_by']=$result_CreateBy['display_name'];
        $result_ShowTask['data']['account']= \Auth::user()->id;

        foreach ($this->getWorkDiary(\Auth::user()->id)['user_infos'] as $key => $value1) {
            if($result_ShowTask['data']['supervisor'] == $value1['pub_user_id']){
                $result_ShowTask['data']['supervisor_name'] = $value1['display_name'];
                break;
            }
        }
        $get_role_deparment_user = array_filter($this->regency['departments'],function ($list_departments) use ($result_ShowTask){
            return isset($result_ShowTask['data']['bdc_department_id']) && $list_departments['id'] == $result_ShowTask['data']['bdc_department_id'];
        });
        if($this->regency['role'] == 'ban_quan_ly'|| $this->regency['role'] == 'supper_admin'){
            $result_ShowTask['data']['permission']='truong_ban_quan_ly';
        }else if(isset($get_role_deparment_user[0]['role_child']) && $get_role_deparment_user[0]['role_child'] == 'truong_bo_phan'){
            $result_ShowTask['data']['permission']='truong_bo_phan';
        }else if(\Auth::user()->id == $result_ShowTask['data']['supervisor']){
            $result_ShowTask['data']['permission']='giam_sat';
        }else{
            $result_ShowTask['data']['permission']='nhan_vien';
        }
        $responseData = [
            'success' => true,
            'message' => 'lấy dữ liệu thành công!',
            'data' => $result_ShowTask['data']
        ];
        return response()->json($responseData);
    }
    public function storeshift()
    {
        $data['meta_title'] = 'Quản lý công việc';
        return view('work-diary-v2.create-edit',$data);
    }
    public function saveTask(Request $request)
    {
        try {
            $workdiary = $request->all();
            $workdiary['building_id']=$this->building_active_id;
            $workdiary['created_by']=\Auth::user()->id;

            $responseData = [
                'success' => true,
                'message' => 'Thêm mới thành công!',
                'href' => route('admin.work-diary-v2.index')
            ];
            return response()->json($responseData);
        } catch (\Exception $e) {
               $responseData = [
                'success' => false,
                'message' => (string)$e->getMessage()
                ];
            // $responseData = [
            //     'success' => false,
            //     'message' => 'thêm mới thất bại'
            // ];
        }

    }
    public function update(WorkDiaryV2Request $request, $id = 0)
    {

        try {
            $workdiary= $request->all();
            $workdiary['building_id']=$this->building_active_id;
            $workdiary['created_by']=\Auth::user()->id;
            $get_list_user=json_decode($workdiary['user_infos']);
            foreach ($get_list_user as $key => $value) {
                foreach ($this->getWorkDiary(\Auth::user()->id)['user_infos'] as $key => $value1) {
                    if($value == $value1['pub_user_id']){
                        $user=[
                            'user_id' =>$value1['pub_user_id'],
                            'name' =>$value1['display_name'],
                            'avatar' =>$value1['avatar']
                        ];
                        $new_user[]=$user;
                        break;
                    }
                }
            }
            $workdiary['user_infos']=\json_encode($new_user);
            $workdiary['id'] =$id;
            $_headers = [
                'ClientId' => env('CLIENT_ID'),
                'ClientSecret' => env('CLIENT_SECRET'),
                'Authorization' =>'Bearer '.Helper::getToken(\Auth::user()->id)
            ];
            $_client = new \GuzzleHttp\Client(['headers' => $_headers]);
            $responseWorkDiary = $_client->request('PUT',env('APP_URL').'/api/admin/v1/task/update',[
                'json' => $workdiary
            ]);
            $result_WorkDiary = json_decode((string) $responseWorkDiary->getBody(), true);
            if($result_WorkDiary['success']==false){
                $responseData = [
                    'success' => false,
                    'message' => 'cập nhập thất bại!'//(string)$result_WorkDiary['message']
                ];
            }else{
                $responseData = [
                    'success' => true,
                    'message' => 'Cập nhập thành công!',
                    'href' => route('admin.work-diary-v2.index')
                ];
            }
        } catch (\Exception $e) {
            // $responseData = [
            //     'success' => false,
            //     'message' => (string)$e->getMessage()
            //     ];
            $responseData = [
                'success' => false,
                'message' => 'cập nhập thất bại'
            ];
        }
        return response()->json($responseData);
    }
    public function change_status(Request $request)
    {
        $data = $request->except('_token');
        $_headers = [
            'Authorization' =>'Bearer '.Helper::getToken(\Auth::user()->id)
        ];
        $_client = new \GuzzleHttp\Client(['headers' => $_headers]);
        $responseWorkDiary = $_client->request('PUT',env('APP_URL').'/api/admin/v1/task/feedback',[
            'json' => $data
        ]);
        $result_WorkDiary = json_decode((string) $responseWorkDiary->getBody(), true);

        if($result_WorkDiary['success']==false){
            $responseData = [
                'success' => false,
                'message' => 'thay đổi trạng thái thất bại!'//(string)$result_WorkDiary['message']
            ];
        }else{
            $responseData = [
                'success' => true,
                'message' => 'Thay đổi trạng thái thành công!'
            ];
        }

        return response()->json($responseData);
    }
    public function change_status_subtask(Request $request)
    {
        $data = $request->except('_token');
        $_headers = [
            'Authorization' =>'Bearer '.Helper::getToken(\Auth::user()->id)
        ];
        $_client = new \GuzzleHttp\Client(['headers' => $_headers]);
        $responseSubtask = $_client->request('PUT',env('APP_URL').'/api/admin/v1/sub-task/update-status',[
            'json' => $data
        ]);
        $result_Subtask = json_decode((string) $responseSubtask->getBody(), true);
        if($result_Subtask['success']==false){
            $responseData = [
                'success' => false,
                'message' => 'thay đổi trạng thái thất bại!'//(string)$result_Subtask['message']
            ];
        }else{
            $responseData = [
                'success' => true,
                'message' => 'Thay đổi trạng thái thành công!'
            ];
        }

        return response()->json($responseData);
    }
    public function change_status_task(Request $request)
    {
        $data = $request->except('_token');
        $_headers = [
            'Authorization' =>'Bearer '.Helper::getToken(\Auth::user()->id)
        ];
        $_client = new \GuzzleHttp\Client(['headers' => $_headers]);
        if($request->status == 'started'){
            // show task
            $result_ShowTask = $this->_taskRespository->filterByIdWeb($request->id);
            if($result_ShowTask['data']['show_sub_tasks']){
                foreach ($result_ShowTask['data']['show_sub_tasks'] as $key => $value) {
                    if($value['status'] == 'pending'){
                        $responseData = [
                            'success' => false,
                            'message' => 'bạn chưa check list các công việc!'
                        ];
                        return response()->json($responseData);
                    }
                }
            }

        }
       
        $responseSubtask = $_client->request('PUT',env('APP_URL').'/api/admin/v1/task/update-status',[
            'json' => $data
        ]);
        $result_Subtask = json_decode((string) $responseSubtask->getBody(), true);
        if($result_Subtask['success']==false){
            $responseData = [
                'success' => false,
                'message' => (string)$result_Subtask['message']
            ];
        }else{
            $responseData = [
                'success' => true,
                'message' => 'Thay đổi trạng thái thành công!',
                'href' => route('admin.work-diary-v2.edit',['id'=> $request->id]),
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

        $responseTask = $_client->request('delete',env('APP_URL').'/api/admin/v1/task/delete?id='.$id.'&building_id='.$this->building_active_id);

        $result_Task = json_decode((string) $responseTask->getBody(), true);
        if($result_Task['success']==false){
            //$request->session()->flash('errors', (string)$result_Task['message']);
            $request->session()->flash('errors', 'xóa thất bại!');
        }else{
            $request->session()->flash('success', 'Xóa thành công!');
        }

    }

    public function changestatus(Request $request)
    {
       DB::update("UPDATE buildingcare.bdc_v2_task_form_checklist
        SET updated_at= now() , status = $request->status
        WHERE id= $request->id");
        //admin.work-diary-v2.index
        return redirect()->route('admin.work-diary-v2.index')->with('success', 'Update Thành Công!');
    }

    public function ajaxGetSelectmaintenance_asset(Request $request)
    {
        $_headers = [
            'Authorization' =>'Bearer '.Helper::getToken(\Auth::user()->id)
        ];
        $_client = new \GuzzleHttp\Client();

        // maintenance_asset
        //$responseMaintenance_asset = $_client->request('Get',env('APP_URL').'/api/admin/v1/maintenance-asset?building_id='.$this->building_active_id.'&title='.$request->search,['headers' => $_headers]);

        //$result_Maintenance_asset = json_decode((string) $responseMaintenance_asset->getBody(), true);

        $result_Maintenance_asset = $this->maintenanceAsset($request,$this->building_active_id);

        $responseData = [
            'success' => true,
            'message' => 'Lấy dữ liệu thành công!',
            'data' => $result_Maintenance_asset['data']
        ];

        return response()->json($responseData);
    }
    public function ajaxGetSelectUserByDepartment(Request $request)
    {
        $result_User_Department = UserInfo::whereHas('bdcDepartmentStaff', function($query) use ($request) {
            if (isset($request->department_id)) {
                $query->where('bdc_department_id', '=', $request->department_id);
            }
        })->where(['bdc_building_id'=>$this->building_active_id,'type'=>2])->select('pub_user_id','display_name')->get()->toArray();
        $responseData = [
            'success' => true,
            'message' => 'Lấy dữ liệu thành công!',
            'data' => $result_User_Department
        ];

        return response()->json($responseData);
    }
    public function feedback_subtask(Request $request)
    {
        $data = $request->except('_token');
        $_headers = [
            'Authorization' =>'Bearer '.Helper::getToken(\Auth::user()->id)
        ];
        $_client = new \GuzzleHttp\Client(['headers' => $_headers]);
        $responseWorkDiary = $_client->request('PUT',env('APP_URL').'/api/admin/v1/sub-task/feedback',[
            'json' => $data
        ]);
        $result_WorkDiary = json_decode((string) $responseWorkDiary->getBody(), true);
        if($result_WorkDiary['success']==false){
            $responseData = [
                'success' => false,
                'message' => 'gửi phản hồi thất bại'//(string)$result_WorkDiary['message']
            ];
        }else{
            $responseData = [
                'success' => true,
                'message' => 'Gửi phản hồi thành công!'
            ];
        }

        return response()->json($responseData);
    }
    public function feedback_task(Request $request)
    {
        $data = $request->except('_token');
        $_headers = [
            'Authorization' =>'Bearer '.Helper::getToken(\Auth::user()->id)
        ];
        $_client = new \GuzzleHttp\Client(['headers' => $_headers]);
        $responseWorkDiary = $_client->request('PUT',env('APP_URL').'/api/admin/v1/task/feedback',[
            'json' => $data
        ]);
        $result_WorkDiary = json_decode((string) $responseWorkDiary->getBody(), true);

        if($result_WorkDiary['success']==false){
            $responseData = [
                'success' => false,
                'message' => 'gửi phản hổi thành công!'//(string)$result_WorkDiary['message']
            ];
        }else{
            $responseData = [
                'success' => true,
                'message' => 'Gửi phản hồi thành công!'
            ];
        }

        return response()->json($responseData);
    }
    public function getCategory(Request $request){
        $taskCategories = $this->_taskCategoryRepository->filterByBuildingId($request->building_id);
        $taskCategories = TaskCategoryFilter::index($taskCategories, $request);
        return $taskCategories;
    }
    public function exportExcel(Request $request)
    {
        $data['filter_workdiary'] = $request->all();
        $array_search='';$arr_search=[];
        unset($request["pagination_subtemp_page"]);
        foreach ($request->all() as $key => $value) {
            $param='&'.$key.'='.(string)$value;
            $array_search.=$param;
            $arr_search+=[$key=>(string)$value];
        }
        // Work-task
        // trưởng ban quản lý
        if($this->regency['role'] == 'ban_quan_ly'|| $this->regency['role'] == 'supper_admin'){
            if($request->next_page || $request->prev_page || $request->page){
                $arr_search = array_merge($arr_search,['limit' => $this->getPagination()['page_size'],'page'=>$request->next_page??$request->prev_page??$request->page]);
                $request->request->add($arr_search); //add request
                $result_Task = $this->task($request,$this->building_active_id);

                $data['pagination'] = $this->getPagination();
                $data['pagination']['page'] = $request->next_page??$request->prev_page??$request->page;
            }else{
                $arr_search = array_merge($arr_search,['limit' => $this->getPagination()['page_size'],'page'=>$this->getPagination()['page']]);
                $request->request->add($arr_search); //add request
                $result_Task = $this->task($request,$this->building_active_id);


                $data['pagination'] = $this->getPagination();
            }
            $data['data']['active_department'] = $data['active_department'] = $request->bdc_department_id??null;
        }else{
            if($request->next_page || $request->prev_page || $request->page){
                $arr_search = array_merge($arr_search,['limit' => $this->getPagination()['page_size'],'page'=>$request->next_page??$request->prev_page??$request->page,'bdc_department_id'=>$this->regency['departments'][0]['id']]);
                $request->request->add($arr_search); //add request
                $responseTask = $this->task($request,$this->building_active_id);
                $data['pagination'] = $this->getPagination();
                $data['pagination']['page'] = $request->next_page??$request->prev_page??$request->page;
            }else{
                $arr_search = array_merge($arr_search,['limit' => $this->getPagination()['page_size'],'page'=>$this->getPagination()['page'],'building_id'=>$this->building_active_id]);
                $request->request->add($arr_search); //add request
                $responseTask = $this->task($request,$this->building_active_id);
                $data['pagination'] = $this->getPagination();
            }
            $result_Task = $responseTask;
            $data['data']['active_department'] = $data['active_department'] = $request->bdc_department_id??null;
        }
        $dfgdfg = Helper::status_worktask_v2['not_yet_started'];
        $result = Excel::create('Danh sách công việc', function ($excel) use ($result_Task) {
            $excel->setTitle('Danh sách công việc');
            $excel->sheet('Danh sách công việc', function ($sheet) use ($result_Task) {
                $receipts = [];
                $row = 1;
                $sheet->row($row, [
                    'STT',
                    'Ca',
                    'Loại công việc',
                    'Tên công việc',
                    'Danh mục',
                    'Bộ phận',
                    'Thời gian bắt đầu',
                    'Thời gian kết thúc',
                    'Checklist',
                    'Trạng thái',
                    'Người tạo',
                    'Người thực hiện',
                    'Người giám sát',
                ]);
                foreach ($result_Task['data'] as $key => $value) {
                    $row++;
                   
                    $shifts = $this->getWorkDiary(\Auth::user()->id)['shifts']['data'];
                    $categorys = $this->getWorkDiary(\Auth::user()->id)['categorys']['data'];
                    $departments = $this->getWorkDiary(\Auth::user()->id)['departments'];
                    $shift_name = 'Chưa phân ca';
                    $department_name = '';
                    $category_name = '';
                    foreach ($shifts as $key_shift => $value_shift) {
                        if($value_shift['id'] == $value['work_shift_id']){
                            $shift_name = $value_shift['work_shift_name'];
                            break;
                        }
                    }
                    $count_sub_tasks=0;
                    foreach ($value['sub_tasks'] as $key_sub_tasks => $value_sub_tasks) {
                        if($value_sub_tasks['status'] !='pending'){
                            $count_sub_tasks++;
                        }
                    }
                    $user_infos = $this->getWorkDiary(\Auth::user()->id)['user_infos'];
                    $created_by = null;
                    $supervisor = null;
                    foreach ($user_infos as $key_user_infos => $value_user_infos) {
                        if($value_user_infos['pub_user_id'] == $value['created_by']){
                            $created_by = $value_user_infos['display_name'];
                        }
                        if($value_user_infos['pub_user_id'] == $value['supervisor']){
                            $supervisor = $value_user_infos['display_name'];
                        }
                    }
                    $new_user_task=[];
                    foreach ($value['task_users'] as $key_task_users => $value_task_users) {
                        $new_user_task[]= $value_task_users['user_name'];
                    }
                    $sheet->row($row, [
                        ($key + 1),
                        $shift_name,
                        $value['type'] == 'phat_sinh' ? 'Phát sinh' : 'Lặp lại',
                        $value['task_name'], 
                        $value['task_category']['category_name'], 
                        $value['department']['name'],
                        date('d/m/Y', strtotime(@$value['start_date'])),
                        date('d/m/Y', strtotime(@$value['due_date'])),
                        $count_sub_tasks.'/'.count($value['sub_tasks']),
                        Helper::status_worktask_v2['not_yet_started'],
                        $created_by,
                        implode(',',$new_user_task),
                        $supervisor
                    ]);
                }
            });
        })->store('xlsx',storage_path('exports/'));
        $file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
        return response()->download($file)->deleteFileAfterSend(true);
             
    }
    public function permissionByTask()
    {
        $permission_user = Api::GET('admin/task/getListEmployees?building_id='.$this->building_active_id,[]);
        $rs = @$permission_user->data;
        if($rs){
            return $rs;
        }
    }
}
