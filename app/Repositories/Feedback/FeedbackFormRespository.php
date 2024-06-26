<?php

namespace App\Repositories\Feedback;

//use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Eloquent\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class FeedbackFormRespository extends Repository {




    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\Feedback\FeedbackForm::class;
    }

    public function getOne($colums = 'id', $id)
    {
        $row = $this->model->where($colums, $id)->first();
        return $row;
    }
    public function findIdFB($id)
    {
        return $this->model->findOrFail($id);
    }
    public function searchBy($building_id,$request='',$where=[],$perpage = 20)
    {
        if (!empty($request->keyword)) {
            $where[] = ['title', 'Like', '%'.$request->keyword.'%'];
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
//       dd($model->orderByRaw($options['order_by'])->paginate($options['per_page']));
        $list_search = $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
//        dd($list_search);
        return $list_search;
    }
    public function searchByApi($building_id,$request='',$where=[],$perpage = 20,$id = 0)
    {
        if (!empty($request->keyword)) {
            $where[] = ['title', 'Like', '%'.$request->keyword.'%'];
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
        if($id>0){
            $model = $model->where('pub_user_profile_id',$id);
        }
        $model= $model->where('bdc_building_id',$building_id);
        $list_search = $model->orderByRaw($options['order_by'])->paginate($options['per_page']);

        return $list_search;
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
        $method = $request->input('method', '');
        if ($method == 'delete') {
            $this->deleteAt($request);
            return back();
        } elseif ($method == 'status') {
            return $this->status($request);
        } elseif ($method == 'per_page') {
            return $this->per_page($request);
        }
        return back();
    }

    public function findByActiveBuilding($active_building)
    {
        return $this->model->where('bdc_building_id', $active_building)->get();
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
}
