@extends('backend.layouts.master')

@section('stylesheet')
<link rel="stylesheet" href="/adminLTE/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
@endsection

@section('content')
<section class="content-header">
    <h1>
        Chiến dịch
        <small>Danh sách</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('/admin') }}"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
        <li class="active">Chiến dịch</li>
    </ol>
</section>

<section class="content" id="content-bo-customer">
    <div class="box box-primary">
        <div class="box-body">
            <form action="" method="get" id="form-search">
                <div class="row form-group">
                    <div class="col-md-8 col-sm-8 col-xs-12 ">
                        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle pull-left" style="margin-right: 10px;">Tác vụ&nbsp;<span class="caret"></span></button>
                        <ul class="dropdown-menu">
                            @can('delete', app(App\Models\Campaign::class))
                            <li>
                                <a href="javascript:" type="button" class="btn-action" data-target="#form-campaign-action" data-method="delete">
                                    <i class="fa fa-trash text-danger"></i>&nbsp; Xóa
                                </a>
                            </li>
                            @endcan
                        </ul>
                        @can('update', app(App\Models\Campaign::class))
                        <a href="{{ route('admin.campaigns.create') }}" type="buttom" class="btn btn-info"><i class="fa fa-plus"></i>&nbsp;&nbsp;Thêm mới</a>
                        @endcan
                    </div>

                    <div class="col-md-4 col-sm-4 col-xs-12">
                        <div class="input-group">
                            <input type="text" class="form-control" name="title" placeholder="Nhập tên chiến dịch" value="{{ !empty($data_search['title']) ? $data_search['title'] : '' }}">
                            <div class="input-group-btn">
                                <button type="submit" title="Tìm kiếm" class="btn btn-info"><i class="fa fa-search"></i></button>
                                <button type="button" title="Tìm kiếm nâng cao" class="btn btn-warning" data-toggle="collapse" data-target="#search-advance"><i class="fa fa-filter"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="collapse row" id="search-advance">
                    <div class="col-sm-2 col-xs-12 pull-right">
                        <button type="submit" form="form-search" class="btn btn-warning" style="width: 100%;"><i class="fa fa-search"></i> Tìm kiếm</button>
                    </div>

                    <div class="col-sm-3 col-xs-12 form-group pull-right no-pd-rt">
                        <select class="form-control select2" name="source" style="width: 100%;">
                            <option value="">Chọn nguồn</option>
                            @foreach($searches['source']->config_value as $key => $customer_source)
                            <option value="{{ $key }}" @if($data_search['source']==$key) selected @endif>{{ $customer_source }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-3 col-xs-12 pull-right no-pd-rt">
                        <select class="form-control" name="project_id" id="select-bo-project" style="width: 100%;">
                            <option value="">Chọn dự án</option>
                            @if(!empty($data_search['project']))
                            <option value="{{ $data_search['project']['id'] }}" selected>{{ $data_search['project']['name']}}</option>
                            @endif
                        </select>
                    </div>

                    <div class="form-group col-sm-4 col-xs-12 pull-right no-pd-rt">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="input-group datetimepicker" data-format="DD-MM-YYYY">
                                    <input type="text" class="form-control pull-right" placeholder="Đến ngày" name="end_date" value="{{ !empty($data_search['end_date']) ? $data_search['end_date'] : '' }}" />
                                    <span class="input-group-addon btn"><i class="fa fa-calendar"></i></span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="input-group datetimepicker" data-format="DD-MM-YYYY">
                                    <input type="text" class="form-control pull-right" placeholder="Từ ngày" name="begin_date" value="{{ !empty($data_search['begin_date']) ? $data_search['begin_date'] : '' }}" />
                                    <span class="input-group-addon btn"><i class="fa fa-calendar"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
            </form>
            <div class="table-responsive">
                <form action='{{ url("/admin/campaigns/action") }}' method="post" id="form-campaign-action">
                    {{ csrf_field() }}
                    @method('post')
                    <input type="hidden" name="method" value="" />
                    <input type="hidden" name="status" value="" />
                    <input type="hidden" name="cb_staff_ids[]" value="" />

                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr class="bg-primary">
                                <th width='20px'>
                                    <input class="iCheck checkAll" type="checkbox" data-target=".checkSingle" />
                                </th>
                                <th width='20px'>#</th>
                                <th>Chiến dịch</th>
                                <th width='10%'>Dự án</th>
                                <th width='10%'>Khách hàng</th>
                                <th width='9%'>Nhân viên</th>
                                <th width='9%'>Phản hồi</th>
                                <th width='9%'>Quan tâm</th>
                                <th width='9%'>Tạo bởi</th>
                                <th width='9%'>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $campaigns->load('project');
                            $campaigns->load('user');
                            @endphp
                            @foreach($campaigns as $campaign)
                            <tr>
                                <td><input type="checkbox" class="iCheck checkSingle" value="{{$campaign->id}}" name="ids[]" /></td>
                                <td>{{ $campaign->id }}</td>
                                <td>
                                    <a href='{{ url("/admin/campaigns/{$campaign->id}/edit") }}'> {{ $campaign->title }} </a>
                                </td>
                                <td> {{ $campaign->getProject() ? $campaign->getProject()['cb_title'] : '' }} </td>
                                <td><a href="javascript:;" class="js-campains-sum-cus" data-cuscampid="{{ $campaign->id }}" data-toggle="modal" data-target="#campaign-list-per">{{ $campaign->sum_customer }}</a></td>
{{--                                <td><a href="javascript:void(0);" class="js-campains-sum-user" data-usercampid="{{ $campaign->id }}" data-toggle="modal" data-target="#campaign-list-per">{{ $campaign->sum_user }}</a></td>--}}
                                <td>{{ $campaign->sum_user }}</td>
                                <td> {{ $campaign->feedback }}/{{ count($campaign->campaign_assign)}}</td>
                                <td> {{ $campaign->status }}/{{ count($campaign->campaign_assign)}}</td>
                                <td>
                                    {{ $campaign->user ? $campaign->user->ub_title : '' }} <br />
                                    {{ $campaign->updated_at->format('d-m-Y') }} <br />
                                </td>
                                <td>
                                    @can('view', app(App\Models\Campaign::class))
                                    <a href='{{ url("/admin/campaigns/{$campaign->id}/edit") }}' type="button" class="btn btn-sm btn-info" title="Xem chi tiết"><i class="fa fa-eye"></i></a>
                                    @endcan

                                    @can('delete', app(App\Models\Campaign::class))
                                    <a title="Xóa" href="javascript:;" data-url="{{ url('admin/campaigns/action') }}" data-id="{{ $campaign->id }}" class="btn btn-sm btn-delete btn-danger">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <input type="submit" class="js-submit-form-index hidden" value="" />
                </form>
            </div>
        </div>
        <!-- /.box-body -->

        <div class="box-footer clearfix">
            <div class="row">
                <div class="col-sm-3">
                    <span class="record-total">Tổng: {{ $campaigns->total() }} bản ghi</span>
                </div>
                <div class="col-sm-6 text-center">
                    <div class="pagination-panel">
                        {{ $campaigns->appends(Request::all())->onEachSide(1)->links() }}
                    </div>
                </div>
                <div class="col-sm-3 text-right">
                    <span class="form-inline">
                        Hiển thị
                        <select name="per_page" class="form-control" data-target="#form-bo-customer-action">
                            @php $list = [10, 20, 50, 100, 200]; @endphp
                            @foreach ($list as $num)
                            <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                            @endforeach
                        </select>
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>
@can('index', app(App\Models\Campaign::class))
    <div id="campaign-list-per" class="modal fade" role="dialog">
        <div class="modal-dialog  modal-lg">
            <!-- Modal content-->
            <div class="modal-content">
                <form action="{{ route('admin.campaign_assign.confirm') }}" method="POST" class="form-validate form-horizontal" id="confirm_assing-diary" autocomplete="off">
                    @csrf
                    <div class="modal-header bg-primary">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Danh sách khách hàng</h4>
                    </div>
                    <div class="modal-body"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
{{--                        <button class="btn btn-primary btn-confirm-assign" style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Duyệt</button>--}}
                    </div>
                </form>
            </div>
        </div>
    </div>
@endcan
@can('index', app(App\Models\CustomerDiary::class))
    <div id="campaign-assign-diary" class="modal fade" role="dialog">
        <div class="modal-dialog  modal-lg">
            <!-- Modal content-->
            <div class="modal-content">
                <form action="{{ route('admin.campaign_assign.confirm') }}" method="POST" class="form-validate form-horizontal" id="confirm_assing-diary" autocomplete="off">
                    @csrf
                    <div class="modal-header bg-primary">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Thông tin phản hồi</h4>
                    </div>
                    <div class="modal-body"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
                        <button class="btn btn-primary btn-confirm-assign" style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Duyệt</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endcan
@endsection


@section('javascript')
<script src="/adminLTE/plugins/moment/moment.min.js"></script>
<script src="/adminLTE/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>

<script>
    $(function() {

    get_data_select2({
        object: '#select-bo-project',
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
});
    $('.js-campains-sum-cus,.js-campains-sum-user').click(function() {
        var customer_sum = $(this).data('cuscampid');
        if(customer_sum){
            $.get('{{ url("/admin/campaigns/ajax-show-cus") }}', {
                assign_id: customer_sum,
            }, function(data) {
                $('#campaign-list-per .modal-body').html(data);
            });
        }
    });
    $('.js-btn-add-edit-diary').click(function() {
        var assign_id = $(this).data('assign');
        $.get('{{ url("/admin/campaign-assign/ajax-edit-diary") }}', {
            assign_id: assign_id,
        }, function(data) {
            $('#campaign-assign-diary .modal-body').html(data);
        });
    });

    $('.btn-confirm-assign').click(function(e) {
        e.preventDefault();

        if (confirm('Có chắc bạn muốn duyệt thông tin này?')) {
            $('#confirm_assing-diary').submit();
        }
    });
sidebar('campaigns', 'campaign');
</script>
@endsection