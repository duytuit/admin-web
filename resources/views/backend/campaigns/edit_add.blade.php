@extends('backend.layouts.master')
@section('content')

<section class="content-header">
    <h1>
        Chiến dịch
        <small>Cập nhật</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('/admin') }}"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
        <li class="active">Chiến dịch</li>
    </ol>
</section>

<section class="content">
    <form action='{{ url("admin/campaigns/{$id}/save") }}' method="post" id="form-edit-add-customer" class="form-validate" autocomplete="off" enctype="multipart/form-data">
        {{ csrf_field() }}
        <div class="row">
            <div class="col-xs-8">
                <div class="box no-border-top">
                    <div class="box-body no-padding">
                        <div class="nav-tabs-custom no-margin">
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#detail-campaign" data-toggle="tab">Thông tin cơ bản</a></li>
                                @if($id)
                                <li class=""><a href="#list-customer" data-toggle="tab">Khách hàng</a></li>
                                @endif
                            </ul>

                            <div class="tab-content">
                                <!-- Thông tin cơ bản chiến dịch-->
                                <div class="tab-pane active" id="detail-campaign">
                                    {{-- Hiện thị thông báo lỗi --}}
                                    @if(session()->get('errors_user'))
                                    @php
                                    $errors_user = session()->get('errors_user');
                                    @endphp
                                    <div class="alert alert-danger">
                                        <p>{{ $errors_user['messages'] }}</p>
                                        <ul>
                                            @foreach ($errors_user['data'] as $error)
                                            <li>
                                                Tk: {{ $error->tvc_account }} - VT: dòng {{ $error->stt }}
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif

                                    @if(session()->get('duplicate'))
                                    @php
                                    $duplicate = session()->get('duplicate');
                                    @endphp
                                    <div class="alert alert-warning">
                                        <p>{{ $duplicate['success'] }}</p>
                                        <ul>
                                            @foreach ($duplicate['data'] as $key => $value)
                                            @if ($value)
                                            <p>Thông tin {{ $key == 'staff' ? 'nhân viên' : 'khách hàng' }} bị trùng</p>
                                            @foreach ($value as $index => $item)
                                            @if($key == 'staff')
                                            <li>
                                                Tk: {{ $item->tvc_account }} - VT: dòng {{ $index }}
                                            </li>
                                            @else
                                            <li>
                                                Tk: Tên: {{ $item['ten_khach_hang'] }}, Email: {{$item['email']}}, SĐT: {{$item['so_dien_thoai']}} - VT: dòng {{ $index }}
                                            </li>
                                            @endif
                                            @endforeach
                                            @endif
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif

                                    <div class="row">
                                        <div class="col-sm-6 col-xs-12 form-group {{ $errors->has('title') ? 'has-error': '' }}">
                                            <label class="control-label">Tên chiến dịch <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="title" placeholder="Tên chiến dịch" value="{{ $campaign->title ?? old('title') ?? ''}}" />
                                            @if ($errors->has('title'))
                                            <em class="help-block">{{ $errors->first('title') }}</em>
                                            @endif
                                        </div>

                                        <div class="col-sm-6 col-xs-12 form-group {{ $errors->has('project_id') ? 'has-error': '' }}">
                                            <label class="control-label">Dự án <span class="text-danger">*</span></label>
                                            <select class="form-control" name="project_id" id="select-bo-project" style="width: 100%;">
                                                <option value="">Chọn dự án</option>
                                                @if(old('project_id') )
                                                @php
                                                $project = \App\Models\Campaign::getProjectById(old('project_id'));
                                                @endphp
                                                <option value="{{ old('project_id') }}" selected="">{{ $project['cb_title'] }}</option>
                                                @elseif(!empty($campaign->project_id))
                                                @php
                                                $project = \App\Models\Campaign::getProjectById($campaign->project_id);
                                                @endphp
                                                <option value="{{ $campaign->project_id }}" selected>{{ $project['cb_title'] }}</option>
                                                @endif
                                            </select>
                                            @if ($errors->has('project_id'))
                                            <em class="help-block">{{ $errors->first('project_id') }}</em>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-6 col-xs-12 form-group">
                                            <label class="control-label" style="padding-top: 0px;">File nhân viên - khách hàng</label>
                                            <input type="file" name="file_user_cus" />
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label">Ghi chú</label>
                                        <textarea name="description" rows="10" class="miniEditor form-control">{{ $campaign->description ?? old('info') ?? $campaign->description ?? '' }}</textarea>
                                    </div>

                                    <hr />
                                    @can('update', app(App\Models\Campaign::class))
                                    @if(!$id)
                                    <button type="submit" class="btn btn-sm btn-success" title="Cập nhật" form="form-edit-add-customer">
                                        <i class="fa fa-save"></i>&nbsp;&nbsp;Thêm mới
                                    </button>
                                    @endif
                                    @endcan
                                </div>

                                {{-- Danh sách khách hàng phân bổ --}}
                                @if($id)
                                <div class="tab-pane" id="list-customer">
                                    @if(!empty($customers->items()))
                                    <form action='{{ url("/admin/campaign-assign/action") }}' method="post" id="form-assigned-customer-action">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="method" value="" />
                                        <input type="hidden" name="status" value="" />
                                        <input type="hidden" name="cb_staff_ids[]" value="" />

                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered table-hover">
                                                <thead>
                                                    <tr class="bg-primary">
                                                        <th width="20px">
                                                            <input class="iCheck checkAll" type="checkbox" data-target=".checkSingle" />
                                                        </th>
                                                        <th width="30">#</th>
                                                        <th width="">Khách hàng</th>
                                                        <th width="10%">SĐT</th>
                                                        <th width="15%">Email</th>
                                                        <th width="12%">Nguồn khách hàng</th>
                                                        <th width="10%">Mức độ</th>
                                                        <th width="15%">Sale</th>
                                                        <th width="10%">Thao tác</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($customers as $customer)
                                                    <tr>
                                                        <td><input type="checkbox" class="iCheck checkSingle" value="{{$customer->id}}" name="ids[]" /></td>
                                                        <td>{{ $customer->id }}</td>
                                                        <td>
                                                            <a href='javascript:;'> {{ $customer->customer_name }} </a>
                                                        </td>
                                                        <td>{{ $customer->customer_phone }} </td>
                                                        <td>{{ $customer->customer_email }} </td>
                                                        <td class="text-uppercase">{{ $customer->source ?: '' }} </td>
                                                        <td>
                                                            @if ($customer->status !== null)
                                                            <a href="javascript:;" class="btn-status label label-sm label-{{ $customer->status == 1 ? 'success' : 'danger' }}">
                                                                {{ $customer->status == 1 ? 'Quan tâm' : 'Không quan tâm' }}
                                                            </a>
                                                            @endif

                                                        </td>
                                                        <td>{{ $customer->staff->ub_account_tvc ?? 'Sale' }} </td>
                                                        <td>
                                                            @if ($customer->check_diary === 0)
                                                            <a href='{{ route("admin.campaign_assign.edit_diary", ['id' => $customer->id]) }}' type="button" class="btn btn-sm btn-warning" title="Phản hồi" data-diary="0" data-assigned="{{ $customer->id }}">
                                                                <i class="fa fa-weixin"></i>
                                                            </a>
                                                            @else
                                                            <a href='javascript:;' class="btn btn-sm btn-success js-btn-add-edit-diary" title="Xem phản hồi" data-diary='{{$customer->campaign->diary_id}}' data-toggle="modal" data-target="#campaign-assign-diary">
                                                                <i class="fa fa-eye"></i>
                                                            </a>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <input type="submit" class="js-submit-form-index hidden" value="" />
                                    </form>
                                    <hr />
                                    <div class="pull-left link-paginate">
                                        <span class="record-total">Tổng: {{ $customers->total() }} bản ghi</span>
                                    </div>
                                    <div class="pull-right link-paginate">
                                        {{ $customers->fragment('list-customers')->links() }}
                                    </div>
                                    <div class="clearfix"></div>
                                    @else
                                    Hiện chưa có chi nhánh nào.
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-4">
                <div class="box box-primary">
                    <div class="box-body">
                        <div>
                            <a href="{{ route('admin.bo-customers.download_file', ['uuid' => '', 'file_name' => 'Import_khach_hang_nhan_vien.xlsx']) }}" class="btn btn-primary"><i class="fa fa-download"></i>&nbsp;&nbsp;File mẫu</a>
                            {{-- <a href="javascript::" class="btn btn-warning" data-toggle="modal" data-target="#file-add-customer"><i class="fa fa-upload"></i>&nbsp;&nbsp;Tải file</a> --}}
                        </div>
                        @if ($campaign->file_user_cus)
                        <div class="form-group">
                            <label class="control-label">File đính kèm</label>
                            <ul>
                                <li><a href='{{ url("/admin/campaigns/download/{$campaign->file_user_cus['uuid']}") }}'>{{ $campaign->file_user_cus['name'] }}</a></li>
                            </ul>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>

<div id="campaign-assign-diary" class="modal fade" role="dialog">
    <div class="modal-dialog  modal-lg">
        <!-- Modal content-->
        <div class="modal-content form-horizontal">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Thông tin phản hồi</h4>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('stylesheet')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-star-rating/4.0.2/css/star-rating.min.css" />
@endsection


@section('javascript')
<script src="/adminLTE/plugins/moment/moment.min.js"></script>

<!-- TinyMCE -->
<script src="/adminLTE/plugins/tinymce/tinymce.min.js"></script>
<script src="/adminLTE/plugins/tinymce/config.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-star-rating/4.0.2/js/star-rating.min.js"></script>

<script>
    $(function() {
    // Chọn dự án
    get_data_select2({
        object: '#select-bo-project',
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

$('.js-btn-add-edit-diary').click(function() {
    var diary_id = $(this).data('diary');
    $.get('{{ url("/admin/campaign-assign/ajax-edit-diary") }}', {
        diary_id: diary_id,
    }, function(data) {
        $('#campaign-assign-diary .modal-body').html(data);
    });
});

// Ratting
$("input.rating").rating();
</script>

<script>
    sidebar('campaigns', 'add-campaign');
</script>
@endsection