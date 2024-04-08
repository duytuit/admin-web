<?php

namespace App\Models\HistoryTransactionAccounting;

use App\Models\Apartments\Apartments;
use App\Models\PublicUser\Users;
use App\Models\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class HistoryTransactionAccounting extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'history_transaction_accounting';

    protected $guarded = [];

    public function apartment()
    {
        return $this->belongsTo(Apartments::class, 'bdc_apartment_id','id');
    }

    public function user_created_by()
    {
        return $this->belongsTo(Users::class, 'created_by','id');
    }
    public function user_updated_by()
    {
        return $this->belongsTo(Users::class, 'updated_by','id');
    }
    public function user_confirm_by()
    {
        return $this->belongsTo(Users::class, 'user_confirm','id');
    }

}
