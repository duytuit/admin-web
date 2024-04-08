@extends('backend.layouts.master')

@section('content')
<section class="content-header">
    <h1>
        Quản lý
        <small>Quản lý dịch vụ</small>
            <a href="{{route('admin.service.apartment.create')}}" class="btn btn-success margin-r-5">
                <i class="fa fa-pencil-square-o" aria-hidden="true"></i> Thêm dịch vụ cho căn hộ </a>
            <a href="{{route('admin.service.apartment.export',Request::all())}}"
                class="btn btn-success margin-r-5">
                <i class="fa fa-pencil-square-o" aria-hidden="true"></i> Xuất ra Excel </a>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Quản lý dịch vụ</li>
    </ol>
   
</section>
<section class="content">
    <div class="box box-primary">
        <div class="box-body">
            <div class="row form-group">
                <form action="{{route('admin.service.apartment.index')}}" method="GET">
                    @if(\Auth::user()->isadmin == 1)
                        <div class="col-sm-1">
                            <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle pull-left"
                                style="margin-right: 10px;">Tác vụ&nbsp;<span class="caret"></span></button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="javascript:" type="button" class="btn-action"
                                        data-target="#form-service-apartment-action" data-method="update_first_time_pay">
                                        <i class="fa fa-edit text-green"></i>&nbsp; Sửa ngày bắt đầu tính phí
                                    </a>
                                    <a href="javascript:" type="button" class="btn-action"
                                        data-target="#form-service-apartment-action" data-method="update_last_time_pay">
                                        <i class="fa fa-edit text-green"></i>&nbsp; Sửa ngày tính phí tiếp theo
                                    </a>
                                    <a href="javascript:" type="button" class="btn-action"
                                        data-target="#form-service-apartment-action" data-method="update_price_type">
                                        <i class="fa fa-edit text-green"></i>&nbsp; Sửa loại giá
                                    </a>

                                    <a href="javascript:" type="button" class="btn-action"
                                        data-target="#form-service-apartment-action" data-method="delete">
                                        <i class="fa fa-trash text-danger"></i>&nbsp; Xóa
                                    </a>
                                    <a href="javascript:" type="button" class="btn-action"
                                        data-target="#form-service-apartment-action" data-method="restore_delete">
                                        <i class="fa fa-trash text-danger"></i>&nbsp; Phục hồi
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="col-sm-2">
                            <div class="input-group date">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" class="form-control pull-right date_picker" name="update_time_pay"
                                    id="datepicker" placeholder="chọn ngày tính phí" value="">
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <select name="bdc_price_type_id" id="update_bdc_price_type_id" class="form-control">
                                <option value="">-- loại giá --</option>
                                <option value="1">một giá</option>
                                <option value="2">lũy tiến</option>
                            </select>
                        </div>
                    @endif
                    <div class="col-sm-3">
                        <input type="text" class="form-control" name="name" placeholder="Nhập tên dịch vụ, căn hộ"
                            value="{{@$filter['name']}}">
                    </div>
                    <div class="col-sm-2">
                        <input type="text" class="form-control date_picker" name="ngay_tinh_phi"
                            placeholder="Ngày tính phí" value="">
                    </div>
                    <div class="col-sm-1">
                        <select name="status" id="status_service" class="form-control">
                            <option value="">-- Trạng thái --</option>
                            <option value="0">Inactive</option>
                            <option value="1">Active</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" title="Tìm kiếm" class="btn btn-info"><i
                                class="fa fa-search"></i></button>
                    </div>
                </form>
            </div>
            <div class="table-responsive">
                @if( in_array('admin.service.apartment.action',@$user_access_router))
                <form action="{{ route('admin.service.apartment.action') }}" method="post"
                    id="form-service-apartment-action">
                    {{ csrf_field() }}
                    <input type="hidden" name="method" value="" />
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="bg-primary">
                            <tr>
                                <th width="30"><input type="checkbox" class="iCheck checkAll"
                                        data-target=".checkSingle" /></th>
                                <th>STT</th>
                                <th>Tên căn hộ</th>
                                <th>Mã căn hộ</th>
                                <th>Dịch vụ</th>
                                <th>Mã dịch vụ</th>
                                <th>Ngày bắt đầu</th>
                                <th>Ngày kết thúc</th>
                                <th>Ngày tính phí tiếp theo</th>
                                <th>Trạng thái</th>
                                <th>Tác vụ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(@$apartmentServices->count() > 0)
                            @foreach(@$apartmentServices as $key => $apartmentService)
                            @php
                                    $service =  App\Models\Service\Service::get_detail_bdc_service_by_bdc_service_id($apartmentService->bdc_service_id);
                                    $vehicle = @$apartmentService->bdc_vehicle_id > 0 ? App\Models\Vehicles\Vehicles::get_detail_vehicle_by_id($apartmentService->bdc_vehicle_id) : null;
                                    $apartment = App\Models\Apartments\Apartments::get_detail_apartment_by_apartment_id($apartmentService->bdc_apartment_id);
                            @endphp
                            <tr @if($apartmentService->deleted_at != null) class="danger" style="text-decoration:
                                line-through;" @endif>
                                <td><input type="checkbox" name="ids[]" value="{{$apartmentService->id}}"
                                        class="iCheck checkSingle" /></td>
                                <td>{{ ($key + 1) + (@$apartmentServices->currentpage() - 1) *
                                    @$apartmentServices->perPage() }}</td>
                                <td>{{ @$apartment->name }}</td>
                                <td>{{ @$apartment->code }}</td>
                                <td>{{@$service->name}}{{@$vehicle ? '- '.@$vehicle->number : ''}} </td>
                                <td>{{@$service->id}} </td>
                                <td>{{$apartmentService->first_time_active ? date('d-m-Y',strtotime($apartmentService->first_time_active)) : '' }} </td>
                                <td>{{$apartmentService->finish ? date('d-m-Y',strtotime($apartmentService->finish)) : '--/--/----' }} </td>
                                <td>{{$apartmentService->last_time_pay ? date('d-m-Y',strtotime($apartmentService->last_time_pay)) : '' }} </td>
                                <td>
                                    @if ($apartmentService->deleted_at == null)
                                    <div class="onoffswitch">
                                        <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox"
                                            data-id="{{ $apartmentService->id }}"
                                            id="myonoffswitch_{{ $apartmentService->id }}"
                                            data-url="{{ route('admin.service.apartment.status') }}"
                                            @if($apartmentService->status == true) checked @endif >
                                        <label class="onoffswitch-label"
                                            for="myonoffswitch_{{ $apartmentService->id }}">
                                            <span class="onoffswitch-inner"></span>
                                            <span class="onoffswitch-switch"></span>
                                        </label>
                                    </div>
                                    @endif
                                </td>
                                <td>
                                    @if ($apartmentService->deleted_at == null)
                                    <a style="float: left"
                                        href="{{ route('admin.service.apartment.edit', ['id' => @$apartmentService->id]) }}"
                                        class="btn btn-sm btn-warning margin-r-5"><i class="fa fa-edit"></i></a>
                                    @if( in_array('admin.service.apartment.destroy',@$user_access_router))
                                    <a href="{{ route('admin.service.apartment.destroy', ['id' => @$apartmentService->id]) }}"
                                        class="btn btn-sm btn-danger" title="xóa"
                                        onclick="return confirm('Bạn muốn xóa dịch vụ này ?');"><i
                                            class="fa fa-trash"></i></a>
                                    @endif
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                </form>
                @endif
            </div>
            <form id="form-service-apartment" action="{{route('admin.service.company.action')}}" method="post">
                @csrf
                <input type="hidden" name="method" value="">
                <div class="row mbm">
                    <div class="col-sm-3">
                        <span class="record-total">Tổng: {{ $apartmentServices->total() }} bản ghi</span>
                    </div>
                    <div class="col-sm-6 text-center">
                        <div class="pagination-panel">
                            {{ $apartmentServices->appends(Request::all())->onEachSide(1)->links() }}
                        </div>
                    </div>
                    <div class="col-sm-3 text-right">
                        <span class="form-inline">
                            Hiển thị
                            <select name="per_page" class="form-control" data-target="#form-service-apartment">
                                @php $list = [10, 20, 50, 100, 200]; @endphp
                                @foreach ($list as $num)
                                <option value="{{ $num }}" {{ $num==$per_page ? 'selected' : '' }}>{{ $num }}</option>
                                @endforeach
                            </select>
                        </span>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
@section('stylesheet')
<style>
    .onoffswitch {
        position: relative;
        width: 70px;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
    }

    .onoffswitch-checkbox {
        display: none;
    }

    .onoffswitch-label {
        display: block;
        overflow: hidden;
        cursor: pointer;
        border: 2px solid #999999;
        border-radius: 16px;
    }

    .onoffswitch-inner {
        display: block;
        width: 200%;
        margin-left: -100%;
        transition: margin 0.3s ease-in 0s;
    }

    .onoffswitch-inner:before,
    .onoffswitch-inner:after {
        display: block;
        float: left;
        width: 50%;
        height: 21px;
        padding: 0;
        line-height: 21px;
        font-size: 9px;
        color: white;
        font-family: Trebuchet, Arial, sans-serif;
        font-weight: bold;
        box-sizing: border-box;
    }

    .onoffswitch-inner:before {
        content: "ACTIVE";
        padding-left: 12px;
        background-color: #00C0EF;
        color: #FFFFFF;
    }

    .onoffswitch-inner:after {
        content: "INACTIVE";
        background-color: #EEEEEE;
        color: #999999;
        text-align: right;
    }

    .onoffswitch-switch {
        display: block;
        width: 23px;
        height: 23px;
        margin: 1px;
        background: #FFFFFF;
        position: absolute;
        top: 0;
        bottom: 0;
        right: 45px;
        border: 2px solid #999999;
        border-radius: 16px;
        transition: all 0.3s ease-in 0s;
    }

    .onoffswitch-checkbox:checked+.onoffswitch-label .onoffswitch-inner {
        margin-left: 0;
    }

    .onoffswitch-checkbox:checked+.onoffswitch-label .onoffswitch-switch {
        right: 0px;
    }
</style>
@endsection

@section('javascript')
<script>
    //onoff status
    $(document).on('click', '.onoffswitch-label', function(e) {
        var div = $(this).parents('div.onoffswitch');
        var input = div.find('input');
        var id = input.attr('data-id');
        if (input.attr('checked')) {
            var checked = 0;
        } else {
            var checked = 1;
        }
        if (!requestSend) {
            requestSend = true;
            $.ajax({
                url: input.attr('data-url'),
                type: 'PUT',
                data: {
                    id: id,
                    status: checked
                },
                success: function(response) {
                    if (response.success == true) {
                        toastr.success(response.message);
                    } else {
                        toastr.error('Không thay đổi trạng thái');
                    }
                    requestSend = false;
                }
            });
        } else {
            e.preventDefault();
        }
    })
</script>
<script>
    //Date picker
        $('input.date_picker').datepicker({
            autoclose: true,
            dateFormat: "dd-mm-yy"
        }).val();

</script>
@endsection