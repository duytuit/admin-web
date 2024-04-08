<?php

namespace App\Repositories\Config;

use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Eloquent\Repository;
use App\Models\Configs\Configs;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;

class ConfigRepository extends Repository
{
    public const PROVISIONAL_RECEIPT = 'provisional_receipt';
    public const PROVISIONAL_RECEIPT_CODE = 'provisional_receipt_code';
    public const RECEIPT_PAYMENT_SLIP = 'receipt_payment_slip';
    public const RECEIPT_PAYMENT_SLIP_CODE = 'receipt_payment_slip_code';
    public const RECEIPT_PAYMENT_SLIP_OTHER = 'receipt_payment_slip_other';
    public const RECEIPT_PAYMENT_SLIP_CODE_OTHER = 'receipt_payment_slip_code_other';
    public const ACCOUNTING_RECEIPT_CODE = 'accounting_receipt_code';
    public const CREDIT_TRANSFER_RECEIPT_CODE = 'credit_transfer_receipt_code';
    public const RECEIPT_CODE = 'receipt_code';
    public const ADJUSTMENT_SLIP = 'adjustment_slip';
    public const BANGKE_PDF = 'bangke_pdf';
    public const RECEIPT_VIEW = 'receipt_view';
    public const ACTIVE = '1';
    public const INACTIVE = '0';

    public const RECEIPT_DEPOSIT = 'receipt_deposit';
    public const RECEIPT_PAYMENT_DEPOSIT = 'receipt_payment_deposit';
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return Configs::class;
    }

    public function myPaginate($keyword, $per_page, $building_id)
    {
        return $this->model
            // ->with('people_hand', 'department', 'pub_profile')
            ->where('bdc_building_id', $building_id)
            ->filter($keyword)
            ->orderBy('updated_at', 'DESC')
            ->paginate($per_page);
    }

    public function getConfigbyKey($key, $building_id)
    {
      $res = $this->model->where([
        'key' => $key,
        'status' => 1,
        'bdc_building_id' => $building_id
      ])->first();
      if($res){return $res->value;}
      else{
        return null;
      }
    }
    
    public function findByKey($buildingId, $key)
    {
      return $this->model->where(['bdc_building_id' => $buildingId, 'key' => $key, 'status' => self::ACTIVE])->get();
    }

    public function findByMultiKey($buildingId)
    {
      return $this->model->where(function($query) {
          return $query->where('key', ConfigRepository::PROVISIONAL_RECEIPT)->orWhere('key', ConfigRepository::RECEIPT_PAYMENT_SLIP);
      })->where(['bdc_building_id' => $buildingId,'status' => ConfigRepository::ACTIVE])->get();
    }

    public function findByMultiKeyByReceiptDeposit($buildingId)
    {
      return $this->model->where(function($query) {
          return $query->where('key', ConfigRepository::RECEIPT_DEPOSIT)->orWhere('key', ConfigRepository::RECEIPT_PAYMENT_DEPOSIT);
      })->where(['bdc_building_id' => $buildingId,'status' => ConfigRepository::ACTIVE])->get();
    }

    public function findByKeyFirst($buildingId, $key)
    {
      return $this->model->where(['bdc_building_id' => $buildingId, 'key' => $key])->first();
    }

    public function findByKeyActiveFirst($buildingId, $key)
    {
      $data = ['bdc_building_id' => $buildingId, 'key' => $key, 'status' => self::ACTIVE];
      return $this->model->where($data)->first();
    }
    public function CheckDuplicateValue($value, $id = null)
    {
      return $this->model->where(['value' => $value])->where(function($query) use($id){
          if($id){
              $query->where('id','<>',$id);
          }
      })->first();
    }
}