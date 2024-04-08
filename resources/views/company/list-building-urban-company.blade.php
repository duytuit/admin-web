@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý
            <small>Công ty</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Quản lý công ty</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="box-body">
                <div class="col-md-12">
                    <!-- Custom Tabs -->
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs">
                            <li class="{{ str_contains(url()->current(),'urban-building') ? 'active' : null }}"><a href="{{ route('admin.company.urban-building.index') }}" >Danh mục khu đô thị - dự án</a></li>
                            <li class="{{ !str_contains(url()->current(),'urban-building') ? 'active' : null }}"><a href="{{ route('admin.company.list.index') }}" >Danh mục công ty</a></li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane {{ str_contains(url()->current(),'urban-building') ? 'active' : null }}" id="{{ route('admin.company.urban-building.index') }}">
                                    <div class="box-header with-border">
                                        <h3>Thông tin công ty</h3>
                                        <div class="row box-body">
                                            <div class="col-md-6">
                                                <dl class="dl-horizontal">
                                                     <form role="form">
                                                        <!-- select -->
                                                        <div class="form-group">
                                                            <select name="company_id" id="company_id" class="form-control"  onchange='this.form.submit()'>
                                                                @foreach ($company as $value)
                                                                    <option value="{{ $value->id }}" @if(@$filter['company_id'] == $value->id )selected @endif > {{ $value->name }}</option>
                                                                @endforeach
                                                         </select>
                                                        </div>
                                                        <noscript><input type="submit" value="Submit"></noscript>
                                                    </form>
                                                </dl>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="col-sm-6 col-xs-6 col-md-6">
                                                    <a href="javascript:;" type="button" class="btn btn-primary show_edit"><i class="fa fa-plus"></i>&nbsp;&nbsp;Thêm khu đô thị</a>
                                                    <a href="{{ route('admin.company.urban-building.create',['company_id'=>@$filter['company_id']]) }}" type="button" class="btn btn-primary"><i class="fa fa-plus"></i>&nbsp;&nbsp;Thêm dự án</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="box-header with-border">
                                        <h3>Khu đô thị</h3>
                                        <div class="table-responsive">
                                            <table class="table table-hover table-striped table-bordered">
                                                <thead class="bg-primary">
                                                <tr>
                                                    <th width='10%'>STT</th>
                                                    <th>Tên</th>
                                                    <th width='10%'>Thao tác</th>
                                                </tr>
                                                </thead>
                                                 <tbody>
                                                    @if($urbans->count() > 0)
                                                        @foreach($urbans as $key => $value)
                                                            <tr>
                                                                <td>{{ $key + 1 }}</td>
                                                                <td>{{ @$value->name }}</td>
                                                                <td>
                                                                    <a href="javascript:;" data-item="{{$value}}" class="btn btn-sm btn-primary show_edit"><i class="fa fa-edit"></i></a>
                                                                    <a href="{{ route('admin.company.urban-building.destroy',['id'=>$value->id]) }}" onclick="return confirm('Bạn có chắc chắn xóa!');" class="btn btn-sm btn-danger" title="Xóa"> <i class="fa fa-trash"></i> </a>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="box-header with-border">
                                        <h3>Thông tin dự án</h3>
                                        <div class="table-responsive">
                                            <table class="table table-hover table-striped table-bordered">
                                                <thead class="bg-primary">
                                                <tr>
                                                    <th>STT</th>
                                                    <th>Tên dự án</th>
                                                    <th>Địa chỉ</th>
                                                    <th>Số điện thoại</th>
                                                    <th>Mô tả</th>
                                                    <th>Trưởng ban quản lý</th>
                                                    <th>Trạng thái</th>
                                                    <th>Hành động</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                    @if($buildings->count() > 0)
                                                        @foreach($buildings as $key => $value)
                                                        <tr>
                                                            <td>{{ $key + 1 }}</td>
                                                            <td>{{ @$value->name }}</td>
                                                            <td>{{ @$value->address }}</td>
                                                            <td>{{ @$value->phone }}</td>
                                                            <td>{{ @$value->description }}</td>
                                                            <td>{{ @$value->manager->company_staff->name }}</td>
                                                            <td>
                                                                <div class="onoffswitch-v2">
                                                                    <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox-v2" data-id="{{ @$value->id }}"
                                                                            id="myonoffswitch_{{ @$value->id }}" data-url="{{ route('admin.company.urban-building.change-status-building') }}" @if(@$value->status == 1) checked @endif >
                                                                    <label class="onoffswitch-label-v2" for="myonoffswitch_{{ @$value->id }}">
                                                                        <span class="onoffswitch-inner"></span>
                                                                        <span class="onoffswitch-switch"></span>
                                                                    </label>
                                                                </div>
                                                            </td>
                                                            <td><a href="{{ route('admin.company.urban-building.edit', $value->id) }}" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i></a></td>
                                                        </tr>
                                                        @endforeach
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                            </div>
                        </div>
                        <!-- /.tab-content -->
                    </div>
                    <!-- nav-tabs-custom -->
                </div>
            </div>
        </div>
    </section>
    <div class="modal fade" id="createUrban" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" style="display: initial;">Thông tin khu đô thị</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form class="form-horizontal" method="POST" data-action="{{ route('admin.company.urban-building.saveUrban') }}" id="create_urban">
                        @csrf
                        <input type="hidden" id="_company_id" name="company_id">
                        <input type="hidden" id="urban_id" name="id">
                        <div class="box-body">
                            <div class="form-group data_content">
                                <label for="content" class="col-sm-3 control-label">Tên khu đô thị</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="name" name="name">
                                    <div class="message_zone_data"></div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer d-flex justify-content-center">
                    <button type="button" class="btn btn-primary" id="add_urban">Lưu</button>
                    <button type="button" class="btn btn-warning" data-dismiss="modal">Hủy</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('stylesheet')
    <style>
        .onoffswitch,.onoffswitch-v2 {
            position: relative;
            width: 70px;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }

        .onoffswitch-checkbox,.onoffswitch-checkbox-v2 {
            display: none;
        }

        .onoffswitch-label,.onoffswitch-label-v2 {
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
        .onoffswitch-checkbox-v2:checked + .onoffswitch-label-v2 .onoffswitch-inner {
            margin-left: 0;
        }

        .onoffswitch-checkbox-v2:checked + .onoffswitch-label-v2 .onoffswitch-switch {
            right: 0px;
        }
    </style>
@endsection
@section('javascript')
    <script>
        var requestSend = false;

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
                    type: 'POST',
                    data: {
                        id: id,
                        active: checked
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
        $(document).on('click', '.onoffswitch-label-v2', function (e) {
            var div = $(this).parents('div.onoffswitch-v2');
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
                    type: 'POST',
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
                        location.reload()
                        requestSend = false;
                    }
                });
            } else {
                e.preventDefault();
            }
        })
        $('.show_edit').click(function (e) { 
            e.preventDefault();
            $('#create_urban')[0].reset();
            let item = $(this).data('item');
            if(item){
                $('#name').val(item.name);
                $('#urban_id').val(item.id);
            }
            $('#_company_id').val($('#company_id').val());
            $('#createUrban').modal('show');
        });
        $('#add_urban').click(function (e) { 
            var form_data = new FormData($('#create_urban')[0]);
            e.preventDefault();
            $.ajax({
                    url: $('#create_urban').attr('data-action'),
                    type: 'POST',
                    data: form_data,
                    contentType: false,
                    processData: false, 
                    success: function (response) {
                        if (response.success == true) {
                            toastr.success(response.message);
                        } 
                        setTimeout(() => {
                            location.reload()
                        }, 1000)
                    },
                    error: function (response){
                        toastr.error(response.responseJSON.errors.name[0]);
                        setTimeout(() => {
                            location.reload()
                        }, 1000)
                    }
            });
        });
    </script>
@endsection