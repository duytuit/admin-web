@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý
            <small>Quản lý dịch vụ tòa nhà</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Quản lý dịch vụ tòa nhà</li>
        </ol>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-body">
                <div class="row form-group">
                    <div class="col-sm-4 col-xs-12">
                        <form action="{{route('admin.v2.service.building.index')}}" method="get">
                            <div class="input-group">
                                <input type="text" class="form-control" name="name" placeholder="Nhập tên dịch vụ"
                                       value="{{@$filter['name']}}">
                                <div class="input-group-btn">
                                    <button type="submit" title="Tìm kiếm" class="btn btn-info"><i
                                                class="fa fa-search"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-sm-8">
                        <a href="{{route('admin.v2.service.apartment.index')}}" class="btn btn-primary margin-r-5">Căn hộ
                            sử dụng dịch vụ</a>
                        <a href="{{ route('admin.v2.service.building.importexcel') }}"
                        class="btn btn-info pull-right margin-r-5"><i class="fa fa-edit"></i>
                            Import dịch vụ cho căn hộ</a>
                        <a href="{{route('admin.v2.service.building.choose')}}"
                           class="btn btn-success pull-right margin-r-5">Chọn dịch
                            vụ</a>
                        <a href="{{ route('admin.v2.service.building.create') }}"
                           class="btn btn-info pull-right margin-r-5"><i class="fa fa-edit"></i>
                            Thêm mới</a>
                    </div>
                </div>
                <div class="row form-group">
                    {{-- <div class="col-sm-1">
                        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle pull-left" style="margin-right: 10px;">Tác vụ&nbsp;<span class="caret"></span></button>
                        <ul class="dropdown-menu">
                            <li>
                                @if(\Auth::user()->isadmin == 1)
                                <a href="javascript:" type="button" class="btn-action" data-target="#form-service-building" data-method="delete">
                                    <i class="fa fa-trash text-danger"></i>&nbsp; Xóa
                                </a>
                                @endif
                            </li>
                        </ul>
                    </div> --}}
                    @if(\Auth::user()->isadmin == 1)
                    <div class="col-sm-3">
                        <select class="form-control" id="type_tinh_cong_no">
                            <option value="" {{ @$type_tinh_cong_no == null ? 'selected' : '' }}>Tính công nợ theo tháng hiện tại</option>
                            <option value="custom_month" {{ @$type_tinh_cong_no != null ? 'selected' : ''}}>Tính công nợ theo kỳ</option>
                        </select>
                    </div>
                    @endif
                    <div class="col-sm-6">
                        <a class="btn btn-warning change-status"
                           data-action="{{route('admin.v2.service.building.status')}}" data-method="Active"><i
                                    class="fa fa-check"></i> Active</a>
                        <a class="btn btn-success change-status"
                           data-action="{{route('admin.v2.service.building.status')}}" data-method="Inactive"><i
                                    class="fa fa-times"></i> Inactive</a>
                        <a class="btn btn-primary order_index_accounting"><i class="fa fa-bars"></i> Sắp xếp dịch vụ ưu tiên hạch toán</a>
                    </div>
                </div>
                <form id="form-service-building" action="{{route('admin.service.company.action')}}" method="post">
                    @csrf
                    <input type="hidden" name="method" value="">
                    <input type="hidden" id="status_order" value="">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-bordered">
                            <thead class="bg-primary">
                            <tr>
                                <th><input type="checkbox" class="iCheck checkAll" data-target=".checkSingle"/></th>
                                <th>Tên dịch vụ</th>
                                <th>Mã dịch vụ</th>
                                <th>Mã thu</th>
                                <th>Chu kỳ</th>
                                <th>Ngày chốt</th>
                                <th>Hạn thanh toán</th>
                                <th>Ngày áp dụng</th>
                                <th>Trạng thái</th>
                                <th>Sử dụng</th>
                                <th>Tác vụ</th>
                            </tr>
                            </thead>
                            <tbody id="tablecontents">
                            @if($services->count() > 0)
                                @foreach($services as $key => $service)
                                    <tr class="row1" data-id="{{ $service->id }}">
                                        <td>
                                            <a class="item_index_accounting" style="display: none"><i class="fa fa-bars"></i></a>
                                            <div class="item_check_box">
                                                <input type="checkbox" name="ids[]" value="{{ $service->id }}" class="iCheck checkSingle"/>
                                            </div>
                                        </td>
                                        <td>{{ @$service->name }}</td>
                                        <td>{{ @$service->id }}</td>
                                        <td>{{ @$service->code_receipt }}</td>
                                        <td>{{ @$service->period->name }}</td>
                                        <td>Ngày {{ @$service->bill_date }}</td>
                                        <td>{{ $service->bdc_period_id == 6 ? '' : 'Ngày '.@$service->payment_deadline }}</td>
                                        <td>{{ @$service->first_time_active ? date('d/m/Y', strtotime($service->first_time_active)) : ''}}</td>
                                        <td>
                                            <div class="onoffswitch">
                                                <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" data-id="{{ $service->id }}"
                                                       id="myonoffswitch_{{ $service->id }}" data-url="{{ route('admin.v2.service.building.status') }}" value="{{$service->status}}" @if($service->status == true) checked @endif >
                                                <label class="onoffswitch-label" for="myonoffswitch_{{ $service->id }}">
                                                    <span class="onoffswitch-inner"></span>
                                                    <span class="onoffswitch-switch"></span>
                                                </label>
                                            </div>
                                        </td>
                                        <td>{{@$service->apartment_service_prices_count }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.v2.service.building.edit', ['id' => $service->id]) }}"
                                               class="btn btn-sm btn-info"><i class="fa fa-edit"></i></a>
                                            {{-- @if(\Auth::user()->isadmin == 1)
                                            <a href="{{ route('admin.v2.service.building.destroy', ['id' => $service->id]) }}" onclick="return confirm('Bạn có chắc chắn muốn xóa không?')"
                                            class="btn btn-sm btn-info btn-danger"><i class="fa fa-trash"></i></a>
                                            @endif --}}
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="row mbm">
                        <div class="col-sm-3">
                            <span class="record-total">Tổng: {{ $services->total() }} bản ghi</span>
                        </div>
                        <div class="col-sm-6 text-center">
                            <div class="pagination-panel">
                                {{ $services->appends(Request::all())->onEachSide(1)->links() }}
                            </div>
                        </div>
                        <div class="col-sm-3 text-right">
                        <span class="form-inline">
                            Hiển thị
                            <select name="per_page" class="form-control" data-target="#form-service-building">
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

        .onoffswitch-inner:before, .onoffswitch-inner:after {
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

        .onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-inner {
            margin-left: 0;
        }

        .onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-switch {
            right: 0px;
        }
    </style>
@endsection
@section('javascript')
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        //onoff status
        $(document).on('click', '.onoffswitch-label', function (e) {
            var div = $(this).parents('div.onoffswitch');
            var input = div.find('input');
            var id = input.attr('data-id');
            if (confirm('Thay đổi trạng thái sẽ ảnh hưởng tới cư dân')) {
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
                        success: function (response) {
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
            }
        });
        
        $('#type_tinh_cong_no').change(function (e) { 
            e.preventDefault();
            $.ajax({
                        url: '{{route('admin.v2.service.building.set_type_tinh_cong_no')}}',
                        type: 'POST',
                        data: {
                            type_tinh_cong_no: $('#type_tinh_cong_no').val(),
                        },
                        success: function (response) {
                            if (response.success == true) {
                                toastr.success(response.message);
                            } else {
                                toastr.error('Không thành công');
                            }
                            requestSend = false;
                        }
            });
        });

        //active or inactive
        $(document).on('click', 'a.change-status', function (e) {
            var ids = [];
            var div = $('div.icheckbox_square-green[aria-checked="true"]');
            div.each(function (index, value) {
                var id = $(value).find('input.checkSingle').val();
                if (id) {
                    ids.push(id);
                }
            });
            var text = $(this).attr('data-method');
            if (ids.length == 0) {
                toastr.error('Vui lòng chọn các dịch vụ để thực hiện thao tác này');
            } else {
                if (!confirm('Bạn có chắc ' + text + ' những dịch vụ này')) {
                    e.preventDefault();
                } else {
                    if (text == 'Active') {
                        var data = {
                            ids: ids,
                            status: 'Active'
                        }
                    } else {
                        data = {
                            ids: ids,
                            status: 'Inactive'
                        }
                    }
                    $.ajax({
                        url: $(this).attr('data-action'),
                        type: 'PUT',
                        data: data,
                        success: function (response) {
                            if (response.success == true) {
                                toastr.success(response.message);

                                setTimeout(() => {
                                    location.reload()
                                }, 1000)
                            }
                        }
                    })
                }
            }
        })
        $(document).ready(function () {
             $('.order_index_accounting').click(function(){
                 if($('#status_order').val() == 1){  // tắt sắp xếp
                    $('#status_order').val(0);
                    $('.item_index_accounting').css('display','none');
                    $('.item_check_box').css('display','block');  
                    $( "#tablecontents" ).sortable({
                        items: "tr",
                        cursor: 'move',
                        opacity: 0.6,
                        disabled: true,
                        update: function() {
                            sendOrderToServer();
                        }
                    });
                 }else{                             // bật sắp xếp
                    $('#status_order').val(1);
                    $('.item_index_accounting').css('display','block');
                    $('.item_check_box').css('display','none'); 
                    $( "#tablecontents" ).sortable({
                        items: "tr",
                        cursor: 'move',
                        opacity: 0.6,
                        disabled: false,
                        update: function() {
                            sendOrderToServer();
                        }
                    });
                 }
                function sendOrderToServer() {
                    var order = [];
                    var token = $('meta[name="csrf-token"]').attr('content');
                   
                    $('tr.row1').each(function(index,element) {
                        order.push({
                            id: $(this).attr('data-id'),
                            position: index+1
                        });
                    });
                    $.ajax({
                        type: "POST", 
                        dataType: "json", 
                        url: "{{ route('admin.v2.service.building.update_index_accounting') }}",
                        data: {
                            order: order,
                            _token: token
                        },
                        success: function(response) {
                            if (response.status == "success") {
                            } else {
                            }
                        }
                    });
                }
             });
        });
    </script>
@endsection