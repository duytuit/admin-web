<?php

namespace App\Models\TransactionPayment;

use App\Models\Apartments\Apartments;
use App\Models\PublicUser\Users;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class TransactionPayment extends Model
{

    use SoftDeletes;

    use ActionByUser;
    protected $table = 'transaction_payments';

    protected $guarded =[];

    public function bdcApartment()
    {
        return $this->belongsTo(Apartments::class, 'bdc_apartment_id', 'id');
    }
    public function User()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id');
    }
}