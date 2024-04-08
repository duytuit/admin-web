@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        Danh sách đăng ký dịch vụ
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">{{ $heading }}</li>
    </ol>
</section>
    <?php
        $route = 'admin.v2.user_request.registerVehicle';
    ?>
<section class="content">
    <div class="box box-primary">
        <div class="box-body ">
            @if(in_array($route,@$user_access_router))
                    <form id="form-search-advance" action="{{ route($route) }}" method="get">
                        {{ csrf_field() }}
                        <div id="search-advance" class="search-advance">
                            <div class="row form-group space-5">

                                <div class="col-sm-2">
                                    <input type="text" name="keyword" value="{{ !empty(@$filter['keyword'])?@$filter['keyword']:'' }}" placeholder="Nhập từ khóa" class="form-control" />
                                </div>
                                <div class="col-sm-2">
                                    <select name="apartment_id" id="ip-apartment"  class="form-control" style="width: 100%;">
                                        <option value="">Chọn căn hộ</option>
                                        @if(@$get_apartment)
                                            <option value="{{$get_apartment->id}}" selected>{{$get_apartment->name}}</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    <select name="type" class="form-control" style="width: 100%;">
                                        <?php $type = !empty(@$filter['type'])?@$filter['type']:null;?>
                                        <option value="" selected>Loại yêu cầu</option>
                                        <option value="1" {{ $type == 1 ? 'selected' : '' }}>Thêm phương tiện</option>
                                        <option value="2" {{ $type == 2 ? 'selected' : '' }}>Hủy phương tiện</option>
                                        <option value="3" {{ $type == 3 ? 'selected' : '' }}>Cấp lại thẻ xe</option>
                                        <option value="4" {{ $type == 4 ? 'selected' : '' }}>Đăng ký chuyển đồ</option>
                                        <option value="5" {{ $type == 5 ? 'selected' : '' }}>Đăng ký sửa chữa</option>
                                        <option value="6" {{ $type == 6 ? 'selected' : '' }}>Đăng ký tiện ích</option>
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    <select name="status" class="form-control" style="width: 100%;">
                                        <option value="" selected>Trạng thái</option>
                                        <option value="0" {{ @$filter['status'] === 0 ? 'selected' : '' }}>Chờ BQL xử lý</option>
                                        <option value="1" {{ @$filter['status'] == 1 ? 'selected' : '' }}>BQL đang xử lý</option>
                                        <option value="2" {{ @$filter['status'] == 2 ? 'selected' : '' }}>Chờ cư dân phản hồi</option>
                                        <option value="3" {{ @$filter['status'] == 3 ? 'selected' : '' }}>Thành công</option>
                                        <option value="4" {{ @$filter['status'] == 4 ? 'selected' : '' }}>Hủy</option>
                                    </select>
                                </div>
                                <div class="col-sm-1">
                                    <button class="btn btn-warning btn-block">Tìm kiếm</button>
                                </div>
                            </div>
                        </div>
                    </form><!-- END #form-search-advance -->
                    <form id="form-feedback" action="{{ route('admin.feedback.action') }}" method="post">
                        @csrf
                        <input type="hidden" name="method" value="" />
                        <input type="hidden" name="status" value="" />

                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-bordered">
                                <thead class="bg-primary">
                                <tr>
                                    <th width="30">ID</th>
                                    <th width="180">Tiêu đề</th>
                                    <th style="text-align: center">Thông tin chi tiết</th>
                                    <th >Căn hộ</th>
                                    <th >Tầng</th>
                                    <th >Tòa nhà</th>
                                    <th width="125">Người lập</th>
                                    <th width="200">Status</th>
                                    <th width="125">Tác vụ</th>
                                </tr>
                                </thead>
                                <tbody>
                                     @foreach ($registerVehicle as $item)
                                        @php
                                             $vehicle = null;
                                             $vehicle_cate = null;
                                             if($item->type == 1){
                                               $data = json_decode($item->data);
                                               $vehicle = $data->number_vehicles;
                                               $_vehicle_cate = @$data->type_vehicles > 0 ? App\Models\VehicleCategory\VehicleCategory::get_detail_vehicles_category_by_id($data->type_vehicles) : null;
                                               $vehicle_cate = @$_vehicle_cate->name;
                                             }
                                             if($item->type == 2 || $item->type ==3){
                                                $data = json_decode($item->data);
                                                $_vehicle = @$data->id_vehicles > 0 ? App\Models\Vehicles\Vehicles::get_detail_vehicle_by_id($data->id_vehicles) : null;
                                                $vehicle = @$_vehicle->number;
                                                $_vehicle_cate = @$_vehicle->vehicle_category_id > 0 ? App\Models\VehicleCategory\VehicleCategory::get_detail_vehicles_category_by_id($_vehicle->vehicle_category_id) : null;
                                               $vehicle_cate = @$_vehicle_cate->name;
                                             }
                                        @endphp
                                         <tr  @if ($item->status === 5) style='background-color: #e5ddac' @endif>
                                            <td>{{ $item->id }}</td>
                                            <td>
                                                @if ($item->type == 1)
                                                    Thêm phương tiện
                                                @elseif($item->type == 2)
                                                    Hủy phương tiện
                                                @elseif($item->type == 4)
                                                    Chuyển đồ
                                                @elseif($item->type == 5)
                                                    Sửa chữa
                                                @elseif($item->type == 6)
                                                    Tiện ích
                                                @else
                                                    Cấp lại thẻ xe
                                                @endif
                                            </td>
                                            <td>
                                             @if ($item->type == 1)
                                                <table class="table table-striped">
                                                    <thead class="bg-primary">
                                                        <tr>
                                                            <th colspan="2" class="text-center">Thông tin đăng ký phương tiện</th>
                                                        </tr>
                                                    </thead>
                                                    @php
                                                        if($item->data){
                                                            $data = json_decode($item->data);
                                                            $category = App\Models\VehicleCategory\VehicleCategory::get_detail_vehicles_category_by_id(@$data->type_vehicles);
                                                            $apartment = App\Models\Apartments\Apartments::get_detail_apartment_by_apartment_id($item->apartment_id);
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
                                                    </tbody>
                                                </table>
                                            @endif
                                            @if ($item->type == 2)
                                                <table class="table table-striped">
                                                    <thead class="bg-primary">
                                                        <tr>
                                                            <th colspan="2" class="text-center">Thông tin hủy phương tiện</th>
                                                        </tr>
                                                    </thead>
                                                    @php
                                                        if($item->data){
                                                            $data = json_decode($item->data);
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
                                                    </tbody>
                                                </table>
                                            @endif
                                            @if ($item->type == 3)
                                                <table class="table table-striped">
                                                    <thead class="bg-primary">
                                                        <tr>
                                                            <th colspan="2" class="text-center">Thông tin cấp lại thẻ xe</th>
                                                        </tr>
                                                    </thead>
                                                    @php
                                                        if($item->data){
                                                            $data = json_decode($item->data);
                                                            $vehicle  =  App\Models\Vehicles\Vehicles::get_detail_vehicle_by_id($data->id_vehicles);
                                                            $category = $vehicle ? App\Models\VehicleCategory\VehicleCategory::get_detail_vehicles_category_by_id(@$vehicle->vehicle_category_id): null;
                                                            $apartment = App\Models\Apartments\Apartments::get_detail_apartment_by_apartment_id($item->apartment_id);
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
                                                    </tbody>
                                                </table>
                                            @endif
                                            
                                            @if (@$item->type == 4)
                                                <table class="table table-striped">
                                                    @php
                                                        if($item->data){
                                                            $data = @$item->data ? json_decode(@$item->data) : null;
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
                                                            <td>
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
                                                                    @foreach ($products as $key => $value)
                                                                         <div>
                                                                             <span><strong>{{($key + 1)}}</strong> {{$value->title}}</span>
                                                                             <span><small><strong> | Mô tả:</strong> {{$value->desc}}</small></span>
                                                                             <span><small><strong> | Số lượng:</strong> {{number_format( $value->amount)}}</small></span>
                                                                         </div>
                                                                    @endforeach
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            @endif
                                            @if (@$item->type == 5)
                                                    <table class="table table-striped">
                                                        <thead class="bg-primary">
                                                            <tr>
                                                                <th colspan="2" class="text-center">Đăng ký sửa chữa</th>
                                                            </tr>
                                                        </thead>
                                                        @php
                                                            if($item->data){
                                                                $data = json_decode($item->data);
                                                            }
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
                                                                    <div>Thời gian dự kiến thi công: </div>
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
                                                                    @if(@$data->summary != 0)
                                                                        <div>Giá : </div>
                                                                        <div><strong>{{number_format(@$data->summary)}}</strong></div>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if(@$data->paid != 0)
                                                                        <div>Tiền cọc: </div>
                                                                        <div><strong>{{number_format(@$data->paid)}}</strong></div>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                @endif
                                                @if (@$item->type == 6)
                                                    <table class="table table-striped">
                                                        <thead class="bg-primary">
                                                            <tr>
                                                                <th colspan="2" class="text-center">Đăng ký tiện ích</th>
                                                            </tr>
                                                        </thead>
                                                        @php
                                                            if($item->data){
                                                                $data = json_decode($item->data);
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
                                                                    <div><strong> {{ @$item->user_created_by->full_name ?? 'không rõ' }}</strong></div>
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
                                                        </tbody>
                                                    </table>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $item->apartment->name??'' }}
                                            </td>
                                            <td>
                                                {{ $item->apartment->floor??0 }}
                                            </td>
                                            <td>
                                                {{ $item->apartment->buildingPlace->name??'' }}
                                            </td>
                                            <td>
                                                <small>
                                                    {{ @$item->user_created_by->full_name ?? 'không rõ' }}<br />
                                                    {{ @$item->created_at }}
                                                </small>
                                            </td>
                                            <td>
                                                @if (@$item->status === 0 || @$item->status === 5)
                                                       <label class="label label-sm label-warning">Chờ BQL xử lý</label>
                                                @elseif(@$item->status === 1)
                                                        <label class="label label-sm label-success">BQL đang xử lý</label>
                                                @elseif(@$item->status === 2)
                                                        <label class="label label-sm label-info">Chờ cư dân phản hồi</label>
                                                @elseif(@$item->status === 3)
                                                        <label class="label label-sm label-primary">Thành công</label>
                                                @else
                                                       <label class="label label-sm label-danger">Hủy</label>   
                                                @endif
                                            </td>
                                            <td>
                                                @if( in_array('admin.v2.user_request.detail_comments',@$user_access_router))
                                                    <a title="Trả lời ý kiến" href="{{ route('admin.v2.user_request.detail_comments',['id'=>@$item->id]) }}" class="btn btn-sm btn-reply btn-warning"><i class="fa fa-comments"></i></a>
                                                @endif
                                            </td>
                                         </tr>
                                     @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="row mbm">
                            <div class="col-sm-3">
                                <span class="record-total">Tổng: {{ @$registerVehicle->total() }} bản ghi</span>
                            </div>
                            <div class="col-sm-6 text-center">
                                <div class="pagination-panel">
                                    {{ @$registerVehicle->appends(Request::all())->onEachSide(1)->links() }}
                                </div>
                            </div>
                            <div class="col-sm-3 text-right">
                        <span class="form-inline">
                            Hiển thị
                            <select name="per_page" class="form-control" data-target="#form-feedback">
                                @php $list = [10, 20, 50, 100, 200]; @endphp
                                @foreach ($list as $num)
                                    <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                                @endforeach
                            </select>
                        </span>
                            </div>
                        </div>
                    </form><!-- END #form-feedback -->
            @endif
        </div>
    </div>
</section>

@endsection

@section('javascript')

<script>
    $(function () {
        get_data_select({
            object: '#sel-name',
            url: '{{ route('admin.feedback.ajax_get_profile') }}',
            data_id: 'id',
            data_text: 'display_name',
            title_default: 'Người gửi'
        });
        get_data_select({
            object: '#ip-apartment,#ip-ap_id',
            url: '{{ url('admin/apartments/ajax_get_apartment') }}',
            data_id: 'id',
            data_text: 'name',
            title_default: 'Chọn căn hộ'
        });
        function get_data_select(options) {
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

    });
    $('.change_status').change(function() {
            var _this = $(this);
            var _token = $('meta[name="csrf-token"]').attr('content');
            var id = _this.data('id');
            var status = _this.val();
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
                },
                dataType: 'json'
            });
        });
    sidebar('feedback');
</script>

@endsection