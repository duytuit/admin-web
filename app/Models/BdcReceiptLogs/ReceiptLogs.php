<?php

namespace App\Models\BdcReceiptLogs;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ActionByUser;

class ReceiptLogs extends Model
{
    use ActionByUser;
    protected $table = 'receipt_logs';

    protected $fillable = [
        'bdc_building_id', 'bill_id', 'bill_code', 'bdc_service_id', 'key', 'input', 'data', 'message', 'status'
    ];

    // protected $casts = [
    //     'input' => 'array',
    //     'data' => 'array'
    // ];
}
