<?php

namespace App\Repositories\BdcProvisionalReceipt;

use App\Repositories\Eloquent\Repository;

class ProvisionalReceiptRepository extends Repository {

    const PHIEUTHU = 'phieu_thu';
    const PHIEUCHI = 'phieu_chi';
    const COMPLETED = 1;
    const NOTCOMPLETED = 0;

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\BdcProvisionalReceipt\ProvisionalReceipts::class;
    }

    public function filterApartmentId($apartmentId)
    {
        return $this->model->where(['bdc_apartment_id' => $apartmentId, 'type' => self::PHIEUTHU, 'status' => self::NOTCOMPLETED])->orderBy('id', 'DESC')->get();
    }

    public function autoIncrementReceiptCode($config, $buildingId)
    {
        $billCount = $this->model->count();
        $filterByKey = $config->getConfigbyKey('provisional_receipt_code', $buildingId);
        if($billCount == 0) {
            $billCode = $filterByKey . "_0000001";
            return $billCode;
        }

        $receipt = collect(DB::select(DB::raw("SELECT MAX(RIGHT(`receipt_code`, 7)) as receipt_code FROM `bdc_receipts` WHERE `type`='phieu_thu_truoc' `bdc_building_id`=:buildingId"), ['buildingId' => $buildingId]))->first();

        $numberReceiptCode = (int)$receipt->receipt_code;
        $numberReceiptCode = $numberReceiptCode + 1;
        $lengthNumberReceiptCode = strlen($numberReceiptCode);
        $idReceiptCode = substr('0000000',  0, 7 - $lengthNumberReceiptCode);
        $receiptCode = $filterByKey . "_" . $idReceiptCode . $numberReceiptCode;
        return $receiptCode;
    }
}
