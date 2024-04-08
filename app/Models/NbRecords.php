<?php

namespace App\Models;

use App\Models\Article;
use App\Models\Model;
use App\Traits\ActionByUser;

class NbRecords extends Model {
    protected $guarded = [];
    use ActionByUser;
    protected $table = 'nb_records';
    public function wallet()
    {
        return $this->belongsTo(NbWallet::class, 'id', 'wallet_id');
    }
}