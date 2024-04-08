<?php

namespace App\Models;

use App\Models\BoCategory;
use App\Models\BoCustomer;
use App\Models\Model;
use Ixudra\Curl\Facades\Curl;

class CustomerDiary extends Model
{
    protected $guarded = [];
    protected $casts   = [
        'filters' => 'array',
    ];

    public function customer()
    {
        return $this->belongsTo(BoCustomer::class, 'cd_customer_id', 'cb_id');
    }

    public function project()
    {
        return $this->belongsTo(BoCategory::class, 'project_id', 'cb_id');
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
    public static function getAllDiary($Camp_assign_id)
    {
        if($Camp_assign_id){
            $list_diary = self::where('campaign_assign_id',$Camp_assign_id)->orderBy('created_at','desc')->get();
            return $list_diary;
        }else{
            return null;
        }
    }
}
