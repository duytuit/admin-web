<?php

namespace App\Repositories\Network;

use App\Models\Permissions\Module;
use App\Models\PublicUser\UserPermission;
use App\Repositories\Eloquent\Repository;

class SocialReactionsRepository extends Repository {

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\Network\SocialReactions::class;
    }
    public function getEmo($user_id,$post_id)
    {
        return $this->model->select('emotion')->where('post_id',$post_id)->where('user_id',$user_id)->first();
    }
    public function insertRow(array $data)
    {
        return $this->model->fill($data)->save();
    }
}
