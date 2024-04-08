<?php

namespace App\Models;

use App\Models\BoCustomer;

use App\Models\BoUser;
use App\Models\Model;

class CustomerGroup extends Model
{
    protected $guarded = [];
    protected $casts   = [
        'criterion' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(BoUser::class, 'user_id', 'ub_id');
    }

    public function getCustomer()
    {
        $where     = 'JSON_CONTAINS(group_id,"' . $this->id . '")';
        $customers = BoCustomer::whereRaw($where);

        return $customers;
    }
    public function getCustomerByid($ids)
    {
        $where     = '';
        foreach ($ids as $id){
            $where .= 'JSON_CONTAINS(group_id,'.$id.') or ';
        }
        $where = trim($where," or ");
        $customers = BoCustomer::whereRaw($where)->select('id')->get()->toArray();

        return $customers;
    }

    public function add_customer()
    {
        // Lấy danh sách KH đủ tiêu chuẩn
        $cb_ids = BoCustomer::getCustomerByCriterion($this);

        if ($cb_ids) {
            foreach ($cb_ids as $id) {
                $customer = BoCustomer::find($id);

                $group_ids = $customer->group_id;
                if (!in_array($this->id, (array) $group_ids)) {
                    $group_ids[] = $this->id;
                }

                $param = ['group_id' => $group_ids];

                $customer->fill($param);
                $customer->save();
            }
        }
    }

    public function remove_customer()
    {
        $used_customer = $this->getCustomer()->get();

        if ($used_customer) {
            $used_customer = collect($used_customer)->map(function ($value) {
                return $value->cb_id;
            })->toArray();
        }

        foreach ($used_customer as $id) {
            $customer = BoCustomer::findById($id);
            $group_id = $customer->group_id;
            $param_array = [];
            foreach ($group_id as $key => $value) {
                if ($this->id == $value) {
                    unset($group_id[$key]);
                }else{
                    $param_array[]=$value;
                }
            }
            $param = [
                'group_id' => $param_array,
            ];
            $customer->fill($param);
            $customer->save();
        }
    }

    public static function updateAll()
    {
        $groups = self::all();
        foreach ($groups as $group) {
            // xóa thành viên cũ
            $group->remove_customer();

            // add thành viên mới
            $group->add_customer();
        }
    }
}
