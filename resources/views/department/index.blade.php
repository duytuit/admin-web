@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý chung
            <small>Quản lý bộ phận</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Quản lý bộ phận</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-body">
                <div class="row form-group">
                    <div class="col-sm-12 pull-right">
                        @if( in_array('admin.users.create', @$user_access_router))
                        <a href="{{ route('admin.users.create') }}" class="btn btn-warning"><i
                                    class="fa fa-edit"></i>
                            Thêm người dùng</a>
                        @endif
                        @if( in_array('admin.department.store', @$user_access_router))
                        <a href="" data-toggle="modal" data-target="#createDepartment" class="btn btn-success"><i
                                    class="fa fa-users"></i>
                            Thêm bộ phận</a>
                            @endif
                        <a href="javascript:" type="button" class="btn btn-success btn-action" data-target="#form-departments" data-method="qrcode">
                            Lấy file QRcode bộ phận
                        </a>
                    </div>
                </div>
                <form id="form-search-advance" action="{{ route('admin.department.index') }}" method="get">
                    <div id="search-advance" class="search-advance">
                        <div class="row form-group space-5">
                            <div class="col-sm-4">
                                <input type="text" name="keyword" class="form-control"
                                       placeholder="Nhập nội dung tìm kiếm" value="{{ @$filter['keyword'] }}">
                            </div>
                            <div class="col-sm-2">
                                <button class="btn btn-primary"><i class="fa fa-search"></i> Tìm kiếm</button>
                            </div>
                        </div>
                    </div>
                </form><!-- END #form-search-advance -->
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Danh sách bộ phận</h3>
                    </div>
                </div>
                <form id="form-departments" action="{{ route('admin.department.action') }}" method="post">
                    @csrf
                    <input type="hidden" name="method" value=""/>
                    <input type="hidden" name="status" value=""/>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-bordered">
                            <thead class="bg-primary">
                            <tr>
                                <th width="30"><input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /></th>
                                <th>STT</th>
                                <th>Tên</th>
                                <th>Mã</th>
                                <th>Tòa</th>
                                <th>ĐT liên hệ</th>
                                <th>Email liên hệ</th>
                                <th>Trưởng bộ phận</th>
                                <th>Mô tả</th>
                                <th>Trạng thái</th>
                                <th>Trạng thái đánh giá</th>
                                <th>Trạng thái notify</th>
                                <th colspan="2">Thao tác</th>
                            </tr>
                            </thead>
                            <tbody>
                                @if($departments->count() > 0)
                                    @foreach($departments as $key => $department)
                                        <tr style="vertical-align: middle">
                                            <td><input type="checkbox" name="ids[]" value="{{$department->id}}" class="iCheck checkSingle" /></td>
                                            <td style="text-align: center">{{ @($key + 1) + ($departments->currentPage() - 1) * $departments->perPage() }}</td>
                                            <td><a href="{{ route('admin.department.show', $department->id) }}">{{ @$department->name }}</a></td>
                                            <td>{{ @$department->code }}</td>
                                            <td>{{ @$department->building->name }}</td>
                                            <td>{{ @$department->phone }}</td>
                                            <td>{{ @$department->email }}</td>
                                            <td>
                                                <a title="Trưởng bộ phận" href="">{{ @$department->head_department->publicUser->BDCprofile->display_name }}</a>
                                            </td>
                                            <td>{{ $department->description }}</td>
                                            <td>
                                                <div class="onoffswitch">
                                                    <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" data-id="{{ $department->id }}"
                                                        id="myonoffswitch_{{ $department->id }}" data-url="{{ route('admin.department.change-status') }}" @if($department->status == true) checked @endif >
                                                    <label class="onoffswitch-label" for="myonoffswitch_{{ $department->id }}">
                                                        <span class="onoffswitch-inner"></span>
                                                        <span class="onoffswitch-switch"></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="onoffswitch1">
                                                    <input type="checkbox" name="onoffswitch1" class="onoffswitch1-checkbox" data-id="{{ $department->id }}"
                                                        id="myonoffswitch_status_{{ $department->id }}" data-url="{{ route('admin.ajax.change-status-app') }}" @if($department->status_app == true) checked @endif >
                                                    <label class="onoffswitch1-label change_status_app" for="myonoffswitch_status_{{ $department->id }}">
                                                        <span class="onoffswitch1-inner"></span>
                                                        <span class="onoffswitch1-switch"></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="onoffswitch2">
                                                    <input type="checkbox" name="onoffswitch2" class="onoffswitch2-checkbox" data-id="{{ $department->id }}"
                                                        id="myonoffswitch_notify_{{ $department->id }}" data-url="{{ route('admin.ajax.change-status-app') }}" @if($department->status_notify == true) checked @endif >
                                                    <label class="onoffswitch2-label change_status_app" for="myonoffswitch_notify_{{ $department->id }}">
                                                        <span class="onoffswitch2-inner"></span>
                                                        <span class="onoffswitch2-switch"></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td style="text-align: center">
                                                <a data-id="{{ $department->id }}" data-action="{{ route('admin.department.edit') }}"
                                                class="btn btn-xs btn-info edit_department" title="Sửa thông tin">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <a class="btn btn-xs btn-danger delete-department"
                                                data-url="{{ route('admin.department.destroy', $department->id) }}"
                                                title="Xóa bộ phận"><i class="fa fa-trash"></i></a>
                                            </td>
                                           
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="row mbm">
                        <div class="col-sm-6">
                            <span class="record-total">Hiển thị {{ $departments->count() }} / {{ $departments->total() }} kết quả</span>
                        </div>
                        <div class="col-sm-6 text-right">
                            <div class="pagination-panel">
                                {{ $departments->links() }}
                            </div>
                        </div>
                    </div>
                </form><!-- END #form-users -->
                <div class="modal-insert">

                </div>
                @include('department.modal.create_department')
                @include('department.modal.create_user_department')
                <br>
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

        .onoffswitch1 {
            position: relative;
            width: 70px;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }

        .onoffswitch1-checkbox {
            display: none;
        }

        .onoffswitch1-label {
            display: block;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid #999999;
            border-radius: 16px;
        }

        .onoffswitch1-inner {
            display: block;
            width: 200%;
            margin-left: -100%;
            transition: margin 0.3s ease-in 0s;
        }

        .onoffswitch1-inner:before, .onoffswitch1-inner:after {
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

        .onoffswitch1-inner:before {
            content: "ACTIVE";
            padding-left: 12px;
            background-color: #00C0EF;
            color: #FFFFFF;
        }

        .onoffswitch1-inner:after {
            content: "INACTIVE";
            background-color: #EEEEEE;
            color: #999999;
            text-align: right;
        }

        .onoffswitch1-switch {
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

        .onoffswitch1-checkbox:checked + .onoffswitch1-label .onoffswitch1-inner {
            margin-left: 0;
        }

        .onoffswitch1-checkbox:checked + .onoffswitch1-label .onoffswitch1-switch {
            right: 0px;
        }
        .onoffswitch2 {
            position: relative;
            width: 70px;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }

        .onoffswitch2-checkbox {
            display: none;
        }

        .onoffswitch2-label {
            display: block;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid #999999;
            border-radius: 16px;
        }

        .onoffswitch2-inner {
            display: block;
            width: 200%;
            margin-left: -100%;
            transition: margin 0.3s ease-in 0s;
        }

        .onoffswitch2-inner:before, .onoffswitch2-inner:after {
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

        .onoffswitch2-inner:before {
            content: "ACTIVE";
            padding-left: 12px;
            background-color: #00C0EF;
            color: #FFFFFF;
        }

        .onoffswitch2-inner:after {
            content: "INACTIVE";
            background-color: #EEEEEE;
            color: #999999;
            text-align: right;
        }

        .onoffswitch2-switch {
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

        .onoffswitch2-checkbox:checked + .onoffswitch2-label .onoffswitch2-inner {
            margin-left: 0;
        }

        .onoffswitch2-checkbox:checked + .onoffswitch2-label .onoffswitch2-switch {
            right: 0px;
        }
    </style>
@endsection
@section('javascript')
    <script>
        var requestSend = false;
        $('.modal').on('hidden.bs.modal', function(){
            $(document).find('.has-error').removeClass('has-error');
            if ($(document).find('.help-block').length) {
                $(document).find('.help-block').remove();
            }
            $(this).find('form')[0].reset();
        });

        //save department
        submitAjaxForm('#add_department', '#create_department', '.div_', '.message_zone');

        //show modal create
        showModalForm('.edit_department', '#editDepartment');

        //edit departmant
        submitAjaxForm('#update_department', '#edit_department', '.create_', '.message_zone_create');

        //delete department
        deleteSubmit('.delete-department');

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
                       status: checked
                   },
                    success: function (response) {
                        if (response.success == true) {
                            toastr.success(response.message);
                        } else {
                            toastr.warning(response.message);
                        }
                        setTimeout(() => {
                            location.reload()
                        }, 1000)
                        requestSend = false;
                    }
                });
            } else {
                e.preventDefault();
            }
        })
            //onoff status
        $(document).on('click', '.onoffswitch1-label', function (e) {
            var div = $(this).parents('div.onoffswitch1');
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
                       status_app: checked
                   },
                    success: function (response) {
                        if (response.success == true) {
                            toastr.success(response.message);
                        } else {
                            toastr.error('Không thay đổi trạng thái đánh giá thành công');
                        }
                        requestSend = false;
                    }
                });
            } else {
                e.preventDefault();
            }
        })
        $(document).on('click', '.onoffswitch2-label', function (e) {
            var div = $(this).parents('div.onoffswitch2');
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
                       status_notify: checked
                   },
                    success: function (response) {
                        if (response.success == true) {
                            toastr.success('Không thay đổi trạng thái thành công');
                        } else {
                            toastr.error('Không thay đổi trạng thái thành công');
                        }
                        requestSend = false;
                    }
                });
            } else {
                e.preventDefault();
            }
        })

    </script>
@endsection