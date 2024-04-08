<?php

namespace App\Repositories\System;

use App\Models\System\Config;
use App\Repositories\Eloquent\Repository;
use Illuminate\Container\Container as App;
use Illuminate\Support\Arr;

class configRepository extends Repository
{

    function model()
    {
        return Config::class;
    }
    public function findAllBy($colums = 'id',$id)
    {
        return $this->model->where($colums, $id)->get();
    }
    public function findAllByOne($colums = 'id',$id)
    {
        return $this->model->where($colums, $id)->first();
    }

    public function getOne($colums = 'id',$id)
    {
        $row = $this->model->where($colums, $id)->first();
        return $row;
    }

    public function findByConfigKey($configKey)
    {
        return $this->model->where('config_key', $configKey)
            ->where('bdc_building_id', \Auth::user()->BDCprofile->bdc_building_id)->first();
    }

    public function save($data)
    {
        Config::updateOrCreate(['id' => $data['id']], [
            'bdc_building_id' => \Auth::user()->BDCprofile->bdc_building_id,
            'config_key' => $data['config_key'],
            'config_value' => json_encode(Arr::only($data, ['api_key_name', 'api_key_sid', 'api_key_secret', 'vpn_merchant_id', 'vpn_secret']))
        ]);
    }
}
