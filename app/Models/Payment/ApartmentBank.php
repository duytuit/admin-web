<?php

namespace App\Models\Payment;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class ApartmentBank extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'bdc_v2_apartment_bank_va';

    protected $guarded =[];

}
