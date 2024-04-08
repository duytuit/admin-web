<?php

namespace App\Repositories\Assets;

use App\Repositories\Eloquent\Repository;
use App\Models\Asset\AssetHandOver;
use App\Models\Asset\AssetApartment;
use Maatwebsite\Excel\Facades\Excel;

class AssetHandOverRepository extends Repository {


    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return AssetHandOver::class;
    }

    public function findById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function myPaginate($request, $active_building, $per_page)
    {
            return $this->model
            ->where('bdc_building_id', $active_building)
            ->where(function($query) use($request){
                if(isset($request->keyword) && $request->keyword != null) {
                    $query->where('customer','like','%'.$request->keyword.'%');
                }
                if(isset($request->asset_id) && $request->asset_id != null) {
                    $query->where('asset_apartment_id',$request->asset_id);
                }
                if(isset($request->bdc_apartment_id) && $request->bdc_apartment_id != null) {
                    $query->where('apartment_id',$request->bdc_apartment_id);
                }
                if(isset($request->status) && $request->status != null) {
                    $query->where('status',$request->status);
                }
                // ngày dự kiến bàn giao
                if (isset($request->from_date)  && $request->from_date != null) {
                    $query->whereDate('date_expected', '>=', date('Y-m-d', strtotime($request->from_date)));
                }
                if (isset($request->to_date)  && $request->to_date != null) {
                    $query->whereDate('date_expected', '<=', date('Y-m-d', strtotime($request->to_date)));
                }
                // ngày bàn giao
                if (isset($request->created_at_from_date)  && $request->created_at_from_date != null) {
                    $query->whereDate('date_of_delivery', '>=', date('Y-m-d', strtotime($request->created_at_from_date)));
                }
                if (isset($request->created_at_to_date)  && $request->created_at_to_date != null) {
                    $query->whereDate('date_of_delivery', '<=', date('Y-m-d', strtotime($request->created_at_to_date)));
                }

            })->whereHas('apartment',function($query) use ($request){
                if(isset($request->ip_place_id) && $request->ip_place_id != null) {
                    $query->where('building_place_id',$request->ip_place_id);
                }
            })
            ->orderBy('updated_at', 'DESC')
            ->paginate($per_page);
    }
    public function action($request,$building_id)
    {
        $method = $request->input('method', '');
        if ($method == 'delete') {
            if(isset($request->ids)){
                $ids = $request->ids;
                foreach ($ids as $key => $value) {
                   $asset_handover = $this->findById($value);
                   $code = @$asset_handover->asset->code;
                   $assetApartment = AssetApartment::where('code',$code)->first();
                   if($assetApartment){
                        $assetApartment->update([
                            'bdc_apartment_id' => null
                        ]);
                   }
                }
            }
           
            $del = $this->deleteAt($request);
            return back()->with('success',$del['msg']);
        }
        return back();
    }
    public function deleteAt($request)
    {
        $ids = $request->input('ids', []);

        // chuyển sang kiểu array
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $list = [];
        foreach ($ids as $id) {
            $list[] = (int) $id;
        }

        $number = $this->model->destroy($list);

        $message = [
            'error'  => 0,
            'status' => 'success',
            'msg'    => "Đã xóa $number bản ghi!",
        ];

        return $message;
    }
    public function export($request, $buildingId)
    {
        $asset_handovers = $this->model
        ->where('bdc_building_id', $buildingId)
        ->orderBy('updated_at', 'DESC')
        ->get();
        $result = Excel::create('danh sách bàn giao tài sản', function ($excel) use ($asset_handovers) {
            $excel->setTitle('danh sách bàn giao tài sản');
            $excel->sheet('danh sách bàn giao tài sản', function ($sheet) use ($asset_handovers) {
                $row = 1;
                $sheet->row($row, [
                    'STT',
                    'Mã tài sản',
                    'Tên tài sản',
                    'Mô tả',
                    'Dự kiến BG',
                    'Ngày bàn giao',
                    'Khách hàng',
                    'Email',
                    'Phone',
                    'Căn hộ',
                    'Thời gian bảo hành',
                    'Tình trạng',
                    'Ngày tạo',
                    'Người tạo',
                    'Ngày cập nhật gần nhất',
                    'Người cập nhật gần nhất',
                ]);
                foreach ($asset_handovers as $key => $value) {
                    $row++;
                    $sheet->row($row, [
                        ($key + 1),
                        @$value->asset->code,
                        @$value->asset->name,
                        $value->description,
                        @$value->date_expected,
                        @$value->date_of_delivery,
                        $value->customer,
                        $value->email,
                        $value->phone,
                        @$value->apartment->name,
                        $value->warranty_period,
                        $value->description,
                        @$value->created_at,
                        @$value->user->email,
                        @$value->updated_at,
                        @$value->user_updated_by->email,
                    ]);
                }
            });
        })->store('xlsx',storage_path('exports/'));
$file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
return response()->download($file)->deleteFileAfterSend(true);
             
    }
}
