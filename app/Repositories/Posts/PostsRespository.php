<?php

namespace App\Repositories\Posts;

//use App\Repositories\Contracts\RepositoryInterface;
use App\Models\Apartments\Apartments;
use App\Models\Customers\Customers;
use App\Models\PostRegister;
use App\Models\PublicUser\UserInfo;
use App\Models\PublicUser\Users;
use App\Repositories\Eloquent\Repository;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

class PostsRespository extends Repository {

    const USE_POST = 1;
    const NOT_USE_POST = 0;



    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\Posts\Posts::class;
    }
    public function getOne($colums = 'id', $id)
    {
        return $this->model->where($colums, $id)->first();
    }

    public function getOneSelect($select,$colums, $id)
    {
        return $this->model->select($select)->where($colums, $id)->first();
    }
    public function searchBy($building_id,$request,$where=[],$perpage = 20)
    {
        if (!empty($request->keyword)) {
            $where[] = ['title', 'Like', '%'.$request->keyword.'%'];
        }

        if (!empty($request->hashtag)) {
            $where[] = ['hashtag', 'Like', '%'.$request->hashtag.'%'];
        }

        if (!empty($request->category_id)) {
            $where[] = ['category_id', 'Like', '%'.$request->category_id.'%'];
        }

        if (!empty($request->type)) {
            $where[] = ['type', '=', $request->type];
        }

        if ($request->status != null) {
            $where[] = ['status', '=', $request->status];
        }

        if ($request->private != null) {
            $where[] = ['private', '=', $request->private];
        }

        if (!empty($request->name)) {
            $where[] = ['pub_user_profile_id', '=', (int)$request->name];
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
        $list_search = $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
        $list_search->load('user','user.info');
        return $list_search;
    }
    public function searchByApi($building_id,$request,$where=[],$perpage = 20)
    {
//        if (!empty($request->keyword)) {
//            $where[] = ['title', 'Like', '%'.$request->keyword.'%'];
//        }
//
//        if (!empty($request->hashtag)) {
//            $where[] = ['hashtag', 'Like', '%'.$request->hashtag.'%'];
//        }

        if (!empty($request->category_id)) {
            $where[] = ['category_id', 'Like', '%'.$request->category_id.'%'];
        }

        if (!empty($request->type)) {
            $where[] = ['type', '=', $request->type];
        }

        if ($request->status != null) {
            $where[] = ['status', '=', $request->status];
        }

        if ($request->private != null) {
            $where[] = ['private', '=', $request->private];
        }

        if (!empty($request->name)) {
            $where[] = ['pub_user_profile_id', '=', (int)$request->name];
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
        if (!empty($request->keyword)) {
            $model->Where(function($query) use ($request){
                $query->orWhere('title', 'like', '%' . $request->keyword . '%');
                $query->orWhere('hashtag', 'like', '%' . $request->keyword . '%');
            });
        }

        $list_search = $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
        $list_search->load('user','user.bdcProfile');
        return $list_search->toArray();
    }


    public function searchByApiCustomer($building_id,$info,$request,$where=[],$perpage = 20)
    {
        if (!empty($request->type) && $request->type == 'event') {
            if (!empty($request->keyword)) {
                $where[] = ['title', 'Like', '%' . $request->keyword . '%'];
            }else{
                $where[] = ['type', '=', $request->type];
            }
        }else{
            if (!empty($request->keyword)) {
                $where[] = ['title', 'Like', '%' . $request->keyword . '%'];
            }
            if (!empty($request->type)) {
                $where[] = ['type', '=', $request->type];
            }
        }

        if (!empty($request->hashtag)) {
            $where[] = ['hashtag', 'Like', '%'.$request->hashtag.'%'];
        }

        if (!empty($request->category_id)) {
            $where[] = ['category_id', 'Like', '%'.$request->category_id.'%'];
        }


        $default = [
            'select'   => ['id','title','type','start_at','end_at','response', 'publish_at', 'image', 'user_id', 'notify','attaches','url_video','lists_notify_apartment'],
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
        $model= $model->where('status',1);
        $model= $model->where('publish_at','<=',now());
        if($info){
            $model->Where(function($query) use ($request,$info){
                $query->orWhereRaw('JSON_CONTAINS(notify, \'"1"\',"$.all_selected")');

                $query->orWhereRaw('JSON_CONTAINS(notify, "['.$info->id.']","$.customer_ids")');

                if (!empty($request->place)) {
                    $query->orWhereRaw('JSON_CONTAINS(notify,"['.@$request->place.']","$.place_ids")');
                }
                foreach ($info->bdcCustomers as $bc){
                    $query->orWhereRaw('JSON_CONTAINS(notify,"['.@$bc->bdcApartment->id.']","$.group_ids")');
                    $query->orWhereRaw('JSON_CONTAINS(notify,"['.@$bc->bdcApartment->floor.']","$.floor_ids")');
                }
            });
        }
        if (!empty($request->type) && $request->type == 'event') {
            if (!empty($request->keyword)) {
                if(!empty($request->register)){
                    if($request->register == 'true'){
                        $model->whereHas('register', function($q) use ($info,$request){
                            $q->where('user_id', $info['id']);
                            $q->where('user_type', 'user');
                            $q->where('post_type', $request->type);
                            if(!empty($request->check_in)){
                                if($request->check_in == 'true'){
                                    $q->where('check_in','!=', null);
                                }else{
                                    $q->where('check_in', null);
                                }
                            }
                        });
                    }else{
                        $register = PostRegister::where('user_id', $info['id'])
                            ->where('user_type', 'user')
                            ->where('post_type', $request->type)
                            ->pluck('post_id')
                            ->toArray();
                        $model= $model->whereNotIn('id',[$register]);
                    }

                }
            }
        }
        $list_search = $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
        $list_search->load('user','user.profileAll');
        return $list_search->toArray();
    }
    public function searchByApiCustomer_v2($building_id,$info,$request,$where=[],$perpage = 20)
    {
        if (!empty($request->type) && $request->type == 'event') {
            if (!empty($request->keyword)) {
                $where[] = ['title', 'Like', '%' . $request->keyword . '%'];
            }else{
                $where[] = ['type', '=', $request->type];
            }
        }else{
            if (!empty($request->keyword)) {
                $where[] = ['title', 'Like', '%' . $request->keyword . '%'];
            }
            if (!empty($request->type)) {
                $where[] = ['type', '=', $request->type];
            }
        }

        if (!empty($request->hashtag)) {
            $where[] = ['hashtag', 'Like', '%'.$request->hashtag.'%'];
        }

        if (!empty($request->category_id)) {
            $where[] = ['category_id', 'Like', '%'.$request->category_id.'%'];
        }


        $default = [
            'select'   => ['id','title','type','start_at','end_at','response', 'publish_at', 'image', 'user_id', 'notify','attaches','url_video','lists_notify_apartment'],
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
        $model= $model->where('status',1);
        $model= $model->where('publish_at','<=',now());
        $model->Where(function($query) use ($request,$info){
            $query->orWhereRaw('JSON_CONTAINS(notify, \'"1"\',"$.all_selected")');
            if (!empty($request->place)) {
                $query->orWhereRaw('JSON_CONTAINS(notify,"['.@$request->place.']","$.place_ids")');
            }
            
        });
        if (!empty($request->type) && $request->type == 'event') {
            if (!empty($request->keyword)) {
                if(!empty($request->register)){
                    if($request->register == 'true'){
                        $model->whereHas('register', function($q) use ($info,$request){
                            $q->where('user_type', 'user');
                            $q->where('post_type', $request->type);
                            if(!empty($request->check_in)){
                                if($request->check_in == 'true'){
                                    $q->where('check_in','!=', null);
                                }else{
                                    $q->where('check_in', null);
                                }
                            }
                        });
                    }else{
                        $register = PostRegister::where('user_id', $info['id'])
                            ->where('user_type', 'user')
                            ->where('post_type', $request->type)
                            ->pluck('post_id')
                            ->toArray();
                        $model= $model->whereNotIn('id',[$register]);
                    }

                }
            }
        }
        $list_search = $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
        return $list_search;
    }
    public function fill($input)
    {
        return $this->model->fill($input);
    }
    public function findFail($id)
    {
        return $this->model->findOrFail($id);
    }
    public function findNew($id)
    {
        return $this->model->findOrNew($id);
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
        } elseif ($method == 'status') {
            $this->status($request);
        } elseif ($method == 'per_page') {
            $this->per_page($request);
        }
        return back();
    }
    public function whereFindFail(array $select,$id,$post_id)
    {
        return $this->model->select($select)->where($id, $post_id)->firstOrFail();
    }
    public function selectBy(array $select,$column,$post_id)
    {
        return $this->model->select($select)->where($column, $post_id)->get()->toArray();
    }
    public function selectByApi(array $select,$column,$post_id,$building_id,$info='')
    {

        $info_post = $this->model->select($select)->where($column, $post_id);

        $info_post= $info_post->where('bdc_building_id',$building_id);
        $info_post= $info_post->where('status',1);
        $info_post= $info_post->where('publish_at','<=',now());

        if($info != ''){
            $info_post->Where(function($query) use ($info){
                $query->orWhereRaw('JSON_CONTAINS(notify->"$.all_selected", \'"1"\')');
                $query->orWhereRaw('JSON_CONTAINS(notify->"$.customer_ids", \'["'.$info['id'].'"]\')');
                foreach ($info['bdc_customers'] as $bc){
                    $query->orWhereRaw('JSON_CONTAINS(notify->"$.group_ids", \'["'.$bc['bdc_apartment']['id'].'"]\')');
                    $query->orWhereRaw('JSON_CONTAINS(notify->"$.floor_ids", \'["'.$bc['bdc_apartment']['floor'].'"]\')');
                }
            });
        }


        $info_post = $info_post->first();
        return $info_post;
    }

    public function getPostById($id)
    {
        return $this->model->where('id',$id)->first();
    }
    public function changeStatusPost($request)
    {
        if ($request->status == 'Active') {
            $this->model->whereIn('id', $request->ids)->update(['status' => self::USE_POST]);
        } elseif ($request->status == 'Inactive') {
            $this->model->whereIn('id', $request->ids)->update(['status' => self::NOT_USE_POST]);
        } else {
            $post = $this->model->where('id',$request->id)->first();
            if ($post->status == self::USE_POST) {
                $post->status = self::NOT_USE_POST;
                $post->save();
            } else {
                $post->status = self::USE_POST;
                $post->save();
            }
        }
    }
    public function changeStatusPostNoti($request,$notify)
    {
        if ($request->status == 'Active') {
            $this->model->whereIn('id', $request->ids)->update(['status' => self::USE_POST]);
        } elseif ($request->status == 'Inactive') {
            $this->model->whereIn('id', $request->ids)->update(['status' => self::NOT_USE_POST]);
        } else {
            $post = $this->model->where('id',$request->id)->first();
            if ($post->status == self::USE_POST) {
                $post->status = self::NOT_USE_POST;
                $post->save();
            } else {
                $post->status = self::USE_POST;
                if(isset($notify['is_sent'])){
                    $notify['is_sent'] = 1;
                }
                $post->notify = json_encode($notify);
                $post->save();
            }
        }
    }
    public function changeStatusPostSms($request,$notify)
    {
        if ($request->status == 'Active') {
            $this->model->whereIn('id', $request->ids)->update(['status' => self::USE_POST]);
        } elseif ($request->status == 'Inactive') {
            $this->model->whereIn('id', $request->ids)->update(['status' => self::NOT_USE_POST]);
        } else {
            $post = $this->model->where('id',$request->id)->first();
            if ($post->status == self::USE_POST) {
                $post->status = self::NOT_USE_POST;
                $post->save();
            } else {
                $post->status = self::USE_POST;
                if(isset($notify['is_sent_sms'])){
                    $notify['is_sent_sms'] = 1;
                }
                $post->notify = json_encode($notify);
                $post->save();
            }
        }
    }
//    public function updateIssent($id,$is_sent)
//    {
//       return $this->model->where('id',$id)->update(['notify'=>]);
//    }


    public function getMenuPost($building_id)
    {
        $posts = $this->model->select(['title','type','category_id','status','publish_at','id'])->where('bdc_building_id',$building_id)->orderBy('updated_at', 'DESC')->limit(5)->get();
        $posts = $posts->load('category');
        return $posts;
    }

    public function countItem($building = 0,$type = 'article')
    {
        return $this->model->where('type',$type)->where('bdc_building_id',$building)->count();
    }
    public function getPostRegister($select,$id,$type)
    {
        return $this->model->select($select)->where('id',$id)->where('type',$type)->firstOrFail();
    }
    public function checkRegisters($customer_id,$notify,$building_id)
    {
        $data_list = array();$data_list_search = array();
        if(isset($notify['customer_selected']) == 1){
            if(!empty($notify['customer_ids'])){
                $list_cus = UserInfo::whereIn('pub_user_id',$notify['customer_ids'])->where('bdc_building_id',$building_id)->select('id')->distinct()->get()->toArray();
                $data_list_search = array_map(function($item){ return $item['id']; }, $list_cus);
                $data_list = array_merge($data_list,$data_list_search);
            }

        }
        if(isset($notify['group_selected']) == 1){
            if(!empty($notify['group_ids'])){
                $list_cus = Customers::whereIn('bdc_apartment_id',$notify['group_ids'])->whereHas('bdcApartment', function ($query) use ($building_id) {$query->where('building_id', '=', $building_id);})->select('pub_user_profile_id')->distinct()->get()->toArray();
                $data_list_search = array_map(function($item){ return $item['pub_user_profile_id']; }, $list_cus);
                $data_list = array_merge($data_list,$data_list_search);
            }

        }
        if(isset($notify['floor_selected']) == 1){
            if(!empty($notify['floor_ids'])){
                $list_apt = Apartments::whereIn('floor',$notify['floor_ids'])->where('building_id',$building_id)->select('id')->distinct()->get()->toArray();
                $data_apt = array_map(function($item){ return $item['id']; }, $list_apt);

                $list_cus = Customers::whereIn('bdc_apartment_id',$data_apt)->whereHas('bdcApartment', function ($query) use ($building_id) {$query->where('building_id', '=', $building_id);})->select('pub_user_profile_id')->distinct()->get()->toArray();
                $data_list_search = array_map(function($item){ return $item['pub_user_profile_id']; }, $list_cus);

                $data_list = array_merge($data_list,$data_list_search);
            }

        }
        if(isset($notify['all_selected']) == 1){
            $list_cus= UserInfo::select('id')->where('bdc_building_id',$building_id)->get()->toArray();
            $data_list_search = array_map(function($item){ return $item['id']; }, $list_cus);
            $data_list = array_merge($data_list,$data_list_search);

        }
        if (in_array($customer_id, $data_list)) {
            return true;
        }
        return false;
    }
    public function getSelectbyId(array $select,$id)
    {
        return $this->model->select($select)->findOrFail($id);
    }
}
