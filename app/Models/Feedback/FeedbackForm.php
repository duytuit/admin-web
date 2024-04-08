<?php

namespace App\Models\Feedback;

use App\Models\Apartments\Apartments;
use App\Models\BoCustomer;
use App\Models\Building\Building;
use App\Models\Comments\Comments;
use App\Models\PublicUser\UserInfo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class FeedbackForm extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'feedback_form';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'url', 'hint', 'bdc_building_id', 'type', 'content', 'status'
    ];

    protected $hidden = [];

    protected $dates = ['deleted_at'];

}
