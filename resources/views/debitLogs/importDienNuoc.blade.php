@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        Lịch sử hệ thống xử lý công nợ
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Lịch sử hệ thống xử lý công nợ</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="row col-sm-12 form-group">
                        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle pull-left"
                            style="margin-right: 10px;">Tác
                            vụ&nbsp;<span class="caret"></span></button>
                        <ul class="dropdown-menu">
                            <li>
                                <a type="button" class="btn-action"
                                    data-target="#form-service-company" data-method="return_run_debit">Chạy lại công nợ</a>
                            </li>
                        </ul>
                    </div>
                    <div class="row form-group ">
                        <form id="form-search-advance" action="{{route('admin.debitlog.importDienNuoc')}}" method="GET">
                            <div id="search-advance" class="search-advance">
                                <div class="col-sm-2">
                                    <select name="bdc_apartment_id" class="form-control apartment-list selectpicker" data-live-search="true">
                                        <option value="" selected>Căn hộ</option>
                                        @if(isset($apartments))
                                            @foreach($apartments as $key => $apartment)
                                                <option value="{{ $key }}"
                                                        @if(@$filter['bdc_apartment_id'] ==  $key) selected @endif>{{ $apartment }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-sm-1">
                                    <select name="bdc_service_id" class="form-control select2">
                                        <option value="" selected>Dịch vụ...</option>
                                        @foreach($serviceBuildingFilter as $serviceBuilding)
                                            <option value="{{ $serviceBuilding->id }}"  @if(@$filter['bdc_service_id'] ==  $serviceBuilding->id) selected @endif>{{ $serviceBuilding->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-1">
                                    <select name="type" class="form-control apartment-list selectpicker">
                                        <option value="" selected>Kiểu xử lý..</option>
                                        <option value="debitprocess_v2:cron" @if(@$filter['type'] ==  'debitprocess_v2:cron') selected @endif>Tạo công nợ</option>
                                        <option value="create_debit_process_v3:cron" @if(@$filter['type'] ==  'create_debit_process_v2:cron') selected @endif>Xử lý công nợ</option>
                                        <option value="dienuocdebitprocess:cron" @if(@$filter['type'] ==  'dienuocdebitprocess:cron') selected @endif>Xử lý điện nước</option>
                                        <option value="phidaukydebitprocess_v2:cron" @if(@$filter['type'] ==  'phidaukydebitprocess_v2:cron') selected @endif>Xử lý phí đầu kỳ</option>
                                    </select>
                                </div>
                                <div class="col-sm-1">
                                    <select name="cycle_name" class="form-control select2">
                                        <option value="" selected>Kỳ</option>
                                        @if(isset($month))
                                            @foreach($month as $key => $value)
                                                <option value="{{ $value }}" @if(@$filter['cycle_name'] ==  $value) selected @endif>{{ $value }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    <div class="input-group date">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                        <input type="text" class="form-control date_picker" name="from_date" id="from_date" value="{{@$filter['from_date']}}" placeholder="Từ..." autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <div class="input-group date">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                        <input type="text" class="form-control date_picker" name="to_date" id="to_date" value="{{@$filter['to_date']}}" placeholder="Đến..." autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-sm-1">
                                    <select name="status" class="form-control apartment-list selectpicker">
                                        <option value="" selected>Trạng thái..</option>
                                        <option value="1" @if(@$filter['status'] ==  '1') selected @endif>Thành công</option>
                                        <option value="2" @if(@$filter['status'] ==  '2') selected @endif>Không thành công</option>
                                    </select>
                                </div>
                                <div class="col-sm-1">
                                    <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Tìm kiếm
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <form id="form-service-company" action="{{route('admin.debitlog.action')}}" method="post">
                        @csrf
                        <input type="hidden" name="method" value="">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="bg-primary">
                        <tr>
                            <th><input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /></th>
                            <th colspan="6">Đầu vào</th>
                            <th>Đầu ra</th>
                            <th>Thông báo</th>
                            <th>Ngày tạo</th>
                        </tr>
                        <tr>
                            <th></th>
                            <th>Mã căn hộ</th>
                            <th>Căn hộ</th>
                            <th>Dịch vụ</th>
                            <th>Số đầu</th>
                            <th>Số cuối</th>
                            <th>Kỳ tháng</th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($debitLogs as $key => $item)
                        @php
                            $input = json_decode($item->input);   
                            $apartment = App\Models\Apartments\Apartments::get_detail_apartment_by_apartment_id($item->bdc_apartment_id);
                            $service = App\Models\Service\Service::get_detail_bdc_service_by_bdc_service_id($item->bdc_service_id);
                            $_service = null;
                            if($service){
                                $_service = $service->name;
                            }
                            if(@$input->bdc_vehicle_id > 0){
                                $vehicle = App\Models\Vehicles\Vehicles::get_detail_vehicle_by_id($input->bdc_vehicle_id);
                                $_service .='_'.@$vehicle->number;
                            } 
                        @endphp
                        <tr>
                            <td><input type="checkbox" name="ids[]" value="{{$item->id}}" class="iCheck checkSingle" /></td>
                            <td style="font-size: 14px">{{ @$apartment->code }}</td>
                            <td style="font-size: 14px">{{ @$apartment->name }}</td>
                            <td style="font-size: 14px">{{ @$_service. '_'.$item->bdc_service_id}}</td>
                            <td style="font-size: 14px">{{ @$input->so_dau }}</td>
                            <td style="font-size: 14px">{{ @$input->so_cuoi }}</td>
                            <td style="font-size: 14px">{{ @$input->cycle_name }}</td>
                            <td style="font-size: 14px">
                                <div>
                                    <a href="javascript:;" onclick="toastr.success('{{json_encode($item->data)}}')" title="{{ @$item->data }}">{{ substr(@$item->data, 0, 10) }}...</a>
                                </div>
                                <div>
                                    <a href="javascript:;" onclick="toastr.success('{{json_encode($item->input)}}')" title="{{ @$item->input }}">{{ substr(@$item->input, 0, 10) }}...</a>
                                </div>
                            </td>
                            <td style="font-size: 14px">{{ @$item->message }}</td>
                            <td style="font-size: 14px">{{ @$item->created_at }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                 
                    <div class="row mbm">
                        <div class="col-sm-3">
                            <span class="record-total">Tổng: {{ $debitLogs->total() }} bản ghi</span>
                        </div>
                        <div class="col-sm-6 text-center">
                            <div class="pagination-panel">
                                {{ $debitLogs->appends(Request::all())->onEachSide(1)->links() }}
                            </div>
                        </div>
                        <div class="col-sm-3 text-right">
                            <span class="form-inline">
                                Hiển thị
                                <select name="per_page" class="form-control" data-target="#form-service-company">
                                    @php $list = [10, 20, 50, 100, 200]; @endphp
                                    @foreach ($list as $num)
                                    <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                                    @endforeach
                                </select>
                            </span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

@endsection

@section('javascript')
<script>
       $('input.date_picker').datepicker({
            autoclose: true,
            dateFormat: "dd-mm-yy"
        }).val();
</script>
@endsection


