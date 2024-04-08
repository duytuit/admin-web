@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý chung
            <small>Chỉnh sửa thông tin dự án</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Chỉnh sửa thông tin dự án</li>
        </ol>
    </section>
    <section class="content">
        <div class="box-body">
            <form action="{{ route('admin.company.urban-building.update', $building->id) }}" method="POST" class="form-horizontal">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-md-12">
                        <!-- Horizontal Form -->
                        <div class="box box-info">
                            <div class="box-header with-border">
                                <h3 class="box-title">Sửa thông tin dự án: </h3>
                                <h4 class="box-title"> <strong>{{ @$company->name }}</strong></h4>
                            </div>
                            <!-- /.box-header -->
                            <!-- form start -->
                            <div class="box-body">
                                <input type="hidden" name="id" value="{{ @$building->id }}">
                                <div class="form-group {{ $errors->has('company_id') ? ' has-error' : '' }}">
                                    <label class="col-md-3 control-label">Công ty (*)</label>
                                    <div class="col-md-6" style="display: flex;">
                                        <select name="company_id" id="company_id" class="form-control"> 
                                            @foreach ($get_company as $value)
                                                    <option value="{{ $value->id }}" @if(@$company->id == $value->id) selected @endif> {{ $value->name }}</option>
                                             @endforeach
                                        </select>
                                        @if ($errors->has('company_id'))
                                            <span class="help-block">
                                                    <strong>{{ $errors->first('company_id') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group {{ $errors->has('urban_id') ? ' has-error' : '' }}">
                                    <label class="col-md-3 control-label">Khu đô thị (*)</label>
                                    <div class="col-md-6">
                                         <select name="urban_id" id="urban_id" class="form-control">
                                                <option value="" selected>Khu đô thị</option>
                                                @if(@$urban)
                                                    <option value="{{$urban->id}}" selected>{{$urban->name}}</option>
                                                @endif
                                        </select>
                                        @if ($errors->has('urban_id'))
                                            <span class="help-block">
                                                    <strong>{{ $errors->first('urban_id') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group {{ $errors->has('building_code_manage') ? ' has-error' : '' }}">
                                    <label class="col-md-3 control-label">Mã dự án (*)</label>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control valid" id="building_code_manage" name="building_code_manage"
                                               value="{{ old('building_code_manage') ?? @$building->building_code_manage }}">
                                        @if ($errors->has('building_code_manage'))
                                            <span class="help-block">
                                                 <strong>{{ $errors->first('building_code_manage') }}</strong>
                                           </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group {{ $errors->has('building_code') ? ' has-error' : '' }}">
                                    <label class="col-md-3 control-label">Mã ban vận hành (*)</label>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control valid" id="building_code" name="building_code"
                                               value="{{ old('building_code') ?? @$building->building_code }}">
                                        @if ($errors->has('building_code'))
                                            <span class="help-block">
                                                 <strong>{{ $errors->first('building_code') }}</strong>
                                           </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group {{ $errors->has('name') ? ' has-error' : '' }}">
                                    <label class="col-md-3 control-label">Tên dự án (*)</label>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control valid" id="name" name="name"
                                               value="{{ old('name') ?? @$building->name }}">
                                        @if ($errors->has('name'))
                                            <span class="help-block">
                                                 <strong>{{ $errors->first('name') }}</strong>
                                           </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group {{ $errors->has('description') ? ' has-error' : '' }}">
                                    <label class="col-md-3 control-label">Mô tả</label>
                                    <div class="col-md-6">
                                    <textarea class="form-control valid" id="description" name="description"
                                              rows="5">{{ old('description') ?? @$building->description }}</textarea>
                                        @if ($errors->has('description'))
                                            <span class="help-block">
                                        <strong>{{ $errors->first('description') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group {{ $errors->has('address') ? ' has-error' : '' }}">
                                    <label class="col-md-3 control-label">Địa chỉ (*)</label>
                                    <div class="col-md-6">
                                    <textarea class="form-control" id="address" name="address"
                                              rows="3">{{ old('address') ?? @$building->address }}</textarea>
                                        @if ($errors->has('address'))
                                            <span class="help-block">
                                        <strong>{{ $errors->first('address') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group {{ $errors->has('phone') ? ' has-error' : '' }}">
                                    <label class="col-md-3 control-label">Mobile</label>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="phone" name="phone"
                                               value="{{old('phone') ?? @$building->phone }}">
                                        @if ($errors->has('phone'))
                                            <span class="help-block">
                                        <strong>{{ $errors->first('phone') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group {{ $errors->has('email') ? ' has-error' : '' }}">
                                    <label class="col-md-3 control-label">Email (*)</label>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="email" name="email"
                                               value="{{ old('email') ?? @$building->email }}">
                                    </div>
                                    @if ($errors->has('email'))
                                        <span class="help-block">
                                    <strong>{{ $errors->first('email') }}</strong>
                                </span>
                                    @endif
                                </div>

                                 <div class="form-group {{ $errors->has('template_mail') ? ' has-error' : '' }}">
                                    <label class="col-md-3 control-label">Template email</label>
                                    <div class="col-md-6">
                                         <select name="template_mail" id="template_mail" class="form-control">
                                                <option value="" selected>Chọn</option>
                                            @foreach ($template_emails as $value)
                                                <option value="{{ $value }}" @if($value == @$building->template_mail) selected @endif> {{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group {{ $errors->has('vnp_secret') ? ' has-error' : '' }}">
                                    <label class="col-md-3 control-label">Hash Key</label>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="vnp_secret" name="vnp_secret"
                                               value="{{ old('vnp_secret') ?? @$building->vnp_secret }}">
                                        @if ($errors->has('vnp_secret'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('vnp_secret') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group {{ $errors->has('manager_id') ? ' has-error' : '' }}">
                                    <label class="col-md-3 control-label">Chọn trưởng ban quản lý (*)</label>
                                    <div class="col-md-6"  style="display: flex;">
                                        <select name="manager_id" id="manager_id" class="form-control">
                                            @if(@$get_manager)
                                                <option value="{{$get_manager->pub_user_id}}" selected>{{$get_manager->display_name}}</option>
                                            @endif
                                        </select>
                                         @if ($errors->has('manager_id'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('manager_id') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                @if(\Auth::user()->isadmin == 1)
                                    <div class="form-group {{ $errors->has('day_lock_cycle_name') ? ' has-error' : '' }}">
                                        <label class="col-md-3 control-label">Ngày khóa sổ kế toán (*)</label>
                                        <div class="col-md-6">
                                            <input type="number" class="form-control valid" id="day_lock_cycle_name" min="0" max="31" name="day_lock_cycle_name" value="{{  @$building->day_lock_cycle_name }}">
                                            @if ($errors->has('day_lock_cycle_name'))
                                                <span class="help-block">
                                            <strong>{{ $errors->first('day_lock_cycle_name') }}</strong>
                                        </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('debit_active') ? ' has-error' : '' }}">
                                        <label class="col-md-3 control-label">Active sửa hạn thanh toán</label>
                                        <div class="col-md-6">
                                                <select name="debit_active" id="debit_active" class="form-control">
                                                    <option value="0" @if(@$building->debit_active == 0) selected @endif>InActive</option>
                                                    <option value="1" @if(@$building->debit_active == 1) selected @endif>Active</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('debit_active') ? ' has-error' : '' }}">
                                        <label class="col-md-3 control-label">Hiển thị menu kế toán v2</label>
                                        <div class="col-md-6">
                                                <select name="config_menu" id="config_menu" class="form-control">
                                                    <option value="1" @if(@$building->config_menu == 1) selected @endif>InActive</option>
                                                    <option value="2" @if(@$building->config_menu == 2) selected @endif>Active</option>
                                            </select>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <!-- /.box-body -->
                            <div class="box-footer">
                                <a href="{{ route('admin.company.urban-building.index') }}" type="button"
                                   class="btn btn-default pull-left">Quay lại</a>
                                <button type="submit" class="btn btn-success pull-right">Cập nhật</button>
                            </div>
                            <!-- /.box-footer -->
                        </div>
                        <!-- /.box -->
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection
@section('javascript')
<script>
    $("#company_id").on('change', function() {
            if ($("#company_id").val()) {
                $("#urban_id").val('').trigger('change')
                $("#manager_id").val('').trigger('change')
                get_data_select_urban({
                    object: '#urban_id',
                    url: '{{url('admin/ajax/ajaxGetUrbanByCompany')}}',
                    data_id: 'id',
                    data_text: 'name',
                    title_default: 'Chọn khu đô thị'
                });
                get_data_select_manager({
                    object: '#manager_id',
                    url: '{{ url('admin/ajax/ajaxGetStaffByCompany') }}',
                    data_id: 'pub_user_id',
                    data_text: 'display_name',
                    title_default: 'Chọn trưởng ban quản lý'
                });
            }
    });
     get_data_select_urban({
                object: '#urban_id',
                url: '{{url('admin/ajax/ajaxGetUrbanByCompany')}}',
                data_id: 'id',
                data_text: 'name',
                title_default: 'Chọn khu đô thị'
            });
    get_data_select_manager({
        object: '#manager_id',
        url: '{{ url('admin/ajax/ajaxGetStaffByCompany') }}',
        data_id: 'pub_user_id',
        data_text: 'display_name',
        title_default: 'Chọn trưởng ban quản lý'
    });
    function get_data_select_urban(options) {
        $(options.object).select2({
            ajax: {
                url: options.url,
                dataType: 'json',
                data: function(params) {
                    var query = {
                        search: params.term,
                        company_id: $('#company_id').val(),
                    }
                    return query;
                },
                processResults: function(json, params) {
                    var results = [{
                        id: '',
                        text: options.title_default
                    }];

                    for (i in json.data) {
                        var item = json.data[i];
                        results.push({
                            id: item[options.data_id],
                            text: item[options.data_text]
                        });
                    }
                    return {
                        results: results,
                    };
                },
                minimumInputLength: 3,
            }
        });
    }
  

    function get_data_select_manager(options) {
        $(options.object).select2({
            ajax: {
                url: options.url,
                dataType: 'json',
                data: function(params) {
                    var query = {
                        search: params.term,
                        company_id: $('#company_id').val(),
                    }
                    return query;
                },
                processResults: function(json, params) {
                    var results = [{
                        id: '',
                        text: options.title_default
                    }];

                    for (i in json.data) {
                        var item = json.data[i];
                        results.push({
                            id: item[options.data_id],
                            text: item[options.data_text]+' - '+item['email']
                        });
                    }
                    return {
                        results: results,
                    };
                },
                minimumInputLength: 3,
            }
        });
    }
</script>   
@endsection