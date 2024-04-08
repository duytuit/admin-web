<?php

namespace App\Http\Controllers\ActivityLog;

use Illuminate\Http\Request;
use App\Http\Controllers\BuildingController;
use App\Models\ActivityLog\ActivityLog;
use App\Models\ActivityLog\LogActionDB;
use App\Models\ActivityLog\LogActiveTool;
use App\Models\Permissions\Permission;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Repositories\Apartments\ApartmentsRespository;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends BuildingController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private $model;

    public function __construct(Request $request,PublicUsersProfileRespository $model, ApartmentsRespository $apartmentRepo)
    {
        $this->model = $model; 
        $this->apartmentRepo = $apartmentRepo;
        parent::__construct($request);
    }
    public function index(Request $request)
    {
        $data['meta_title'] = 'Activity Log';
        $data['filter'] = $request->all();
        if(isset($data['filter']['bdc_apartment_id'])){
            $data['get_apartment'] = $this->apartmentRepo->findById($data['filter']['bdc_apartment_id']);
        }
        $activity_log = ActivityLog::where(function($query) use($request){

            if(isset($request->keyword_word) && $request->keyword_word != null){
                $query->where('properties', 'like', '%' . $request['keyword_word'] . '%');
            }

        })->orderBy('created_at','desc')->paginate(30);

        if(isset($data['filter']['subject_id'])){
            $data['user_info'] = $this->model->findAllBy_v1((int)$data['filter']['subject_id']);
        }

        $data['activity_log'] = $activity_log;
        $data['display_count'] = count($activity_log);
        return view('activity-log.index', $data);
    }
    public function LogActiveTool(Request $request)
    {
        $building_id = $this->building_active_id;
        $data['meta_title'] = 'Activity Log';
        $data['filter'] = $request->all();
        $data['per_page'] = Cookie::get('per_page',10);
        $data['permissions'] = Permission::select('id','title')->get();
        if(isset($data['filter']['bdc_apartment_id'])){
            $data['get_apartment'] = $this->apartmentRepo->findById($data['filter']['bdc_apartment_id']);
        }
        $log_active_tool = LogActiveTool::where(function ($query) use ($request) {
            if ($request->tool_id) {
                $query->where('tool_id', (int)$request->tool_id);
            }
            if ($request->request_id) {
                $query->where('request_id', $request->request_id);
            }
            if ($request->action) {
                $query->where('action', $request->action);
            }
            if (isset($request->from_date) && $request->from_date !=null && isset($request->to_date) && $request->to_date !=null) {
                $from_date = strtotime($request->from_date.'+7 hour');
                $to_date = strtotime($request->to_date.'+31 hour');
                $query->whereBetween('timestamp',[$from_date,$to_date]);
            }
        })
        ->where(function ($query) use ($request) {
            if ($request->bdc_apartment_id) {
                $bdc_apartment_id = (int)$request->bdc_apartment_id;
                $query->where('url','like','%apartment_id='.$bdc_apartment_id.'%')
                        ->where('url','like','%apartment_id='.$bdc_apartment_id.'%');
            }
        })->orderBy('updated_at','desc')->paginate(10);
        $data['log_active_tool'] = $log_active_tool;
        return view('activity-log.log_active_tool', $data);
    }
    public function LogActionDB(Request $request)
    {
        $building_id = $this->building_active_id;
        $data['meta_title'] = 'Activity Log';
        $data['filter'] = $request->all();
        if(isset($data['filter']['bdc_apartment_id'])){
            $data['get_apartment'] = $this->apartmentRepo->findById($data['filter']['bdc_apartment_id']);
        }
        $data['per_page'] = Cookie::get('per_page',10);
        $log_action_db =LogActionDB::where(function ($query) use ($request) {
            if ($request->row_id) {
                $query->where('row_id', (int)$request->row_id);
            }
            if ($request->request_id) {
                $query->where('request_id', $request->request_id);
            }
          
            if ($request->table) {
                $query->where('table', $request->table);
            }
            if ($request->action) {
                $query->where('action', $request->action);
            }
            if (isset($request->from_date) && $request->from_date !=null && isset($request->to_date) && $request->to_date !=null) {
                $from_date = strtotime($request->from_date.'+7 hour');
                $to_date = strtotime($request->to_date.'+31 hour');
                $query->whereBetween('timestamp',[$from_date,$to_date]);
            }
        })
        ->where(function ($query) use ($request) {
            if ($request->bdc_apartment_id) {
                $bdc_apartment_id = (int)$request->bdc_apartment_id;
                $query->where('data_old','like','%apartment_id":"'.$bdc_apartment_id.'%')
                        ->where('data_new','like','%apartment_id":"'.$bdc_apartment_id.'%');
            }
        })->orderBy('updated_at','desc')->paginate(10);
        $data['log_action_db'] = $log_action_db;
        return view('activity-log.log_action_db', $data);
    }
    public function ajaxGetSelectTable(Request $request)
    {
        if ($request->search) {
            $where[] = ['table', 'like', '%' . $request->search . '%'];
            return response()->json($this->searchByAll(['where' => $where], $this->building_active_id));
        }
        return response()->json($this->searchByAll(['select' => ['request_id', 'table']], $this->building_active_id));
    }
    public function searchByAll(array $options = [],$building_id)
    {
        $default = [
            'select'   => '*',
            'where'    => [],
            'order_by' => '_id DESC',
            'per_page' => 20,
        ];

        $options = array_merge($default, $options);

        extract($options);

        $model = LogActionDB::select($options['select']);

        if ($options['where']) {
            $model = $model->where($options['where']);
        }
        $model = $model->where(['building_id' => $building_id]);
        return $model->orderBy('updated_at','desc')->paginate($options['per_page']);
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
