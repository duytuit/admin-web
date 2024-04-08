<?php

namespace App\Repositories\ServicePartners;


use App\Repositories\Eloquent\Repository;
use App\Models\ServicePartners\ServicePartners;
use Excel;

class ServicePartnersRepository extends Repository {
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return ServicePartners::class;
    }
    public function myPaginate($keyword, $per_page, $active_building)
    {
        return $this->model
          ->where('bdc_building_id', $active_building)
          ->filter($keyword)
          ->orderBy('updated_at', 'DESC')
          ->paginate($per_page);
    }
     public function getServicePartnersbyHandbookId($id){
        return $this->model->where('bdc_handbook_id',$id)->first();
    }
    public function filterExportExcel($buildingId, $request)
    {
        $response = $this->model
          ->where('bdc_building_id', $buildingId)
          ->filter($request)
          ->orderBy('updated_at', 'DESC')->get();

        // $array = unserialize($strBillIds);

        $result = Excel::create('dang ky dich vu', function ($excel) use ($response) {
            $excel->setTitle('dang ky dich vu');
            $excel->sheet('dang ky dich vu', function ($sheet) use ($response) {
                $service_partners = [];
                $row = 1;
                $sheet->row($row, [
                    'STT',
                    'Tên khách hàng',
                    'SĐT',
                    'Email',
                    'Thời gian đặt',
                    'Thời gian tạo',
                    'Ghi chú',
                    'Trạng thái',
                    'Người tạo',
                    'Ngày duyệt',
                    'Người duyệt',
                ]);
                foreach ($response as $key => $value) {
                    $row++;
                    // trạng thái
                    if ($value->status == 1) {
                        $status = 'Đã duyệt';
                    } else {
                        $status = 'Chưa duyệt';
                    }

                    $sheet->row($row, [
                        ($key + 1),
                        $value->customer,
                        $value->phone,
                        $value->email,
                        $value->timeorder,
                        $value->updated_at,
                        $value->description, 
                        $status,
                        @$value->PubUsers->infoWeb->display_name ?? '',
                        $value->confirm_date ?? '--/--/----',
                        @$value->Approved->infoWeb->email ?? '',
                    ]);
                }
            });
        })->store('xlsx',storage_path('exports/'));
$file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
return response()->download($file)->deleteFileAfterSend(true);
             
    }
}
