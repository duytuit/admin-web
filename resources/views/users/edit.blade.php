@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        {{$meta_title}}
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li >Nhân viên</li>
        <li class="active">Cập nhập tài khoản</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-body ">
            @if($errors->any())
            <em class="help-block text-red">{{$errors->first()}}</em>
            @endif
            <form id="form-users" action="{{ route('admin.users.update',['id' => $user->id]) }}" method="put">
                
                <div class="col-sm-12 col-xs-12 form-group {{ $errors->has('email') ? 'has-error': '' }}">
                    <label class="control-label">Email đăng nhập <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="email" placeholder="Email đăng nhập" value="{{ $user->email ?? old('email') ?? ''}}" />
                    @if ($errors->has('email'))
                    <em class="help-block">{{ $errors->first('email') }}</em>
                    @endif
                </div>

                <div class="col-sm-12 col-xs-12 form-group">
                    <label class="control-label">Số điện thoại</label>
                    <input type="text" class="form-control" name="mobile" placeholder="Số điện thoại" value="{{ $user->mobile ?? old('mobile') ?? ''}}" />
                </div>

                <div class="col-sm-12 col-xs-12 form-group">
                    <label class="control-label">Tên nhân viên</label>
                    <input type="text" class="form-control" name="display_name" placeholder="Tên nhân viên" value="{{ @$user->BDCprofileV2->display_name ?? old('display_name') ?? ''}}" />
                </div>

                <div class="col-sm-12 col-xs-12 form-group">
                    <label class="control-label">Mã nhân viên</label>
                    <input type="text" class="form-control" name="staff_code" placeholder="Mã nhân viên" value="{{ @$user->BDCprofileV2->staff_code ?? old('staff_code') ?? ''}}" />
                </div>

                <div class="col-sm-12 col-xs-12 form-group">
                    <label class="control-label">Chứng minh thư/ hộ chiếu</label>
                    <input type="text" class="form-control" name="cmt" placeholder="Chứng minh thư/ hộ chiếu" value="{{ @$user->BDCprofileV2->cmt ?? old('cmt') ?? ''}}" />
                </div>

                <div class="col-sm-12 col-xs-12 form-group">
                    <label class="control-label">Nơi cấp</label>
                    <input type="text" class="form-control" name="cmt_address" placeholder="Nơi cấp" value="{{ @$user->BDCprofileV2->cmt_address ?? old('cmt_address') ?? ''}}" />
                </div>
                @if(Auth::user()->isadmin == 1)
                    <div class="col-sm-12 col-xs-12 form-group">
                        <div class="col-md-6">
                            @php
                                $company_id = 0;
                            @endphp
                            @foreach($buildings as $key => $value)
                                @if($company_id != $value->company_id)
                                    @php
                                        $company = \App\Models\Building\Company::find($value->company_id);
                                    @endphp
                                    <div><h4>{{@$company->name}}</h4></div>
                                    @php
                                        $company_id = $value->company_id;
                                    @endphp
                                @endif
                                @php
                                    $user_infor = \App\Models\PublicUser\UserInfo::where('bdc_building_id',$value->id)->where('pub_user_id',$user->id)->where('status',1)->where('type',2)->first();
                                @endphp
                               <div class="col-md-12">
                                    <div class="col-sm-8">
                                        <div class="form-group">
                                            <label for="check_per_{{ $value->id }}">{{ $value->name }}</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-2 group_check">
                                        <input type="checkbox" name="building_ids[]" {{@$user_infor ? 'checked' :''}} value="{{ $value->id }}" class="iChecked checkSingle"/>
                                    </div>
                               </div>
                            @endforeach
                        </div>
                        <div class="col-md-6">
                            <div class="box-body">
                                @if(\Auth::user()->isadmin == 1)
                                    <div class="box-header with-border">
                                        <h3 class="box-title bold">Quyền supper hệ thống <input type="checkbox"  {{ @$user->isadmin != 1 ? '' : 'checked' }} class="icheckbox_square-green iradio_square-green" name="IsAdmin"></h3>
                                    </div>
                                @else
                                    <div class="box-header with-border">
                                        <h3 class="box-title bold">Nhóm quyền người dùng</h3>
                                    </div>
                                @endif
                                <div class="col-sm-12">
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
                                    <div class="col-sm-12">
                                        <div class="row list_group_check">
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
                        </div>
                    </div>
                @endif
                <div class="col-sm-12 col-xs-12 form-group ">
                    <button type="submit" class="btn btn-info pull-right">Cập Nhập</button>

                </div>

            </form><!-- END #form-users -->
        </div>
    </div>
</section>
@endsection

@section('javascript')

<script>
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
        var arrayModule = {!! json_encode($data->pluck('id')->toArray()) !!};
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
    $('.list_group_check .group_check input').on('ifChecked ifUnchecked', function(event){
        console.log($('.list_group_check .group_check input[name="groups_permission[]"]:checked').map(function(){return $(this).val();}).get());
        var check_per = $('.list_group_check .group_check input[name="groups_permission[]"]:checked').map(function(){return $(this).val();}).get();
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