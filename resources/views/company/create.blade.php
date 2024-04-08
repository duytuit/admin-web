@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý chung
            <small>Thêm dự án thuộc công ty</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Thêm dự án thuộc công ty</li>
        </ol>
    </section>
    <section class="content">
        <div class="box-body">
            <form action="{{ route('admin.company.urban-building.store') }}" method="POST" class="form-horizontal">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-md-12">
                        <!-- Horizontal Form -->
                        <div class="box box-info">
                            <div class="box-header with-border">
                                <h3 class="box-title">Thêm dự án</h3>
                            </div>
                            <!-- /.box-header -->
                            <!-- form start -->
                            <div class="box-body">
                                <div class="form-group {{ $errors->has('company_id') ? ' has-error' : '' }}">
                                    <label class="col-md-3 control-label">Công ty</label>
                                    <div class="col-md-6">
                                         <select name="company_id" id="company_id" class="form-control">
                                                <option value="" selected>Chọn công ty</option>
                                                @foreach ($company as $value)
                                                    <option value="{{ $value->id }}" @if(@$get_company->id == $value->id) selected @endif> {{ $value->name }}</option>
                                                @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group {{ $errors->has('urban_id') ? ' has-error' : '' }}">
                                    <label class="col-md-3 control-label">Khu đô thị</label>
                                    <div class="col-md-6"  style="display: flex;">
                                        <select name="urban_id" id="urban_id" class="form-control"> </select>
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
                                               value="{{ old('name') }}">
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
                                        <textarea class="form-control valid" id="description" name="description" rows="5">{{ old('description')}}</textarea>
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
                                        <textarea class="form-control" id="address" name="address" rows="3">{{ old('address') }}</textarea>
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
                                               value="{{old('phone') }}">
                                        @if ($errors->has('phone'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('phone') }}</strong>
                                             </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group {{ $errors->has('email') ? ' has-error' : '' }}">
                                    <label class="col-md-3 control-label">Email dự án (*)</label>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="email" name="email"
                                               value="{{ old('email') }}">
                                        @if ($errors->has('email'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('email') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                @if(Auth::user()->isadmin == 1)
                                    <div class="form-group {{ $errors->has('day_lock_cycle_name') ? ' has-error' : '' }}">
                                        <label class="col-md-3 control-label">Ngày Khóa sổ kế toán(*)</label>
                                        <div class="col-md-6">
                                            <input type="number" class="form-control valid" id="day_lock_cycle_name" min="0" value="0" max="31" name="day_lock_cycle_name"
                                                   value="{{ old('day_lock_cycle_name') }}">
                                            @if ($errors->has('day_lock_cycle_name'))
                                                <span class="help-block">
                                                    <strong>{{ $errors->first('day_lock_cycle_name') }}</strong>
                                                 </span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                <div class="form-group {{ $errors->has('template_mail') ? ' has-error' : '' }}">
                                    <label class="col-md-3 control-label">Template email</label>
                                    <div class="col-md-6">
                                         <select name="template_mail" id="template_mail" class="form-control">
                                                <option value="" selected>Chọn</option>
                                            @foreach ($template_emails as $value)
                                                <option value="{{ $value }}" @if($value == old('template_mail')) selected @endif> {{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group {{ $errors->has('vnp_secret') ? ' has-error' : '' }}">
                                    <label class="col-md-3 control-label">Hash Key</label>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="vnp_secret" name="vnp_secret"
                                               value="{{ old('vnp_secret') }}">
                                        @if ($errors->has('vnp_secret'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('vnp_secret') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                {{-- <div class="form-group {{ $errors->has('merchant_9p_id') ? ' has-error' : '' }}">
                                    <label class="col-md-3 control-label">Merchant 9Pay</label>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="merchant_9p_id" name="merchant_9p_id"
                                               value="{{ old('merchant_9p_id') }}">
                                        @if ($errors->has('merchant_9p_id'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('merchant_9p_id') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group {{ $errors->has('partnert_9p_id') ? ' has-error' : '' }}">
                                    <label class="col-md-3 control-label">Partnert 9Pay</label>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="partnert_9p_id" name="partnert_9p_id"
                                               value="{{ old('partnert_9p_id') }}">
                                        @if ($errors->has('partnert_9p_id'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('partnert_9p_id') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                </div> --}}
                                <div class="form-group {{ $errors->has('manager_id') ? ' has-error' : '' }}">
                                    <label class="col-md-3 control-label">Chọn trưởng ban quản lý (*)</label>
                                    <div class="col-md-6" style="display: flex;">
                                        <select name="manager_id" id="manager_id" class="form-control"> </select>
                                        @if ($errors->has('manager_id'))
                                            <span class="help-block">
                                                    <strong>{{ $errors->first('manager_id') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <!-- /.box-body -->
                            <div class="box-footer">
                                <a href="/admin/company" type="button"
                                   class="btn btn-default pull-left">Quay lại</a>
                                <button type="submit" class="btn btn-success pull-right">Tạo mới</button>
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
    $(document).ready(function () {
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
