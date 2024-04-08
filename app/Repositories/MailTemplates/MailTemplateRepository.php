<?php

namespace App\Repositories\MailTemplates;

use App\Models\System\TemplateMail;
use App\Repositories\Eloquent\Repository;

class MailTemplateRepository extends Repository
{

    function model()
    {
        return TemplateMail::class;
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

    public function getEventTemplates($where, $buildingId)
    {
        $result = $this->model->where('type', 2)->where('bdc_building_id', $buildingId);
        if ($where) {
            return $result->where('name', 'like', '%' . $where . '%')->get();
        }

        return $result->get();
    }

    public function getForgotPassTemplates($where, $buildingId)
    {
        $result = $this->model->where('type', 3)->where('bdc_building_id', $buildingId);
        if ($where) {
            return $result->where('name', 'like', '%' . $where . '%')->get();
        }

        return $result->get();
    }
    public function getResidentTemplates($where, $buildingId)
    {
        $result = $this->model->where('type', 4)->where('bdc_building_id', $buildingId);
        if ($where) {
            return $result->where('name', 'like', '%' . $where . '%')->get();
        }

        return $result->get();
    }

    public function getInvoiceTemplates($where, $buildingId)
    {
        $result = $this->model->where('type', 1)->where('bdc_building_id', $buildingId);
        if ($where) {
            return $result->where('name', 'like', '%' . $where . '%')->get();
        }

        return $result->get();
    }

    public function getDefaultTemplate($buildingId)
    {
        $result = $this->model->where('type', 3)->where('bdc_building_id', $buildingId);

        return $result->first();
    }
}
