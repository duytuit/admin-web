<?php

namespace App\Models;

use App\Models\BoCategory;
use App\Models\BoUser;
use App\Models\Model;
use App\Traits\MyActivityTraits;
use Ixudra\Curl\Facades\Curl;

class Campaign extends Model
{
    use MyActivityTraits;

    protected $guarded = [];
    protected $casts   = [
        'file_user_cus' => 'array',
    ];

    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty  = true;

    public function project()
    {
        return $this->belongsTo(BoCategory::class, 'project_id', 'cb_id');
    }

    public function user()
    {
        return $this->belongsTo(BoUser::class, 'user_id', 'ub_id');
    }

    public function campaign_assign()
    {
        return $this->hasMany(CampaignAssign::class, 'campaign_id', 'id');
    }

    public static function getProjectById($id)
    {
        $url = 'https://bo.dxmb.vn/api/category/show/' . $id;

        $projects = Curl::to($url)
            ->withHeader('Content-MD5: BO.PCN@DXMB!@#')
            ->asJson(true)
            ->post();

        if ($projects['success'] == true) {
            return $projects['data'];
        }

        return null;
    }

    public function getProject()
    {
        $url = 'https://bo.dxmb.vn/api/category/show/' . $this->project_id;

        $projects = Curl::to($url)
            ->withHeader('Content-MD5: BO.PCN@DXMB!@#')
            ->asJson(true)
            ->post();

        if ($projects['success'] == true) {
            return $projects['data'];
        }

        return null;
    }
}
