<?php

namespace App\Repositories\ApartmentHandOver;

use App\Repositories\Eloquent\Repository;
use App\Models\Apartments\Apartments;
use App\Models\PublicUser\Users;
use App\Repositories\Apartments\ApartmentsRespository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Customers\Customers;

class ApartmentHandOverRespository extends Repository {


    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
          return \App\Models\PublicUser\UserInfo::class;
    }
    public function getDataFile($file,$building_id,$app_id)
    {
        $path = $file->getRealPath();

        $excel_data = Excel::load($path)->get();

        storage_path('upload', $file->getClientOriginalName());

        $url = [
            'name' => $file->getClientOriginalName(),
            'uuid' => (string) \Webpatser\Uuid\Uuid::generate(),
        ];

        if ($excel_data->count()) {
            $excel_apartmentHandOver = $this->unsetApartmentHandOver($excel_data);
            $data_apartmentHandOver = $this->apartmentHandOverExelData($excel_apartmentHandOver,$url,$building_id);
            $save = $this->apartmentHandOverDataSave($data_apartmentHandOver,$building_id,$app_id);
        }

        return $save;
    }
    public function unsetApartmentHandOver($data_apartmentHandOver)
    {
        $duplicate=[];
        $array_data_apartmentHandOver=[];
        for ($i = 0; $i < count($data_apartmentHandOver); $i++) {
             if (!empty($data_apartmentHandOver[$i])) {
                if ($data_apartmentHandOver[$i]['index'] != null &&
                    $data_apartmentHandOver[$i]['name'] != null &&
                    $data_apartmentHandOver[$i]['phone'] != null &&
                    $data_apartmentHandOver[$i]['email'] != null &&
                    $data_apartmentHandOver[$i]['password'] != null &&
                    $data_apartmentHandOver[$i]['sex'] != null &&
                    $data_apartmentHandOver[$i]['type'] != null &&
                    $data_apartmentHandOver[$i]['address'] != null &&
                    $data_apartmentHandOver[$i]['date_handover'] != null &&
                    $data_apartmentHandOver[$i]['apartment_name'] != null &&
                    $data_apartmentHandOver[$i]['floor'] != null &&
                    $data_apartmentHandOver[$i]['place'] != null 
                ){
                    $apartmentHandOver_data=[
                    'index' =>  $data_apartmentHandOver[$i]['index'],
                    'display_name' =>  $data_apartmentHandOver[$i]['name'],
                    'cmt' => $data_apartmentHandOver[$i]['cmt'],
                    'phone' => $data_apartmentHandOver[$i]['phone'],
                    'birthday' => $data_apartmentHandOver[$i]['birthday'],
                    'email' => $data_apartmentHandOver[$i]['email'],
                    'password' => $data_apartmentHandOver[$i]['password'],
                    'gender' => $data_apartmentHandOver[$i]['sex'],
                    'status_confirm' => $data_apartmentHandOver[$i]['type'],
                    'address' => $data_apartmentHandOver[$i]['address'],
                    'handover_date' => $data_apartmentHandOver[$i]['date_handover'],
                    'note_confirm' => $data_apartmentHandOver[$i]['note_confirm'],
                    'apartment_name' => $data_apartmentHandOver[$i]['apartment_name'],
                    'floor' => $data_apartmentHandOver[$i]['floor'],
                    'place' => $data_apartmentHandOver[$i]['place'],  
                    ];
                    $array_data_apartmentHandOver[]= $apartmentHandOver_data;
                }
               
            }
           
            for ($j = $i + 1; $j < count($data_apartmentHandOver); $j++) {


                if (!empty($data_apartmentHandOver[$i])) {
                if ($data_apartmentHandOver[$j]['index'] != null &&
                    $data_apartmentHandOver[$j]['name'] != null &&
                    $data_apartmentHandOver[$j]['cmt'] != null &&
                    $data_apartmentHandOver[$j]['phone'] != null &&
                    $data_apartmentHandOver[$j]['email'] != null &&
                    $data_apartmentHandOver[$j]['password'] != null &&
                    $data_apartmentHandOver[$j]['sex'] != null &&
                    $data_apartmentHandOver[$j]['type'] != null &&
                    $data_apartmentHandOver[$j]['address'] != null &&
                    $data_apartmentHandOver[$j]['date_handover'] != null &&
                    $data_apartmentHandOver[$j]['apartment_name'] != null &&
                    $data_apartmentHandOver[$j]['floor'] != null &&
                    $data_apartmentHandOver[$j]['place'] != null 
                ){
                   if ($data_apartmentHandOver[$i]['phone'] == $data_apartmentHandOver[$j]['phone'] || $data_apartmentHandOver[$i]['email'] == $data_apartmentHandOver[$j]['email']) {
                        $duplicate_data=[
                            'index' =>  $data_apartmentHandOver[$j]['index'],
                            'display_name' =>  $data_apartmentHandOver[$j]['name'],
                            'cmt' => $data_apartmentHandOver[$j]['cmt'],
                            'phone' => $data_apartmentHandOver[$j]['phone'],
                            'birthday' => $data_apartmentHandOver[$j]['birthday'],
                            'email' => $data_apartmentHandOver[$j]['email'],
                            'password' => $data_apartmentHandOver[$j]['password'],
                            'gender' => $data_apartmentHandOver[$j]['sex'],
                            'status_confirm' => $data_apartmentHandOver[$j]['type'],
                            'address' => $data_apartmentHandOver[$j]['address'],
                            'handover_date' => $data_apartmentHandOver[$j]['date_handover'],
                            'note_confirm' => $data_apartmentHandOver[$j]['note_confirm'],
                            'apartment_name' => $data_apartmentHandOver[$j]['apartment_name'],
                            'floor' => $data_apartmentHandOver[$j]['floor'],
                            'place' => $data_apartmentHandOver[$j]['place'],  
                        ];
                        array_push($duplicate, $duplicate_data);
                        unset($array_data_apartmentHandOver[$i]);
                    }

                }
               
            }
                
            }
           
        }
        $data = [
            'data' => $array_data_apartmentHandOver,
            'duplicate' => $duplicate,
        ];

        return $data;
    }
     public function apartmentHandOverExelData($customers,$url,$building_id)
    {

        $check_phone = $this->customerAllByphone($building_id);
        $check_email = $this->customerAllByemail($building_id);
        $has_ap=[];$fail_ap=[];$new_ap=[];
        foreach ($customers['data'] as $key => $cus) {
            if($cus['index'] && $cus['display_name'] && !in_array($cus['phone'], $check_phone) && !in_array($cus['email'], $check_email)){
                $new_ap[] = [
                    'index'=> $cus['index'],
                    'display_name' => $cus['display_name'],
                    'cmt' => $cus['cmt'],
                    'phone' => $cus['phone'],
                    'birthday' => $cus['birthday'],
                    'email' => $cus['email'],
                    'password' => $cus['password'],
                    'gender' => $cus['gender'],
                    'status_confirm' => $cus['status_confirm'],
                    'type' => 1,
                    'address' => $cus['address'],
                    'handover_date' => $cus['handover_date'],
                    'note_confirm' => $cus['note_confirm'],
                    'apartment_name' => $cus['apartment_name'],
                    'floor' => $cus['floor'],
                    'place' => $cus['place'],
                ];
            }else{
                if ( !in_array($cus['phone'], $check_phone) || !in_array($cus['email'], $check_email)) {
                    if($cus['index'] && $cus['display_name'] && ($cus['phone'] || $cus['email'])){
                        $new_ap[] = [
                            'index'=> $cus['index'],
                            'display_name' => $cus['display_name'],
                            'cmt' => $cus['cmt'],
                            'phone' => $cus['phone'],
                            'birthday' => $cus['birthday'],
                            'email' => $cus['email'],
                            'password' => $cus['password'],
                            'gender' => $cus['gender'],
                            'status_confirm' => $cus['status_confirm'],
                            'type' => 1,
                            'address' => $cus['address'],
                            'handover_date' => $cus['handover_date'],
                            'note_confirm' => $cus['note_confirm'],
                            'apartment_name' => $cus['apartment_name'],
                            'floor' => $cus['floor'],
                            'place' => $cus['place'],
                        ];
                    }else{
                        $fail_ap[] = [
                            'index'=> $cus['index'],
                            'display_name' => $cus['display_name'],
                            'cmt' => $cus['cmt'],
                            'phone' => $cus['phone'],
                            'birthday' => $cus['birthday'],
                            'email' => $cus['email'],
                            'password' => $cus['password'],
                            'gender' => $cus['gender'],
                            'status_confirm' => $cus['status_confirm'],
                            'type' => 1,
                            'address' => $cus['address'],
                            'handover_date' => $cus['handover_date'],
                            'note_confirm' => $cus['note_confirm'],
                            'apartment_name' => $cus['apartment_name'],
                            'floor' => $cus['floor'],
                            'place' => $cus['place'],
                        ];
                    }
                } else {
                    $has_ap[] = [
                        'index'=> $cus['index'],
                        'display_name' => $cus['display_name'],
                        'cmt' => $cus['cmt'],
                        'phone' => $cus['phone'],
                        'birthday' => $cus['birthday'],
                        'email' => $cus['email'],
                        'password' => $cus['password'],
                        'gender' => $cus['gender'],
                        'status_confirm' => $cus['status_confirm'],
                        'type' => 1,
                        'address' => $cus['address'],
                        'handover_date' => $cus['handover_date'],
                        'note_confirm' => $cus['note_confirm'],
                        'apartment_name' => $cus['apartment_name'],
                        'floor' => $cus['floor'],
                        'place' => $cus['place'],
                    ];
                }
            }

        }
        if (!empty($has_ap)) {
            $messages[]= [
                'messages' => 'Có ' . count($has_ap) . ' cư dân đã có trên hệ thống',
                'data'     => $has_ap,
            ];
        }

        if(!empty($customers['duplicate'])){
            $messages[]= [
                'messages' => 'Có ' . count($customers['duplicate']) . ' cư dân bị trùng trong file',
                'data'     => $customers['duplicate'],
            ];
        }
        if(!empty($new_ap)){
            $messages[]= [
                'messages' => 'Có ' . count($new_ap) . ' cư dân đầy đủ dữ liệu',
                'data'     => $new_ap,
            ];
        }
        if(!empty($fail_ap)){
            $messages[]= [
                'messages' => 'Có ' . count($fail_ap) . ' cư dân bị thiếu dữ liệu',
                'data'     => $fail_ap
            ];
        }
        $data['messages'] = $messages;

        $data_new = [
            'data_cus' =>$new_ap,
            'has_cus' =>$has_ap,
            'url_file'  => $url,
            'duplicate' => $customers['duplicate']??[],
        ];
        $data['data'] = $data_new;
        return $data;
    }

    public function apartmentHandOverDataSave($dataExel,$building_id,$app_id)
    {
        $data_ap=[];
        if ($dataExel['data']['data_cus'] && !(session()->get('errors_user'))) {
            foreach ($dataExel['data']['data_cus'] as $index => $cus) {
                $data_ap[] = [
                    'display_name'  => $cus['display_name'],
                    'cmt' => $cus['cmt'],
                    'phone' => $cus['phone'],
                    'birthday' => date('Y-m-d', strtotime($cus['birthday'])),
                    'email' => $cus['email'],
                    'gender' => $cus['gender'],
                    'status_confirm' => $cus['status_confirm'],
                    'address' => $cus['address'],
                    'handover_date' => $cus['handover_date'],
                    'note_confirm' => $cus['note_confirm'],
                    'type' => Users::USER_APP,
                    'bdc_building_id' => $building_id,
                    'app_id' => $app_id??'buildingcare',
                ];
            }

        }
        if ($dataExel['data']['has_cus'] && !(session()->get('errors_user'))) {
            foreach ($dataExel['data']['has_cus'] as $index => $cus) {
                $data_ap[] = [
                    'display_name'  => $cus['display_name'],
                    'cmt' => $cus['cmt'],
                    'phone' => $cus['phone'],
                    'birthday' => date('Y-m-d', strtotime($cus['birthday'])),
                    'email' => $cus['email'],
                    'gender' => $cus['gender'],
                    'status_confirm' => $cus['status_confirm'],
                    'address' => $cus['address'],
                    'handover_date' => $cus['handover_date'],
                    'note_confirm' => $cus['note_confirm'],
                    'type' => Users::USER_APP,
                    'bdc_building_id' => $building_id,
                    'app_id' => $app_id??'buildingcare',
                ];
            }

        }
        $dataExel['data'] = array_merge($dataExel['data'],['customers' =>$data_ap]);
        return $dataExel;
    }
    public function customerAllByphone($building_id)
    {
        return array_map(function($item){ return $item['phone']; }, $this->model->select('phone')->where('bdc_building_id',$building_id)->where('type',Users::USER_APP)->get()->toArray());
    }
    public function customerAllByemail($building_id)
    {
        return array_map(function($item){ return $item['email']; }, $this->model->select('email')->where('bdc_building_id',$building_id)->where('type',Users::USER_APP)->get()->toArray());
    }
    public function insert(array $data) {
        return $this->model->insert($data);
    }
}
