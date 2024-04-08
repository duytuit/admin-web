<?php

namespace App\Repositories\SettingSendMails;

use App\Models\System\SettingSendMail;
use App\Repositories\Eloquent\Repository;

class SettingSendMailRepository extends Repository
{

    function model()
    {
        return SettingSendMail::class;
    }

    public function deleteMulti($ids)
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    public function get($where = null)
    {
        if (!$where) {
            return $this->model->all();
        }
        return $this->model->where('name', 'like', '%' . $where . '%')->get();
    }

    public function getSetingInvoice($buildingId)
    {
        $result = $this->model->where('type', 1)->where('bdc_building_id', $buildingId);

        return $result->get();
    }

    public function getSetingEvent($buildingId)
    {
        $result = $this->model->where('type', 2)->where('bdc_building_id', $buildingId);

        return $result->get();
    }

    public function getForgotPass($buildingId)
    {
        $result = $this->model->where('type', 3)->where('bdc_building_id', $buildingId);

        return $result->get();
    }
    public function getResident($buildingId)
    {
        $result = $this->model->where('type', 4)->where('bdc_building_id', $buildingId);

        return $result->get();
    }

    public function getTemplate($buildingId, $type, $status) {
        $result = $this->model
            ->with('mailTemplate')
            ->where('type', $type)
            ->where('bdc_building_id', $buildingId)
            ->where('status', $status)
            ->first();

        if ($result) {
            return $result->mailTemplate->toArray();
        } else {
            return [];
        }
    }
}
