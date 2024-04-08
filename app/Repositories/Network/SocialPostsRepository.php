<?php

namespace App\Repositories\Network;

use App\Models\Permissions\Module;
use App\Models\PublicUser\UserPermission;
use App\Repositories\Eloquent\Repository;

class SocialPostsRepository extends Repository {
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = -1;

    const VISIBLE_ONLY_ME = 0;
    const VISIBLE_MY_FLOOR = 1;
    const VISIBLE_MY_BUILDING = 2;

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\Network\SocialPost::class;
    }
    public function searchByApi($request, $where = [], $perpage = 20, $info = null, $lis_cus = null)
    {
        /*
            status: -1: đã xoá. 1: active (default)
            Filter mặc định: theo building id đã truyền, và có status active. Query thêm trường visible:
            - nếu visible = 2: luôn lấy
            - nếu visible = 1: chỉ lấy nếu bài viết đó của cư dân cùng tầng
            - nếu visible = 0: chỉ lấy nếu bài viết đó do người hiện tại đăng

            Tham số visible:
                 0 - chỉ mình tôi
                 1 - tầng hiện tại
                 2 - toà nhà hiện tại
            reaction?: string, cảm xúc của người hiện tại
        */
        if (!empty($request->content)) {
            $where[] = ['content', 'Like', '%'.$request->content.'%'];
        }
        if (!empty($request->status)) {
            $where[] = ['status', $request->status];
        }
       $where[] =['bdc_building_id', $request->building_id];

        $default = [
            'select'   => ['id','user_id','content','status','images','response','visible','created_at','updated_at','new'],
            'where'    => $where,
            // 'order_by' => 'id DESC',
            'per_page' => $perpage,
        ];

        $options = array_merge($default, $where);
        extract($options);

        return $this->model->select($options['select'])->where(function ($query) use($where) {
            $query->where($where)->whereIn('visible', [self::VISIBLE_MY_FLOOR, self::VISIBLE_MY_BUILDING]);
        })->orWhere(function ($query) use($where) {
            $query->where($where)->where('visible', '=', self::VISIBLE_ONLY_ME);
        })->orderBy('updated_at', 'desc')->paginate($options['per_page']);


    }
    public function createSocial($input,$id)
    {
        $item = $this->model->findOrNew($id);
        $item->fill($input)->save();
      return $item;
    }
    public function getOne($id)
    {
      return $this->model->with('pubProfile')->find($id);
    }
    public function getSelectbyId(array $select,$id)
    {
      return $this->model->select($select)->find($id);
    }
}
