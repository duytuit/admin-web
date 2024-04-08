<?php

namespace App\Repositories\NotifyLog;

//use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Eloquent\Repository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cookie;

class NotifyLogRespository extends Repository {
    const STATUS_NEW = 0;
    function model()
    {
        return \App\Models\NotifyLog\NotifyLog::class;
    }
    public function find ($id, $columns = array('*'))
    {
        return $this->model->where('_id', $id)->first();
    }
    public function findByStatus ($status,$per_page=10,$users)
    {
        // $list_nofi = $this->model->where('building_id',$users->bdc_building_id)->where('user_id',$users->pub_user_id)->paginate($per_page)->toArray();
        // $data_list = array_map(function($item){ return $item['_id']; }, $list_nofi['data']);
        // $this->model->where('see_at','')->update(['see_at'=>Carbon::now()]);
        return [];
    }
    public function findByStatusVsSee ($user)
    {
        return 1;
        if($user){
            return 1;
        }
        return $this->model->select('see_at')->where('status', 0)->where('user_id', $user->pub_user_id)->where('building_id', $user->bdc_building_id)->where('see_at', '')->count();
    }
    public function readSaveCheck ($id=0,$timelast='',$user)
    {
        if($id>0){
            $read =  $this->update(['status'=>1,'read_at'=>Carbon::now()],$id,'_id');
        }
        if($timelast != ''){
            $read =  $this->model->where('created_at','<=',Carbon::parse($timelast))->where('status',0)->where('user_id',$user->pub_user_id)->where('building_id',$user->bdc_building_id)->update(['status'=>1,'read_at'=>Carbon::now()]);
        }
        return $read;
    }
}