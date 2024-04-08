@extends('backend.layouts.master')

@section('stylesheet')
<link rel="stylesheet" href="/adminLTE/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-star-rating/4.0.2/css/star-rating.min.css" />
@endsection

@section('content')

<section class="content-header">
    <h1>
        Khách hàng
        <small>Cập nhật</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('/admin') }}"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
        <li class="active">Khách hàng</li>
    </ol>
</section>

@can('view', app(App\Models\BoCustomer::class))
<section class="content">
    <form action="" method="post" id="form-edit-add-customer" class="form-validate" autocomplete="off">
        {{ csrf_field() }}
        <div class="row">
            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
                <div class="box no-border-top">
                    <div class="box-body no-padding">
                        <div class="nav-tabs-custom no-margin">
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#bo-customer" data-toggle="tab">Thông tin cơ bản</a></li>
                                @can('index', app(App\Models\CustomerDiary::class))
                                @if( $id )
                                <li class=""><a href="#customer-diary" data-toggle="tab">Nhật ký CSKH</a></li>
                                @endif
                                @endcan
                            </ul>

                            <div class="tab-content">
                                <!-- Thông tin cơ bản khách hàng -->
                                <div class="tab-pane active" id="bo-customer">
                                    @if ($errors->has('is_customer'))
                                    <div class="alert alert-danger">
                                        <ul>
                                            <li>{{ $errors->first('is_customer') }}</li>
                                        </ul>
                                    </div>
                                    @endif

                                    <div class="row">
                                        <div class="col-sm-6 col-xs-12 form-group {{ $errors->has('cb_name') ? 'has-error': '' }}">
                                            <label class="control-label">Tên khách hàng <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="cb_name" placeholder="Tên khách hàng" value="{{ $bo_customer->cb_name ?? old('cb_name') ?? ''}}" />
                                            @if ($errors->has('cb_name'))
                                            <em class="help-block">{{ $errors->first('cb_name') }}</em>
                                            @endif
                                        </div>

                                        <div class="col-sm-6 col-xs-12 form-group">
                                            <label class="control-label">Ngày sinh</label>
                                            <div class="input-group datetimepicker" data-format="DD-MM-YYYY">
                                                <input type="text" name="birthday" value="{{ $bo_customer->birthday ?? old('birthday') ?? '' }}" class="form-control" placeholder="Ngày sinh">
                                                <span class="input-group-addon btn"><i class="fa fa-calendar"></i></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-6 col-xs-12 form-group {{ $errors->has('cb_phone') ? 'has-error': '' }}">
                                            <label class="control-label">Số điện thoại <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="cb_phone" placeholder="Số điện thoại" value="{{ $bo_customer->cb_phone ?? old('cb_phone') ?? ''}}">
                                            @if ($errors->has('cb_phone'))
                                            <em class="help-block">{{ $errors->first('cb_phone') }}</em>
                                            @endif
                                        </div>

                                        <div class="col-sm-6 col-xs-12 form-group">
                                            <label class="control-label">Email</label>
                                            <input type="email" class="form-control" name="cb_email" placeholder="abc@gmail.com" value="{{ $bo_customer->cb_email ?? old('cb_email') ?? ''}}" />
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-6 col-xs-12 form-group">
                                            <label class="control-label">Số CMND</label>
                                            <input type="text" class="form-control" name="cb_id_passport" placeholder="Số CMBD/Hộ chiếu" value="{{ $bo_customer->cb_id_passport ?? old('cb_id_passport') ?? ''}}" />
                                        </div>

                                        <div class="col-sm-6 col-xs-12 form-group">
                                            <label class="control-label">Ngày cấp</label>
                                            <div class="input-group datetimepicker" data-format="DD-MM-YYYY">
                                                <input type="text" name="cmnd_date" value="{{ $bo_customer->cmnd_date ?? old('cmnd_date') ?? '' }}" class="form-control" placeholder="Ngày cấp">
                                                <span class="input-group-addon btn"><i class="fa fa-calendar"></i></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label">Nơi cấp</label>
                                        <textarea name="issued_by" rows="2" class="form-control  resize-disabled" placeholder="Nơi cấp CMND/Hộ chiếu">{{ $bo_customer->issued_by ?? old('issued_by') ?? '' }}</textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-6 col-xs-12 form-group">
                                            <label class="control-label">Tỉnh/Thành phố</label>
                                            <select id="select-city" class="form-control" name="city" style="width: 100%;">
                                                <option value="">Chọn tỉnh/Thành phố</option>
                                                @if(!empty($bo_customer) && ($bo_customer->city || old('city')) )
                                                @php
                                                $city_id = $bo_customer->city ?? old('city') ?? 0 ;
                                                $city = \App\Models\City::find($city_id);
                                                @endphp
                                                <option value="{{ $bo_customer->city }}" selected="">{{ $city->name }}</option>
                                                @endif
                                            </select>
                                            @if ($errors->has('city'))
                                            <em class="help-block">{{ $errors->first('city') }}</em>
                                            @endif
                                        </div>

                                        <div class="col-sm-6 col-xs-12 form-group">
                                            <label class="control-label">Quận/Huyện</label>
                                            <select id="select-district" class="form-control" name="district" style="width: 100%;">
                                                <option value="">Chọn quận/huyện</option>
                                                @if(!empty($bo_customer) && ($bo_customer->district || old('district')) )
                                                @php
                                                $district_id = $bo_customer->district ?? old('district') ?? 0 ;
                                                $district = \App\Models\District::find($district_id);
                                                @endphp
                                                <option value="{{ $bo_customer->district }}" selected="">{{ $district->name }}</option>
                                                @endif
                                            </select>
                                            @if ($errors->has('district'))
                                            <em class="help-block">{{ $errors->first('district') }}</em>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label">Địa chỉ liên hệ</label>
                                        <textarea name="address" rows="2" class="form-control  resize-disabled" placeholder="Địa chỉ chi tiết">{{ $bo_customer->address ?? old('address') ?? '' }}</textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-6 col-xs-12 form-group">
                                            <label class="control-label">Nhân viên</label>
                                            <select class="form-control" name="cb_staff_id[]" id="select-bo-user" style="width: 100%;" multiple>
                                                @if(old('cb_staff_id') )
                                                @foreach(old('cb_staff_id') as $staff)
                                                @php
                                                $staff = \App\Models\BoUser::findBY([['ub_id', $staff]])->first();
                                                @endphp
                                                <option value="{{ $staff->ub_id }}" selected="">{{ $staff->ub_title }} - {{ $staff->group->gb_title ?? '' }}</option>
                                                @endforeach
                                                @elseif(!empty($bo_customer->cb_staff_id))
                                                @php
                                                $staff_ids = explode(',', $bo_customer->cb_staff_id);
                                                $staffs = \App\Models\BoUser::whereIn('ub_id', $staff_ids )->get();
                                                @endphp
                                                @foreach($staffs as $staff)
                                                <option value="{{ $staff->ub_id }}" selected="">{{ $staff->ub_title }} - {{ $staff->group->gb_title ?? '' }}</option>
                                                @endforeach
                                                @endif
                                            </select>
                                        </div>

                                        <div class="col-sm-6 col-xs-12 form-group">
                                            <label class="control-label">Dự án</label>
                                            <select class="form-control" name="project_id" id="select-bo-project" style="width: 100%;">
                                                <option value="">Chọn dự án</option>
                                                @if(old('project_id') )
                                                @php
                                                $project = \App\Models\Campaign::getProjectById(old('project_id'));
                                                @endphp
                                                <option value="{{ old('project_id') }}" selected="">{{ $project['cb_title'] }}</option>
                                                @elseif(!empty($bo_customer->project_id))
                                                @php
                                                $project = \App\Models\Campaign::getProjectById($bo_customer->project_id);
                                                @endphp
                                                <option value="{{ $bo_customer->project_id }}" selected>{{ $project['cb_title'] }}</option>
                                                @endif
                                            </select>
                                        </div>

                                        {{-- <div class="col-sm-6 col-xs-12 form-group">
                                            <label class="control-label">Đơn vị</label>
                                            <select class="form-control" name="partner_id" id="select-branch" style="width: 100%;">
                                                <option value="">Chọn đơn vị</option>
                                                @if(old('partner_id') )
                                                @php
                                                $partner = \App\Models\Branch::find(old('partner_id'));
                                                @endphp
                                                <option value="{{ $partner->id }}" selected="">{{ $partner->title }}</option>
                                        @elseif(!empty($bo_customer->partner_id))
                                        @php
                                        $partner = \App\Models\Branch::find($bo_customer->partner_id);
                                        @endphp
                                        <option value="{{ $bo_customer->partner_id }}" selected>{{ $partner->title }}</option>
                                        @endif
                                        </select> --}}
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-6 col-xs-12 form-group">
                                            <label class="control-label">Nguồn khách hàng</label>
                                            <select class="form-control select2" name="cb_source" style="width: 100%;">
                                                <option value="">Chọn nguồn</option>
                                                @foreach($customer_source->config_value as $key => $source)
                                                <option value="{{ $key }}" @if($bo_customer->cb_source == $key ) selected @endif>{{ $source }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-sm-6 col-xs-12 form-group">
                                            <label class="control-label">Phản hồi</label>
                                            <div>
                                                <label>
                                                    <input type="radio" class="iCheck" value="1" name="status" {{ $bo_customer->status == 1 ? 'checked' : '' }} />
                                                    Quan tâm
                                                </label>
                                                <label>
                                                    <input type="radio" class="iCheck" value="0" name="status" {{ $bo_customer->status == 0 ? 'checked' : '' }} />
                                                    Không quan tâm
                                                </label>
                                                <label>
                                                    <input type="radio" class="iCheck" value="" name="status" {{ $bo_customer->status === null ? 'checked' : '' }} />
                                                    Bình thường
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <hr />
                                    @can('update', app(App\Models\BoCustomer::class))
                                    <button type="submit" class="btn btn-sm btn-success" title="Cập nhật" form="form-edit-add-customer">
                                        <i class="fa fa-save"></i>&nbsp;&nbsp;{{ $id ? 'Cập nhật' : 'Thêm mới'}}
                                    </button>
                                    @endcan
                                </div>

                                <!-- Thông tin nhật ký CSKH -->
                                @can('index', app(App\Models\CustomerDiary::class))
                                @if($id)
                                <div class="tab-pane" id="customer-diary">
                                    <div class="form-group">
                                        <label class="control-label">
                                            Danh sách
                                            @can('update', app(App\Models\CustomerDiary::class))
                                            <a class="btn btn-social-icon btn-dropbox btn-sm js-btn-add-edit-diary" data-diary="0" data-customer="{{ $id }}" data-toggle="modal" data-target="#edit-add-diary"><i class="fa fa-plus"></i></a>
                                            @endcan
                                        </label>
                                    </div>
                                    @foreach($diaries as $diary)
                                    <div class="panel panel-primary">
                                        <div class="panel-heading">
                                            <h4 class="panel-title">
                                                <a data-toggle="collapse" href="#{{ $diary->cd_id }}">{{ $diary->created_at->format('d-m-Y H:i:s') }}</a>
                                                <a href="javascript::" class="js-btn-add-edit-diary pull-right" title="Sửa" data-diary="{{ $diary->cd_id }}" data-customer="{{ $id }}" data-toggle="modal" data-target="#edit-add-diary"><i class="fa fa-edit"></i></a>
                                            </h4>
                                        </div>
                                        <div id="{{ $diary->cd_id }}" class="panel-collapse collapse in">
                                            <div class="form-horizontal">
                                                <div class="panel-body">
                                                    <div class="form-group">
                                                        <label class="col-sm-2 control-label" style="padding-top: 0px;">KH phản hồi</label>
                                                        <div class="col-sm-10">
                                                            <label style="margin-right: 15px;">
                                                                <input type="radio" class="iCheck" {{ $diary->status == 1 ? 'checked' : 'disabled' }}>
                                                                Quan tâm
                                                            </label>
                                                            <label>
                                                                <input type="radio" class="iCheck" {{ $diary->status == 0 ? 'checked' : 'disabled' }}>
                                                                Không quan tâm
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="col-sm-2 control-label" style="padding-top: 0px;">Dự án quan tâm <span class="text-danger">*</span></label>
                                                        <div class="col-sm-10">
                                                            <p>{{ $diary->project ? $diary->project['cb_title'] : 'Không có dự án' }} </p>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="col-sm-2 control-label">Điểm số <span class="text-danger">*</span></label>
                                                        <div class="col-sm-10" style="font-size: 12px;">
                                                            <input id="input-1" name="input-1" class="rating rating-loading" data-min="0" data-max="5" data-step="0.1" value="{{ $diary->cd_rating }}" data-size="xs" disabled="">
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="col-sm-2 control-label" style="padding-top: 0px;">Ghi chú</label>
                                                        <div class="col-sm-10">
                                                            {!! $diary->cd_description !!}
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="col-sm-2 control-label" style="padding-top: 0px;">Ngày cập nhật</label>
                                                        <div class="col-sm-10">
                                                            {{ date('d-m-Y H:i', strtotime($diary->updated_at)) }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xs-4">
                <div class="box box-primary">
                    <div class="box-body">
                        <div>
                            <a href="{{ route('admin.bo-customers.download_file', ['uuid' => '', 'file_name' => 'Import_khach_hang.xlsx']) }}" class="btn btn-primary"><i class="fa fa-download"></i>&nbsp;&nbsp;File mẫu</a>
                            <a href="javascript::" class="btn btn-warning" data-toggle="modal" data-target="#file-add-customer"><i class="fa fa-upload"></i>&nbsp;&nbsp;Tải file</a>
                        </div>
                        @if ($bo_customer->files)
                        <div class="form-group">
                            <label class="control-label">File đính kèm</label>
                            <ul>
                                <li><a href='{{ url("/admin/bo-customers/download/{$bo_customer->files['uuid']}") }}'>{{ $bo_customer->files['name'] }}</a></li>
                            </ul>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
@endcan

{{-- Modal thêm mới nhật ký CSKH --}}
@can('update', app(App\Models\CustomerDiary::class))
@if( !empty($bo_customer->id) )
<div id="edit-add-diary" class="modal fade" role="dialog">
    <div class="modal-dialog  modal-lg">
        <!-- Modal content-->
        <form action="{{ url('/admin/bo-customers/edit-add-diary') }}" method="post" id="form-add-diary" class="form-validate form-horizontal">
            {{ csrf_field() }}

            <input type="hidden" name="hashtag">

            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Thêm mới nhật ký CSKH</h4>
                </div>
                <div class="modal-body">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
                    <button type="submit" class="btn btn-primary btn-js-action" style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endcan

{{-- Modal up file khách hàng --}}
<div id="file-add-customer" class="modal fade" role="dialog">
    <div class="modal-dialog  modal-lg">
        <!-- Modal content-->
        <form action="{{ url('/admin/bo-customers/upload') }}" method="post" id="upload-file-customer" class="form-validate form-horizontal" enctype="multipart/form-data">
            @csrf

            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Tải file khách hàng</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger print-error-msg" style="display:none">
                        <ul></ul>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label" style="padding-top: 0px;">File khách hàng <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input type="file" name="import_file" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label" style="padding-top: 0px;">Chọn dự án <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <select class="form-control" id="select-project-file" name="project_id" style="width: 100%">
                                <option value="">Chọn dự án</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label" style="padding-top: 0px;">Chọn nguồn</label>
                        <div class="col-sm-9">
                            <select class="form-control select2" id="cb-source" name="cb_source" style="width: 100%">
                                <option value="">Chọn nguồn</option>
                                @foreach($customer_source->config_value as $key => $customer_source)
                                <option value="{{ $key }}">{{ $customer_source }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label" style="padding-top: 0px;">Phân bổ cho</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="select-user" name="cb_staff_id[]" style="width: 100%" multiple>
                                <option value="">Chọn nhân viên</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
                    <button class="btn btn-primary btn-upload-file" style="margin-right: 5px;"><i class="fa fa-upload"></i>&nbsp;&nbsp;Tải lên</button>
                    <input type="submit" class="btn-submit-file hidden" />
                </div>
            </div>
        </form>
    </div>

</div>

@endsection

@section('javascript')
<script src="/adminLTE/plugins/moment/moment.min.js"></script>
<script src="/adminLTE/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-star-rating/4.0.2/js/star-rating.min.js"></script>

<script src="/adminLTE/plugins/input-mask/jquery.inputmask.js"></script>
<script src="/adminLTE/plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
<script src="/adminLTE/plugins/input-mask/jquery.inputmask.extensions.js"></script>

<script>
    $(function() {
    // Chọn tỉnh/ thành phố
    get_data_select2({
        object: '#select-city',
        url: '{{ url("/admin/cities/ajax-get-city") }}',
        data_id: 'code',
        data_text: 'name',
        title_default: 'Chọn tỉnh/thành phố'
    });

    // Chọn đơn vị
    get_data_select2({
        object: '#select-branch',
        url: '{{ url("/admin/bo-customers/ajax/get-all-branches") }}',
        data_id: 'id',
        data_text: 'title',
        title_default: 'Chọn đơn vị'
    });

    // Chọn dự án cho khách hàng
    get_data_select2({
        object: '#select-bo-project',
        url: '{{ route("admin.campaigns.project") }}',
        data_id: 'id',
        data_text: 'title',
        title_default: 'Chọn dự án'
    });

    // Chọn dự án khi tải file
    get_data_select2({
        object: '#select-project-file',
        url: '{{ url("/admin/bo-customers/ajax/get-all-project") }}',
        data_id: 'cb_id',
        data_text: 'cb_title',
        title_default: 'Chọn dự án'
    });

    function get_data_select2(options) {
        $(options.object).select2({
            ajax: {
                url: options.url,
                dataType: 'json',
                data: function(params) {
                    var query = {
                        search: params.term,
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

    // Chọn nhân viên
    get_data_select_user({
        object: '#select-bo-user',
        data_id: 'ub_id',
        data_text1: 'ub_title',
        data_text2: 'gb_title',
        title_default: 'Chọn nhân viên'
    });

    get_data_select_user({
        object: '#select-user',
        data_id: 'ub_id',
        data_text1: 'ub_title',
        data_text2: 'gb_title',
        title_default: 'Chọn nhân viên'
    });

    function get_data_select_user(options) {
        $(options.object).select2({
            ajax: {
                url: '{{ url("/admin/bo-customers/get-user-group") }}',
                dataType: 'json',
                data: function(params) {
                    var query = {
                        search: params.term,
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
                            text: item[options.data_text1] + ' - ' + item[options.data_text2]
                        });
                    }
                    return {
                        results: results,
                    };
                },
            }
        });
    }

    //Chọn quận huyện
    $('#select-district').select2({
        ajax: {
            url: '{{ url("/admin/branches/ajax/address") }}',
            dataType: 'json',
            data: function(params) {
                var city = $('#select-city').val();
                var query = {
                    search: params.term,
                    city: city
                }
                return query;
            },
            processResults: function(data, params) {
                var results = [];

                for (i in data) {
                    var item = data[i];
                    results.push({
                        id: item.code,
                        text: item.name
                    });
                }
                return {
                    results: results
                };
            },
        }
    });

});

// Ratting
$("input.rating").rating();

// Validation
$(".btn-js-action").click(function(e) {
    e.preventDefault();

    var _token = $("[name='_token']").val();
    var customer_id = $("[name='diary[cd_customer_id]']").val();
    var project_id = $("[name='diary[project_id]']").val();
    var status = $("[name='diary[status]']").val();
    var cd_rating = $("[name='diary[cd_rating]']").val();
    var cd_description = $("[name='diary[cd_description]']").val();

    $.ajax({
        url: "{{ url('/admin/bo-customers/validator-add-diary') }}",
        type: 'POST',
        data: {
            _token: _token,
            cd_customer_id: customer_id,
            project_id: project_id,
            status: status,
            cd_rating: cd_rating,
            cd_description: cd_description,
        },
        success: function(data) {
            if ($.isEmptyObject(data.error_branches)) {
                var hash = location.hash;
                $('input[name="hashtag"]').val(hash);
                $('#form-add-diary').submit();
            } else {
                printErrorMsg(data.error_branches);
            }
        }
    });
});

$(".btn-upload-file").click(function(e) {
    e.preventDefault();

    var _token = $("[name='_token']").val();
    var cb_staff_id = $("#select-user").val();
    var cb_source = $("#cb-source").val();
    var project_id = $("#select-project-file").val();
    var import_file = $("[name='import_file'").val();

    $.ajax({
        url: "{{ url('/admin/bo-customers/validator-upload') }}",
        type: 'POST',
        data: {
            _token: _token,
            cb_staff_id: cb_staff_id,
            project_id: project_id,
            cb_source: cb_source,
            import_file: import_file,
        },
        success: function(data) {
            if ($.isEmptyObject(data.error_upload)) {
                $('#upload-file-customer').submit();
            } else {
                printErrorMsg(data.error_upload);
            }
        }
    });
});

function printErrorMsg(msg) {
    $(".print-error-msg").find("ul").html('');
    $(".print-error-msg").css('display', 'block');
    $.each(msg, function(key, value) {
        $(".print-error-msg").find("ul").append('<li>' + value + '</li>');
    });
}

$('.js-btn-add-edit-diary').click(function() {
    $(".print-error-msg").find("ul").html('');
    $(".print-error-msg").css('display', 'none');

    var diary_id = $(this).data('diary');
    var customer_id = $(this).data('customer');
    $.get('{{ url("/admin/bo-customers/ajax-edit-diary") }}', {
        diary_id: diary_id,
        customer_id: customer_id
    }, function(data) {
        $('#edit-add-diary .modal-body').html(data);
    });
});
$('[data-mask]').inputmask();
sidebar('bo-customers', 'add');
</script>
@endsection