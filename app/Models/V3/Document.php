<?php

namespace App\Models\V3;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class Document extends Model
{
    use SoftDeletes;

    use ActionByUser;
    protected $table = "bdc_documents";

    const TYPE_BUILDING = 1;
    const TYPE_APARTMENT = 2;
    const TYPE_APARTMENT_GROUP = 3;



}
