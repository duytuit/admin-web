<?php

namespace App\Repositories\PaymentInfo;

use App\Models\PaymentInfo\PaymentInfo;
use App\Repositories\Eloquent\Repository;

class PaymentInfoRepository extends Repository {
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return PaymentInfo::class;
    }

    function findPayment($id)
    {
        return $this->model->with('building')->findOrFail($id);
    }

    function findByBuilding($building_id)
    {
        return $this->model->where('bdc_building_id', $building_id)->get();
    }
    function findByBankacountBuilding($bank_account,$building_id)
    {
        return $this->model->where('bank_account',$bank_account)->where('bdc_building_id', $building_id)->first();
    }
}
