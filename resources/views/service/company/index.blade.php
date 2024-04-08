@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý
            <small>Quản lý dịch vụ</small>
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
                    <div class="col-sm-4 col-xs-12">
                        <form id="form-search-service" action="{{route('admin.service.company.index')}}" method="get">
                            <div class="input-group">
                                <input type="text" class="form-control" name="name" placeholder="Nhập tên dịch vụ"
                                       value="{{@$filter['name']}}">
                                <div class="input-group-btn">
                                    <button type="submit" title="Tìm kiếm" form="form-search-service" class="btn btn-info submit-search"><i
                                                class="fa fa-search"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-sm-4">
                    </div>
                    <div class="col-sm-4">
                        <!-- <a href="{{route('admin.service.company.choose')}}" class="btn btn-success pull-right">Chọn dịch
                            vụ</a> -->
                        <a href="{{ route('admin.service.company.create') }}"
                           class="btn btn-info pull-right margin-r-5"><i class="fa fa-edit"></i>
                            Thêm mới</a>
                    </div>
                </div>
                <form id="form-service-company" action="{{route('admin.perpage.action')}}" method="post">
                    @csrf
                    <input type="hidden" name="method" value="">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-bordered">
                            <thead class="bg-primary">
                            <tr>
                                <th>STT</th>
                                <th>Tên dịch vụ</th>
                                <th>Mã dịch vụ</th>
                                <th>Chu kỳ</th>
                                <th>Ngày chốt</th>
                                <th>Hạn thanh toán</th>
                                <th>Ngày áp dụng</th>
                                <th>Trạng thái</th>
                                <th>Sử dụng</th>
                                <th>Tác vụ</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if($services->count() > 0)
                                @foreach($services as $key => $service)
                                    <tr>
                                        <td>{{ @($key + 1) + ($services->currentpage() - 1) * $services->perPage()  }}</td>
                                        <td>{{ @$service->name }}</td>
                                        <td>{{ @$service->id }}</td>
                                        <td>{{ @$service->period->name }}</td>
                                        <td>Ngày {{ @$service->bill_date }}</td>
                                        <td>Ngày {{ @$service->payment_deadline }}</td>
                                        <td>{{date('d/m/Y', strtotime(@$service->first_time_active))}}</td>
                                        <td>
                                            <div class="onoffswitch">
                                                <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" data-id="{{ $service->id }}"
                                                       id="myonoffswitch_{{ $service->id }}" data-url="{{ route('admin.service.building.status') }}" @if($service->status == true) checked @endif >
                                                <label class="onoffswitch-label" for="myonoffswitch_{{ $service->id }}">
                                                    <span class="onoffswitch-inner"></span>
                                                    <span class="onoffswitch-switch"></span>
                                                </label>
                                            </div>
                                        </td>
                                        <td>Tòa nhà: {{@$service->children_count }} </br>
                                        Căn hộ: {{@$service->apartment_use_service_count }}</td>

                                        <td class="text-center">
                                            <a href="{{ route('admin.service.company.edit', ['id' => $service->id]) }}"
                                               class="btn btn-sm btn-info"><i class="fa fa-edit"></i></a>
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
        })


        $(document).on('click', '.submit-search', function (e) {
            e.preventDefault();
            $('#form-search-service').submit();
        })

    </script>
@endsection