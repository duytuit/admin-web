<?php

namespace App\Models;

use App\Models\Article;
use App\Models\Model;
use App\Traits\ActionByUser;

class NbRecordType extends Model {

    use ActionByUser;
    protected $table = 'nb_records_type';
    public function record()
    {
        return $this->belongsTo(NbRecords::class, 'record_type', 'id');
    }
}