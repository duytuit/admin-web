@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            {{$meta_title}}
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Phân quyền nhân viên</li>
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
                                <b>Email đăng nhập</b> <span class="pull-right">{{ @$user->email }}</span>
                            </li>
                            <li class="list-group-item">
                                @if(isset($user->BDCprofile))
                                    @foreach($user->BDCprofile()->where('bdc_building_id', $active_bulding)->get() as $info)
                                        <ul>
                                            <li class="list-group-item">
                                                <b>Tên hiển thị</b> <span
                                                        class="pull-right">{{ @$info->display_name }}</span>
                                            </li>
                                            <li class="list-group-item">
                                                <b>Code</b> <span class="pull-right">{{ @$info->staff_code?:'' }}</span>
                                            </li>
                                            <li class="list-group-item">
                                                <b>Email</b> <span class="pull-right">{{ @$info->email }}</span>
                                            </li>
                                            <li class="list-group-item">
                                                <b>Số điện thoại</b> <span class="pull-right">{{ @$info->phone }}</span>
                                            </li>
                                            <li class="list-group-item">
                                                <b>Tòa nhà</b> <span class="pull-right">{{ @$info->building->name }}</span>
                                            </li>
                                        </ul>
                                    @endforeach
                                @endif

                            </li>
                        </ul>
                    </div>
                    <!-- /.box -->
                </div>
            </div>
            <div class="box-body">
                @if(\Auth::user()->isadmin == 1)
                    <div class="box-header with-border">
                        <h3 class="box-title bold">Quyền supper hệ thống <input type="checkbox"  {{ @$user->isadmin != 1 ? '' : 'checked' }} class="icheckbox_square-green iradio_square-green" name="IsAdmin"></h3>
                    </div>
                    <div class="box-header with-border">
                        <h3 class="box-title bold">Nhóm quyền người dùng <input type="checkbox" class="iChecked checkAll"
                                                                                data-target=".checkSingle"/></h3>
                    </div>
                @else
                    <div class="box-header with-border">
                        <h3 class="box-title bold">Nhóm quyền người dùng</h3>
                    </div>
                @endif
                <div class="col-sm-6">
                    <div class="form-group hidden">
                        <label for="">Chọn nhóm quyền </label>
                        <div id="group-permission" style=" margin: 5px 0px 15px;">
                            <select id="groups_permission" class="groups_permission" name="groups_permission[]" class="form-control" style="width: 100%;" multiple>
                                @if(isset($listGroupsPermission))
                                    @foreach ($listGroupsPermission as $item)
                                        @if($item->status == 1)
                                           <option value="{{ $item['id'] }}" selected >{{ $item['name'] }}</option>
                                        @endif
                                        @if($item->status != 1 && \Auth::user()->isadmin == 1)
                                           <option value="{{ $item['id'] }}" selected >{{ $item['name'] }}</option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="row">
                            @if(isset($listGroupsPermissions))
                                @foreach ($listGroupsPermissions as $item)
                                    @if($item->status == 1 && $item->id != 10 && $item->id != 66)
                                        <div class="col-sm-8">
                                            <div class="form-group">
                                                <label for="check_per_{{ $item->id }}">{{ $item->name }}</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-2 group_check">
                                            <input type="checkbox" name="groups_permission[]" id="check_per_{{ $item->id }}" value="{{ $item->id }}" class="iChecked checkSingle" @if(in_array($item->id, $groupSelect)) checked @endif/>
                                        </div>
                                    @endif

                                    @if(($item->id == 10 || $item->id == 66)  && \Auth::user()->isadmin == 1)
                                        <div class="col-sm-8">
                                            <div class="form-group">
                                                <label for="check_per_{{ $item->id }}">{{ $item->name }}</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-2 group_check">
                                            <input type="checkbox" name="groups_permission[]" id="check_per_{{ $item->id }}" value="{{ $item->id }}" class="iChecked checkSingle" @if(in_array($item->id, $groupSelect)) checked @endif/>
                                        </div>
                                    @endif

                                    @if($item->status != 1 && \Auth::user()->isadmin == 1)
                                        <div class="col-sm-8">
                                            <div class="form-group">
                                                <label for="check_per_{{ $item->id }}">{{ $item->name }}</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-2 group_check">
                                            <input type="checkbox" name="groups_permission[]" id="check_per_{{ $item->id }}" value="{{ $item->id }}" class="iChecked checkSingle" @if(in_array($item->id, $groupSelect)) checked @endif/>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
                <!-- list building -->
                {{--<div class="col-sm-6">
                    <div class="form-group hidden">
                        <label for="">List Building</label>
                        <div id="list_building" style=" margin: 5px 0px 15px;">
                            <select id="list_building" class="list_building" name="list_building[]" class="form-control" style="width: 100%;" multiple>
                                @if(isset($listBuilding))
                                    @foreach ($listBuilding as $item)
                                        <option value="{{ $item['id'] }}" selected >{{ $item['name'] }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="row">
                            @if(isset($listBuilding))
                                @foreach ($listBuilding as $item)
                                    <div class="col-sm-8">
                                        <div class="form-group">
                                            <label for="check_building_{{ $item->id }}">{{ $item->name }}</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-2 building_check">
                                        <input type="checkbox" name="list_building[]" id="check_building_{{ $item->id }}" value="{{ $item->id }}" class="iChecked checkSingle" @if(in_array($item->id, $BuildingSelect)) checked @endif/>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>--}}
                <div class="clearfix"></div>
                <div class="row hidden">
                    <div class="col-md-8">
                        <div class="box box-solid">
                            <!-- /.box-header -->
                            <div class="box-body">
                                <div class="box-group" id="accordion">
                                    <!-- we are adding the .panel class so bootstrap.js collapse plugin detects it -->
                                    @if($data->count() > 0)
                                        @foreach($data as $module)
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
                                                            <span class="btn-group">
                                                                <a data-action="{{ route('admin.users.permission.update', $user->id) }}" data-module="{{ $module->id }}"
                                                                   class="btn btn-danger" id="add-multi-permission"><i class="fa fa-check"></i> Cập nhật quyền</a>
                                                            </span>
                                                            </div>
                                                        </div>
                                                        <div class="table-responsive">
                                                            <table class="table table-hover table-striped table-bordered">
                                                                <thead class="bg-olive">
                                                                <tr>
                                                                    <th>Tên quyền</th>
                                                                    <th><input type="checkbox" class="iChecked checkAll_{{ $module->id }}"
                                                                               data-target=".checkSingle_{{$module->id}}"/></th>
                                                                </tr>
                                                                </thead>
                                                                <tbody>
                                                                @if($module->permissions->count() > 0)
                                                                    @foreach ($module->permissions()->orderBy('route_name', 'asc')->get() as $item)
                                                                        <tr valign="middle">
                                                                            <td>{{ @$item->title  }}</td>
                                                                            <td><input type="checkbox" name="ids[]"
                                                                                       value="{{ $item->id }}"
                                                                                       class="iChecked checkSingle_{{$module->id}}"
                                                                                       @if(in_array($item->id, $permissionUser)) checked @endif/>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                @endif
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
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
    </section>
@endsection
@section('javascript')

    <script type="text/javascript">

           
        $(document).ready(function () {
           
            $('input[type=checkbox][name=IsAdmin]').change(function() {
                if ($(this).is(':checked')) {
                      $.get('{{ route('admin.users.ajaxChangeIsAdmin') }}', {
                          id: {{$id}},
                          isadmin: 1,
                        }, function(data) {
                            toastr.success('Sửa quyền thành công!');
                        });
                  }else{
                   $.get('{{ route('admin.users.ajaxChangeIsAdmin') }}', {
                          id: {{$id}},
                          isadmin: 0,
                        }, function(data) {
                            toastr.success('Sửa quyền thành công!');
                        });
                    }
           });
            var arrayModule = {!! json_encode($module->pluck('id')->toArray()) !!};
            console.log(arrayModule)
            $('input.iChecked').iCheck({
                checkboxClass: 'icheckbox_square-green',
                radioClass: 'iradio_square-green',
                increaseArea: '20%' // optional
            });
            $.each(arrayModule, function( index, value ) {
                var className = 'input.checkAll_'+value;
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
            var input = 'input.checkSingle_'+module_id;
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
        });
        $('#groups_permission').select2({
            language: 'vi',
            ajax: {
                url: '{{ route("admin.users.ajaxGetSelectGroup")}}',
                dataType: 'json',
                data: function(params) {
                    var query = {
                        keyword: params.term,
                    }
                    return query;
                },
                processResults: function(json, params) {
                    var results = [];

                    if (json.data) {
                        for (i in json.data) {
                            var item = json.data[i];
                            results.push({
                                id: item.id,
                                text: item.name
                            });
                        }
                    }
                    return {
                        results: results
                    };
                },
            }
        });
        $('#groups_permission').on('select2:select', function(e) {
            var data = e.params.data;
            $.get('{{ route('admin.users.updateGroupPermission') }}', {
                id: {{$id}},
                permission:$("#groups_permission").select2("val")
            }, function(data) {
                toastr.success('Thêm nhóm quyền thành công');
            });
        });
        $('#groups_permission').on('select2:unselect', function(e) {
            var data = e.params.data;
            $.get('{{ route('admin.users.updateGroupPermission') }}', {
                id: {{$id}},
                permission:$("#groups_permission").select2("val")
            }, function(data) {
                toastr.success('Xóa nhóm quyền thành công');
            });
        });
        $('.group_check input').on('ifChecked ifUnchecked', function(event){
            console.log($('.group_check input[name="groups_permission[]"]:checked').map(function(){return $(this).val();}).get());
            var check_per = $('.group_check input[name="groups_permission[]"]:checked').map(function(){return $(this).val();}).get();
            $.get('{{ route('admin.users.updateGroupPermission') }}', {
                id: {{$id}},
                permission:check_per
            }, function(data) {
                toastr.success('Thay đổi quyền thành công');
            });
             // alert value
        });
        // -----------------------------------------


        $('.building_check input').on('ifChecked ifUnchecked', function(event){

            var check_building = $('.building_check input[name="list_building[]"]:checked').map(function(){return $(this).val();}).get();
            $.get('{{ route('admin.users.updateUserWithBuilding') }}', {
                id: {{$id}},
                permission:check_building
            }, function(data) {
                toastr.success('Cập nhập tài khoản theo tòa thành công');
            });
             // alert value
        });
    </script>

@endsection