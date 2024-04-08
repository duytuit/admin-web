<?php

namespace App\Models\Payment;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class PaymentOrder extends Model
{
    use SoftDeletes;

    use ActionByUser;
    protected $table = 'bdc_v2_payment_create_order';

    protected $guarded =[];

}
