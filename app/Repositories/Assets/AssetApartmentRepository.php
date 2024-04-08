<?php

namespace App\Repositories\Assets;

use App\Repositories\Eloquent\Repository;
use App\Models\Asset\AssetApartment;
use Maatwebsite\Excel\Facades\Excel;

class AssetApartmentRepository extends Repository {


    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return AssetApartment::class;
    }

    public function findById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function findByIdApartmentIsNull($building_id,$code)
    {
        return $this->model->whereNull('bdc_apartment_id')->where(['bdc_building_id' => $building_id, 'code' => $code])->first();
    }

    public function myPaginate($request, $active_building, $per_page)
    {
            return $this->model
            ->where('bdc_building_id', $active_building)
            ->where(function($query) use($request){
                if(isset($request->keyword) && $request->keyword != null) {
                    $query->where('name','like','%'.$request->keyword.'%')
                          ->orWhere('code','like','%'.$request->keyword.'%');
                }
            })
            ->orderBy('updated_at', 'DESC')
            ->paginate($per_page);
    }

    public function action($request,$building_id)
    {
        $method = $request->input('method', '');
        if ($method == 'delete') {
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
        $assets = $this->model
        ->where('bdc_building_id', $buildingId)
        ->orderBy('updated_at', 'DESC')
        ->get();
        $result = Excel::create('danh sách tài sản', function ($excel) use ($assets) {
            $excel->setTitle('danh sách tài sản');
            $excel->sheet('danh sách tài sản', function ($sheet) use ($assets) {
                $row = 1;
                $sheet->row($row, [
                    'STT',
                    'Mã tài sản',
                    'Tên tài sản',
                    'Loại tài sản',
                    'Căn hộ',
                    'Tòa nhà',
                    'Mô tả',
                    'Ngày tạo',
                    'Người tạo',
                    'Ngày cập nhật gần nhất',
                    'Người cập nhật gần nhất',
                ]);
                foreach ($assets as $key => $value) {
                    $row++;
                    $sheet->row($row, [
                        ($key + 1),
                        $value->code,
                        $value->name,
                        @$value->asset_category->title,
                        @$value->apartment->name,
                        @$value->building_place->name,
                        $value->description,
                        @$value->created_at,
                        @$value->user_created_by->email,
                        @$value->updated_at,
                        @$value->user_updated_by->email,
                    ]);
                }
            });
        })->store('xlsx',storage_path('exports/'));
$file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
return response()->download($file)->deleteFileAfterSend(true);
             
    }
    public function searchByAll(array $options = [],$building_id,$asset_category_id = null)
    {
        $default = [
            'select'   => '*',
            'where'    => [],
            'order_by' => 'id DESC',
            'per_page' => 20,
        ];

        $options = array_merge($default, $options);
        extract($options);

        $model = $this->model->select($options['select']);

        if ($options['where']) {
            $model = $model->where($options['where']);
        }
        $model = $model->where('bdc_building_id',$building_id);
        if($asset_category_id != 'no_category_id'){
            $model = $model->where('asset_category_id',$asset_category_id);
            $model = $model->whereNull('bdc_apartment_id');
        }
        return $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
    }
    public function findByCode($building_id,$code)
    {
        return $this->model->whereNull('bdc_apartment_id')->where(['bdc_building_id' => $building_id, 'code' => $code])->first();
    }
}
