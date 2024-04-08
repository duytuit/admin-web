@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        Ý kiến phản hồi
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Ý kiến phản hồi</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-sm-8">
            <div class="box box-primary">
                <div class="box-body">
                    <div class="form-group">
                        <label>Form Yêu Cầu</label>
                        <div class="row attached-images space-5">
                            @if ($user_request->type == 1)
                                <table class="table table-striped table-bordered table-hover">
                                    <thead class="bg-primary">
                                        <tr>
                                            <th colspan="2" class="text-center">Thông tin đăng ký phương tiện</th>
                                        </tr>
                                    </thead>
                                    @php
                                        if($user_request->data){
                                            $data = json_decode($user_request->data);
                                            $category = App\Models\VehicleCategory\VehicleCategory::get_detail_vehicles_category_by_id(@$data->type_vehicles);
                                            $apartment = App\Models\Apartments\Apartments::get_detail_apartment_by_apartment_id($user_request->apartment_id);
                                            $buildingPlace =$apartment ? App\Models\Building\BuildingPlace::get_detail_bulding_place_by_bulding_place_id($apartment->building_place_id) : null;
                                        }
                                    @endphp
                                    <tbody>
                                        <tr>
                                            <td width="50%">
                                                <div>Chủ phương tiện: </div>
                                                <div><strong>{{@$data->full_name}}</strong></div>
                                            </td>
                                            <td>
                                                <div>Loại xe: </div>
                                                <div><strong>{{$category->name}}</strong></div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div>Biển số xe: </div>
                                                <div><strong>{{@$data->number_vehicles}}</strong></div>
                                            </td>
                                            <td>
                                                <div>Ngày bắt đầu sử dụng dự kiến: </div>
                                                <div><strong>{{date('d/m/Y',strtotime(@$data->date_begin))}}</strong> </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div style="padding: 0;">Ảnh phương tiện: </div>
                                                    @if($data->image_vehicles && count(@$data->image_vehicles) > 0)
                                                        @php
                                                            $image_vehicles= $data->image_vehicles;
                                                        @endphp
                                                        @if($image_vehicles)
                                                            @foreach($image_vehicles as $_image_vehicles)
                                                                <span class="comment-content-file-item">
                                                                    <a target="_blank" href="{{ $_image_vehicles }}" style="height:170px;display: inline-flex;"><img src="{{ $_image_vehicles }}" class="set-custom-img" ></a>
                                                                </span>
                                                            @endforeach
                                                        @endif
                                                    @endif
                                            </td>
                                            <td>
                                                <div style="padding: 0;">Ảnh đăng ký xe: </div>
                                                    @if($data->image_reg_vehicles && count(@$data->image_reg_vehicles) > 0)
                                                        @php
                                                            $image_reg_vehicles= $data->image_reg_vehicles;
                                                        @endphp
                                                        @if($image_reg_vehicles)
                                                            @foreach($image_reg_vehicles as $_image_reg_vehicles)
                                                                    <span class="comment-content-file-item">
                                                                        <a target="_blank" href="{{ $_image_reg_vehicles }}" style="height:170px;display: inline-flex;"><img src="{{ $_image_reg_vehicles }}" class="set-custom-img" ></a>
                                                                    </span>
                                                            @endforeach
                                                        @endif
                                                    @endif
                                                <div style="padding: 0;">Ảnh đăng ký cmnd: </div>
                                                    @if($data->image_cmnd && count(@$data->image_cmnd) > 0)
                                                        @php
                                                            $image_cmnd= $data->image_cmnd;
                                                        @endphp
                                                        @if($image_cmnd)
                                                            @foreach($image_cmnd as $_image_cmnd)
                                                                    <span class="comment-content-file-item">
                                                                        <a target="_blank" href="{{ $_image_cmnd }}" style="height:170px;display: inline-flex;"><img src="{{ $_image_cmnd }}" class="set-custom-img" ></a>
                                                                    </span>
                                                            @endforeach
                                                        @endif
                                                    @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div>Trạng thái: </div>
                                            </td>
                                            <td>
                                                @if (@$user_request->status === 0 || @$user_request->status === 5)
                                                       <label class="label label-sm label-warning">Chờ BQL xử lý</label>
                                                @elseif(@$user_request->status === 1)
                                                        <label class="label label-sm label-success">BQL đang xử lý</label>
                                                @elseif(@$user_request->status === 2)
                                                        <label class="label label-sm label-info">Chờ cư dân phản hồi</label>
                                                @elseif(@$user_request->status === 3)
                                                        <label class="label label-sm label-primary">Thành công</label>
                                                @else
                                                       <label class="label label-sm label-danger">Hủy</label>   
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            @endif
                            @if ($user_request->type == 2)
                                <table class="table table-striped table-bordered table-hover">
                                    <thead class="bg-primary">
                                        <tr>
                                            <th colspan="2" class="text-center">Thông tin hủy phương tiện</th>
                                        </tr>
                                    </thead>
                                    @php
                                        if($user_request->data){
                                            $data = json_decode($user_request->data);
                                            $vehicle  =  App\Models\Vehicles\Vehicles::get_detail_vehicle_by_id($data->id_vehicles);
                                            $category = $vehicle ? App\Models\VehicleCategory\VehicleCategory::get_detail_vehicles_category_by_id(@$vehicle->vehicle_category_id): null;
                                        }
                                    @endphp
                                    <tbody>
                                        <tr>
                                            <td width="50%">
                                                <div>Biển số xe: </div>
                                                <div><strong>{{@$vehicle->number}}</strong></div>
                                            </td>
                                            <td>
                                                <div>Loại xe: </div>
                                                <div><strong>{{@$category->name}}</strong></div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div>Ngày kết thúc gửi: </div>
                                                <div><strong>{{@$data->date_end ? date('d/m/Y',strtotime(@$data->date_end)) : '--/--/----'}}</strong></div>
                                            </td>
                                            <td>
                                                <div>Lý do: </div>
                                                <div><strong>{{@$data->reason}}</strong></div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div>Trạng thái: </div>
                                            </td>
                                            <td>
                                                @if (@$user_request->status === 0 || @$user_request->status === 5)
                                                       <label class="label label-sm label-warning">Chờ BQL xử lý</label>
                                                @elseif(@$user_request->status === 1)
                                                        <label class="label label-sm label-success">BQL đang xử lý</label>
                                                @elseif(@$user_request->status === 2)
                                                        <label class="label label-sm label-info">Chờ cư dân phản hồi</label>
                                                @elseif(@$user_request->status === 3)
                                                        <label class="label label-sm label-primary">Thành công</label>
                                                @else
                                                       <label class="label label-sm label-danger">Hủy</label>   
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            @endif
                            @if ($user_request->type == 3)
                                <table class="table table-striped table-bordered table-hover">
                                    <thead class="bg-primary">
                                        <tr>
                                            <th colspan="2" class="text-center">Thông tin cấp lại thẻ xe</th>
                                        </tr>
                                    </thead>
                                    @php
                                        if($user_request->data){
                                            $data = json_decode($user_request->data);
                                            $vehicle  =  App\Models\Vehicles\Vehicles::get_detail_vehicle_by_id($data->id_vehicles);
                                            $category = $vehicle ? App\Models\VehicleCategory\VehicleCategory::get_detail_vehicles_category_by_id(@$vehicle->vehicle_category_id): null;
                                            $apartment = App\Models\Apartments\Apartments::get_detail_apartment_by_apartment_id($user_request->apartment_id);
                                            $buildingPlace =$apartment ? App\Models\Building\BuildingPlace::get_detail_bulding_place_by_bulding_place_id($apartment->building_place_id) : null;
                                        }
                                    @endphp
                                    <tbody>
                                        <tr>
                                            <td width="50%">
                                                <div>Biển số xe: </div>
                                                <div><strong>{{@$vehicle->number}}</strong></div>
                                            </td>
                                            <td>
                                                <div>Loại xe: </div>
                                                <div><strong>{{@$category->name}}</strong></div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">
                                                <div>Lý do: </div>
                                                <div><strong>{{@$data->reason}}</strong></div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div>Trạng thái: </div>
                                            </td>
                                            <td>
                                                @if (@$user_request->status === 0 || @$user_request->status === 5)
                                                       <label class="label label-sm label-warning">Chờ BQL xử lý</label>
                                                @elseif(@$user_request->status === 1)
                                                        <label class="label label-sm label-success">BQL đang xử lý</label>
                                                @elseif(@$user_request->status === 2)
                                                        <label class="label label-sm label-info">Chờ cư dân phản hồi</label>
                                                @elseif(@$user_request->status === 3)
                                                        <label class="label label-sm label-primary">Thành công</label>
                                                @else
                                                       <label class="label label-sm label-danger">Hủy</label>   
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            @endif
                            @if (@$user_request->type == 4)
                            <table class="table table-striped">
                                @php
                                    if($user_request->data){
                                        $data = @$user_request->data ? json_decode(@$user_request->data) : null;
                                        $products = $data->products;
                                        $times =@$data->times;
                                        $detail_times = '';
                                        if(@$times){
                                            foreach ($times as $key => $value) {
                                                if($key != 0){
                                                    $detail_times .= $value ? ' | '.str_replace(' ',' đến ',@$value) : '';
                                                }else{
                                                    $detail_times .= $value ? str_replace(' ',' đến ',@$value) : '';
                                                }
                                            }
                                        }
                                    }
                                    $comment_images =@$data->files ? @$data->files : null;
                                    $comment_files =@$data->files ? @$data->files : null;
                                @endphp
                                <thead class="bg-primary">
                                    <tr>
                                        <th colspan="2" class="text-center">Đăng ký {{@$data->pass == 1 ? 'chuyển đồ ra' : 'chuyển đồ vào'}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td width="50%">
                                            <div>Người đăng ký: </div>
                                            <div><strong>{{ @$item->user_created_by->full_name ?? 'không rõ' }}</strong></div>
                                        </td>
                                        <td>
                                            <div>Số điện thoại: </div>
                                            <div><strong>{{@$data->phone}}</strong></div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td >
                                            <div>Khung giờ đăng ký: </div>
                                            <div><strong>{{@$detail_times}}</strong></div>
                                        </td>
                                        <td>
                                            <div>Ngày đăng ký: </div>
                                            <div><strong>{{@$data->date ? date('d/m/Y',strtotime(@$data->date)) : '--/--/----'}}</strong></div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <div>Đồ vận chuyển: </div>
                                            @if (@$products)
                                                @foreach ($products as $key => $item)
                                                    <div>
                                                        <span><strong>{{($key + 1)}}</strong> {{$item->title}}</span>
                                                        <span><small><strong>Mô tả:</strong> {{$item->desc}}</small></span>
                                                        <span><small><strong>Số lượng:</strong> {{number_format( $item->amount)}}</small></span>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </td>
                                    </tr>
                                    <td colspan="2">
                                        <div style="padding: 0;">Tài liệu đính kèm: </div>
                                        @if($comment_images && count($comment_images->images) > 0)
                                                @php
                                                    $comment_images= $comment_images->images;
                                                @endphp
                                            @if($comment_images)
                                                @foreach($comment_images as $_comment_images)
                                                         <span class="comment-content-file-item">
                                                            <a target="_blank" href="{{ $_comment_images }}" style="height:170px;display: inline-flex;"><img src="{{ $_comment_images }}" class="set-custom-img" ></a>
                                                        </span>
                                                @endforeach
                                            @endif
                                        @endif
                                        @if($comment_files && count($comment_files->files) > 0)
                                            @php
                                                $comment_files = $comment_files->files;
                                            @endphp
                                            @if($comment_files)
                                                @foreach($comment_files as $_comment_files)
                                                        <div class="comment-content-file-item">
                                                            @if (\app\Commons\Helper::check_file_type_is_image($_comment_files))
                                                                <a target="_blank" href="{{ $_comment_files }}" style="height:15px;display: inline-flex;"><img src="{{ $_comment_files }}" class="set-custom-img" ></a>
                                                            @else
                                                                <a target="_blank" href="{{ $_comment_files }}" style="height:15px"> </a>
                                                            @endif
                                                        </div>
                                                @endforeach
                                            @endif
                                        @endif
                                    </td>
                                    <tr>
                                        <td>
                                            <div>Trạng thái: </div>
                                        </td>
                                        <td>
                                            @if (@$user_request->status === 0 || @$user_request->status === 5)
                                                   <label class="label label-sm label-warning">Chờ BQL xử lý</label>
                                            @elseif(@$user_request->status === 1)
                                                    <label class="label label-sm label-success">BQL đang xử lý</label>
                                            @elseif(@$user_request->status === 2)
                                                    <label class="label label-sm label-info">Chờ cư dân phản hồi</label>
                                            @elseif(@$user_request->status === 3)
                                                    <label class="label label-sm label-primary">Thành công</label>
                                            @else
                                                   <label class="label label-sm label-danger">Hủy</label>   
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        @endif
                        @if (@$user_request->type == 5)
                                <table class="table table-striped">
                                    <thead class="bg-primary">
                                        <tr>
                                            <th colspan="2" class="text-center">Đăng ký sửa chữa</th>
                                        </tr>
                                    </thead>
                                    @php
                                        if($user_request->data){
                                            $data = json_decode($user_request->data);
                                        }
                                        $comment_images =$data->files ? $data->files : null;
                                        $comment_files =$data->files ? $data->files : null;
                                    @endphp
                                    <tbody>
                                        <tr>
                                            <td width="50%">
                                                <div>Người đăng ký: </div>
                                                <div><strong>{{@$data->full_name}}</strong></div>
                                            </td>
                                            <td>
                                                <div>Điện thoại: </div>
                                                <div><strong>{{@$data->phone}}</strong></div>
                                                <div><strong>{{@$data->email}}</strong></div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">
                                                <div>Ngày vận chuyển: </div>
                                                <div><strong>{{@$data->from ? date('d/m/Y',strtotime(@$data->from)) : '--/--/----'}} đến {{@$data->to ? date('d/m/Y',strtotime(@$data->to)) : '--/--/----'}}</strong></div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div>Đơn vị thi công: </div>
                                                <div><strong>{{@$data->construction}}</strong></div>
                                            </td>
                                            <td>
                                                <div>Ghi chú: </div>
                                                <div><strong>{{@$data->content}}</strong></div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                @if($data->summary != 0)
                                                    <div>Giá : </div>
                                                    <div><strong>{{number_format(@$data->summary)}}</strong></div>
                                                @endif
                                            </td>
                                            <td>
                                                @if($data->paid != 0)
                                                    <div>Tiền cọc: </div>
                                                    <div><strong>{{number_format(@$data->paid)}}</strong></div>
                                                @endif
                                            </td>
                                        </tr>
                                        <td colspan="2">
                                            <div style="padding: 0;">Tài liệu đính kèm: </div>
                                            @if($comment_images && count($comment_images->images) > 0)
                                                    @php
                                                        $comment_images= $comment_images->images;
                                                    @endphp
                                                @if($comment_images)
                                                    @foreach($comment_images as $_comment_images)
                                                             <span class="comment-content-file-item">
                                                                <a target="_blank" href="{{ $_comment_images }}" style="height:170px;display: inline-flex;"><img src="{{ $_comment_images }}" class="set-custom-img" ></a>
                                                            </span>
                                                    @endforeach
                                                @endif
                                            @endif
                                            @if($comment_files && count($comment_files->files) > 0)
                                                @php
                                                    $comment_files = $comment_files->files;
                                                @endphp
                                                @if($comment_files)
                                                    @foreach($comment_files as $_comment_files)
                                                            <div class="comment-content-file-item">
                                                                @if (\app\Commons\Helper::check_file_type_is_image($_comment_files))
                                                                    <a target="_blank" href="{{ $_comment_files }}" style="height:15px;display: inline-flex;"><img src="{{ $_comment_files }}" class="set-custom-img" ></a>
                                                                @else
                                                                    <a target="_blank" href="{{ $_comment_files }}" style="height:15px"> </a>
                                                                @endif
                                                            </div>
                                                    @endforeach
                                                @endif
                                            @endif
                                        </td>
                                        <tr>
                                            <td>
                                                <div>Trạng thái: </div>
                                            </td>
                                            <td>
                                                @if (@$user_request->status === 0 || @$user_request->status === 5)
                                                       <label class="label label-sm label-warning">Chờ BQL xử lý</label>
                                                @elseif(@$user_request->status === 1)
                                                        <label class="label label-sm label-success">BQL đang xử lý</label>
                                                @elseif(@$user_request->status === 2)
                                                        <label class="label label-sm label-info">Chờ cư dân phản hồi</label>
                                                @elseif(@$user_request->status === 3)
                                                        <label class="label label-sm label-primary">Thành công</label>
                                                @else
                                                       <label class="label label-sm label-danger">Hủy</label>   
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            @endif
                            @if (@$user_request->type == 6)
                                <table class="table table-striped">
                                    <thead class="bg-primary">
                                        <tr>
                                            <th colspan="2" class="text-center">Đăng ký tiện ích</th>
                                        </tr>
                                    </thead>
                                    @php
                                        if($user_request->data){
                                            $data = json_decode($user_request->data);
                                            $times =@$data->time;
                                            $detail_times = '';
                                            if(@$times){
                                                foreach ($times as $key => $value) {
                                                    if($key != 0){
                                                        $detail_times .= $value ? ' | '.str_replace(' ',' đến ',@$value) : '';
                                                    }else{
                                                        $detail_times .= $value ? str_replace(' ',' đến ',@$value) : '';
                                                    }
                                                }
                                            }
                                        }
                                    @endphp
                                    <tbody>
                                        <tr>
                                            <td width="50%">
                                                <div>Người đăng ký: </div>
                                                <div><strong> {{ @$user_request->user_created_by->full_name ?? 'không rõ' }}</strong></div>
                                            </td>
                                            <td>
                                                <div>Điện thoại: </div>
                                                <div><strong>{{@$data->phone}}</strong></div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div>Ngày đăng ký: </div>
                                                <div><strong>{{@$data->date ? date('d/m/Y',strtotime(@$data->date)) : '--/--/----'}}</strong></div>
                                            </td>
                                            <td>
                                                <div>Tiện ích: </div>
                                                <div><strong>{!!App\Commons\Helper::type_utilities[$data->service_type]!!}</strong></div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">
                                                <div>Khung giờ đăng ký: </div>
                                                <div><strong>{{@$detail_times}}</strong></div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div>Trạng thái: </div>
                                            </td>
                                            <td>
                                                @if (@$user_request->status === 0 || @$user_request->status === 5)
                                                       <label class="label label-sm label-warning">Chờ BQL xử lý</label>
                                                @elseif(@$user_request->status === 1)
                                                        <label class="label label-sm label-success">BQL đang xử lý</label>
                                                @elseif(@$user_request->status === 2)
                                                        <label class="label label-sm label-info">Chờ cư dân phản hồi</label>
                                                @elseif(@$user_request->status === 3)
                                                        <label class="label label-sm label-primary">Thành công</label>
                                                @else
                                                       <label class="label label-sm label-danger">Hủy</label>   
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                    @if($user_request->status != 3)
                        <div class="col-sm-6">
                        </div>
                        <div class="col-sm-4">
                        <select name="status" id="user_request_status" class="form-control" style="width: 100%">
                            <option value="1" {{ $user_request->status === 1 ? 'selected' : '' }}>BQL đang xử lý</option>
                            <option value="2" {{ $user_request->status === 2 ? 'selected' : '' }}>Chờ cư dân phản hồi</option>
                            <option value="3" {{ $user_request->status === 3 ? 'selected' : '' }}>Thành công</option>
                            <option value="4" {{ $user_request->status === 4 ? 'selected' : '' }}>Hủy</option>
                        </select>
                        </div>
                        <div class="col-sm-2">
                            <a href="javascript:;" id="btn-set-confirm" data-type="{{$user_request->type}}" class="btn btn-success">Xác nhận</a>
                        </div>
                    @endif
                    @if($user_request->status == 3 && (@$user_request->type == 4 || @$user_request->type == 5 || @$user_request->type == 6))
                            <div class="col-sm-2">
                                <a href="{{ route('admin.configs.view',['user_request_id'=>$user_request->id]) }}" target="_blank" class="btn btn-default">In form đăng ký</a>
                            </div>
                    @endif
                </div>
                <div class="box-footer box-comments">
                @foreach ($user_request_revert as $keyitem => $item)
                    <div class="box-comment" id="comment-{{ $item->id }}">
                        <!-- User image -->
                        <div class="img-user img-circle img-sm" style="background: {{ $colors[$item->user_id % 7] }}">
                            @php
                                @$user_comment = $item->user;
                                $created_at_comment = \Carbon\Carbon::parse($item->created_at);
                            @endphp
                            @if($user_comment)
                                @if($user_comment->avatar)
                                    <img src="{{$user_comment->avatar}}" alt="{{ @$user_comment->full_name ?? 'không rõ'}}" style="border-radius: 50%;">
                                @else
                                    @php
                                        $words = explode(' ',@$user_comment->full_name?? 'không rõ');
                                        $name = end($words);
                                        $char = substr($name, 0, 1);
                                    @endphp
                                    <strong>{{ strtoupper($char) }}</strong>
                                @endif

                            @endif
                        </div>
                        <div class="comment-text">
                            <div class="comment-body">
                                <span class="username">{{ @$user_comment->full_name?? 'không rõ' }}</span>
                                @if(@$item->files->images && count(@$item->files->images) > 0)
                                     @php
                                         $comment_images= $item->files->images;
                                     @endphp
                                    @if($comment_images)
                                        @foreach($comment_images as $_comment_images)
                                                @php 
                                                    $arrUrl = explode("/", $_comment_images);
                                                    $urlName = end($arrUrl);
                                                @endphp
                                                <span class="comment-content-file-item">
                                                    @if (\app\Commons\Helper::check_file_type_is_image($_comment_images))
                                                        <a target="_blank" href="{{ $_comment_images }}" style="height:15px;display: inline-flex;"><img src="{{ $_comment_images }}" class="set-custom-img" ></a>
                                                    @else
                                                        <a class="download" href="{{ $_comment_images }}" style="height:15px">
                                                            {{ $urlName }}
                                                        </a>
                                                    @endif
                                                </span>
                                        @endforeach
                                     @endif
                                @endif
                                @if(@$item->files->files && count(@$item->files->files) > 0)
                                    @php
                                        $comment_files = $item->files->files;
                                    @endphp
                                    @if($comment_files)
                                        @foreach($comment_files as $_comment_files)
                                                @php 
                                                    $arrUrl = explode("/", $_comment_files);
                                                    $urlName = end($arrUrl);
                                                @endphp
                                                <span class="comment-content-file-item">
                                                    @if (\app\Commons\Helper::check_file_type_is_image($_comment_files))
                                                        <a target="_blank" href="{{ $_comment_files }}" style="height:15px;display: inline-flex;"><img src="{{ $_comment_files }}" class="set-custom-img" ></a>
                                                    @else
                                                        <a target="_blank" href="{{ $_comment_files }}" style="height:15px">
                                                            {{ $urlName }}
                                                        </a>
                                                    @endif
                                                </span>
                                        @endforeach
                                    @endif
                                @endif
                                <div class="comment-content">{!! $item->content !!}</div>
                            </div><!-- /.comment-body -->

                            <div class="comment-info">
                                @if ($user_request->status != 3)
                                    <a class="text-muted btn-comment-delete" href="javascript:;" data-id="{{ $item->id }}" data-id_request="{{ $user_request->id }}">Xóa</a>
                                    &middot; 
                                @endif
                                <span class="text-muted">{{ $created_at_comment->diffForHumans($now) }}</span>
                            </div><!-- /.comment-info -->
                            <div class="comment-reply">
                                @if ($item->commentChild && count($item->commentChild) > 0)
                                    @foreach ($item->commentChild as $key => $reply)
                                      @php
                                           $created_at_reply = \Carbon\Carbon::parse($reply->created_at);
                                      @endphp
                                        <div class="box-comment" id="comment-{{ $reply->id }}">
                                            <div class="img-user img-circle img-sm" style="background: {{ $colors[$reply->user_id % 7] }}">
                                                @php
                                                    @$user_reply = $reply->user;
                                                @endphp
                                                @if(@$user_reply)
                                                    @if(@$user_reply->avatar)
                                                        <img src="{{$user_reply->avatar}}" alt="{{ @$user_reply->full_name?? 'không rõ'}}" style="border-radius: 50%;">
                                                    @else
                                                        @php
                                                            $words = explode(' ', @$user_reply->full_name?? 'không rõ');
                                                            $name = end($words);
                                                            $char = substr($name, 0, 1);
                                                        @endphp
                                                        <strong>{{ strtoupper($char) }}</strong>
                                                    @endif
                                                @endif
                                            </div>
                                            <div class="comment-text">
                                                <div class="comment-body">
                                                    <span class="username">{{ @$user_reply->full_name?? 'không rõ' }}</span>
                                                    @if(@$reply->files->images && count(@$reply->files->images) > 0)
                                                        @php
                                                            $comment_1_images= $reply->files->images;
                                                        @endphp
                                                        @if($comment_1_images)
                                                            @foreach($comment_1_images as $_comment_1_images)
                                                                    @php 
                                                                        $arrUrl = explode("/", $_comment_1_images);
                                                                        $urlName = end($arrUrl);
                                                                    @endphp
                                                                    <span class="comment-content-file-item">
                                                                        @if (\app\Commons\Helper::check_file_type_is_image($_comment_1_images))
                                                                            <a target="_blank" href="{{ $_comment_1_images }}" style="height:15px;display: inline-flex;"><img src="{{ $_comment_1_images }}" class="set-custom-img" ></a>
                                                                        @else
                                                                            <a class="download" href="{{ $_comment_1_images }}" style="height:15px">
                                                                                {{ $urlName }}
                                                                            </a>
                                                                        @endif
                                                                    </span>
                                                            @endforeach
                                                        @endif
                                                    @endif
                                                    @if(@$reply->files->files && count(@$reply->files->files) > 0)
                                                        @php
                                                            $comment_1_files = $reply->files->files;
                                                        @endphp
                                                        @if($comment_1_files)
                                                            @foreach($comment_1_files as $_comment_1_files)
                                                                    @php 
                                                                        $arrUrl = explode("/", $_comment_1_files);
                                                                        $urlName = end($arrUrl);
                                                                    @endphp
                                                                    <span class="comment-content-file-item">
                                                                        @if (\app\Commons\Helper::check_file_type_is_image($_comment_1_files))
                                                                            <a target="_blank" href="{{ $_comment_1_files }}" style="height:15px;display: inline-flex;"><img src="{{ $_comment_1_files }}" class="set-custom-img" ></a>
                                                                        @else
                                                                            <a target="_blank" href="{{ $_comment_1_files }}" style="height:15px">
                                                                                {{ $urlName }}
                                                                            </a>
                                                                        @endif
                                                                    </span>
                                                            @endforeach
                                                        @endif
                                                    @endif
                                                    <div class="comment-content">{!! nl2br($reply->content) !!}</div>
                                                </div>
                                                <div class="comment-info">
                                                    <a class="text-muted btn-comment-delete" href="javascript:;" data-id="{{ $reply->id }}" data-id_request="{{ $user_request->id }}">Xóa</a>
                                                    &middot;
                                                    <span class="text-muted">{{ $created_at_reply->diffForHumans($now) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                               
                            </div><!-- /.comment-reply -->
                            @if( in_array('admin.comments.save',@$user_access_router))
                                @if ($user_request->status != 3)
                                    <div class="comment-form" id="reply-{{ $item->id }}">
                                        <div class="attach-file-{{ @$keyitem }} form-group" style="display: flex;padding-left: 20px;"></div>
                                        <img class="img-responsive img-circle img-sm" src="/adminLTE/img/user-default.png" alt="Alt Text">
                                        <div class="img-push" style="position: relative;">
                                            <textarea data-textarea_id="{{@$keyitem}}" data-type="feedback" data-id_request="{{ $user_request->id }}" data-parent_id="{{ $item->id }}" data-action="reply" class="form-control input-comment input-auto-height" rows="1" placeholder="Viết bình luận ... Sau đó ENTER"></textarea>
                                                <label style="background-color: #3c8dbc; right: 10px; top: 2px; position: absolute; display: flex; align-items: center;justify-content: center;"class="img-responsive img-circle img-sm"  >
                                                    <i class="fa fa-files-o" style="font-size: large;"></i>
                                                    <input id='inputFile-{{ @$keyitem }}' multiple="multiple" type="file" style="display: none;" data-input='{{@$keyitem}}' />
                                                </label>
                                        </div>
                                    </div><!-- /.comment-form -->
                                @endif
                            @endif
                        </div><!-- /.comment-text -->
                    </div><!-- /.box-comment -->
                    @endforeach
                </div>
                @if( in_array('admin.comments.save',@$user_access_router))
                    @if ($user_request->status != 3)
                        <div class="box-footer">
                            <div class="attach-file form-group" style="display: flex;"></div>
                            <form id="reply" action="#" method="post">
                                
                                <img class="img-responsive img-circle img-sm" src="/adminLTE/img/user-default.png" alt="Alt Text">
                                <!-- .img-push is used to add margin to elements next to floating images -->
                                <div class="img-push" style="position: relative;">
                                    <textarea data-id_request="{{ $user_request->id }}" data-type="feedback" data-parent_id="0" data-action="comment" class="form-control input-comment input-auto-height" rows="1" placeholder="Viết bình luận ... Sau đó ENTER"></textarea >
                                    <label  style="background-color: #3c8dbc; right: 10px; top: 2px; position: absolute; display: flex; align-items: center;justify-content: center;" class="img-responsive img-circle img-sm"  >
                                            <i class="fa fa-files-o" style="font-size: large;"></i>
                                            <input id='inputFile' type="file" multiple="multiple" style="display: none;"/>
                                    </label>
                                </div>
                            </form>
                        </div>
                    @endif
                @endif
            </div>
        </div>
        <div class="col-sm-4">
            <div class="box box-primary">
                <div class="box-body">
                    <h4>Comments mới nhất</h4>
                    <ul class="listcomments">
                        @foreach($comment_lists as $lc)
                            @php
                                 @$user_comment_list = $lc->user;
                            @endphp
                            <li>
                                <div class="img-user img-circle img-sm" style="background: {{ $colors[$lc->user_id % 7] }};line-height: 30px !important;">
                                    @if(@$user_comment_list)
                                        @if($user_comment_list->avatar)
                                            <img src="{{$user_comment_list->avatar}}" alt="{{@$user_comment_list->full_name?? 'không rõ' }}" style="border-radius: 50%;width: 30px;height: 30px;">
                                        @else
                                            @php
                                                $words = explode(' ', @$user_comment_list->full_name?? 'không rõ');
                                                $name = end($words);
                                                $char = substr($name, 0, 1);
                                            @endphp
                                            <strong>{{ strtoupper($char) }}</strong>
                                        @endif
                                    @endif
                                </div>
                                <div class="info_comment" style="width: calc(100% - 30px);float: left;padding-left: 5px;">
                                    <p>{{ @$user_comment_list->full_name?? 'không rõ' }}</p>
                                    <p>{{$lc->content}}</p>
                                </div>
                            </li>
                            {{-- @if ($lc->commentChild && count($lc->commentChild) > 0)
                                @foreach ($lc->commentChild as $key_1 => $lc_reply)
                                    @php
                                        @$user_lc_reply = $lc_reply->user;
                                    @endphp
                                    <li>
                                        <div class="img-user img-circle img-sm" style="background: {{ $colors[$lc_reply->user_id % 7] }};line-height: 30px !important;">
                                            @if(@$user_lc_reply)
                                                @if(@$user_lc_reply->avatar)
                                                    <img src="/{{$user_lc_reply->avatar}}" alt="{{@$user_lc_reply->full_name?? 'không rõ' }}" style="border-radius: 50%;width: 30px;height: 30px;">
                                                @else
                                                    @php
                                                        $words = explode(' ', @$user_lc_reply->full_name?? 'không rõ');
                                                        $name = end($words);
                                                        $char = substr($name, 0, 1);
                                                    @endphp
                                                    <strong>{{ strtoupper($char) }}</strong>
                                                @endif
                                            @endif
                                        </div>
                                        <div class="info_comment" style="width: calc(100% - 30px);float: left;padding-left: 5px;">
                                            <p>{{ @$user_lc_reply->full_name?? 'không rõ' }}</p>
                                            <p>{{$lc_reply->content}}</p>
                                        </div>
                                    </li>
                                @endforeach
                            @endif --}}
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div id="add-vehiclecard" class="modal fade" role="dialog">
        <div class="modal-dialog  modal-lg">
            <!-- Modal content-->
            @if( in_array('admin.vehiclecards.insert',@$user_access_router))
                <form action="{{ route('admin.vehiclecards.insert') }}" method="post" id="form-add-vehiclecard" class="form-validate form-horizontal">
                    {{ csrf_field() }}
                    <input type="hidden" name="hashtag">
                    <input type="hidden" name="user_request_push_card_vehicle">
                    <div class="modal-content">
                        <div class="modal-header bg-primary">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Thêm mới thẻ</h4>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-danger alert_pop_add_vehiclecard" style="display: none;">
                                <ul></ul>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <div class="col-sm-2">
                                            <label for="in-re_name">Mã thẻ</label>
                                        </div>
                                        <div class="col-sm-8">
                                            <input type="text" name="code" id="in-code" class="form-control" placeholder="Mã thẻ">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-2">
                                             <label>Tòa nhà</label>
                                        </div>
                                        <div class="col-sm-8">
                                              <select name="building_place_id" id="place_id" class="form-control" style="width: 100%;">
                                                    <option value="">Chọn tòa nhà</option>
                                              </select>
                                        </div>
                                     </div>
                                    <div class="form-group">
                                        <div class="col-sm-2">
                                            <label>Căn hộ</label>
                                        </div>
                                        <div class="col-sm-8">
                                            <select name="bdc_apartment_id" id="ap_id" class="form-control" style="width: 100%;">
                                                <option value="">Chọn căn hộ</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-2">
                                            <label>Loại phương tiện</label>
                                        </div>
                                        <div class="col-sm-8">
                                            <select name="vehicle_category_id" id="select-vc_type" class="form-control select-vc_type_card" style="width: 100%;">
                                                <option value="">Chọn phương tiện</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-2">
                                            <label>Biển số</label>
                                        </div>
                                        <div class="col-sm-8">
                                            <select name="number" id="in-vc_vehicle_number" class="form-control in-vc_vehicle_number_card" style="width: 100%;">
                                                <option value="">Chọn biển số</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-2">
                                            <label>Ghi chú</label>
                                        </div>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" placeholder="ghi chú" id="context" name="description"
                                            rows="3"></textarea>
                                        </div>
                                    </div>
                                   
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
                            <button type="button" class="btn btn-primary btn-js-action-vehiclecard" style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận</button>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>
</section>
@include('vehicles.v2.modals.add-vehicle')
@endsection

@section('stylesheet')

<link rel="stylesheet" href="/adminLTE/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" href="/adminLTE/plugins/lightbox/ekko-lightbox.css" />

@endsection

@section('javascript')

@include('user-request.js-comment')
<style>
.box-comments .box-comment a img.set-custom-img{
    width: 150px!important;
    max-height: 70px!important;
    object-fit: contain;
    min-height: 70px!important;
}
</style>
<script src="/adminLTE/plugins/lightbox/ekko-lightbox.min.js"></script>
<script>
    $(document).delegate('*[data-toggle="lightbox"]', 'click', function(event) {
        event.preventDefault();
        $(this).ekkoLightbox();
    });
    $(document).ready(function(){
        $('#inputFile').change(function(e){
            let files = e.target.files;
            $('.attach-file').html('');
            for (let index = 0; index < files.length; index++) {
                const element = files[index];
                let html_image = '<a href="#" style="margin-left: 10px;display: inline-flex;">'+element.name+'</a>';
                $('.attach-file').append(html_image);
            }
        })
        $('.comment-form input').change(function(e){
             var key_input = $(this).attr('data-input');
             let files = e.target.files;
            $('.attach-file-'+key_input).html('');
            for (let index = 0; index < files.length; index++) {
                const element = files[index];
                let html_files = '<a href="#" style="margin-left: 10px;display: inline-flex;">'+element.name+'</a>';
                $('.attach-file-'+key_input).append(html_files);
            }
        })
    });
    async function post_comment(param){
        console.log(param);
        let method='post';
        let param_query_old = "{{ $array_search }}";
        let param_query = param_query_old.replaceAll("&amp;", "&")
        let _result = await call_api_form_data(method, 'admin/addUserReqComment'+param_query,param);
        console.log(_result);
        toastr.success(_result.mess);
     
    }
    $('#btn-set-confirm').click(function (e) { 
        e.preventDefault();
        if($('#user_request_status').val() == 3 && $(this).data('type') == 1){
            $('#ip-ap_id').append($('<option>', { 
                value: '{{@$apartment->id}}',
                text : '{{@$apartment->name}}',
            }).prop("selected", true));
            $('#ip-place_id').append($('<option>', { 
                value: '{{@$buildingPlace->id}}',
                text : '{{@$buildingPlace->name}}',
            }).prop("selected", true));
            $('.select_vc_type_vehicle').val('{{@$data->type_vehicles}}').change();
            $('#first_time_active').val('{{@$data->date_begin}}');
            $('input[name=user_request_push]').val('{{@$user_request->id}}');
            $('.in_vc_vehicle_number').val('{{@$data->number_vehicles}}');
            $.ajax({
                    url: '/admin/v2/vehicles/getPriceVehicle',
                    type: 'POST',
                    data: {
                        apartment_id: '{{@$apartment->id}}',
                        vehicle_category_id: $('.select_vc_type_vehicle').val()
                    },
                    success: function (res) {
                        let {progressive_prices, progressivePrice: progressive_price} = res.data;

                        let select_progressive_prices = "";
                        progressive_prices.forEach((item)=>{
                            const {price, id, name, priority_level} = item;
                            const {priority_level: priority_level1} = progressive_price;
                            if (priority_level === priority_level1) {
                                select_progressive_prices += `<option value=${id} selected>${name} - ${price} VNĐ - Mức ${priority_level}</option>`;
                            }
                            else {
                                if (priority_level < priority_level1) {
                                    select_progressive_prices += `<option value=${id} >${name} - ${price} VNĐ - Mức ${priority_level}</option>`;
                                }
                            }
                        })
                        let list_progressives = $('#progressive_price_id');
                        list_progressives.empty();
                        list_progressives.prepend(select_progressive_prices);
                    },
                    error: function (e) {
                        console.log(e);
                    }
            });
            $('#add-vehicle').modal('show');
        }
        if($('#user_request_status').val() == 3 && $(this).data('type') == 3){
            $('input[name=user_request_push_card_vehicle]').val('{{@$user_request->id}}');
            $('#ap_id').append($('<option>', { 
                value: '{{@$apartment->id}}',
                text : '{{@$apartment->name}}',
            }).prop("selected", true));
            $('.select-vc_type_card').append($('<option>', { 
                value: '{{@$category->id}}',
                text : '{{@$category->name}}',
            }).prop("selected", true));
            $('.in-vc_vehicle_number_card').append($('<option>', { 
                value: '{{@$vehicle->number}}',
                text : '{{@$vehicle->number}}',
            }).prop("selected", true));
            $('#place_id').append($('<option>', { 
                value: '{{@$buildingPlace->id}}',
                text : '{{@$buildingPlace->name}}',
            }).prop("selected", true));
            $('#add-vehiclecard').modal('show');
        }
        if($('#user_request_status').val() == 3 && $(this).data('type') == 2){
          
            window.location.href = "{{route('admin.v2.vehicles.edit',['id'=> @$data->id_vehicles])}}"+'?finish={{@$data->date_end}}'+'&id_request={{@$user_request->id}}';

        }
        if(($('#user_request_status').val() == 1 || $('#user_request_status').val() == 4) && $('#user_request_status').val() != 2){
            var _token = $('meta[name="csrf-token"]').attr('content');
            var id = '{{@$user_request->id}}';
            var status = $('#user_request_status').val();
            $.ajax({
                type: 'POST',
                url: '{{ route('admin.v2.user_request.change_status') }}',
                data: {
                    _token: _token,
                    status: status,
                    ids: id
                },
                success: function(data){
                    toastr.success(data.msg);
                },
                dataType: 'json'
            });
        }
        if($('#user_request_status').val() == 3 && ($(this).data('type') == 4 || $(this).data('type') == 5 || $(this).data('type') == 6)){
            var _token = $('meta[name="csrf-token"]').attr('content');
            var id = '{{@$user_request->id}}';
            var status = $('#user_request_status').val();
            $.ajax({
                type: 'POST',
                url: '{{ route('admin.v2.user_request.change_status') }}',
                data: {
                    _token: _token,
                    status: status,
                    ids: [id]
                },
                success: function(data){
                    toastr.success(data.msg);
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                },
                dataType: 'json'
            });
        }
    });
    function isImage(filename) {
            var ext = getExtension(filename);
            switch (ext.toLowerCase()) {
                case 'jpg':
                case 'gif':
                case 'bmp':
                case 'png':
                    //etc
                    return true;
            }
            return false;
        }
    // check file or image
    function getExtension(filename) {
        var parts = filename.split('.');
        return parts[parts.length - 1];
    }
     $("#btn-set-change-status").on('click', function () {
        var _this = $(this);
        var id = _this.data('id');
        var url = _this.data('url');
        var _token = $('meta[name="csrf-token"]').attr('content');
        var data = {
            _token: _token,
            method: 'status',
            status: 1,
            ids: [id]
        };
        showLoading();
        $.post(url, data, function (json) {
            hideLoading(); 
            $(".tag_status").html("<span>Đã hoàn thành</span>");
            _this.remove();
        });
    });
    // $("#ip-ap_id,#select-vc_type").on('change',function () {
    //     var type = $("#select-vc_type").val();
    //     var apartment = $("#ip-ap_id").val();
    //     $('#in-vc_vehicle_number').html('<option value="">Chọn biển số</option>');
    //     if(type || apartment){
    //         getNumberVehicle(apartment,type);
    //     }
    // });
    // function getNumberVehicle(apartment,cate){
    //     get_data_select2({
    //         object: '#in-vc_vehicle_number',
    //         url: '{{ url('admin/vehiclecards/ajax_get_vehiclecard') }}',
    //         data_id: 'number',
    //         data_text: 'number',
    //         title_default: 'Chọn biển số',
    //         apartment: apartment,
    //         cate: cate
    //     });
    // }
    // function get_data_select2(options) {
    //             $(options.object).select2({
    //                 ajax: {
    //                     url: options.url,
    //                     dataType: 'json',
    //                     data: function(params) {
    //                         var query = {
    //                             search: params.term,
    //                             apartment: options.apartment,
    //                             cate: options.cate,
    //                         }
    //                         return query;
    //                     },
    //                     processResults: function(json, params) {
    //                         var results = [{
    //                             id: '',
    //                             text: options.title_default
    //                         }];

    //                         for (i in json.data) {
    //                             var item = json.data[i];
    //                             results.push({
    //                                 id: item[options.data_id],
    //                                 text: item[options.data_text]
    //                             });
    //                         }
    //                         return {
    //                             results: results,
    //                         };
    //                     },
    //                     minimumInputLength: 3,
    //                 }
    //             });
    // }
    // get_data_select_card_vehicle({
    //     object: '#select-vc_type',
    //     url: '{{ url('admin/v2/vehiclecategory/ajax_get_vehicle_cate') }}',
    //     data_id: 'id',
    //     data_text: 'name',
    //     title_default: 'Chọn loại phương tiện'
    // });
    // function get_data_select_card_vehicle(options) {
    //     $(options.object).select2({
    //         ajax: {
    //             url: options.url,
    //             dataType: 'json',
    //             data: function(params) {
    //                 var query = {
    //                     search: params.term,
    //                     place_id: $("#ip-place_id").val()
    //                 }
    //                 return query;
    //             },
    //             processResults: function(json, params) {
    //                 var results = [{
    //                     id: '',
    //                     text: options.title_default
    //                 }];

    //                 for (i in json.data) {
    //                     var item = json.data[i];
    //                     results.push({
    //                         id: item[options.data_id],
    //                         text: item[options.data_text]
    //                     });
    //                 }
    //                 return {
    //                     results: results,
    //                 };
    //             },
    //             minimumInputLength: 3,
    //         }
    //     });
    //  }
     $(".btn-js-action-vehiclecard").on('click',function () {
                var _this = $(this);
                $(".alert_pop_add_vehiclecard").hide();
                var vehicle_number = $(".select-vc_type_card").val();
                var code = $("#in-code").val();
                var type = $(".in-vc_vehicle_number_card").val();
                var apartment = $("#ap_id").val();
                if(code.length <=0){
                    $(".alert_pop_add_vehiclecard").show();
                    $(".alert_pop_add_vehiclecard ul").html('<li>Mã code không được bỏ trống</li>')
                }else if(code.length <=5 || code.length >=45){
                    $(".alert_pop_add_vehiclecard").show();
                    $(".alert_pop_add_vehiclecard ul").html('<li>Mã code không được nhỏ hơn 5 hoặc lớn hơn 45 ký tự</li>')
                }else if(apartment == ''){
                    $(".alert_pop_add_vehiclecard").show();
                    $(".alert_pop_add_vehiclecard ul").html('<li>Phải chọn căn hộ</li>')
                }else if(type == ''){
                    $(".alert_pop_add_vehiclecard").show();
                    $(".alert_pop_add_vehiclecard ul").html('<li>Phải chọn loại phương tiện</li>')
                }else if(!vehicle_number){
                    $(".alert_pop_add_vehiclecard").show();
                    $(".alert_pop_add_vehiclecard ul").html('<li>Biển số phải được chọn</li>')
                }else{
                    $("#form-add-vehiclecard").submit();
                }
    });
    $(function () {
            get_data_select_apartment1({
                object: '#ip-place_id',
                url: '{{ url('admin/apartments/ajax_get_building_place') }}',
                data_id: 'id',
                data_text: 'name',
                data_code: 'code',
                title_default: 'Chọn tòa nhà'
            });
            function get_data_select_apartment1(options) {
                $(options.object).select2({
                    ajax: {
                        url: options.url,
                        dataType: 'json',
                        data: function(params) {
                            var query = {
                                search: params.term,
                            }
                            return query;
                        },
                        processResults: function(json, params) {
                            var results = [{
                                id: '',
                                text: options.title_default
                            }];

                            for (i in json.data) {
                                var item = json.data[i];
                                results.push({
                                    id: item[options.data_id],
                                    text: item[options.data_text]+' - '+item[options.data_code]
                                });
                            }
                            return {
                                results: results,
                            };
                        },
                        minimumInputLength: 3,
                    }
                });
            }
            get_data_select({
                object: '#ip-apartment',
                url: '{{ url('admin/apartments/ajax_get_apartment') }}',
                data_id: 'id',
                data_text: 'name',
                title_default: 'Chọn căn hộ'
            });

            $("#ip-place_id,#place_id").on('change', function(){
                if($("#ip-place_id").val()){
                    get_data_select({
                        object: '#ip-ap_id',
                        url: '{{ url('admin/apartments/ajax_get_apartment_with_place') }}',
                        data_id: 'id',
                        data_text: 'name',
                        title_default: 'Chọn căn hộ'
                    });
                }
            });
            $('#select-vc_type').on('change',function (){
                $.ajax({
                    url: '/admin/v2/vehicles/getPriceVehicle',
                    type: 'POST',
                    data: {
                        apartment_id: $('#ip-ap_id').val(),
                        vehicle_category_id: $('#select-vc_type').val()
                    },
                    success: function (res) {
                        let {progressive_prices, progressivePrice: progressive_price} = res.data;

                        let select_progressive_prices = "";
                        progressive_prices.forEach((item)=>{
                            const {price, id, name, priority_level} = item;
                            const {priority_level: priority_level1} = progressive_price;
                            if (priority_level === priority_level1) {
                                select_progressive_prices += `<option value=${id} selected>${name} - ${price} VNĐ - Mức ${priority_level}</option>`;
                            }
                            else {
                                if (priority_level < priority_level1) {
                                    select_progressive_prices += `<option value=${id} >${name} - ${price} VNĐ - Mức ${priority_level}</option>`;
                                }
                            }
                        })
                        let list_progressives = $('#progressive_price_id');
                        list_progressives.empty();
                        list_progressives.prepend(select_progressive_prices);
                    },
                    error: function (e) {
                        console.log(e);
                    }
                })
                // }
            });
            function get_data_select(options) {
                $(options.object).select2({
                    ajax: {
                        url: options.url,
                        dataType: 'json',
                        data: function(params) {
                            var query = {
                                search: params.term,
                                place_id: $("#ip-place_id").val() ?? $("#place_id").val(),
                            }
                            return query;
                        },
                        processResults: function(json, params) {
                            var results = [{
                                id: '',
                                text: options.title_default
                            }];

                            for (i in json.data) {
                                var item = json.data[i];
                                results.push({
                                    id: item[options.data_id],
                                    text: item[options.data_text]
                                });
                            }
                            return {
                                results: results,
                            };
                        },
                        minimumInputLength: 3,
                    }
                });
            }

            $(".btn-js-action-vehicle").on('click',function () {
                let _this = $(this);
                let alert_pop_add_vehicle = $('.alert_pop_add_vehicle');
                alert_pop_add_vehicle.hide();
                _this.attr('type','button');
                let vehicle_number = $(".in_vc_vehicle_number").val();
                let name = $("#in-vc_name").val();
                let type = $(".select_vc_type_vehicle").val();
                let apt = $("#ip-ap_id").val();
                let html = '';
                let first_time_active = $("#first_time_active").val();
                if(name.length <=2 || name.length >=50){
                    html+='<li>Tên phương tiện không được nhỏ hơn 3 hoặc lớn hơn 50 ký tự</li>';
                }if(vehicle_number == '' && (vehicle_number.length <=5 || vehicle_number.length >=13)){
                    html+='<li>Biển số không được nhỏ hơn 6 hoặc lớn hơn 12 ký tự</li>';
                }if(apt == ''){
                    html+='<li>Trường Căn hộ không được để trống</li>';
                }if(type == ''){
                    html+='<li>Trường loại phương tiện không được để trống</li>';
                }
                if (first_time_active == '') {
                    html+='<li>Ngày áp dụng tính phí không được để trống</li>';
                }

                if(html != ''){
                    $(".alert_pop_add_vehicle").show();
                    $(".alert_pop_add_vehicle ul").html(html);
                    hideLoading();
                    return;
                }

                showLoading();

                $.ajax({
                    url: '/admin/v2/vehicles/checkNumberVehicle',
                    type: 'POST',
                    data: {
                        'number':  vehicle_number,
                        'cate_vehicle':  $('#select-vc_type').val(),
                        'apartment_id':  $('#ip-ap_id').val()
                    },
                    success: function (res) {
                        if (res.data.count === 0) {
                            $('#form-add-verhicle').submit();
                        }
                        else {
                            html+='<li>Biển số xe đã tồn tại trên hệ thống vui lòng kiểm tra lại</li>';
                            $(".alert_pop_add_vehicle").show();
                            $(".alert_pop_add_vehicle ul").html(html);
                            hideLoading();
                        }
                    }
                })

            });

        });
</script>

<script>
    sidebar('feedback');
</script>

@endsection