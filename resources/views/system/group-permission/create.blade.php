@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        {{$meta_title}}
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li >Nhóm quyền</li>
        <li class="active">Thêm mới</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-body ">
            @if($errors->any())
            <em class="help-block text-red">{{$errors->first()}}</em>
            @endif
            <form id="form-users" action="{{ route('admin.system.group_permission.store') }}" method="post">
                @csrf
                <input type="hidden" name="method" value="" />
                <input type="hidden" name="status" value="" />
                <input type="hidden" name="id" value="{{@$id}}" />
                <input type="hidden" name="create_by" value="{{$create_by??$group->create_by}}" />
                <input type="hidden" name="update_by" value="{{$update_by??$group->update_by??0}}" />

                <div class="col-sm-12 col-xs-12 form-group {{ $errors->has('name') ? 'has-error': '' }}">
                    <label class="control-label">Tên nhóm quyền <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="name" placeholder="Tên nhóm quyền" value="{{  $group->name??old('name') }}" />
                    @if ($errors->has('name'))
                    <em class="help-block">{{ $errors->first('name') }}</em>
                    @endif
                </div>
                <div class="col-sm-12 col-xs-12 form-group">
                    <label class="control-label">Mô tả nhóm quyền</label>
                    <textarea name="description" id="textarea-description" cols="30" rows="5" class="form-control">{{  $group->description??old('description') }}</textarea>
                </div>

                <div class="col-sm-12 col-xs-12 form-group ">
                    <button type="submit" class="btn btn-info pull-right"> @if(isset($id)) Cập nhật @else Tạo mới @endif</button>

                </div>
            </form><!-- END #form-users -->
                <div class="clearfix"></div>
                @if(isset($data))
                    <div class="box-body">
                        <div class="box-header with-border">
                            <h3 class="box-title bold">Phân quyền nhóm</h3>
                        </div>
                        <div class="row">
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
{{--                                                                        @if($group->status !=1 )--}}
                                                                            <span class="btn-group">
                                                                                <a data-action="{{ route('admin.system.group_permission.update', $id) }}" data-module="{{ $module->id }}"
                                                                                   class="btn btn-danger" id="add-multi-permission"><i class="fa fa-check"></i> Cập nhật quyền</a>
                                                                            </span>
{{--                                                                        @endif--}}
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
                                                                            <?php
                                                                                $check_double_per = array();
                                                                            ?>
                                                                            @foreach ($module->permissions()->where('has_menu',1)->groupBy('route_name')->orderBy('route_name','asc')->get() as $key_1 => $item_1)
                                                                                    <tr valign="middle">
                                                                                        <td  style="font-weight: bold;font-size: 15px;">{{ @$item_1->title  }}</td>
                                                                                        <td><input type="checkbox" name="ids[]"
                                                                                                value="{{ $item_1->id }}"
                                                                                                class="iChecked checkSingle_{{$module->id}}"
                                                                                                @if(in_array($item_1->id, $permissionGroups)) checked @endif/>
                                                                                        </td>
                                                                                    </tr>
                                                                                    @foreach ($module->permissions()->where('has_menu',0)->groupBy('route_name')->orderBy('route_name','asc')->get() as $key_2 => $item_2)
                                                                                        @if(str_contains($item_1->route_name, 'admin.') && str_contains($item_2->route_name, 'admin.') && explode('.',$item_1->route_name)[1] == explode('.',$item_2->route_name)[1] && !in_array($item_2->route_name, $check_double_per))
                                                                                            <?php
                                                                                                array_push($check_double_per,$item_2->route_name);
                                                                                            ?>
                                                                                            <tr valign="middle">
                                                                                                <td style="padding-left: 30px;" >{{ @$item_2->title  }}</td>
                                                                                                <td><input type="checkbox" name="ids[]"
                                                                                                        value="{{ $item_2->id }}"
                                                                                                        class="iChecked checkSingle_{{$module->id}}"
                                                                                                        @if(in_array($item_2->id, $permissionGroups)) checked @endif/>
                                                                                                </td>
                                                                                            </tr>  
                                                                                        @endif
                                                                                    @endforeach
                                                                            @endforeach
                                                                            @foreach ($module->permissions()->where('has_menu',0)->groupBy('route_name')->orderBy('route_name','asc')->get() as $key_3 => $item_3)
                                                                                @if(!in_array($item_3->route_name, $check_double_per))
                                                                                    <?php
                                                                                      array_push($check_double_per,$item_3->route_name);
                                                                                    ?>
                                                                                    <tr valign="middle">
                                                                                        <td style="padding-left: 30px;" >{{ @$item_3->title  }}</td>
                                                                                        <td><input type="checkbox" name="ids[]"
                                                                                                value="{{ $item_3->id }}"
                                                                                                class="iChecked checkSingle_{{$module->id}}"
                                                                                                @if(in_array($item_3->id, $permissionGroups)) checked @endif/>
                                                                                        </td>
                                                                                    </tr>  
                                                                                @endif
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
                @endif
        </div>
    </div>
</section>
@endsection

@section('javascript')

<script>
    sidebar('users', 'users');
</script>
@if(isset($data))
    <script  type="text/javascript">
        // $(document).ready(function () {
        document.addEventListener('DOMContentLoaded', (event) => {
            var arrayModule = {!! json_encode($module->pluck('id')->toArray()) !!};
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
            //cap nhat quyen
            $(document).on('click', '#add-multi-permission', function (e) {
                var pub_group_id = $('input[name="pub_group_id"]').val();
                var module_id = $(this).attr('data-module');
                var input = 'input.checkSingle_'+module_id;
                var ids = [];
                var div = $('div.icheckbox_square-green.checked');
                div.each(function (index, value) {
                    var id = $(value).find(input).val();
                    if (id) {
                        ids.push(id);
                    }
                });
                // if (ids.length == 0) {
                //     toastr.error('Vui lòng chọn quyền để thực hiện tác vụ này');
                // } else {
                    if (!requestSend) {
                        if (!confirm('Bạn có chắc chắn cập nhật quyền cho bộ phận này?')) {
                            e.preventDefault();
                        } else {
                            requestSend = true;
                            $.ajax({
                                url: $(this).attr('data-action'),
                                type: 'POST',
                                data: {
                                    ids: ids,
                                    pub_group_id: pub_group_id,
                                    departmentID: $(this).attr('data-department'),
                                    module_id: module_id
                                },
                                success: function (response) {
                                    if (response.success == true) {
                                        toastr.success(response.message);
                                        $('input[name="pub_group_id"]').val(response.pub_group_id);
                                    } else {
                                        toastr.error(response.message);
                                        // setTimeout(() => {
                                        //     location.reload()
                                        // }, 2000);
                                    }
                                    requestSend = false;
                                }
                            })
                        }
                    }
                // }
            })
        });
    </script>
@endif
@endsection