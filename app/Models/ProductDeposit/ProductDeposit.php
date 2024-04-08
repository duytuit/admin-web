<?php

namespace App\Models\ProductDeposit;

use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Models\BdcDebitDetail\DebitDetail;
use App\Models\Period\Period;
use Illuminate\Database\Eloquent\Model;
use App\Models\PublicUser\Users;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class ProductDeposit extends Model
{
    const TYPE = [
        '0'=>'Chưng cư',
        '1'=>'Biệt thự',
        '2'=>'Đất nền',
        '3'=>'Liền kề'
    ];
    const NEEDED = [
        '0'=>'Gửi bán',
        '1'=>'Cho thuê'
    ];
    const STATUS = [
        '0'=>'Unactive',
        '1'=>'Active'
    ];
    const STATUS_DEPOSIT = [
        '0'=>'Chưa mở bán',
        '1'=>'Mở bán',
        '2'=>'Chờ mở bán'
    ];

    use ActionByUser;
    protected $table = 'product_deposit';

    protected $fillable = [
        'name',
        'address',
        'description',
        'direction',
        'type',
        'needed',
        'acreage',
        'price',
        'images',
        'begin_date',
        'status',
        'status_deposit',
        'user_id',
        'product_id'
    ];
}
