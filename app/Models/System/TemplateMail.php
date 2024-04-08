<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class TemplateMail extends Model
{
    public $table = 'bdc_template_mails';
    public $fillable = ['bdc_building_id', 'type', 'name', 'content', 'subject'];

}
