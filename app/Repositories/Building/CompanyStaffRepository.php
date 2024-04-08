<?php

namespace App\Repositories\Building;

use App\Models\Building\CompanyStaff;
use App\Repositories\Eloquent\Repository;

class CompanyStaffRepository extends Repository {
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return CompanyStaff::class;
    }

    public function getStaffByPublicId($userId, $companyId)
    {
        return $this->model->where('pub_user_id', $userId)->where('bdc_company_id', $companyId)->first();
    }
   

}
