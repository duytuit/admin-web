<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ActionByUser;

class SettingSendMail extends Model
{
    use ActionByUser;
    protected $table = 'bdc_setting_send_mail';

    protected $fillable = ['bdc_building_id', 'type', 'status', 'mail_template_id'];

    public function mailTemplate()
    {
        return $this->belongsTo(TemplateMail::class, 'mail_template_id', 'id');
    }
}
