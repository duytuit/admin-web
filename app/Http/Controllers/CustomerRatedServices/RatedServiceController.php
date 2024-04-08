<?php

namespace App\Http\Controllers\CustomerRatedServices;

use App\Commons\Api;
use App\Commons\Helper;
use App\Http\Controllers\BuildingController;
use Illuminate\Http\Request;
use App\Models\Building\Building;
use App\Models\CustomerRatedServices\CustomerRatedServices;
use App\Models\Department\Department;
use App\Models\DepartmentStaff\DepartmentStaff;
use App\Repositories\CustomerRatedServices\CustomerRatedServicesRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PHPExcel_Style_Border;

class RatedServiceController extends BuildingController
{

    
    private $_customerRatedServicesRepository;

    public function __construct(Request $request,
            CustomerRatedServicesRepository $customerRatedServicesRepository
        )
    {
        $this->_customerRatedServicesRepository = $customerRatedServicesRepository;
        parent::__construct($request);
    }
    public function total(Request $request)
    {
        $data['meta_title'] = 'Tổng hợp đánh giá';
        $data['per_page'] = Cookie::get('per_page', 10);
        $request->merge([
            'from_date' => $request->get('from_date',Carbon::now()->firstOfMonth()->format('d-m-Y')),
        ]);
        $request->merge([
            'to_date' => $request->get('to_date',Carbon::now()->lastOfMonth()->format('d-m-Y')),
        ]);
        $data['filter'] = $request->all();
        $ds_nhan_vien = $this->get_total($request)->paginate($data['per_page']);
        foreach ($ds_nhan_vien as $key => $value) {
            $ds_nhan_vien[$key]['vote'] = CustomerRatedServices::select('department_id','point',  DB::raw('count("id") as total'))
                                                                ->where(function($query) use ($request){
                                                                    // tìm bộ phận
                                                                    if($request->from_date && $request->to_date){
                                                                        $from_date = Carbon::parse($request->from_date)->format('Y-m-d');
                                                                        $to_date = Carbon::parse($request->to_date)->format('Y-m-d');
                                                                        $query->whereDate('created_at','>=',$from_date);
                                                                        $query->whereDate('created_at','<=',$to_date);
                                                                    }
                                                                })
                                                                ->where('department_id',$value->department_id)->groupBy('department_id','point')->get();
            $ds_nhan_vien[$key]['total_point'] = CustomerRatedServices::where(function($query) use ($request){
                                                                    // tìm bộ phận
                                                                    if($request->from_date && $request->to_date){
                                                                        $from_date = Carbon::parse($request->from_date)->format('Y-m-d');
                                                                        $to_date = Carbon::parse($request->to_date)->format('Y-m-d');
                                                                        $query->whereDate('created_at','>=',$from_date);
                                                                        $query->whereDate('created_at','<=',$to_date);
                                                                    }
                                                                })
                                                                ->where('department_id',$value->department_id)->sum('point');
            $ds_nhan_vien[$key]['total_employee'] = CustomerRatedServices::where(function($query) use ($request){
                                                                    // tìm bộ phận
                                                                    if($request->from_date && $request->to_date){
                                                                        $from_date = Carbon::parse($request->from_date)->format('Y-m-d');
                                                                        $to_date = Carbon::parse($request->to_date)->format('Y-m-d');
                                                                        $query->whereDate('created_at','>=',$from_date);
                                                                        $query->whereDate('created_at','<=',$to_date);
                                                                    }
                                                                })
                                                                ->where('department_id',$value->department_id)->count('id');                                                                                                                  
        }
        $data['ds_nhan_vien'] =  $ds_nhan_vien;
         // tổng điểm tất cả
         $data['tong_diem'] = CustomerRatedServices::whereHas('department',function($query) use ($request){
                                                        $query->where('bdc_building_id',$this->building_active_id);
                                                        // tìm bộ phận
                                                        if($request->bdc_department_id){
                                                            $query->where('id',$request->bdc_department_id);
                                                        }
                                                    })
                                                    ->where(function($query) use ($request){
                                                        if($request->keyword){
                                                            $query->whereHas('user_info_rated',function($query) use ($request){
                                                                // tìm nhân viên
                                                                $query->where('display_name','like','%'.$request->keyword.'%')
                                                                    ->orwhere('email','like','%'.$request->keyword.'%')
                                                                    ->orwhere('phone','like','%'.$request->keyword.'%');
                                                            });
                                                        }
                                                        if($request->from_date && $request->to_date){
                                                            $from_date = Carbon::parse($request->from_date)->format('Y-m-d');
                                                            $to_date = Carbon::parse($request->to_date)->format('Y-m-d');
                                                            $query->whereDate('created_at','>=',$from_date);
                                                            $query->whereDate('created_at','<=',$to_date);
                                                        }
                                                    })->sum('point');
         // tổng lượt đánh giá tất cả
         $data['tong_danh_gia'] = CustomerRatedServices::whereHas('department',function($query) use ($request){
                                                        $query->where('bdc_building_id',$this->building_active_id);
                                                        // tìm bộ phận
                                                        if($request->bdc_department_id){
                                                            $query->where('id',$request->bdc_department_id);
                                                        }
                                                    })
                                                    ->where(function($query) use ($request){
                                                        if($request->keyword){
                                                            $query->whereHas('user_info_rated',function($query) use ($request){
                                                                // tìm nhân viên
                                                                $query->where('display_name','like','%'.$request->keyword.'%')
                                                                    ->orwhere('email','like','%'.$request->keyword.'%')
                                                                    ->orwhere('phone','like','%'.$request->keyword.'%');
                                                            });
                                                        }
                                                        if($request->from_date && $request->to_date){
                                                            $from_date = Carbon::parse($request->from_date)->format('Y-m-d');
                                                            $to_date = Carbon::parse($request->to_date)->format('Y-m-d');
                                                            $query->whereDate('created_at','>=',$from_date);
                                                            $query->whereDate('created_at','<=',$to_date);
                                                        }
                                                    })->count('point');
         // tổng vote
         $data['tong_vote'] = CustomerRatedServices::select('department_id','point',  DB::raw('count("id") as total'))
                                                            ->whereHas('department',function($query) use ($request){
                                                                $query->where('bdc_building_id',$this->building_active_id);
                                                                // tìm bộ phận
                                                                if($request->bdc_department_id){
                                                                    $query->where('id',$request->bdc_department_id);
                                                                }
                                                            })
                                                            ->where(function($query) use ($request){
                                                                if($request->keyword){
                                                                    $query->whereHas('user_info_rated',function($query) use ($request){
                                                                        // tìm nhân viên
                                                                        $query->where('display_name','like','%'.$request->keyword.'%')
                                                                            ->orwhere('email','like','%'.$request->keyword.'%')
                                                                            ->orwhere('phone','like','%'.$request->keyword.'%');
                                                                    });
                                                                }
                                                                if($request->from_date && $request->to_date){
                                                                    $from_date = Carbon::parse($request->from_date)->format('Y-m-d');
                                                                    $to_date = Carbon::parse($request->to_date)->format('Y-m-d');
                                                                    $query->whereDate('created_at','>=',$from_date);
                                                                    $query->whereDate('created_at','<=',$to_date);
                                                                }
                                                            })
                                                            ->groupBy('department_id')->get();
         // bộ phận
         $data['bo_phan'] = Department::where('bdc_building_id',$this->building_active_id)->where('status_app',1)->get();
         // giới hạn đánh giá
         $building= Building::find($this->building_active_id);
         $building->limit_audit = json_decode( $building->limit_audit );
         $data['building'] = $building;

        return view('customer-rated-services.tong_hop_danh_gia', $data);
    }
    public function update_limit_audit(Request $request)
    {
        $building = Building::find($request->building_id);
        if($building){
                if($request->limit != 'khong_gioi_han' && $request->gia_tri_gioi_han == null){
                    return redirect()->route('admin.rated_service.total')->with('warning', 'Chưa nhập giá trị giới hạn!');
                }
                $value = [
                    'type'=>$request->limit,
                    'limit'=> $request->limit != 'khong_gioi_han' ? $request->gia_tri_gioi_han : null
                ];
                $building->limit_audit = json_encode($value);
                $building->save();
                return redirect()->route('admin.rated_service.total')->with('success', 'Thiết lập thành công!');
        }
        return redirect()->route('admin.rated_service.total')->with('success', 'Thiết lập thất bại!');
    }
    public function detail(Request $request)
    {
        $data['meta_title'] = 'Chi tiết đánh giá';
        $data['per_page'] = Cookie::get('per_page', 10);
        $request->merge([
            'from_date' => $request->get('from_date',Carbon::now()->firstOfMonth()->format('d-m-Y')),
        ]);
        $request->merge([
            'to_date' => $request->get('to_date',Carbon::now()->lastOfMonth()->format('d-m-Y')),
        ]);
        $data['filter'] = $request->all();
        $danh_gia_chi_tiet = $this->get_detail($request)->paginate($data['per_page']);
        //dd($danh_gia_chi_tiet);
        $data['danh_gia_chi_tiet'] =  $danh_gia_chi_tiet;
        $data['app_danh_gia'] =  Helper::app_company;
        // bộ phận
        $data['bo_phan'] = Department::whereHas('department_staffs')->where('bdc_building_id',$this->building_active_id)->get();
        return view('customer-rated-services.chi_tiet_danh_gia', $data);
    }
    public function action(Request $request)
    {
        $method = $request->input('method','');
        if ($method == 'per_page') {
            $this->per_page($request);
            return back();
        }
       
    }
    public function get_total(Request $request)
    {
       return CustomerRatedServices::where(function($query) use ($request){
            if($request->keyword){
                $query->whereHas('user_info_rated',function($query) use ($request){
                    // tìm nhân viên
                    $query->where('display_name','like','%'.$request->keyword.'%')
                        ->orwhere('email','like','%'.$request->keyword.'%')
                        ->orwhere('phone','like','%'.$request->keyword.'%');
                });
            }
            if($request->from_date && $request->to_date){
                $from_date = Carbon::parse($request->from_date)->format('Y-m-d');
                $to_date = Carbon::parse($request->to_date)->format('Y-m-d');
                $query->whereDate('created_at','>=',$from_date);
                $query->whereDate('created_at','<=',$to_date);
            }
        })
        ->where(function($query) use ($request){
            $query->where('bdc_building_id',$this->building_active_id);
            // tìm bộ phận
            if($request->bdc_department_id){
                $query->where('id',$request->bdc_department_id);
            }
        })
        ->groupBy('department_id')
        ->orderBy('department_id')
        ->orderBy('created_at','desc');
    }
    public function get_detail(Request $request)
    {
        return CustomerRatedServices::where(function($query) use ($request){
                            // tìm ngày đánh giá
                            if($request->from_date && $request->to_date){
                                $from_date = Carbon::parse($request->from_date)->format('Y-m-d');
                                $to_date = Carbon::parse($request->to_date)->format('Y-m-d');
                                $query->whereDate('created_at','>=',$from_date);
                                $query->whereDate('created_at','<=',$to_date);
                            }
                            if($request->keyword){
                                $query->where('customer_name','like','%'.$request->keyword.'%')
                                    ->orwhere('email','like','%'.$request->keyword.'%')
                                    ->orwhere('phone','like','%'.$request->keyword.'%')
                                    ->orwhere('apartment_name','like','%'.$request->keyword.'%')
                                    ->orWhereHas('user_info_rated', function( $query ) use ( $request ){
                                            $query->where('display_name','like','%'.$request->keyword.'%')
                                                ->orwhere('email','like','%'.$request->keyword.'%')
                                                ->orwhere('phone','like','%'.$request->keyword.'%');
                                    });
                            }
                            if($request->from_where !=null){
                                $query->where('from_where',$request->from_where);
                            }
                            if(isset($request->type_object) || $request->type_object !=null){
                                if($request->type_object == 1){ // cư dân
                                    $query->where('apartment_name','<>','Vãng lai');
                                }
                                if($request->type_object == 2){ // vãng lai
                                    $query->where('apartment_name','Vãng lai');
                                }
                            }
                })
                ->where(function($query) use ($request){
                    $query->where('bdc_building_id',$this->building_active_id);
                    // tìm bộ phận
                    if($request->bdc_department_id){
                        $query->where('id',$request->bdc_department_id);
                    }
                })
                ->orderBy('created_at','desc');
       
    }
    public function export_total(Request $request)
    {
        $request->merge([
            'from_date' => $request->get('from_date',Carbon::now()->firstOfMonth()->format('d-m-Y')),
        ]);
        $request->merge([
            'to_date' => $request->get('to_date',Carbon::now()->lastOfMonth()->format('d-m-Y')),
        ]);
      
        $ds_nhan_vien = $this->get_total($request)->get();
        
        $result = Excel::create('Danh_sach_danh_gia_'.date('d-m-Y-H-i-s', time()), function ($excel) use ($ds_nhan_vien,$request) {
            $excel->setTitle('Danh sách');
            $excel->sheet('Danh sách', function ($sheet) use ($ds_nhan_vien,$request) {
                $result = [];
                foreach ($ds_nhan_vien as $key => $v) {
                    // Tổng điểm
                    $v->total_point = CustomerRatedServices::where(function($query) use ($request){
                        // tìm bộ phận
                        if($request->from_date && $request->to_date){
                            $from_date = Carbon::parse($request->from_date)->format('Y-m-d');
                            $to_date = Carbon::parse($request->to_date)->format('Y-m-d');
                            $query->whereDate('created_at','>=',$from_date);
                            $query->whereDate('created_at','<=',$to_date);
                        }
                    })
                    ->where('department_id',$v->department_id)->sum('point');
                    // Tổng lượt đánh giá
                    $v->total_employee = CustomerRatedServices::where(function($query) use ($request){
                        // tìm bộ phận
                        if($request->from_date && $request->to_date){
                            $from_date = Carbon::parse($request->from_date)->format('Y-m-d');
                            $to_date = Carbon::parse($request->to_date)->format('Y-m-d');
                            $query->whereDate('created_at','>=',$from_date);
                            $query->whereDate('created_at','<=',$to_date);
                        }
                    })
                    ->where('department_id',$v->department_id)->count('id');   
                    // tổng vote
                    $v->vote = CustomerRatedServices::select('department_id','point',  DB::raw('count("id") as total'))
                                                                ->where(function($query) use ($request){
                                                                    // tìm bộ phận
                                                                    if($request->from_date && $request->to_date){
                                                                        $from_date = Carbon::parse($request->from_date)->format('Y-m-d');
                                                                        $to_date = Carbon::parse($request->to_date)->format('Y-m-d');
                                                                        $query->whereDate('created_at','>=',$from_date);
                                                                        $query->whereDate('created_at','<=',$to_date);
                                                                    }
                                                                })
                                                                ->where('department_id',$v->department_id)
                                                                ->groupBy('department_id','point')->get();
                     $department = Department::get_detail_department_by_id($v->department_id);
                     if($v->vote){
                        $diem_1 = 0;
                        $diem_2 = 0;
                        $diem_3 = 0;
                        $diem_4 = 0;
                        $diem_5 = 0;
                        foreach ($v->vote as $key_1 => $value) {
                            if($value->point == -3){
                                $diem_1 = $value->total;
                            }
                            if($value->point == -1){
                                $diem_2 = $value->total;
                            }
                            if($value->point == 1){
                                $diem_3 = $value->total;
                            }
                            if($value->point == 3){
                                $diem_4 = $value->total;
                            }
                            if($value->point == 5){
                                $diem_5 = $value->total;
                            }
                           
                        } 
                        $result[] = [
                            'STT' => $key+1,
                            'Nhân viên/ Nhà thầu' => @$v->user_info_rated->display_name,
                            'Mã nhân viên/ Nhà thầu' => @$v->user_info_rated->staff_code,
                            'Bộ phận' => @$department->name,
                            'SĐT' => @$v->user->phone,
                            'Email' => @$v->user->email,
                            'Tổng điểm' => $v->total_point,
                            'Tổng lượt đánh giá' =>  $v->total_employee,
                            'Rất không hài lòng' => $diem_1,
                            'Chưa hài lòng' => $diem_2,
                            'Bình thường' => $diem_3,
                            'Hài lòng' => $diem_4,
                            'Rất hài lòng' => $diem_5,
                        ];
                     }else{
                        $result[] = [
                            'STT' => $key+1,
                            'Nhân viên/ Nhà thầu' => @$v->user_info_rated->display_name,
                            'Mã nhân viên/ Nhà thầu' => @$v->user_info_rated->staff_code,
                            'Bộ phận' => @$department->name,
                            'SĐT' => @$v->user->phone,
                            'Email' => @$v->user->email,
                            'Tổng điểm' => $v->total_point,
                            'Tổng lượt đánh giá' =>  $v->total_employee,
                            'Rất không hài lòng' => 0,
                            'Chưa hài lòng' => 0,
                            'Bình thường' => 0,
                            'Hài lòng' => 0,
                            'Rất hài lòng' => 0,
                        ];
                     }
                }
                $sheet->setAutoSize(true);
                if ($result) {
                    $sheet->fromArray($result);
                }
                $sheet->cell('A1:M1', function ($cell) {
                    // change header color
                    $cell->setFontColor('#000000')
                        ->setBackground('#cecece')
                        ->setFontWeight('bold')
                        ->setFontSize(10)
                        ->setAlignment('center')
                        ->setValignment('center');
                });
            });
        })->store('xlsx',storage_path('exports/'));
        $file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
        return response()->download($file)->deleteFileAfterSend(true);
             
    }
    public function export_detail(Request $request)
    {
        $request->merge([
            'from_date' => $request->get('from_date',Carbon::now()->firstOfMonth()->format('d-m-Y')),
        ]);
        $request->merge([
            'to_date' => $request->get('to_date',Carbon::now()->lastOfMonth()->format('d-m-Y')),
        ]);
        $ds_nhan_vien = $this->get_detail($request)->get();
        $building = Building::get_detail_building_by_building_id($this->building_active_id);
        $result = Excel::create('Danh_sach_danh_gia_chi_tiet_'.date('d-m-Y-H-i-s', time()), function ($excel) use ($ds_nhan_vien,$request,$building) {
            $excel->setTitle('Danh sách');
            $excel->sheet('Danh sách', function ($sheet) use ($ds_nhan_vien,$request,$building) {
                $result = [];
                $apartment_temp = null;
                $row = 5;
                $sheet->cells('C1', function ($cells) use($building) {
                    $cells->setFontSize(22);
                    $cells->setFontWeight('bold');
                    $cells->setValue($building->name);
                    $cells->setAlignment('center');
                });

                $sheet->cells('C2', function ($cells) use ($request) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('Iatalic');
                    if (isset($request['from_date']) && isset($request['to_date'])) {

                        $cells->setValue('Từ ngày :..' . $request['from_date'] . '..Đến :..' . $request['to_date']);
                    } else {
                        $cells->setValue('Từ ngày..............Đến.............. ');
                    }
                    $cells->setAlignment('center');
                });
                $sheet->cells('A5', function ($cells) {
                    $cells->setFontSize(12);
                    $cells->setValue('STT');
                    $cells->setBackground('##F5C09D'); 
                });
                $sheet->cells('B5', function ($cells) {
                    $cells->setFontSize(12);
                    $cells->setValue('KHÁCH HÀNG');
                    $cells->setBackground('##F5C09D'); 
                });
                $sheet->cells('C5', function ($cells) {
                    $cells->setFontSize(12);
                    $cells->setValue('THỜI GIAN ĐÁNH GIÁ');
                    $cells->setBackground('##F5C09D'); 
                });
                $sheet->cells('D5', function ($cells) {
                    $cells->setFontSize(12);
                    $cells->setValue('MỨC ĐỘ HÀI LÒNG');
                    $cells->setBackground('##F5C09D'); 
                });
                $sheet->cells('E5', function ($cells) {
                    $cells->setFontSize(12);
                    $cells->setValue('ĐIỂM');
                    $cells->setBackground('##F5C09D'); 
                });
                $sheet->cells('F5', function ($cells) {
                    $cells->setFontSize(12);
                    $cells->setValue('ĐỐI TƯỢNG');
                    $cells->setBackground('##F5C09D'); 
                });
                $count=0;
                $sum_point = 0;
                $sum_total_point = 0;
                $diem_1 = 0;
                $diem_2 = 0;
                $diem_3 = 0;
                $diem_4 = 0;
                $diem_5 = 0;
                foreach ($ds_nhan_vien as $key => $v) {
                    $row ++;
                    $sum_total_point +=$v->point;
                    $department = Department::get_detail_department_by_id($v->department_id);
                    if($apartment_temp != @$department->id){
                        $count++;
                        if($key != 0 || $ds_nhan_vien->count() == 1){
                            $sheet->mergeCells("A$row:D$row");
                            $sheet->cells("A$row:D$row", function ($cells) {
                                $cells->setValignment('center');
                                $cells->setAlignment('center');
                            });
                            $sheet->cells("A$row:F$row", function ($cells) {
                                $cells->setBackground('#BBD9A7'); 
                            });
                            $sheet->setCellValueByColumnAndRow(0, $row,'TỔNG ĐIỂM BỘ PHẬN');
                            $sheet->setCellValueByColumnAndRow(4, $row,$sum_point);
                            $row ++;
                        }
                        $apartment_temp = @$department->id;
                        $sheet->setCellValueByColumnAndRow(0, $row,$count.'/ '. $department->name);
                        $sheet->cells("A$row:F$row", function ($cells) {
                            $cells->setBackground('#FFFF09'); 
                        });
                        $row ++;
                        $sum_point=0;
                    }
                    $sum_point +=$v->point;
                    if ($v->point == -3) {
                        $danh_gia = 'Rất không hài lòng';
                        $diem_1 += $v->point;
                    } elseif ($v->point == -1) {
                        $danh_gia = 'Chưa hài lòng';
                        $diem_2 += $v->point;
                    } elseif ($v->point == 1) {
                        $danh_gia = 'Bình thường';
                        $diem_3 += $v->point;
                    } elseif ($v->point == 3) {
                        $danh_gia = 'Hài lòng';
                        $diem_4 += $v->point;
                    } elseif ($v->point == 5) {
                        $danh_gia = 'Rất hài lòng';
                        $diem_5 += $v->point;
                    }
                    
                    $sheet->setCellValueByColumnAndRow(0, $row, ($key+1));
                    $sheet->setCellValueByColumnAndRow(1, $row, $v->customer_name);
                    $sheet->setCellValueByColumnAndRow(2, $row, $v->created_at);
                    $sheet->setCellValueByColumnAndRow(3, $row, $danh_gia);
                    $sheet->setCellValueByColumnAndRow(4, $row, $v->point);
                    $sheet->setCellValueByColumnAndRow(5, $row, $v->apartment_name == 'Vãng lai' ? 'Vãng lai' : 'Cư dân');

                }
                $row++;
                $sheet->mergeCells("A$row:D$row");
                $sheet->cells("A$row:D$row", function ($cells) {
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                });
                $sheet->cells("A$row:F$row", function ($cells) {
                    $cells->setBackground('#BBD9A7'); 
                });
                $sheet->setCellValueByColumnAndRow(0, $row,'TỔNG ĐIỂM BỘ PHẬN');
                $sheet->setCellValueByColumnAndRow(4, $row,$sum_point);
                $sheet->setAutoSize(true);
                $row++;
                $row++;
                $sheet->cells("A$row", function ($cells) {
                    $cells->setFontSize(12);
                    $cells->setValue('TỔNG KẾT');
                    $cells->setFontWeight('bold');
                });
                $row++;
                $sheet->cells("B$row", function ($cells) {
                    $cells->setFontSize(12);
                    $cells->setValue('TỔNG ĐIỂM BQL');
                    $cells->setFontWeight('bold');
                });
                $sheet->setCellValueByColumnAndRow(2, $row, $sum_total_point .' điểm');
                $row++;
                $sheet->cells("B$row", function ($cells) {
                    $cells->setFontSize(12);
                    $cells->setValue('TỶ LỆ MỨC ĐỘ HÀI LÒNG');
                    $cells->setFontWeight('bold');
                });
                $row++;
                $sheet->cells("B$row", function ($cells) {
                    $cells->setFontSize(12);
                    $cells->setValue('Rất không hài lòng');
                });
                $sheet->setCellValueByColumnAndRow(2, $row, ($diem_1/$sum_total_point)*100 .' %');
                $row++;
                $sheet->cells("B$row", function ($cells) {
                    $cells->setFontSize(12);
                    $cells->setValue('Chưa hài lòng');
                });
                $sheet->setCellValueByColumnAndRow(2, $row, ($diem_2/$sum_total_point)*100 .' %');
                $row++;
                $sheet->cells("B$row", function ($cells) {
                    $cells->setFontSize(12);
                    $cells->setValue('Bình thường');
                });
                $sheet->setCellValueByColumnAndRow(2, $row, ($diem_3/$sum_total_point)*100 .' %');
                $row++;
                $sheet->cells("B$row", function ($cells) {
                    $cells->setFontSize(12);
                    $cells->setValue('Hài lòng');
                });
                $sheet->setCellValueByColumnAndRow(2, $row, ($diem_4/$sum_total_point)*100 .' %');
                $row++;
                $sheet->cells("B$row", function ($cells) {
                    $cells->setFontSize(12);
                    $cells->setValue('Rất hài lòng');
                });
                $sheet->setCellValueByColumnAndRow(2, $row, ($diem_5/$sum_total_point)*100 .' %');
            });
        })->store('xlsx',storage_path('exports/'));
        $file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
        return response()->download($file)->deleteFileAfterSend(true);
             
    }
    public function auditApp(Request $request)
    {
        $data['meta_title'] = 'Chi tiết đánh giá APP';
        $data['per_page'] = Cookie::get('per_page', 10);

        $data['filter'] =  $request->all();

        $limit =  $data['per_page'] ?  $data['per_page'] : 10;
        $page = isset($request->page) ? $request->page : 1;

        // $request->request->add(['limit' => $limit]);
        // $request->request->add(['page' => $page]);

        $request->request->add(['building_id' => $this->building_active_id]);

        $array_search='';
        $i=0;
        foreach ($request->all() as $key => $value) {
            if($i == 0){
                $param='?'.$key.'='.(string)$value;
            }else{
                $param='&'.$key.'='.(string)$value;
            }
            $i++;
            $array_search.=$param;
        }
        $data['array_search'] = $array_search;

        // $getListFeedbackNoteEvaluate = Api::GET('admin/evaluate/getListFeedbackNoteEvaluate',$request->all());

        // if($getListFeedbackNoteEvaluate->status == true){
        //     $_getListFeedbackNoteEvaluate = new LengthAwarePaginator($getListFeedbackNoteEvaluate->data->list, $getListFeedbackNoteEvaluate->data->count, $limit, $page,  ['path' => route('admin.rated_service.auditApp')]);
        // }
        // $getListEvaluate = Api::GET('admin/evaluate/getListEvaluate',$request->all());

        // if($getListEvaluate->status == true){
        //     $_getListEvaluate = new LengthAwarePaginator($getListEvaluate->data->list, $getListEvaluate->data->count, $limit, $page,  ['path' => route('admin.rated_service.auditApp')]);
        // }

        // $data['getListFeedbackNoteEvaluate'] = @$_getListFeedbackNoteEvaluate;
        // $data['getListEvaluate'] = @$_getListEvaluate;

        return view('customer-rated-services.list_audit_app', $data);
       
    }
}
