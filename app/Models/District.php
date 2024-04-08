<?php

namespace App\Models;

use App\Models\Model;

class District extends Model
{
    protected $casts = [
        'location'  => 'array',
    ];
}
