@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            {{$meta_title}}
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Phân quyền nhân viên trong bộ phận</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-body">
                <div class="box-header with-border">
                    <h3 class="box-title bold">Thông tin nhân viên</h3>
                </div>
                <!-- left column -->
                <div class="col-md-12">
                    <!-- form start -->
                    <div class="box-body">
                        <ul class="list-group list-group-unbordered">
                            <li class="list-group-item">
                                <b>Email đăng nhập</b> <span class="pull-right">{{ $staff->publicUser->email }}</span>
                            </li>
                            <li class="list-group-item">
                                <ul>
                                    <li class="list-group-item">
                                        <b>Tên hiển thị</b> <span
                                                class="pull-right">{{ @$staff->publicUser->BDCprofile->display_name }}</span>
                                    </li>
                                    <li class="list-group-item">
                                        <b>Code</b> <span class="pull-right">{{ @$staff->publicUser->BDCprofile->staff_code?:'' }}</span>
                                    </li>
                                    <li class="list-group-item">
                                        <b>Email</b> <span class="pull-right">{{ @$staff->publicUser->BDCprofile->email }}</span>
                                    </li>
                                    <li class="list-group-item">
                                        <b>Số điện thoại</b> <span class="pull-right">{{ @$staff->publicUser->BDCprofile->phone }}</span>
                                    </li>
                                    <li class="list-group-item">
                                        <b>Tòa nhà</b> <span class="pull-right">{{ @$staff->publicUser->BDCprofile->building->name }}</span>
                                    </li>
                                    <li class="list-group-item">
                                        <b>Bộ phận</b> <span class="pull-right">{{ @$staff->department->name }}</span>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                    <!-- /.box -->
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="">
                        <div class="box-header with-border">
                            <h3 class="box-title bold">quyền người dùng ở bộ phận</h3>
                        </div>
                        <div class="">
                            <div class="col-md-12">
                                <div class="box box-solid">
                                    <!-- /.box-header -->
                                    <div class="">
                                        <div class="box-group" id="accordion">
                                            <!-- we are adding the .panel class so bootstrap.js collapse plugin detects it -->
                                            @if($data->count() > 0)
                                                @foreach($data as $module)
                                                    @if($module->permissions()->whereIn('id', $permission_ids)->count() > 0)
                                                        <div class="panel box box-primary">
                                                            <a data-toggle="collapse" data-parent="#accordion"
                                                               href="#collapse{{ $module->id }}"
                                                               aria-expanded="{{ $active_module == $module->id ? 'true' : 'false' }}"
                                                               class="{{ $active_module != $module->id ? 'collapsed' : '' }}">
                                                                <div class="box-header with-border bg-light-blue">
                                                                    <div class="text-create-recipt">
                                                                        <i class="fa {{ $module->icon_web }}"></i> {{ $module->name }}
                                                                    </div>
                                                                </div>
                                                            </a>
                                                            <div id="collapse{{ $module->id }}"
                                                                 class="panel-collapse collapse {{ $active_module == $module->id ? 'in' : '' }}"
                                                                 aria-expanded="{{ $active_module == $module->id ? 'true' : 'false' }}"
                                                                 style="{{ $active_module != $module->id ? 'height: 0px;' : '' }}">
                                                                <div class="box-body">
                                                                    <div class="row form-group">
                                                                        <div class="col-sm-8">
                                                            <span class="btn-group hidden">
                                                                <a data-action="{{ route('admin.department.updatePermissionDeny', $staff->id) }}"
                                                                   data-module="{{ $module->id }}"
                                                                   class="btn btn-danger" id="add-multi-permission"><i
                                                                            class="fa fa-check"></i> Cập nhật quyền</a>
                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="table-responsive">
                                                                        <table class="table table-hover table-striped table-bordered">
                                                                            <thead class="bg-olive">
                                                                            <tr>
                                                                                <th>Tên quyền</th>
                                                                                <th hidden><input type="checkbox"
                                                                                           class="iChecked checkAll_{{ $module->id }}"
                                                                                           data-target=".checkSingle_{{$module->id}}"/>
                                                                                </th>
                                                                            </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                            @foreach ($module->permissions()->whereIn('id', $permission_ids)->orderBy('route_name', 'asc')->get() as $item)
                                                                                <tr valign="middle">
                                                                                    <td>{{ @$item->title  }}</td>
                                                                                    <td hidden><input type="checkbox" name="ids[]"
                                                                                               value="{{ $item->id }}"
                                                                                               class="iChecked checkSingle_{{$module->id}}"
                                                                                               @if(in_array($item->id, $permission_check)) checked @endif/>
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                    <!-- /.box-body -->
                                </div>
                                <!-- /.box -->
                            </div>
                            <div class="col-md-2"></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="">
                        <div class="box-header with-border">
                            <h3 class="box-title bold">quyền người dùng được phân</h3>
                        </div>
                        <div class="">
                            <div class="col-md-12">
                                <div class="box box-solid">
                                    <!-- /.box-header -->
                                    <div class="">
                                        <div class="box-group" id="accordion">
                                            <!-- we are adding the .panel class so bootstrap.js collapse plugin detects it -->
                                            @if($data->count() > 0)
                                                @foreach($data as $module)
                                                    @if($module->permissions()->whereIn('id', $permissionUser)->count() > 0)
                                                        <div class="panel box box-primary">
                                                            <a data-toggle="collapse" data-parent="#accordion"
                                                               href="#collapse{{ $module->id }}"
                                                               aria-expanded="{{ $active_module == $module->id ? 'true' : 'false' }}"
                                                               class="{{ $active_module != $module->id ? 'collapsed' : '' }}">
                                                                <div class="box-header with-border bg-light-blue">
                                                                    <div class="text-create-recipt">
                                                                        <i class="fa {{ $module->icon_web }}"></i> {{ $module->name }}
                                                                    </div>
                                                                </div>
                                                            </a>
                                                            <div id="collapse{{ $module->id }}"
                                                                 class="panel-collapse collapse {{ $active_module == $module->id ? 'in' : '' }}"
                                                                 aria-expanded="{{ $active_module == $module->id ? 'true' : 'false' }}"
                                                                 style="{{ $active_module != $module->id ? 'height: 0px;' : '' }}">
                                                                <div class="box-body">
                                                                    <div class="row form-group">
                                                                        <div class="col-sm-8">
                                                            <span class="btn-group hidden">
                                                                <a data-action="{{ route('admin.department.updatePermissionDeny', $staff->id) }}"
                                                                   data-module="{{ $module->id }}"
                                                                   class="btn btn-danger" id="add-multi-permission"><i
                                                                            class="fa fa-check"></i> Cập nhật quyền</a>
                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="table-responsive">
                                                                        <table class="table table-hover table-striped table-bordered">
                                                                            <thead class="bg-olive">
                                                                            <tr>
                                                                                <th>Tên quyền</th>
                                                                                <th hidden><input type="checkbox"
                                                                                           class="iChecked checkAll_{{ $module->id }}"
                                                                                           data-target=".checkSingle_{{$module->id}}"/>
                                                                                </th>
                                                                            </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                            @foreach ($module->permissions()->whereIn('id', $permissionUser)->orderBy('route_name', 'asc')->get() as $item)
                                                                                <tr valign="middle">
                                                                                    <td>{{ @$item->title  }}</td>
                                                                                    <td hidden><input type="checkbox" name="ids[]"
                                                                                               value="{{ $item->id }}"
                                                                                               class="iChecked checkSingle_{{$module->id}}"
                                                                                               @if(in_array($item->id, $permissionUser)) checked @endif/>
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                    <!-- /.box-body -->
                                </div>
                                <!-- /.box -->
                            </div>
                            <div class="col-md-2"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection
@section('javascript')

    <script>
        $(document).ready(function () {
            var arrayModule = {!! json_encode($module->pluck('id')->toArray()) !!};
            $('input.iChecked').iCheck({
                checkboxClass: 'icheckbox_square-green',
                radioClass: 'iradio_square-green',
                increaseArea: '20%' // optional
            });
            $.each(arrayModule, function (index, value) {
                var className = 'input.checkAll_' + value;
                $(className).on('ifToggled', function (e) {
                    var target = $(this).data('target');
                    if (this.checked) {
                        $(target).iCheck('check');
                    } else {
                        $(target).iCheck('uncheck');
                    }
                });
            });
            $('input:checked').each(function (index, value) {
                $(value).parents('div.icheckbox_square-green').attr("aria-checked", "true");
            });

        });
        sidebar('users', 'users');
        $(document).on('click', '#add-multi-permission', function (e) {
            var ids = [];
            var module_id = $(this).attr('data-module');
            var input = 'input.checkSingle_' + module_id;
            var div = $('div.icheckbox_square-green.checked');
            div.each(function (index, value) {
                var id = $(value).find(input).val();
                if (id) {
                    ids.push(id);
                }
            });
            if (!confirm('Bạn có chắc chắn cập nhật quyền cho người này?')) {
                e.preventDefault();
            } else {
                $.ajax({
                    url: $(this).attr('data-action'),
                    type: 'POST',
                    data: {
                        ids: ids,
                        module_id: $(this).attr('data-module')
                    },
                    success: function (response) {
                        if (response.success == true) {
                            toastr.success(response.message);
                        } else {
                            toastr.error('Không thể cập nhật quyền cho user này!');
                        }
                    }
                })
            }
        })
    </script>

@endsection