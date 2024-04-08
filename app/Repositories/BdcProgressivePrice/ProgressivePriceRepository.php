<?php

namespace App\Repositories\BdcProgressivePrice;

use App\Repositories\Eloquent\Repository;

class ProgressivePriceRepository extends Repository {
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\BdcProgressivePrice\ProgressivePrice::class;
    }

    public function findByProgressiveId($progressiveId)
    {
        return $this->model->where('progressive_id', $progressiveId)->get();
    }
}
