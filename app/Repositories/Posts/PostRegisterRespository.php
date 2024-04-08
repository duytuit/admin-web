<?php

namespace App\Repositories\Posts;

//use App\Repositories\Contracts\RepositoryInterface;
use App\Models\PublicUser\Users;
use App\Repositories\Eloquent\Repository;
use Illuminate\Support\Facades\Cookie;

class PostRegisterRespository extends Repository {

    const USE_POST = 1;
    const NOT_USE_POST = 0;



    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\Posts\PostRegister::class;
    }
    public function getOne($colums = 'id', $id)
    {
        return $this->model->where($colums, $id)->first();
    }
    public function delCheckExit($post, $user)
    {
        return $this->model->where([['post_id', $post->id],['post_type', $post->type],['user_id', $user->id],['user_type', 'user']])->delete();
    }
    public function saveRegister($param)
    {
        return $this->model->fill($param)->save();
    }
    public function countRegister($post)
    {
        return $this->model->where([['post_id', $post->id],['post_type', $post->type]])->count();
    }
    public function countCheckin($post)
    {
        return $this->model->where([['post_id', $post->id],['post_type', $post->type],['check_in','!=',null]])->count();
    }
    public function getItem($post_id,$user)
    {
        return $this->model->where('post_id', $post_id)->where('user_id', $user->id)->where('user_type', 'user')->first();
    }
}
