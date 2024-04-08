<?php

namespace App\Repositories\Feedback;

//use App\Repositories\Contracts\RepositoryInterface;
use App\Helpers\dBug;
use App\Repositories\Eloquent\Repository;
use App\Util\Debug\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class FeedbackRespository extends Repository {

    const COMPLETED = 1;
    const TYPE_REPAIR_APARTMENT = 'repair_apartment';
    const TYPE_WARRANTY_CLAIM = 'warranty_claim';

    const STATUS_CHUA_XY_LY = 'chua_xu_ly';
    const STATUS_BQL_DA_TIEP_NHAN = 'bql_da_tiep_nhan';
    const STATUS_BQL_DA_NHAN_HO_SO = 'bql_da_nhan_ho_so';
    const STATUS_CHO_CDT_PHAN_HOI = 'cho_cdt_phan_hoi';
    const STATUS_YC_BO_SUNG = 'yc_bo_sung';
    const STATUS_CDT_TU_CHOI = 'cdt_tu_choi';
    const STATUS_CDT_DUYET_YC_COC = 'cdt_duyet_yc_coc';
    const STATUS_DA_COC = 'da_coc';
    const STATUS_DANG_THI_CONG = 'dang_thi_cong';
    const STATUS_TAM_DUNG = 'tam_dung';
    const STATUS_HOAN_THANH = 'hoan_thanh';

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\Feedback\Feedback::class;
    }

    public function getOne($colums = 'id', $id)
    {
        $row = $this->model->where($colums, $id)->first();
        return $row;
    }
    public function findIdFB($id)
    {
        return $this->model->find($id);
    }
    public function searchBy($building_id,$request,$where=[],$type='fback',$perpage = 20)
    {
        if (!empty($request->keyword)) {
            $where[] = ['title', 'Like', '%'.$request->keyword.'%'];
        }

        if (!empty($type)) {
            $where[] = ['type', '=', $type];
        }

        if (!empty($request->rating)) {
            $where[] = ['rating', '=', $request->rating];
        }

        if ($request->repair_status != null) {
            $where[] = ['repair_status', '=', $request->repair_status];
        }
        if (!empty($request->name)) {
            $where[] = ['pub_user_profile_id', '=', (int)$request->name];
        }
        if (!empty($request->apartment_id)) {
            $where[] = ['bdc_apartment_id', '=', (int)$request->apartment_id];
        }

        if ($request->status != null) {
            $where[] = ['status', '=', $request->status];
        }

        $default = [
            'select'   => '*',
            'where'    => $where,
            'order_by' => 'id DESC',
            'per_page' => $perpage,
        ];

        $options = array_merge($default, $where);
        
        extract($options);

        $model = $this->model->select($options['select']);

        if ($options['where']) {
            $model = $model->where($options['where']);
        }
        $model= $model->where('bdc_building_id',$building_id);
        if (!empty($request->floor)) {
            $where[]= $model->whereHas('bdcApartment', function ($query) use ($request) {
                $query->where('floor', '=', $request->floor);
            });
        }
        $list_search = $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
        return $list_search;
    }
    public function searchByApi($building_id,$request,$where=[],$perpage = 20,$id = 0)
    {
        if (!empty($request->keyword)) {
            $where[] = ['title', 'Like', '%'.$request->keyword.'%'];
        }

        if (!empty($request->type)) {
            $where[] = ['type', '=', $request->type];
        }

        if (!empty($request->rating)) {
            $where[] = ['rating', '=', $request->rating];
        }

        if ($request->status != null) {
            $where[] = ['status', '=', $request->status];
        }
        if (!empty($request->name)) {
            $where[] = ['pub_user_profile_id', '=', (int)$request->name];
        }
        if (!empty($request->apartment_id)) {
            $where[] = ['bdc_apartment_id', '=', (int)$request->apartment_id];
        }
        if (!empty($request->building_id)) {
            $where[] = ['bdc_building_id', '=', (int)$request->building_id];
        }

        $default = [
            'select'   => '*',
            'where'    => $where,
            'order_by' => 'id DESC',
            'per_page' => $perpage,
        ];

        $options = array_merge($default, $where);
        extract($options);

        $model = $this->model->select($options['select']);

        if ($options['where']) {
            $model = $model->where($options['where']);
        }
        if($id>0){
            $model = $model->where('pub_user_profile_id',$id);
        }
        $model= $model->where('bdc_building_id',$building_id)->where('type', '<>', self::TYPE_REPAIR_APARTMENT);
        $list_search = $model->orderByRaw($options['order_by'])->paginate($options['per_page']);

        return $list_search;
    }

    public function searchRepairApartment($building_id, $request, $perpage = 20)
    {
        return $this->model->where(['bdc_building_id' => $building_id, 'type' => self::TYPE_REPAIR_APARTMENT, 'bdc_apartment_id' => $request->bdc_apartment_id])->orderBy('created_at', 'desc')->paginate($perpage);
    }


    public function deleteAt($request)
    {
        $ids = $request->input('ids', []);

        // chuyển sang kiểu array
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $list = [];
        foreach ($ids as $id) {
            $list[] = (int) $id;
        }

        $number = $this->model->destroy($list);

        $message = [
            'error'  => 0,
            'status' => 'success',
            'msg'    => "Đã xóa $number bản ghi!",
        ];

        return response()->json($message);
    }
    public function getDashbroad($building_id)
    {
        return $this->model->where('bdc_building_id',$building_id)->orderBy('updated_at', 'DESC')->limit(5)->get();
    }
    public function status($request)
    {
        $ids    = $request->input('ids', []);
        $status = $request->input('status', 1);

        // convert to array
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $list = [];
        foreach ($ids as $id) {
            $list[] = (int) $id;
        }

        $this->model->whereIn('id', (array) $list)->update(['status' => (int) $status]);

        $message = [
            'error'  => 0,
            'status' => 'success',
            'msg'    => 'Đã cập nhật trạng thái!',
        ];

        return response()->json($message);
    }

    public function per_page($request)
    {
        $per_page = $request->input('per_page', 20);

        Cookie::queue('per_page', $per_page, 60 * 24 * 30);

        return back();
    }
    public function action($request)
    {
        Log::info('check_command_feekback','2_'.json_encode($request->all()));
        $method = $request->input('method', '');
        if ($method == 'delete') {
            $this->deleteAt($request);
        } elseif ($method == 'status') {
            $this->status($request);
        } elseif ($method == 'per_page') {
            $this->per_page($request);
        }
        return back();
    }

    public function findByActiveBuilding($active_building)
    {
        return $this->model->where('bdc_building_id', $active_building)->where('status', self::COMPLETED)->get();
    }
    public function whereFindFail(array $select,$id,$post_id)
    {
        return $this->model->select($select)->where($id, $post_id)->firstOrFail();
    }
    public function logFeedbackApartment($apartment_id)
    {
        return $this->model->where('bdc_apartment_id', $apartment_id)->get();
    }
    public function updateStatus($status,$building_id,$id)
    {
        return $this->model->where('bdc_building_id', $building_id)->where('id',$id)->update(['status'=>$status]);
    }
    public function countItem($building = 0,$type = 'fback')
    {
        return $this->model->where('type',$type)->where('bdc_building_id',$building)->count();
    }

    public function repairChangeStatus($ids, $type = self::STATUS_HOAN_THANH)
    {
        $this->model->whereIn('id', $ids)->update(['repair_status' => $type]);

        $message = [
            'error'  => 0,
            'status' => 'success',
            'msg'    => 'Đã cập nhật trạng thái!',
        ];

        return response()->json($message);
    }
    public function repairChangeStatusV2($ids, $type)
    {
        $this->model->whereIn('id', $ids)->update(['status' => $type]);

        $message = [
            'error'  => 0,
            'status' => 'success',
            'msg'    => 'Đã cập nhật trạng thái!',
        ];

        return response()->json($message);
    }
    public function searchAjaxByAll(array $options = [],$building_id, $perpage = 10)
    {
        $default = [
            'select'   => '*',
            'where'    => [],
            'order_by' => 'id DESC',
            'per_page' => $perpage,
        ];

        $options = array_merge($default, $options);
        extract($options);

        $model = $this->model->select($options['select']);
        if ($options['where']) {
            $model = $model->where($options['where']);
        }
        $model = $model->where(['bdc_building_id'=>$building_id,'type'=>'fback']);
       
        return $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
    }
}
