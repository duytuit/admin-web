<?php

namespace App\Models;

use App\Models\Article;
use App\Models\Model;
use App\Traits\ActionByUser;

class Currency extends Model {

    use ActionByUser;
    protected $table = 'currency';
    public function wallet()
    {
        return $this->belongsTo(NbWallet::class, 'currency_code', 'code');
    }
}