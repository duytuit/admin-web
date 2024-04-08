<?php

namespace App\Repositories\Network;

use App\Models\Permissions\Module;
use App\Models\PublicUser\UserPermission;
use App\Repositories\Eloquent\Repository;

class SocialCommentsRepository extends Repository {

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\Network\SocialComments::class;
    }
    public function searchByApi($request,$where=[],$perpage = 20)
    {

        if (!empty($request->content)) {
            $where[] = ['content', 'Like', '%'.$request->content.'%'];
        }

        $default = [
            'select'   => ['id','post_id','parent_id','user_id','user_type','content','files','created_at','updated_at','new'],
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
        if($request->post_id){
            $model = $model->where('post_id',$request->post_id);
        }
        $model = $model->where('parent_id',0);

        $list_search = $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
        return $list_search;
    }

    public function createSocialComments($input,$id)
    {
        $item = $this->model->findOrNew($id);
        $item->fill($input)->save();
        return $item;
    }
    public function getReply($post_id,$parent_id)
    {
        return $this->model->where('post_id',$post_id)->where('parent_id',$parent_id)->with('pubProfile')->get();
    }
    public function getOne($id)
    {
        return $this->model->findOrFail($id);
    }
    public function getOneByPost($id,$post_id)
    {
        return $this->model->where('id',$id)->where('post_id',$post_id)->with('pubProfile')->first();
    }

    public function getCountPost($post_id)
    {
        return $this->model->where(['post_id'=>$post_id,'parent_id'=>0])->count();
    }
}
