
<div class="box-body">
    <div class="row box box-default">
        <div class="box-header with-border">
            <h3 class="box-title bold">Nhóm quyền bộ phận</h3>
        </div>
        <div class="col-sm-12">
            <div class="col-sm-6">
                <input type="hidden" value="{{ @$department->pub_group_id }}" name="pub_group_id">
                <div class="row">
                    @if(isset($listGroupsPermissions))
                        @foreach ($listGroupsPermissions as $item)
                            <div class="col-sm-8">
                                <div class="form-group">
                                    <label for="group_{{ $item->id}}">{{ $item->name }}</label>
                                </div>
                            </div>
                            <div class="col-sm-2 group_check">
                                <input type="checkbox" name="groups_permission[]" value="{{ $item->id }}" id="group_{{ $item->id}}"  data-department="{{ $department->id }}" class="iChecked checkSingle" @if(in_array($item->id, $groupSelect)) checked @endif/>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="row hidden">
            <div class="col-md-8">
                <div class="box box-solid">
                    <!-- /.box-header -->
                    <div class="box-body">
                        <input type="hidden" value="{{ @$department->pub_group_id }}" name="pub_group_id">
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
                                                            <a data-action="{{ route('admin.department.group-permission') }}" data-department="{{ $department->id }}" data-module="{{ $module->id }}"
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
                                                        @if($module->permissions()->whereIn('id', $checkPermission)->count() > 0)
                                                            @foreach ($module->permissions()->whereIn('id', $checkPermission)->orderBy('route_name', 'asc')->get() as $item)
                                                                <tr valign="middle">
                                                                    <td>{{ @$item->title  }}</td>
                                                                    <td><input type="checkbox" name="ids[]"
                                                                               value="{{ $item->id }}"
                                                                               class="iChecked checkSingle_{{$module->id}}"
                                                                               @if(in_array($item->id, $permission_ids)) checked @endif
                                                                               @if(in_array($item->id, $permission_ids) && !in_array($item->id, $permissionUser)) disabled @endif/>
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
        <div class="box-footer">
            <a href="{{ route('admin.department.index') }}" type="button" class="btn btn-default pull-left">Quay
                lại</a>
        </div>
    </div>
</div>
<script>

</script>
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
                if (ids.length == 0) {
                    toastr.error('Vui lòng chọn quyền để thực hiện tác vụ này');
                } else {
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
                }
            });
            $('#groups_permission').select2({
                language: 'vi',
                ajax: {
                    url: '{{ route("admin.department.ajaxGetSelectGroup")}}',
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
                $.get('{{ route('admin.department.updateGroupPermission') }}', {
                    id: {{$id}},
                    permission:$("#groups_permission").select2("val")
                }, function(data) {
                    toastr.success('Thêm nhóm quyền thành công');
                });
            });
            $('#groups_permission').on('select2:unselect', function(e) {
                var data = e.params.data;
                $.get('{{ route('admin.department.updateGroupPermission') }}', {
                    id: {{$id}},
                    permission:$("#groups_permission").select2("val")
                }, function(data) {
                    toastr.success('Xóa nhóm quyền thành công');
                });
            });
            $('.group_check input').on('ifChecked ifUnchecked', function(event){
                console.log($('.group_check input[name="groups_permission[]"]:checked').map(function(){return $(this).val();}).get());
                var check_per = $('.group_check input[name="groups_permission[]"]:checked').map(function(){return $(this).val();}).get();
                $.post('{{ route('admin.department.group-permission') }}', {
                    id: {{$id}},
                    pub_group_id: $('input[name="pub_group_id"]').val(),
                    departmentID: $(this).attr('data-department'),
                    permission:check_per
                }, function(data) {
                    toastr.success('Thay đổi quyền thành công');
                });
                // alert value
            });
        });


    </script>