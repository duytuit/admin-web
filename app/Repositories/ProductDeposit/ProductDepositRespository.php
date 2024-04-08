<?php

namespace App\Repositories\ProductDeposit;

use App\Repositories\Eloquent\Repository;

class ProductDepositRespository extends Repository 
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\ProductDeposit\ProductDeposit::class;
    }

    public function getList($per_page = 20)
    {
        return $this->model;
    }

    public function changeStatus($id, $status = self::STATUS_ACTIVE)
    {
        return $this->model->where('id', $id)->update(['status' => $status]);
    }

    public function destroy($id)
    {
        return $this->model->destroy($id);
    }
}
