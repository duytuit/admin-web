<?php

namespace App\Models;

use App\Models\Model;

class PasswordReset extends Model
{
    protected $fillable = [
        'email',
        'token',
    ];
}
