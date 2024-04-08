<?php

namespace App\Repositories\Fcm;

//use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Customers\CustomersRespository;
use App\Repositories\Eloquent\Repository;
use const App\Repositories\Service\BUILDING_USER;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class FcmRespository extends Repository{


    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\Fcm\Fcm::class;
    }
    public function checkFcmUser($user_id,$device_id,$type){
        return $this->model->where(['user_id'=>$user_id,'device_id'=>$device_id,'user_type'=>$type])->first();
    }
    public function getFcmToken($userids){
        return $this->model->where(['user_id'=>$userids])->whereNotNull('token')->select('token','user_type')->get();
    }
    public function updateToken($user_id,$device_id,$token,$type){
        return $this->model->where(['user_id'=>$user_id,'device_id'=>$device_id,'user_type'=>$type])->first()->update(['token'=>$token]);
    }
    public function newToken($user_id,$device_id,$token,$type){
        return $this->model->create(['user_id'=>$user_id,'token'=>$token,'device_id'=>$device_id,'user_type'=>$type]);
    }

    public function newToken2($user_id, $device_id, $token, $type, $type_device,$bundle_id)
    {
        return $this->model->create(['user_id' => $user_id, 'token' => $token, 'device_id' => $device_id, 'user_type' => $type, 'type_device' => $type_device,'bundle_id'=>$bundle_id]);
    }

    public function updateTokenNewUser($id, $user_id)
    {
        return $this->model->where(['id' => $id])->first()->update(['user_id' => $user_id]);
    }
    public function checkDevice($user_id,$type){
        return $this->model->where('user_id',$user_id)->where('type_device',$type)->count();
    }
    public function checkToken($token,$user_type,$type_device){
        return $this->model->where('token',$token)->where('user_type',$user_type)->where('type_device',$type_device)->first();
    }
    public function deletefcm($user_id,$type){
        return $this->model->where(['user_id'=>$user_id,'user_type' => $type])->delete();
    }

    public function deleteByDeviceId($device_id){
        return $this->model->where('device_id',$device_id)->delete();
    }

}
