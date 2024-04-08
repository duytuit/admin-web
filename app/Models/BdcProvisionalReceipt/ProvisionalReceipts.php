<?php

namespace App\Models\BdcProvisionalReceipt;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class ProvisionalReceipts extends Model
{
    use ActionByUser;
    protected $table = 'bdc_provisional_receipt';

    protected $fillable = [
        'bdc_building_id', 'bdc_apartment_id', 'config_id', 'name', 'payment_type', 'type', 'price', 'description', 'status', 'receipt_code'
    ];
}
