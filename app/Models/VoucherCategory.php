<?php

namespace App\Models;

use App\Models\Model;
use App\Models\BoUser;
use App\Models\Voucher;

class VoucherCategory extends Model
{
    public function user()
    {
        return $this->belongsTo(BoUser::class, 'user_id');
    }

    public function articles()
    {
        return $this->hasMany(Voucher::class, 'category_id');
    }
}
