<?php

namespace App\Repositories\TransactionPayment;

use App\Models\TransactionPayment\TransactionPayment;
use App\Repositories\Eloquent\Repository;
use Carbon\Carbon;
use Excel;
use Illuminate\Support\Facades\DB;

class TransactionPaymentRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return TransactionPayment::class;
    }
    public function myPaginate($keyword, $per_page, $active_building)
    {
        return $this->model
            ->where('bdc_building_id', $active_building)
            ->filter($keyword)
            ->orderBy('updated_at', 'DESC')
            ->paginate($per_page);
    }
    public function getServicePartnersbyHandbookId($id)
    {
        return $this->model->where('bdc_handbook_id', $id)->first();
    }
    public function transactionPaymentReceipt($buildingId, $request)
    {
        $sql = "SELECT 
                    *,(dong_tien - chi_tien + hoan_tien) as so_du
                FROM
                    (SELECT 
                     `tb1`.*,`virtual_account_payments`.`virtual_acc_id`,`virtual_account_payments`.`virtual_acc_name`,`virtual_account_payments`.`virtual_acc_mobile`,`bdc_apartments`.`building_id`,`bdc_apartments`.`name`
                    FROM
                        (SELECT DISTINCT
                        `a`.`bdc_apartment_id`,
                            (SELECT 
                                    COALESCE(SUM(`b`.`amount`), 0) 
                                FROM
                                    `transaction_payments` AS `b`
                                WHERE
                                    `a`.`bdc_apartment_id` = `b`.`bdc_apartment_id`";
        if (isset($request['from_date']) && $request['from_date'] != null && isset($request['to_date']) && $request['to_date'] != null) {
            $from_date = Carbon::parse($request['from_date'])->format('Y-m-d');
            $to_date =   Carbon::parse($request['to_date'])->format('Y-m-d');
            $sql .= " AND `b`.`created_at` >= '$from_date' AND `b`.`created_at` <= '$to_date 23:59:59'";
        }
        $sql .= " AND `b`.`status` = 1 AND `b`.`bdc_receipt_id` IS NULL) AS 'dong_tien',
                            (SELECT 
                                    COALESCE(SUM(`c`.`amount`), 0) 
                                FROM
                                    `transaction_payments` AS `c`
                                WHERE
                                    `a`.`bdc_apartment_id` = `c`.`bdc_apartment_id`
                                        AND `c`.`bdc_receipt_id` IS NOT NULL AND `c`.`type` = 'chi_tien'";
        if (isset($request['from_date']) && $request['from_date'] != null && isset($request['to_date']) && $request['to_date'] != null) {
            $from_date = Carbon::parse($request['from_date'])->format('Y-m-d');
            $to_date =   Carbon::parse($request['to_date'])->format('Y-m-d');
            $sql .= " AND `c`.`created_at` >= '$from_date' AND `c`.`created_at` <= '$to_date 23:59:59'";
        }
        $sql .= " AND `c`.`deleted_at` IS NULL) AS 'chi_tien',
                            (SELECT 
                                    COALESCE(SUM(`d`.`amount`), 0) 
                                FROM
                                    `transaction_payments` AS `d`
                                WHERE
                                    `a`.`bdc_apartment_id` = `d`.`bdc_apartment_id`
                                        AND `d`.`bdc_receipt_id` IS NOT NULL AND `d`.`type` = 'hoan_tien'";
        if (isset($request['from_date']) && $request['from_date'] != null && isset($request['to_date']) && $request['to_date'] != null) {
            $from_date = Carbon::parse($request['from_date'])->format('Y-m-d');
            $to_date =   Carbon::parse($request['to_date'])->format('Y-m-d');
            $sql .= " AND `d`.`created_at` >= '$from_date' AND `d`.`created_at` <= '$to_date 23:59:59'";
        }
        $sql .= " AND `d`.`deleted_at` IS NULL) AS 'hoan_tien'
                    FROM
                        `transaction_payments` AS `a`
                    WHERE
                        `a`.`deleted_at` IS NULL) AS tb1
                    INNER JOIN `bdc_apartments` ON `bdc_apartments`.`id` = `tb1`.`bdc_apartment_id`
                    INNER JOIN `virtual_account_payments` ON `virtual_account_payments`.`bdc_apartment_id` = `tb1`.`bdc_apartment_id`
                    WHERE `bdc_apartments`.`deleted_at` IS NULL AND `virtual_account_payments`.`deleted_at` IS NULL) AS tb2
                    WHERE `tb2`.`building_id` = $buildingId";
        if (isset($request['bdc_apartment_id']) && $request['bdc_apartment_id'] != null) {
            $bdc_apartment_id    = $request['bdc_apartment_id'];
            $sql .= " AND `tb2`.`bdc_apartment_id` = $bdc_apartment_id";
        }
        return DB::select(DB::raw($sql));
    }
    public function transactionPaymentReceiptAmount($buildingId, $bdc_apartment_id)
    {
        $sql = "SELECT 
                    *,(dong_tien - chi_tien + hoan_tien) as so_du
                FROM
                    (SELECT 
                        *
                    FROM
                        (SELECT DISTINCT
                        `a`.`bdc_apartment_id`,
                            (SELECT 
                                   COALESCE(SUM(`b`.`amount`), 0) 
                                FROM
                                    `transaction_payments` AS `b`
                                WHERE
                                    `a`.`bdc_apartment_id` = `b`.`bdc_apartment_id`";
        $sql .= " AND `b`.`status` = 1 AND `b`.`bdc_receipt_id` IS NULL) AS 'dong_tien',
                            (SELECT 
                                    COALESCE(SUM(`c`.`amount`), 0) 
                                FROM
                                    `transaction_payments` AS `c`
                                WHERE
                                    `a`.`bdc_apartment_id` = `c`.`bdc_apartment_id`
                                        AND `c`.`bdc_receipt_id` IS NOT NULL AND `c`.`type` = 'chi_tien'";
        $sql .= " AND `c`.`deleted_at` IS NULL) AS 'chi_tien',
                            (SELECT 
                                   COALESCE(SUM(`d`.`amount`), 0) 
                                FROM
                                    `transaction_payments` AS `d`
                                WHERE
                                    `a`.`bdc_apartment_id` = `d`.`bdc_apartment_id`
                                        AND `d`.`bdc_receipt_id` IS NOT NULL AND `d`.`type` = 'hoan_tien'";
        $sql .= " AND `d`.`deleted_at` IS NULL) AS 'hoan_tien'
                    FROM
                        `transaction_payments` AS `a`
                    WHERE
                        `a`.`deleted_at` IS NULL) AS tb1
                    INNER JOIN `bdc_apartments` ON `bdc_apartments`.`id` = `tb1`.`bdc_apartment_id`
                    WHERE
                        `bdc_apartments`.`deleted_at` IS NULL) AS tb2
                    WHERE
                        `tb2`.`building_id` = $buildingId";
        if (isset($bdc_apartment_id) && $bdc_apartment_id != null) {
            $sql .= " AND `tb2`.`bdc_apartment_id` = '$bdc_apartment_id'";
        }
        return DB::select(DB::raw($sql));
    }
}
