@extends('backend.layouts.master')

@section('stylesheet')
<link rel="stylesheet" href="/adminLTE/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-star-rating/4.0.2/css/star-rating.min.css" />
@endsection

@section('content')

<section class="content-header">
    <h1>
        Khách hàng phân bổ
        <small>Phản hồi</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('/admin') }}"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
        <li class="active">Khách hàng phân bổ</li>
    </ol>
</section>

<section class="content">
    <form action="" method="post" id="form-save-diary" class="form-validate" autocomplete="off">
        {{ csrf_field() }}
        <div class="row">
            <div class="col-xs-8">
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="form-horizontal">
                            <input type="hidden" name="assign_id" value="{{$assign_customer->id}}" />

                            <div class="form-group">
                                <label class="col-sm-3 control-label" style="padding-top: 0px;">Họ và tên</label>
                                <div class="col-sm-9">
                                    <input type="text" name="customer_name" id="input_customer_name" value="{{ $assign_customer->customer_name }}" class="form-control" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label" style="padding-top: 0px;">Email</label>
                                <div class="col-sm-9">
                                    <input type="text" name="customer_email" id="input_customer_email" value="{{ $assign_customer->customer_email }}" class="form-control" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label" style="padding-top: 0px;">Số điện thoại</label>
                                <div class="col-sm-9">
                                    <input type="text" name="customer_phone" id="input_customer_phone" value="{{ $assign_customer->customer_phone }}" class="form-control" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label" style="padding-top: 0px;">Chiến dịch:</label>
                                <div class="col-sm-9">
                                    {{ $assign_customer->campaign->title }}</p>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label" style="padding-top: 0px;">Dự án:</label>
                                <div class="col-sm-9">
                                    <select class="form-control" name="project_id" id="select-project-edit-diary" style="width: 100%;">
                                        <option value="">Chọn dự án</option>
                                        @if(old('project_id') )
                                        @php
                                        $project = \App\Models\Campaign::getProjectById(old('project_id'));
                                        @endphp
                                        <option value="{{ old('project_id') }}" selected="">{{ $project['cb_title'] }}</option>
                                        @elseif(!empty($assign_customer->campaign->project_id))
                                        @php
                                        $project = \App\Models\Campaign::getProjectById($assign_customer->campaign->project_id);
                                        @endphp
                                        <option value="{{ $assign_customer->campaign->project_id }}" selected>{{ $project['cb_title'] }}</option>
                                        @endif
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label" style="padding-top: 0px;">Quan tâm:</label>
                                <div class="col-sm-9">
                                    <label style="margin-right: 15px;">
                                        <input type="radio" name="feedback" value="1" class="iCheck" checked />
                                        Quan tâm
                                    </label>
                                    <label>
                                        <input type="radio" name="feedback" value="-1" class="iCheck" />
                                        Không quan tâm
                                    </label>
                                </div>
                            </div>

                            <div class="form-group {{ $errors->has('cd_rating') ? 'has-error': '' }}">
                                <label class="col-sm-3 control-label">Điểm số <span class="text-danger">*</span>:</label>
                                <div class="col-sm-9">
                                    <div class="cb_rating"></div>
                                    <input id="input-1" name="cd_rating" class="rating rating-loading" data-min="0" data-max="5" data-step="1" value="5" data-size="xs">
                                    @if ($errors->has('cd_rating'))
                                    <em class="help-block">{{ $errors->first('cd_rating') }}</em>
                                    @endif
                                </div>
                            </div>


                            <div class="form-group">
                                <label class="col-sm-3 control-label">Ghi chú:</label>
                                <div class="col-sm-9">
                                    <textarea class="miniEditor form-control" name="cd_description"></textarea>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">Tiêu chí:</label>
                                <div class="col-sm-9">
                                    @foreach ($filters as $filter)
                                    <label class="control-label">{{ $filter['title'] }}:</label>
                                    <div style="padding: 15px; display: flex;">
                                        @foreach ($filter['value'] as $item)
                                        <label style="display:flex;flex:1">
                                            <input type="checkbox" name="filters[]" value="{{ $item['id'] }}" class="iCheck" />
                                            &nbsp;&nbsp;{{ $item['value'] }}
                                        </label>
                                        @endforeach
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-sm btn-success" title="Cập nhật" form="form-save-diary">
                            <i class="fa fa-save"></i>&nbsp;&nbsp;Thêm mới
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
@endsection

@section('javascript')
<script src="/adminLTE/plugins/moment/moment.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-star-rating/4.0.2/js/star-rating.min.js"></script>

<script src="/adminLTE/plugins/input-mask/jquery.inputmask.js"></script>
<script src="/adminLTE/plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
<script src="/adminLTE/plugins/input-mask/jquery.inputmask.extensions.js"></script>

{{-- TinyMCE --}}
<script src="/adminLTE/plugins/tinymce/tinymce.min.js"></script>
<script src="/adminLTE/plugins/tinymce/config.js"></script>

<script>
    $(function() {
    // Chọn dự án
    get_data_select2({
        object: '#select-project-edit-diary',
        url: '{{ route("admin.campaigns.project") }}',
        data_id: 'id',
        data_text: 'title',
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
});

// Ratting
$("input.rating").rating();

$('[data-mask]').inputmask();
sidebar('campaigns', 'assigned');
</script>
@endsection