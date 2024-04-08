<?php

namespace App\Models;

use App\Models\BoCategory;
use App\Models\BoUser;
use App\Models\CustomerDiary;
use App\Models\Model;
use App\Traits\MyActivityTraits;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Ixudra\Curl\Facades\Curl;
use App\Traits\ActionByUser;

class BoCustomer extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract, JWTSubject
{
    use SoftDeletes,
        Authenticatable,
        Authorizable,
        CanResetPassword,
        MustVerifyEmail,
        MyActivityTraits,
        Notifiable;

    use ActionByUser;
    protected $table   = 'b_o_customers';
    protected $guarded = ['id'];
    protected $casts   = [
        'group_id' => 'array',
        'files'    => 'array',
    ];


    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty  = true;

    public function getUidAttribute()
    {
        return $this->cb_id;
    }

    public function getNameAttribute()
    {
        return $this->cb_name;
    }

    public function getCharAttribute()
    {
        $words = explode(' ', $this->convert_vi_to_en(isset($this->name)?$this->name:'Unknow User'));
        $name  = end($words);
        $char  = substr($name, 0, 1);
        return $char;
    }

    public function getPhoneAttribute()
    {
        $phone = preg_replace('/[^0-9\.\(\)\-\+\s]+/', '', $this->cb_phone);
        return $phone;
    }

    public function getEmailAttribute()
    {
        return $this->cb_email;
    }

    public function getPasswordAttribute()
    {
        return $this->cb_password;
    }

    public function getAvatarAttribute()
    {
        return $this->cb_avatar;
    }

    public function comments()
    {
        return $this->morphMany('App\Models\Comment', 'user');
    }

    public function bo_category()
    {
        return $this->belongsTo(BoCategory::class, 'project_id', 'cb_id');
    }

    public function user()
    {
        return $this->belongsTo(BoUser::class, 'tc_created_by', 'ub_id');
    }

    public function diaries()
    {
        return $this->hasMany(CustomerDiary::class, 'cd_customer_id', 'cb_id')->orderBy('created_at', 'desc');
    }

    public function bo_users()
    {
        $customer     = self::where('cb_id', $this->cb_id)->first();
        $cb_staff_ids = [];
        if (!empty($customer->cb_staff_id)) {
            $cb_staff_ids = explode(',', $customer->cb_staff_id);
        }

        $cb_staffs = [];
        if (!empty($cb_staff_ids)) {
            $cb_staffs = BoUser::whereIn('ub_id', $cb_staff_ids)->get();
        }

        if ($cb_staffs) {
            $cb_staffs = collect($cb_staffs)->map(function ($value) {
                $group_name             = $value->group ? ' - ' . $value->group->gb_title : '';
                return $value->ub_title = $value->ub_title . $group_name;
            });
        }

        return $cb_staffs;
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * @param [type] $arr_customer
     * Bắt buộc phải có
     *  - cb_phone : Số điện thoại khách hàng
     *  - project_id : id dự án
     *  - cb_id : id của khác hàng khi edit, khi thêm mới thì không cần nếu có thì = 0
     * @return boolean
     */
    public static function is_exist($option)
    {
        $where = [
            ['cb_phone', $option['cb_phone']],
            ['project_id', $option['project_id']],
        ];

        if (!empty($option['cb_id'])) {
            $where[] = ['cb_id', '!=', $option['cb_id']];
        }
        $customer = self::findBy($where);

        if ($customer->count()) {
            return true;
        }

        return false;
    }

    public static function getCustomerByCriterion($group)
    {
        $criterion    = $group->criterion;
        $use_customer = $group->getCustomer()->get();

        $use_customer = collect($use_customer)->map(function ($value) {
            return $value->id;
        })->toArray();

        if ($criterion) {
            $customers = self::whereNotIn('id', $use_customer);
            // Mức độ quan tâm
            if ($criterion['status'] != null) {
                $customers = $customers->where('status', $criterion['status']);
            }
            // Địa chỉ(Khu vực)
            if (!empty($criterion['address'])) {
                if ($criterion['address']['city'] !== null) {
                    $customers = $customers->where('city', $criterion['address']['city']);
                }

                if ($criterion['address']['district'] !== null) {
                    $customers = $customers->where('district', $criterion['address']['district']);
                }
            }
            // Dự án
            if (!empty($criterion['project'])) {
                $customers = $customers->whereIn('project_id', $criterion['project']);
            }
            // Ngày sinh
            if (!empty($criterion['birthday'])) {
                if ($criterion['birthday']['from'] !== null && $criterion['birthday']['to'] == null) {
                    $customers = $customers->where('birthday', '>=', $criterion['birthday']['from']);
                }

                if ($criterion['birthday']['from'] == null && $criterion['birthday']['to'] !== null) {
                    $customers = $customers->where('birthday', '<=', $criterion['birthday']['to']);
                }

                if ($criterion['birthday']['from'] !== null && $criterion['birthday']['to'] !== null) {
                    $customers = $customers->whereBetween('birthday', [$criterion['birthday']['from'], $criterion['birthday']['to']]);
                }
            }
            // Nguồn khách hàng
            if (!empty($criterion['cb_source'])) {
                $customers = $customers->where('cb_source', $criterion['cb_source']);
            }

            $customers = $customers->distinct('cb_phone')->pluck('id');
        } else {
            $customers = self::distinct('cb_phone')->pluck('id');
        }
        return $customers;
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
    public static function getusers($email,$phone)
    {
        $customer     = self::where('cb_email', $email)->where('cb_phone', $phone)->first();

        return $customer;
    }
    public static function getDescription ($cb_id)
    {
        $customer     = CustomerDiary::where('cd_customer_id', $cb_id)->orderByRaw('id DESC')->select('cd_description')->first();

        return $customer['cd_description'];
    }
    public static function Check_phonenumber($phone)
    {
        $customer     = self::where('cb_phone', $phone)->first();

        return $customer;
    }
}
