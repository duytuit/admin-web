<?php

namespace App\Models\VirtualAccountPayment;

use App\Models\Apartments\Apartments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class VirtualAccountPayment extends Model
{
    use SoftDeletes;

    use ActionByUser;
    protected $table = 'virtual_account_payments';

    protected $guarded =[];

    public function bdcApartment()
    {
        return $this->belongsTo(Apartments::class, 'bdc_apartment_id', 'id');
    }
}
