<?php

namespace App\Repositories\V3\UserTempRepository;

use App\Models\V3ModelTemp\Users;
use App\Repositories\V3\BaseRepository\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class UserTempRepository extends BaseRepository
{

    /**
     * UserRepository constructor.
     * @param Users $model
     */
    public function __construct(Users $model)
    {
        $this->model = $model;
    }

    /**
     * @return array
     */
    public function getAllAdmin(): array
    {
        return $this->query()
            ->whereHas('info',function (Builder $query){
                $query->where('type',Users::USER_WEB)
                    ->where('app_id',"buildingcare");
            })
            ->with(['info'=>function($query){
                $query->with('building');
            }])
            ->where('isadmin',1)
            ->get()
            ->toArray();
    }

    public function getAccountOther(): array
    {
        return $this->query()
            ->whereHas('infoWeb',function (Builder $query){
                $query->where('type',Users::USER_WEB);
            })
            ->with(['infoWeb'=>function($query){
                $query->with('building');
            }])
            ->with('info')
            ->where('isadmin',0)
            ->get()
            ->toArray();
    }

    public function getAllUserNormal()
    {
        return $this->query()
            ->whereHas('appActiveProfile')
            ->get()
            ->toArray();
    }

    // User Not Null mobile & email
    public function getAllUserCustomer()
    {
        return $this->query()
            ->whereHas('appProfile')
            ->whereNotNull('mobile')
            ->where('mobile','!=','')
            ->whereNull('deleted_at')
            ->with('info')
            ->get();
    }


    public function getAllUserCustomerMobile()
    {
        return $this->query()
            ->whereHas('appProfile')
            ->where('mobile','like','%0%')
//            ->where('email','like','%@%')
            ->whereNull('email')
            ->whereNull('status_auth')
            ->whereNull('deleted_at')
            ->with('info')
            ->get();
    }

    public function getAllUserCustomerEmail()
    {
        return $this->query()
            ->whereHas('appProfile')
//            ->where('mobile','like','%0%')
            ->where('email','like','%@%')
            ->whereNull('mobile')
            ->whereNull('status_auth')
            ->whereNull('deleted_at')
            ->with('info')
            ->get();
    }

}
