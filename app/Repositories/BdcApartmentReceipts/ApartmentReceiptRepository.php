<?php

namespace App\Repositories\BdcApartmentReceipts;

use App\Repositories\Eloquent\Repository;

class ApartmentReceiptRepository extends Repository {
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\BdcApartmentReceipts\ApartmentReceipts::class;
    }

}
