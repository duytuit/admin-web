<?php

namespace App\Repositories\VehicleCards;

//use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Eloquent\Repository;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class VehicleCardsRespository extends Repository
{

    const USE_VC = 1;
    const NOT_USE_VC = 0;

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\VehicleCards\VehicleCards::class;
    }

    public function searchBy($buiding_id, $request, $where = [])
    {

        $default = [
            'select'   => '*',
            'where'    => $where,
            'order_by' => 'id DESC'
        ];

        $options = array_merge($default, $where);
        extract($options);
        $model = $this->model->select($options['select']);

        if (!empty($request->keyword)) {
            $model = $model->Where(function ($query) use ($request) {
                $query->orWhere('code', 'like', '%' . $request->keyword . '%');
                $query->orWhereHas('bdcVehicle', function ($query1) use ($request) {
                    $query1->where('number', 'like', '%' . $request->keyword . '%');
                    $query1->orWhere('name', 'like', '%' . $request->keyword . '%');
                });
            });
        }
        if (!empty($request->apartment)) {
            $where[] = $model->whereHas('bdcVehicle', function ($query) use ($request) {
                $query->where('bdc_apartment_id', '=', $request->apartment);
            });
        }
        if (!empty($request->cate)) {
            $where[] = $model->whereHas('bdcVehicle', function ($query) use ($request) {
                $query->where('vehicle_category_id', $request->cate);
            });
        }

        $where[] = $model->whereHas('bdcVehicle.bdcApartment', function ($query) use ($buiding_id,$request) {
            $query->where('building_id', '=', $buiding_id);
            if(isset($request->place_id) && $request->place_id !=null){
                $query->where('building_place_id', $request->place_id);
            }
        });

        if ($request->status !=null) {
            $model = $model->where('status', '=', $request->status);
        }
        $list_search = $model->orderByRaw($options['order_by']);

        return $list_search;
    }
    public function searchByAll(array $options = [])
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

        return $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
    }
    public function getOne($colums = 'id', $id)
    {
        $row = $this->model->where($colums, $id)->first();
        $row->load('bdcVehicle');
        return $row;
    }
    public function destroyIn(array $data)
    {
        return $this->model->destroy($data);
    }

    public function createCheck($request, $vehicle)
    {
        //$check = $this->findBy('bdc_vehicle_id',$vehicle->id,['id']);

        $result_vehiclecad_inactive =  $this->model->where(['bdc_vehicle_id' => $vehicle->id, 'status' => 1])->count();
        if ($result_vehiclecad_inactive == 0) {
            return $this->create([
                'bdc_vehicle_id' => $vehicle->id,
                'code' => strtoupper($request->code),
                'description' => $request->description,
                'status' => 1
            ]);
        }
        return false;
    }

    public function vehicleCardAllByCode()
    {
        return $this->model->pluck('code')->toArray();
    }

    public function getDataFile($file)
    {
        $path = $file->getRealPath();

        $excel_data = Excel::load($path)->get();

        storage_path('upload', $file->getClientOriginalName());

        $url = [
            'name' => $file->getClientOriginalName(),
            'uuid' => (string) \Webpatser\Uuid\Uuid::generate(),
        ];

        if ($excel_data->count()) {
            $excel_vehicle = $this->unsetVehicleCard($excel_data);
            $data_vehicle = $this->vehicleCardExelData($excel_vehicle, $url);
            $save = $this->vehicleCardDataSave($data_vehicle);
        }

        return $save;
    }

    public function unsetVehicleCard($vehiclecard)
    {
        $duplicate = [];
        for ($i = 0; $i < count($vehiclecard) - 1; $i++) {
            for ($j = $i + 1; $j < count($vehiclecard); $j++) {
                if (!empty($vehiclecard[$j]) && $vehiclecard[$i]['code'] !== null && $vehiclecard[$i]['code'] == $vehiclecard[$j]['code']) {
                    $duplicate[$j] = $vehiclecard[$j];
                    unset($vehiclecard[$j]);
                } elseif (!empty($vehiclecard[$j]) && $vehiclecard[$i]['number'] !== null && $vehiclecard[$i]['number'] == $vehiclecard[$j]['number']) {
                    $duplicate[$j] = $vehiclecard[$j];
                    unset($vehiclecard[$j]);
                }
            }
        }
        $vehecl_card = [];
        for ($i = 0; $i < count($vehiclecard); $i++) {
            if (isset($vehiclecard[$i])) {
                if (
                    $vehiclecard[$i]['index'] == null &&
                    $vehiclecard[$i]['code'] == null &&
                    $vehiclecard[$i]['number'] == null
                ) {

                    continue;
                }
                $vehecl_card[] = $vehiclecard[$i];
            }
        }
        $data = [
            'data' => $vehecl_card,
            'duplicate' => $duplicate,
        ];

        return $data;
    }

    public function vehicleCardExelData($vehiclecard, $url)
    {
        $check_vehiclecard = $this->vehicleCardAllByCode();
        $has_ap = [];
        $fail_ap = [];
        $new_ap = [];
        foreach ($vehiclecard['data'] as $key => $vh) {
            if (!in_array($vh->code, $check_vehiclecard)) {
                if ($vh->index && $vh->code && $vh->number) {
                    $new_ap[] = [
                        'index' => $vh->index,
                        'code' => $vh->code,
                        'number' => $vh->number,
                        'status' => 1,
                        'description' => $vh->description,
                    ];
                } else {
                    $fail_ap[] = [
                        'index' => $vh->index,
                        'code' => $vh->code,
                        'number' => $vh->number,
                        'description' => $vh->description,
                    ];
                }
            } else {
                $has_ap[] = [
                    'index' => $vh->index,
                    'code' => $vh->code,
                    'number' => $vh->number,
                    'description' => $vh->description,
                ];
            }
        }

        if (!empty($has_ap)) {
            $messages[] = [
                'messages' => 'Có ' . count($has_ap) . ' Vé xe đã có trên hệ thống',
                'data'     => $has_ap,
            ];
        }

        if (!empty($vehiclecard['duplicate'])) {
            $dlc[] = json_decode(reset($vehiclecard['duplicate']), True);
            $messages[] = [
                'messages' => 'Có ' . count($vehiclecard['duplicate']) . ' Vé xe bị trùng trong file',
                'data'     => $dlc,
            ];
        }
        if (!empty($new_ap)) {
            $messages[] = [
                'messages' => 'Có ' . count($new_ap) . ' vé xe đầy đủ dữ liệu',
                'data'     => $new_ap,
            ];
        }

        if (!empty($fail_ap)) {
            $messages[] = [
                'messages' => 'Có ' . count($fail_ap) . ' vé xe bị thiếu dữ liệu',
                'data'     => $fail_ap
            ];
        }

        $data['messages'] = $messages;
        $data_new = [
            'data_vhc' => $new_ap,
            'url_file'  => $url,
            'duplicate' => $vehiclecard['duplicate'] ?? [],
        ];
        $data['data'] = $data_new;
        return $data;
    }
    public function vehicleCardDataSave($dataExel)
    {
        $time = Carbon::now();
        if ($dataExel['data']['data_vhc'] && !(session()->get('errors_user'))) {
            foreach ($dataExel['data']['data_vhc'] as $index => $vh) {
                $data_ap[] = [
                    'code'  => $vh['code'],
                    'number' => $vh['number'],
                    'bdc_vehicle_id' => 0,
                    'status' => 1,
                    'description' => $vh['description'],
                    'created_at'     => $time,
                    'updated_at'     => $time,
                ];
            }
        }
        $dataExel['data'] = array_merge($dataExel['data'], ['vehiclecard' => $data_ap ?? '']);
        return $dataExel;
    }
    public function insert(array $data)
    {
        return $this->model->insert($data);
    }
    public function getVcById($id)
    {
        return $this->model->where('id', $id)->first();
    }
    public function getVcCheckStatusByVehicle_id($bdc_vehicle_id)
    {
        return $this->model->where(['bdc_vehicle_id' => $bdc_vehicle_id, 'status' => 1])->get();
    }
    public function changeStatusVc($request)
    {
        if ($request->status == 'Active') {
            $this->model->whereIn('id', $request->ids)->update(['status' => self::USE_VC]);
        } elseif ($request->status == 'Inactive') {
            $this->model->whereIn('id', $request->ids)->update(['status' => self::NOT_USE_VC]);
        } else {
            $post = $this->model->where('id', $request->id)->first();
            if ($post->status == self::USE_VC) {
                $post->status = self::NOT_USE_VC;
                $post->save();
            } else {
                $post->status = self::USE_VC;
                $post->save();
            }
        }
    }
}
