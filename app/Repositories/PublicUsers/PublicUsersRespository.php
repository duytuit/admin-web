<?php

namespace App\Repositories\PublicUsers;

//use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Eloquent\Repository;
use App\Services\ServiceSendMail;
use App\Services\ServiceSendMailV2;
use Illuminate\Support\Facades\Hash;
use App\Models\Building\Building;
use App\Models\Campain;
use App\Models\PublicUser\UserInfo;
use App\Models\SentStatus;
use Illuminate\Support\Facades\Cookie;

class PublicUsersRespository extends Repository {


    const RESIDENT = 4;
    const NEW_USER =100;
    const NEW_PROFILE =99;
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\PublicUser\Users::class;
    }

    public function checkExit($email){
        return $this->model->where('email',$email)->first();
    }
    public function checkExitWeb($email){
        return $this->model->where('email',$email)->wherehas('BDCprofile')->first();
    }
    public function checkExitById($id,$email){
        return $this->model->where('id','<>',$id)->where('email',$email)->first();
    }
    public function checkPhoneById($id,$phone){
        return $this->model->where('id','<>',$id)->where('mobile',$phone)->first();
    }
    public function checkPhone($phone){
        return $this->model->where('mobile',$phone)->first();
    }
    public function checkPhoneWeb($phone){
        return $this->model->where('mobile',$phone)->wherehas('BDCprofile')->first();
    }
    public function checkPhoneEmail($phone,$email){
        $check = $this->model;
        if($email){
            $check = $check->orWhere('email','=',$email);
        }
        if($phone){
            $check = $check->orWhere('mobile','=',$phone);
        }
        $check = $check->first();
        return $check;
    }
    public function checkPhoneEmailHasCus($phone,$email,$building_id=null){
        $check = $this->model;
        if($email){
            $check = $check->orWhere('email','=',$email);
        }
        if($phone){
            $check = $check->orWhere('mobile','=',$phone);
        }
        $check = $check->whereHas('info', function ($query) use ($email,$phone,$building_id) {
            if($email){
                 $query->where('email', '=', $email);
            }
            if($phone){
                 $query->where('mobile', '=', $phone);
            }
            $query->where('bdc_building_id', '=', $building_id);
        });
        $check = $check->first();
        return $check;
    }
    public function updatePass($email,$pass){
        return $this->model->where('email',$email)->update(['password'=>Hash::make($pass)]);
    }
    public function checkPass($email,$pass){
        $check = $this->model->where('email',$email)->first();
        if(Hash::check($pass,$check->password)){
            $check_pas = 1;
        }else{
            $check_pas = 0;
        }
        return $check_pas;
    }
    public function resetPass($email,$pass){
        $check = $this->model->where('email',$email)->update(['password'=>Hash::make($pass)]);
        return $check;
    }
    public function resetPassByPhone($phone,$pass){
        $check = $this->model->where('mobile',$phone)->update(['password'=>Hash::make($pass)]);
        return $check;
    }
    public function getUserProfile($email){
        $check = $this->model->where('email',$email)->with('profileAll')->first();
        return $check;
    }
    public function getUserById($id){
        $check = $this->model->where('id',$id)->with('profileAll')->first();
        return $check;
    }
    public function sendMail($email,$pass,$user,$building_id)
    {
       $name_building= Building::where('id',$building_id)->first()->name??'';
        $data = [
            'params' => [
                '@ten' =>$user['display_name']??$user??'',
                '@pass' => $pass,
                '@ngay' => date('d/m/Y',time()),
                '@urlLogin' => url('/login'),
                '@urlApp' => url('/login'),
                '@toanha'=>$name_building
            ],
            'cc' => $email,
            'building_id' => $building_id,
            'type' => self::NEW_USER,
            'status' => 'create'
        ];
        $type = config('typeCampain.NEW_USER');
        if ($pass == null) {
            $data['type'] = self::RESIDENT;
            $data['status'] = 'update';
            $type = config('typeCampain.RESIDENT');
        }
        $total = ['email'=> 1, 'app'=> 0, 'sms'=> 0];
        $campain = Campain::updateOrCreateCampain("Gửi email cho: ".$email, $type, null, $total, $building_id, 0, 0);

         
        $data['campain_id'] = $campain->id;
        try {
            ServiceSendMailV2::setItemForQueue($data);
            return ;
        } catch (\Exception $e)
        {
            return $e->getMessage();
        }
    }
    public function per_page($request)
    {
        $per_page = $request->input('per_page',20);

        Cookie::queue('per_page', $per_page, 60 * 24 * 30);

        return back();
    }
    public function action($request,$building_id)
    {
        $method = $request->input('method', '');
        if ($method == 'delete') {
            if(!isset($request->ids) || $request->ids == null){
                return back()->with('warning','chưa có bản ghi nào được chọn');
            }
            foreach ($request->ids as $key => $value) {
                 $user_info = UserInfo::find($value);
                 if($user_info){
                    $user_info->delete();
                    $this->model->destroy($user_info->pub_user_id);
                 }
             }
            return back()->with('success','xóa thành công');
        } elseif ($method == 'status') {
            $status =  $this->status($request);
            return back()->with('success',$status['msg']);
        } elseif ($method == 'restore') {
            if(!isset($request->ids) || $request->ids == null){
                return back()->with('warning','chưa có bản ghi nào được chọn');
            }
            foreach ($request->ids as $key => $value) {
                $user_info = UserInfo::withTrashed()->find($value);
                if($user_info){
                   $user_info->restore();
                   $user = $this->model->withTrashed()->find($user_info->pub_user_id);
                   if($user) $user->restore();
                }
            }
            return back()->with('success','phục hồi bản ghi thành công');
        }elseif ($method == 'delete_trash') {
            if(!isset($request->ids) || $request->ids == null){
                return back()->with('warning','chưa có bản ghi nào được chọn');
            }
            foreach ($request->ids as $key => $value) {
                $user_info = UserInfo::withTrashed()->find($value);
                if($user_info){
                   $user_info->forceDelete();
                   $user = $this->model->withTrashed()->find($user_info->pub_user_id);
                   if($user) $user->forceDelete();
                }
            }
            return back()->with('success','xóa vĩnh viễn thành công');
        } elseif ($method == 'per_page') {
            return $this->per_page($request);
        }
        return back();
    }

}
