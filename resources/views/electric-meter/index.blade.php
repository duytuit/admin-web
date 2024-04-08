@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <div>
        <span>Danh sách ghi số điện nước</span>
            <a href="{{ route('admin.electricMeter.import') }}" class="btn btn-info tinh_cong_no">
                <i class="fa fa-file-excel-o"></i>
                Import chỉ số đầu
            </a>
            <a href="{{ route('admin.debitlog.importDienNuoc') }}" class="btn bg-olive">Lịch sử tính công nợ</a>
            <a href="{{ route('admin.v2.debit.debitLogs') }}" class="btn bg-olive">Tính công nợ</a>
    </div>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Căn hộ</li>
    </ol>
</section>
<section class="content">
    <div class="box box-primary">
        <div class="box-body ">
            <form id="form-search-apartment" action="{{ route('admin.electricMeter.index') }}" method="get">
              <div class="row col-sm-12 form-group">
                    <div class="col-sm-1">
                        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle pull-left"
                            style="margin-right: 10px;margin-bottom: 15px;">Tác vụ&nbsp;<span class="caret"></span></button>
                        <ul class="dropdown-menu">
                            <li>
                                @if( in_array('admin.electricMeter.delete',@$user_access_router))
                                    <a href="javascript:" type="button" class="btn-action"
                                        data-target="#form-search-electric-meter" data-method="delete">
                                        <i class="fa fa-trash text-danger"></i>&nbsp; Xóa
                                    </a>
                                @endif
                                <a href="javascript:" type="button" class="btn-action" data-target="#form-search-electric-meter"
                                    data-method="download_image">
                                    <i class="fa fa-cloud-download text-success"></i>&nbsp; Tải ảnh xuống
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="col-sm-2" style="padding-left:0">
                        <select name="ip_place_id" id="ip-place_id" class="form-control" style="width: 100%;">
                            <option value="">Chọn tòa nhà</option>
                            <?php $place_building = isset($get_place_building) ? $get_place_building : '' ?>
                            @if($place_building)
                            <option value="{{$place_building->id}}" selected>{{$place_building->name}}</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-sm-2" style="padding-left:0">
                        <select name="bdc_apartment_id" id="ip-apartment" class="form-control">
                            <option value="">Căn hộ</option>
                            <?php $apartment = isset($get_apartment) ? $get_apartment: '' ?>
                            @if($apartment)
                            <option value="{{$apartment->id}}" selected>{{$apartment->name}}</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <select name="cycle_name" class="form-control select2">
                            <option value="" selected>tháng chốt số</option>
                            @foreach($cycle_names as $cycle_name)
                            <option value="{{ $cycle_name }}" @if($chose_cycle_name==$cycle_name) selected @endif>{{ $cycle_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-1">
                        <select name="floor" class="form-control" style="width: 100%">
                        <option value="">Tầng</option>
                            @php 
                                $search_floor = !empty($data_search['floor']) ? $data_search['floor'] : ''; 
                            @endphp
                            @foreach ($floors as $item)
                            <option value="{{ $item['floor'] }}" @if($item['floor'] == $search_floor) selected @endif >Tầng {{ $item['floor'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-1">
                        <select name="type" class="form-control" style="width: 100%">
                            <option value="0" {{isset($filter['type']) ? $filter['type'] == 0 ? 'selected' : '' : '' }}>Điện</option>
                            <option value="1" {{isset($filter['type']) ? $filter['type'] == 1 ? 'selected' : '' : '' }}>Nước</option>
                            <option value="2" {{isset($filter['type']) ? $filter['type'] == 2 ? 'selected' : '' : '' }}>Nước Nóng</option>
                        </select>
                    </div>
                    
                    <div class="col-sm-1">
                        <div class="input-group-btn">
                            <button type="submit" title="Tìm kiếm" title="Tìm kiếm" class="btn btn-info" style="margin-right: 130px;"
                                form="form-search-apartment"><i class="fa fa-search"></i> Tìm</button>
                        </div>
                    </div>
                    <div class="col-sm-1">
                        <div class="input-group-btn">
                            <a href="{{ route('admin.electricMeter.export',Request::all()) }}" class="btn btn-success"><i
                                    class="fa fa-download"></i>Export Exel</a>
                        </div>
                    </div>
                </div>

            </form><!-- END #form-search-advance -->
            <div class="clearfix"></div>
            <form id="form-search-electric-meter" action="{{ route('admin.electricMeter.action') }}" method="post">
                @csrf
                <input type="hidden" name="method" value="" />
                <input type="hidden" name="status" value="" />

                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="bg-primary">
                            <tr>
                                <th width="3%"><input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /></th>
                                <th >STT</th>
                                <th >Căn hộ</th>
                                <th >Mã căn hộ</th>
                                <th >Dịch vụ</th>
                                <th >Chỉ số đầu</th>
                                <th >Chỉ số cuối</th>
                                <th >Tiêu thụ</th>
                                <th >Hình thức</th>
                                <th >Ngày chốt số</th>
                                <th >Hình ảnh</th>
                                <th >Người tạo</th>
                                <th >Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($electric_meters)
                            @foreach($electric_meters as $key => $value)
                                <tr >
                                    <td>
                                        @if($value->status == 0)
                                            <input type="checkbox" name="ids[]" value="{{ $value->id }}" class="iCheck checkSingle" />
                                        @endif
                                    </td>
                                        @php
                                                $apart = App\Models\Apartments\Apartments::get_detail_apartment_by_apartment_id($value->bdc_apartment_id);
                                                $service_apartment = App\Repositories\BdcApartmentServicePrice\ApartmentServicePriceRepository::findApartmentServicePriceByApartment($value->bdc_apartment_id,$value->type);
                                        @endphp
                                    <td>
                                        <a target="_blank" href="/admin/activity-log/log-action?row_id={{$value->id}}"> {{ $value->id }}</a>
                                    </td>
                                    <td>{{@$apart->name}}</td>
                                    <td>{{@$apart->code}}</td>
                                    <td>{{@$service_apartment->name}}</td>
                                    <td>{{$value->type_action ==1 ? '--': $value->before_number}}</td>
                                    <td>{{ $value->after_number}}</td>
                                    <td>{{$value->type_action ==1 ? '--': $value->after_number - $value->before_number}}</td>
                                    <td>{{$value->type_action ==1 ? 'Thay đồng hồ': ($value->type_action ==2 ? 'Thay đổi giá giữa kỳ' : '')}}</td>
                                    <td>{{ date('d-m-Y',strtotime($value->date_update))}}</td>
                                    <td>
                                        @if ($value->images)
                                            <a target="_blank" href="{{$value->images}}" style="height:15px;display: inline-flex;">Xem ảnh</a>
                                        @endif
                                    </td>
                                    <td>
                                        <?php
                                        //$user = App\Models\PublicUser\Users::find($value->created_by);
                                        $user= \App\Models\PublicUser\V2\User::find($value->created_by);
                                        if(!$user)
                                        {
                                            //$user= \App\Models\PublicUser\V2\User::find($value->created_by);
                                            $user = App\Models\PublicUser\Users::find($value->created_by);
                                        }
                                        ?>
                                        {{$user ? @$user->email : ''}}</td>
                                    <td>
                                        <a href="javascript:;" data-id="{{ $value->id }}" class="btn btn-sm btn-warning view_detail" title="chi tiết"><i class="fa fa-codepen"></i></a>
                                    </td>
                                </tr>
                             @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="row mbm">
                    <div class="col-sm-3">
                        <span class="record-total">Hiển thị: {{ @$electric_meters->count() }} / {{ @$electric_meters->total() }} Kết quả</span>
                    </div>
                    <div class="col-sm-6 text-center">
                        <div class="pagination-panel">
                                {{ @$electric_meters->appends(Request::all())->onEachSide(1)->links() }}
                        </div>
                    </div>
                    <div class="col-sm-3 text-right">
                        <span class="form-inline">
                            Hiển thị
                            <select name="per_page" class="form-control" data-target="#form-search-electric-meter">
                                @php $list = [5,10, 20, 50, 100, 200]; @endphp
                                @foreach ($list as $num)
                                <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                                @endforeach
                            </select>
                        </span>
                    </div>
                </div>
            </form><!-- END #form-users -->
        </div>
    </div>
</section>


@include('electric-meter.modal.make_electric_water')
@include('electric-meter.modal.detail')
@endsection
<style>
    .modal-body {
      min-height: 500px;
    }
</style>
@section('javascript')

<script>
            get_data_select_apartment({
                object: '#ip-apartment,#ip-ap_id',
                url: '{{ url('admin/apartments/ajax_get_apartment') }}',
                data_id: 'id',
                data_text: 'name',
                title_default: 'Chọn căn hộ'
            });
            get_data_select_apartment1({
                object: '#ip-place_id',
                url: '{{ url('admin/apartments/ajax_get_building_place') }}',
                data_id: 'id',
                data_text: 'name',
                data_code: 'code',
                title_default: 'Chọn tòa nhà'
            });
            function get_data_select_apartment(options) {
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
            $('.change_').change(function (e) { 
                e.preventDefault();
                $.ajax({
                    type: "POST",
                    url: '{{route('admin.electricMeter.count')}}',
                    data:  {
                        cycle_name_handle: $('#cycle_name_handle').val(),
                        type_handle: $('#type_handle').val()
                    },
                    dataType: 'json',
                    success: function (response) {
                        console.log(response);
                        $('#count_apartment').text(response.count);
                    }
                });
            });
            $('.view_detail').click(function (e) { 
                e.preventDefault();
                $('#after_number').val('');
                $('#electric_meter_id').val('');
                $('#aparmtent_name').val('');
                $('#cycle_name').val('');
                $('#before_number').val('');
                $('#service_name').val('');
                if($(this).attr('data-id')){
                    $.ajax({
                        type: "POST",
                        url: '{{route('admin.electricMeter.view_detail')}}',
                        data:  {
                            id: parseInt($(this).attr('data-id')) 
                        },
                        dataType: 'json',
                        success: function (response) {
                            console.log(response);
                           
                            if(response.electric_meter){
                                $('#image_detail').attr("src", response.electric_meter.images);
                                $('#after_number').val(response.electric_meter.after_number);
                                $('#electric_meter_id').val(response.electric_meter.id);
                                $('#aparmtent_name').text(response.electric_meter.apartment_name);
                                $('#cycle_name').text(response.electric_meter.month_create);
                                $('#before_number').text(response.electric_meter.before_number);
                                $('#service_name').text(response.electric_meter.type == '0' ? 'Điện' : 'Nước');
                                $('#showDetail').modal('show');
                            }
                            
                        }
                   });
                }
            });
            $('#remove_image').click(function (e) {
                e.preventDefault();
                if($('#electric_meter_id').val()){
                    $.ajax({
                        type: "POST",
                        url: '{{route('admin.electricMeter.removeImage')}}',
                        data:  {
                            id: $('#electric_meter_id').val()
                        },
                        dataType: 'json',
                        success: function (response) {
                            if(response.sussces == true){
                                toastr.success(response.msg);
                                setTimeout(() => {
                                    location.reload()
                                }, 1000)
                            }else{
                                toastr.warning(response.msg);
                            }

                        }
                    });
                }
            });
            $('#previous').click(function (e) { 
                e.preventDefault();
                if($('#electric_meter_id').val()){
                    $.ajax({
                        type: "POST",
                        url: '{{route('admin.electricMeter.previous')}}',
                        data:  {
                            id: $('#electric_meter_id').val() 
                        },
                        dataType: 'json',
                        success: function (response) {
                            console.log(response);
                            if(response.electric_meter){
                                $('#image_detail').attr("src", response.electric_meter.images);
                                $('#after_number').val(response.electric_meter.after_number);
                                $('#electric_meter_id').val(response.electric_meter.id);
                                $('#aparmtent_name').text(response.electric_meter.apartment_name);
                                $('#cycle_name').text(response.electric_meter.month_create);
                                $('#before_number').text(response.electric_meter.before_number);
                                $('#service_name').text(response.electric_meter.type == '0' ? 'Điện' : 'Nước');
                            }
                            
                        }
                   });
                }
            });
            $('#next').click(function (e) { 
                e.preventDefault();
                if($('#electric_meter_id').val()){
                    $.ajax({
                        type: "POST",
                        url: '{{route('admin.electricMeter.next')}}',
                        data:  {
                            id: $('#electric_meter_id').val()  
                        },
                        dataType: 'json',
                        success: function (response) {
                            console.log(response);
                            if(response.electric_meter){
                                $('#image_detail').attr("src", response.electric_meter.images);
                                $('#after_number').val(response.electric_meter.after_number);
                                $('#electric_meter_id').val(response.electric_meter.id);
                                $('#aparmtent_name').text(response.electric_meter.apartment_name);
                                $('#cycle_name').text(response.electric_meter.month_create);
                                $('#before_number').text(response.electric_meter.before_number);
                                $('#service_name').text(response.electric_meter.type == '0' ? 'Điện' : 'Nước');
                            }
                            
                        }
                   });
                }
            });
            $('#submit_electric').click(function (e) { 
                e.preventDefault();
                if($('#after_number').val()){
                    $.ajax({
                        type: "POST",
                        url: '{{route('admin.electricMeter.save')}}',
                        data:  {
                            after_number: $('#after_number').val(),
                            id: $('#electric_meter_id').val() 
                        },
                        dataType: 'json',
                        success: function (response) {
                            if(response.sussces == true){
                                toastr.success(response.msg);
                            }else{
                                toastr.warning(response.msg);
                            }
                            
                        }
                   });
                }
            });
            $('#set_rotate_image').click(function (e) { 
                let angle = getRotationDegrees($('#image_detail'));
                angle += 90;
                if(angle == 360){
                    angle = 0;
                    $('#image_detail').css('transform', 'rotate(' + angle + 'deg) scale(1)');
                }else{
                    $('#image_detail').css('transform', 'rotate(' + angle + 'deg) scale(0.8)');
                }
            });
            function getRotationDegrees(obj) {
               var matrix = obj.css("-webkit-transform") ||
                obj.css("-moz-transform") ||
                obj.css("-ms-transform") ||
                obj.css("-o-transform") ||
                obj.css("transform");
                if(matrix !== 'none') {
                var values = matrix.split('(')[1].split(')')[0].split(',');
                var a = values[0];
                var b = values[1];
                var angle = Math.round(Math.atan2(b, a) * (180/Math.PI));
                } else { var angle = 0; }
                return (angle < 0) ? angle + 360 : angle;
            }
            
</script>


@endsection
