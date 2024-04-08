<?php

namespace App\Http\Controllers\WorkDiary;

use App\Http\Controllers\BuildingController;
use App\Models\WorkDiary\WorkDiary;
use App\Models\BoUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Validator;
use Carbon\Carbon;
use App\Http\Requests\WorkDiary\WorkDiaryRequest;
use App\Models\Campain;
use App\Models\Fcm\Fcm;
use App\Models\SentStatus;
use App\Repositories\WorkDiary\WorkDiaryRepository;
use App\Repositories\WorkDiary\WorkDiaryStatusRepository;
use App\Repositories\UsersRepository\UserInfoRepository;
use App\Repositories\Department\DepartmentRepository;
use App\Repositories\Department\DepartmentStaffRepository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Repositories\SystemFiles\SystemFilesRespository;
use App\Repositories\Building\BuildingRepository;
use App\Repositories\Feedback\FeedbackRespository;
use App\Repositories\MaintenanceAsset\MaintenanceAssetRepository;
use App\Services\FCM\SendNotifyFCMService;
use App\Services\LogImportService;
use Illuminate\Support\Facades\DB;

class WorkDiaryController extends BuildingController
{
    const POST_NEW = "NTASK";
    private $model;
    private $department;
    private $department_staff;
    private $user_profile;
    private $system_file;
    private $building;
    private $feedback;
    private $maintenance_asset;

    private $auth_id;

    /**
     * Constructor.
     */
    public function __construct(
        Request $request,
        WorkDiaryRepository $model,
        DepartmentRepository $department,
        DepartmentStaffRepository $department_staff,
        PublicUsersProfileRespository $user_profile,
        SystemFilesRespository $system_file,
        BuildingRepository $building,
        FeedbackRespository $feedback,
        MaintenanceAssetRepository $maintenance_asset
    )
    {
        //$this->middleware('route_permision');
        $this->model            = $model;
        $this->department       = $department;
        $this->department_staff = $department_staff;
        $this->user_profile     = $user_profile;
        $this->system_file      = $system_file;
        $this->building         = $building;
        $this->feedback         = $feedback;
        $this->maintenance_asset= $maintenance_asset;
        parent::__construct($request);
        // $this->auth_id          = \Auth::user()->getUserInfoId($this->building_active_id)->id;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // dd(\Auth::user());
        $CheckUserInfo= \Auth::user()->getUserInfoId($this->building_active_id);
        if(!$CheckUserInfo){
           return redirect()->away('/admin')->with(['warning' => 'Vui lòng cập nhật bộ phận và nhân sự để sử dụng tính năng này!']);
        }
        $this->auth_id= \Auth::user()->getUserInfoId($this->building_active_id)->id;
       
        $data               = $this->getAttribute();
        $data['filter']     = $request->all();
        $data['meta_title'] = 'Quản lý tòa nhà';
        $data['per_page']   = Cookie::get('per_page', 10);

        // kiểm tra tài khoản có thuộc ban giám sát hay superadmin hay không
        $supervisors = [];
        $check = false;

        $building          = $this->building->getActiveBuilding($this->building_active_id);
        $bdc_department_id = $building->bdc_department_id;
        $department        = $this->department->findByBuildingIdAndDepartmentId($this->building_active_id, $bdc_department_id);
        $supervisors       = [];

        if( $department ) {
            $list_id = $department->department_staffs;
            foreach ($list_id as $staff) {
                $supervisors[] = @$staff->publicUser->getUserInfoId($this->building_active_id)->id;
            }
        }

        if( \Auth::user()->id == $building->manager_id || in_array($this->auth_id, $supervisors) ) {
            $check = true;
        }
//        dd( \Auth::user()->infoWeb()->where('bdc_building_id',$this->building_active_id)->first());

        // end kiểm tra
        $data['tasks']      = $this->model->myPaginate($data['filter'], $data['per_page'], $this->building_active_id, $this->auth_id, $check);
        $data['keyword']    = $request->input('keyword', '');
        $data['colors']     = $this->model::COLOR;
        $data               = array_merge($data, $this->getCountForDashboart());
        // dd($data);
        return view('work-diary.index', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(WorkDiaryRequest $request)
    {
        $this->auth_id          = \Auth::user()->getUserInfoId($this->building_active_id)->id;
        $data               = $request->except('_token');

        // if assign_to no selected else assign it with auth_id
        if( $data['assign_to'] == 0 && $data['bdc_department_id'] == 0 ) {
            $data['assign_to'] = $this->auth_id;
        }

        $data['created_by']      = $this->auth_id;
        $data['bdc_building_id'] = $this->building_active_id;

        // save log json
        $new_log       = [
            'user_id' => $this->auth_id,
            'action'  => 'add',
            'time'    => date("H:i d/m/Y"),
        ];

        $data['logs']  = [];

        array_push($data['logs'], $new_log);
        $data['logs']       = json_encode($data['logs']);

        $new_task = $this->model->create($data);

        // if has file
        if( $request->file_work_diarys ) {
            $file_for_cronjob = [];
            foreach($request->file_work_diarys as $file_work_diary) {
                $checkFile = $this->system_file->checkMultiFile($file_work_diary, '', $this->building_active_id, \Auth::user()->getUserInfoId($this->building_active_id));
                // if file not exits
                if( $checkFile['status'] == 'NOT_OK' ) {
                    $dataResponse = [
                        'success' => false,
                    ];
                } else {
                    // if file exits

                    $file_data=[
                        'building_id' => $this->building_active_id,
                        'name'        => $checkFile['data']['name'],
                        'description' => '',
                        'type'        => $checkFile['data']['type'],
                        'url'         => $checkFile['data']['url'],
                        'model_type'  => 'App\Models\WorkDiary',
                        'model_id'    => $new_task->id,
                        'status'      => 1
                    ];

                    array_push($file_for_cronjob, $file_data);

                    $insertFile = $this->system_file->create($file_data);
                    // check insert file
                    if(!$insertFile){
                        $dataResponse = [
                            'file_message' => 'Thêm mới file không thành công!',
                        ];
                    }

                }
            }

            LogImportService::setItemForQueueTask($file_for_cronjob, $this->building_active_id);
        }

        // notify
        if ( $new_task ) {
            $list_id = [];

            // staff selected
            $building                 = $this->building->getActiveBuilding($this->building_active_id);
            $bdc_department_id        = $building->bdc_department_id;
            // all member supervisors
            $supervisor_ids           = $this->department_staff->staffByDepartment($bdc_department_id)->pluck('pub_user_id');
            $list_id                  = array_merge($list_id, $supervisor_ids->toArray());

            // if have department
            if( $new_task->bdc_department_id != 0 ) {
                $head_department = $this->department->findByBuildingIdAndDepartmentId($this->building_active_id, $new_task->bdc_department_id)->head_department;
                if($head_department) {
                    // head of department staff
                    $head_department_staff_id = $head_department->publicUser->getUserInfoId($this->building_active_id)->id;
                    array_push($list_id, $new_task->created_by, (int) $new_task->assign_to, $head_department_staff_id);
                }

            } else {
                array_push($list_id, $new_task->created_by, (int) $new_task->assign_to);
            }

            $list_id = array_unique($list_id);

            $data_noti = [
                'type'    => SendNotifyFCMService::NEW_TASK,
                'screen'  => 'man hinh',
                'message' => 'Đã có công việc mới',
                'id'      => $new_task->id,
                'user_id' => $list_id,
                "action_name"=> self::POST_NEW,
                "image"=> '',
            ];
            
            $countTokent = (int)Fcm::getCountTokenbyUserId($list_id);  
            $total = ['email'=>0, 'app'=> $countTokent, 'sms'=> 0];
            $campain = Campain::updateOrCreateCampain("Thông báo công việc", config('typeCampain.TASK'), $new_task->id, $total, $this->building_active_id, 0, 0);
    
            SendNotifyFCMService::setItemForQueueNotify(array_merge($data_noti,['building_id' =>$this->building_active_id, 'campain_id' => $campain->id,'app'=>'v1']));

        }

        $dataResponse = [
            'success' => true,
            'message' => 'Thêm mới công việc thành công!',
            'url'     => route('admin.work-diary.index'),
        ];

        // return response()->json($dataResponse);
        return redirect( route('admin.work-diary.index') )->with('success', 'Thêm mới công việc thành công!');
    }

    public function update(WorkDiaryRequest $request, $id = 0)
    {
        $this->auth_id          = \Auth::user()->getUserInfoId($this->building_active_id)->id;
        if( $this->model->find($id)->status == 5 ) {
            abort('404');
        }

        $data               = $request->except('_token', 'file_work_diarys');

        $data['updated_by'] = $this->auth_id;

        // update log json
        $new_log       = [
            'user_id' => $this->auth_id,
            'action'  => 'edit',
            'time'    => date("H:i d/m/Y"),
        ];

        // merge with old logs of model
        $data['logs']           = json_decode($this->model->find($id)->logs, true);
        if ( $data['logs'] == null ) {
            $data['logs']  = [];
        }
        array_push($data['logs'], $new_log);
        $data['logs']       = json_encode($data['logs']);

        $new_task = $this->model->update($data, $id);

        // if has file
        if( $request->file_work_diarys ) {
            $file_for_cronjob = [];
            $this->system_file->deleteModulFile('App\Models\WorkDiary', $id);
            foreach($request->file_work_diarys as $file_work_diary) {
                $checkFile = $this->system_file->checkMultiFile($file_work_diary, '', $this->building_active_id, \Auth::user()->getUserInfoId($this->building_active_id));
                // if file not exits
                if( $checkFile['status'] == 'NOT_OK' ) {
                    $dataResponse = [
                        'success' => false,
                    ];
                } else {
                    // if file exits

                    $file_data=[
                        'building_id' => $this->building_active_id,
                        'name'        => $checkFile['data']['name'],
                        'description' => '',
                        'type'        => $checkFile['data']['type'],
                        'url'         => $checkFile['data']['url'],
                        'model_type'  => 'App\Models\WorkDiary',
                        'model_id'    => $id,
                        'status'      => 1
                    ];

                    array_push($file_for_cronjob, $file_data);

                    $insertFile = $this->system_file->create($file_data);
                    // check insert file
                    if(!$insertFile){
                        $dataResponse = [
                            'file_message' => 'Thêm mới file không thành công!',
                        ];
                    }

                }
            }

            LogImportService::setItemForQueueTask($file_for_cronjob, $this->building_active_id);
        }

        // notify
        $list_id = [];
        $new_task = $this->model->find($id);
        // staff selected
        $building                 = $this->building->getActiveBuilding($this->building_active_id);
        $bdc_department_id        = $building->bdc_department_id;
        // all member supervisors
        $supervisor_ids           = $this->department_staff->staffByDepartment($bdc_department_id)->pluck('pub_user_id');
        $list_id                  = array_merge($list_id, $supervisor_ids->toArray());

        // if have department
        if( $new_task->bdc_department_id != 0 ) {
            $head_department = $this->department->findByBuildingIdAndDepartmentId($this->building_active_id, $new_task->bdc_department_id)->head_department;
                if($head_department) {
                    // head of department staff
                    $head_department_staff_id = $head_department->publicUser->getUserInfoId($this->building_active_id)->id;

                    array_push($list_id, $new_task->created_by, (int) $new_task->assign_to, $head_department_staff_id);
                }
        } else {
            array_push($list_id, $new_task->created_by, (int) $new_task->assign_to);
        }

        $list_id = array_unique($list_id);
        $countTokent = (int)Fcm::getCountTokenbyUserId($list_id);  
        $total = ['email'=>0, 'app'=> $countTokent, 'sms'=> 0];
        $campain = Campain::updateOrCreateCampain("Đã có công việc được cập nhật", config('typeCampain.TASK'), $new_task->id, $total, $this->building_active_id, 0, 0);

        $data_noti = [
            'type'    => SendNotifyFCMService::NEW_TASK,
            'screen'  => 'man hinh',
            'message' => 'Đã có công việc được cập nhật',
            'id'      => $new_task->id,
            'user_id' => $list_id,
            "action_name"=> self::POST_NEW,
            "image"=> '',
            'building_id' =>$this->building_active_id,
            'campain_id' => $campain->id,
            'app'=>'v1'
        ];

        SendNotifyFCMService::setItemForQueueNotify($data_noti);

        return redirect( route('admin.work-diary.index') )->with('success', 'Cập nhật công việc thành công!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\WorkDiary  $buildingHandbook
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data                      = $this->getAttribute();
        $data['meta_title']        = 'Quản lý tòa nhà';
        $data['task']              = $this->model->find($id);
        // find user of department
        $department = $this->department->findByBuildingIdAndDepartmentId($this->building_active_id, $data['task']->bdc_department_id);

        if( $department ) {
            $list_id = $department->department_staffs->pluck('pub_user_id');
            $data['userprofiles']      = $this->user_profile->findByPubUserIdAndBuildingId($list_id, $this->building_active_id);
        } else {
            $data['userprofiles']      = $this->user_profile->findByPubUserIdAndBuildingId([$data['task']->assign_to], $this->building_active_id);
        }

        return view('work-diary.edit', $data);
    }

    public function create()
    {
        $data = $this->getAttribute();
        $data['meta_title'] = 'Quản lý tòa nhà';
        return view('work-diary.create', $data);
    }

    public function reportWork($id)
    {
        $data['meta_title']       = 'Quản lý tòa nhà';
        $data['task']             = $this->model->find($id);
        $data['system_files']     = $this->system_file->getModulFile('App\Models\WorkDiary',$id)??'';
        $data['check_permission'] = $this->checkPermission($data['task']);
        $data['review_note']      = json_decode($data['task']->review_note, true);
        $data['maintenance_asset']= $data['task']->maintenance_asset;
        $data['feedback']         = $data['task']->feedback;
        // dd($data);

        // get value of name user, name status for view
        if(!empty($data['review_note'])) {
            foreach($data['review_note'] as $key => $val) {
                $data['review_note'][$key]['user_id']         = $this->user_profile->find($val['user_id'])->display_name;
                $data['review_note'][$key]['previous_status'] = $this->checkStatus($val['previous_status']);
                $data['review_note'][$key]['current_status']  = $this->checkStatus($val['current_status']);
                $data['avatar'][$key]                         = $this->user_profile->find($val['user_id'])->avatar;
            };
        }
        // dd($data);
        return view('work-diary.report-work', $data);
    }

    public function delete(Request $request)
    {
        $id = $request->input('ids')[0];
        $this->model->delete(['id' => $id]);

        $request->session()->flash('success', 'Xóa công việc thành công');
    }

    private function getAttribute()
    {
        return [
            'departments'         => $this->department->findByBuildingId($this->building_active_id),
            // 'deadlines'           => $this->model->findByBuildingId($this->building_active_id)->pluck('end_at'),
            'feedbacks'           => $this->feedback->findByActiveBuilding($this->building_active_id),
            'maintenance_assets'  => $this->maintenance_asset->findByActiveBuilding($this->building_active_id),
        ];
    }

    public function ajaxGetPeopleHand(Request $request)
    {
        $bdc_department_id = $request->input('bdc_department_id');
        $department = $this->department->findByBuildingIdAndDepartmentId($this->building_active_id, $bdc_department_id);
        $list_id = $department->department_staffs;
        $data['userprofiles'] = [];
        foreach ($list_id as $staff) {
            $data['userprofiles'][] = $staff->publicUser->getUserInfoId($this->building_active_id);
        }

        return \response()->json($data);
    }

    public function ajaxGetPreviousStatus($id)
    {
        $previous_status = $this->model->find($id)->status;

        return \response()->json([
            'success'         => true,
            'previous_status' => $previous_status,
        ]);
    }

    public function ajaxDelMultiWorkDiary(Request $request)
    {
        $this->model->deleteMulti($request->ids);
        $dataResponse = [
            'success' => true,
            'message' => 'Xóa công việc thành công!'
        ];
        return response()->json($dataResponse);
    }

    public function ajaxUpdateReviewNote(Request $request, $id)
    {
        $this->auth_id          = \Auth::user()->getUserInfoId($this->building_active_id)->id;
        $previous_status = $request->previous_status;
        $new_status      = $request->new_status;

        do {
            $files_name = $request->name_fileupload;
            $file_doc_new=null;
            if($files_name){
                $forder = date('d-m-Y');
                $directory = 'media/image/feedback';
                if (!is_dir($directory)) {
                    mkdir($directory);
                    mkdir($directory . '/' . $forder);
                }else{
                    if (!is_dir($directory . '/' . $forder)) {
                        mkdir($directory . '/' . $forder);
                    }
                }
                $file_doc =$_SERVER['DOCUMENT_ROOT'].'/' . $directory . '/' . $forder . '/' . $request->name_fileupload;
                $file_doc_new='/' . $directory . '/' . $forder . '/' . $request->name_fileupload;
                $file = fopen($file_doc, "wb");
                $data_file = explode(',', $request->fileBase64);
                fwrite($file, base64_decode($data_file[1]));
                fclose($file);
            }
            $new_review_note = [
                'user_id'         => $this->auth_id,
                'previous_status' => $previous_status,
                'current_status'  => $new_status,
                'note'            => $request->content,
                'date'            => date("H:i d/m/Y"),
                'file_name'            => $request->name_fileupload,
                'url_file'            =>  $file_doc_new,
            ];

            // merge with old review_note of model
            $data['review_note']           = json_decode($this->model->find($id)->review_note, true);

            if ( $data['review_note'] == null ) {
                $data['review_note']  = [];
            }

            array_push($data['review_note'], $new_review_note);

            $data['review_note']       = json_encode($data['review_note']);

            $this->model->update($data, $id);

            // if new status greater previous status else update
            if( $new_status != WorkDiary::PROCESSING || $previous_status != WorkDiary::RE_WORK ) {
                $this->changeStatus($new_status, $id);
            }

            // change value when selected re_work
            if ( $new_status == WorkDiary::RE_WORK ) {
                $previous_status = WorkDiary::RE_WORK;
                $new_status      = WorkDiary::PROCESSING;
            }
        } while( $new_review_note['current_status'] == WorkDiary::RE_WORK );

        $dataResponse = [
            'success'         => true,
            'user'            => $this->user_profile->find($this->auth_id)->display_name,
            'previous_status' => ''
        ];
        return response()->json($dataResponse);
    }

    public function checkStatus($status_id)
    {
        switch($status_id) {
            case WorkDiary::UN_PROCESS:
                return "Chưa thực hiện";
                break;
            case WorkDiary::PROCESSING:
                return "Đang thực hiện";
                break;
            case WorkDiary::PROCESSED:
                return "Đã thực hiện";
                break;
            case WorkDiary::RE_WORK:
                return "Cần làm lại";
                break;
            case WorkDiary::CHECKED:
                return "Đã kiểm tra";
                break;
            case WorkDiary::DONE:
                return "Đã duyệt";
                break;
        }
    }

    public function downloadfile(Request $request)
    {
            //file path in server
        $file_path = $_SERVER['DOCUMENT_ROOT'].'/'.$request->downloadfile;
        // check if file exist in server
        if(file_exists($file_path)) {
            header('Content-Description: File Transfer');
            header('Content-Disposition: attachment; filename="'.basename($file_path).'"');
            header('Expires: 0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file_path));
            // Clear output buffer
            flush();
            readfile($file_path);
            exit();
        }else{
            echo "File not found.";
        }
    }
    public function checkPermission($task)
    {
        $this->auth_id          = \Auth::user()->getUserInfoId($this->building_active_id)->id;
        $building          = $this->building->getActiveBuilding($this->building_active_id);
        $bdc_department_id = $building->bdc_department_id;
        $department        = $this->department->findByBuildingIdAndDepartmentId($this->building_active_id, $bdc_department_id);
        $supervisor_ids    = [];

        if($department) {
            $list_id           = $department->department_staffs;
            $supervisor_ids    = [];
            foreach ($list_id as $staff) {
                $supervisor_ids[] = @$staff->publicUser->getUserInfoId($this->building_active_id)->id ?? '';
            }
        }

        if( \Auth::user()->id == $building->manager_id ) {
            return WorkDiary::P_MANAGER;
        } elseif( in_array($this->auth_id, $supervisor_ids) ) {
            return WorkDiary::P_SUPERVISOR;
        } elseif( $this->auth_id == $task->assign_to ) {
            return WorkDiary::P_ASSIGN_TO;
        } elseif ( $this->auth_id == $task->created_by ) {
            return WorkDiary::P_CREATED_BY;
        } else {
            return WorkDiary::P_OTHER;
        }
    }

    public function changeStatus($new_status, $id)
    {
        $data['status'] = $new_status;

        $this->model->update($data, $id);
    }

    public function action(Request $request)
    {
        if ($request->has('per_page')) {
            $per_page = $request->input('per_page', 10);
            Cookie::queue('per_page', $per_page, 60 * 24 * 30);
            Cookie::queue('tab', $request->tab);
        }

        return redirect()->back()->with('tab', $request->tab);
    }

    public function getCountForDashboart()
    {
        $start_day   = date("Y/m/d H:i:s", strtotime('today'));
        $end_day     = date("Y/m/d H:i:s", strtotime('tomorrow')-1);
        $start_week  = date("Y/m/d H:i:s", strtotime('monday this week'));
        $end_week    = date("Y/m/d H:i:s", strtotime('monday next week')-1);
        $start_month = date("Y/m/d H:i:s", strtotime('midnight first day of this month'));
        $end_month   = date("Y/m/d H:i:s", strtotime('midnight first day of next month')-1);

        return [
            'all_tasks_day'         => $this->model->findByTime($this->building_active_id, $start_day, $end_day)->count(),
            'all_tasks_week'        => $this->model->findByTime($this->building_active_id, $start_week, $end_week)->count(),
            'all_tasks_month'       => $this->model->findByTime($this->building_active_id, $start_month, $end_month)->count(),

            'done_tasks_day'        => $this->model->findByTime($this->building_active_id, $start_day, $end_day)->where('status', WorkDiary::DONE)->count(),
            'done_tasks_week'       => $this->model->findByTime($this->building_active_id, $start_week, $end_week)->where('status', WorkDiary::DONE)->count(),
            'done_tasks_month'      => $this->model->findByTime($this->building_active_id, $start_month, $end_month)->where('status', WorkDiary::DONE)->count(),

            'checked_tasks_day'     => $this->model->findByTime($this->building_active_id, $start_day, $end_day)->where('status', WorkDiary::CHECKED)->count(),
            'checked_tasks_week'    => $this->model->findByTime($this->building_active_id, $start_week, $end_week)->where('status', WorkDiary::CHECKED)->count(),
            'checked_tasks_month'   => $this->model->findByTime($this->building_active_id, $start_month, $end_month)->where('status', WorkDiary::CHECKED)->count(),

            'unchecked_tasks_day'   => $this->model->findByTime($this->building_active_id, $start_day, $end_day)->where('status', '<', WorkDiary::CHECKED)->count(),
            'unchecked_tasks_week'  => $this->model->findByTime($this->building_active_id, $start_week, $end_week)->where('status', '<', WorkDiary::CHECKED)->count(),
            'unchecked_tasks_month' => $this->model->findByTime($this->building_active_id, $start_month, $end_month)->where('status', '<', WorkDiary::CHECKED)->count(),
        ];
    }
}