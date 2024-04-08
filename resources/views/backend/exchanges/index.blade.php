@extends('backend.layouts.master')

@section('content')
<section class="content-header">
    <h1>
        Điểm giao dịch
        <small>Danh sách</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Điểm giao dịch</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <form action="" method="get" id="form-search">
                <div class="row">
                    <div class="col-sm-9 col-xs-12 ">
                        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle pull-left" style="margin-right: 10px;">Tác vụ&nbsp;<span class="caret"></span></button>
                        <ul class="dropdown-menu">
                            @can('delete', app(App\Models\Exchange::class))
                            <li>
                                <a href="javascript:" type="button" class="btn-action" data-target="#form-exchange-action" data-method="delete"><i class="fa fa-trash text-danger"></i>&nbsp; Xóa</a>
                            </li>
                            @endcan

                            @can('update', app(App\Models\Exchange::class))
                            <li>
                                <a href="javascript:" type="button" class="btn-action" data-target="#form-exchange-action" data-method="active"><i class="fa fa-check text-success"></i>&nbsp;Activate</a>
                            </li>
                            <li>
                                <a href="javascript:" type="button" class="btn-action" data-target="#form-exchange-action" data-method="inactive"><i class="fa fa-close text-warning"></i>&nbsp;Inactivate</a>
                            </li>
                            @endcan
                        </ul>
                        @can('update', app(App\Models\Exchange::class))
                        <a href="{{ url('admin/exchanges/edit/0') }}" type="buttom" class="btn btn-info"><i class="fa fa-plus"></i>&nbsp;&nbsp;Thêm mới</a>
                        @endcan
                    </div>

                    <div class="col-sm-3 col-xs-12">
                        <div class="input-group">
                            <input type="text" class="form-control" name="name" value="{{ !empty($data_search['name']) ? $data_search['name']: '' }}" placeholder="Nhập tên địa điểm giao dịch">
                            <div class="input-group-btn">
                                <button type="submit" title="Tìm kiếm" class="btn btn-info"><i class="fa fa-search"></i> </button>
                                <button type="button" title="Tìm kiếm nâng cao" class="btn btn-warning" data-toggle="collapse" data-target="#search-advance"><i class="fa fa-filter"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="clearfix" style="height: 15px;"></div>

                <div class="collapse row" id="search-advance">
                    <div class="col-sm-1 col-xs-12 pull-right">
                        <button type="submit" form="form-search" class="btn btn-warning"><i class="fa fa-search"></i>&nbsp;&nbsp;Tìm kiếm</button>
                    </div>

                    <div class="col-sm-2 col-xs-12 form-group pull-right no-pd-rt">
                        <select class="form-control" name="district" id="select-district" style="width: 100%;">
                            <option value="">Chọn Quận/Huyện</option>
                            @if(!empty($data_search['district']))
                            <option value="{{ $data_search['district']['code'] }}" selected>{{ $data_search['district']['name']}}</option>
                            @endif
                        </select>
                    </div>

                    <div class="col-sm-2 col-xs-12 form-group pull-right no-pd-rt">
                        <select class="form-control" name="city" id="select-city" style="width: 100%;">
                            <option value="">Chọn Tỉnh/Thành phố</option>
                            @if(!empty($data_search['city']))
                            <option value="{{ $data_search['city']['code'] }}" selected>{{ $data_search['city']['name']}}</option>
                            @endif
                        </select>
                    </div>

                    <div class="form-group col-sm-2 col-xs-12 no-pd-rt pull-right">
                        <select class="form-control select2" name="status" style="width: 100%;">
                            <option value="">Chọn trạng thái</option>
                            <option value="1" @if($data_search && $data_search['status']==1) selected @endif>
                                Hoạt động
                            </option>
                            <option value="0" @if($data_search && $data_search['status']==='0' ) selected @endif>
                                Chưa hoạt động
                            </option>
                        </select>
                    </div>
                </div>
                <div class="clearfix"></div>
            </form>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
            <div class="table-responsive">
                <form action="{{url("/admin/exchanges/action")}}" method="post" id="form-exchange-action">
                    {{ csrf_field() }}

                    <input type="hidden" name="method" value="" />
                    <input type="hidden" name="status" value="" />

                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr class="bg-info">
                                <th width='20px'>
                                    <input class="checkAll iCheck" type="checkbox" data-target=".checkSingle" />
                                </th>
                                <th width='20px'>#</th>
                                <th>Điểm giao dịch</th>
                                <th width='10%'>Hotline</th>
                                <th width='10%'>Tỉnh/Thành phố</th>
                                <th width='10%'>Quận huyện</th>
                                <th width='15%'>Địa chỉ chi tiết</th>
                                <th width='13%' class="text-center">Last update</th>
                                <th width='7%'>Trạng thái</th>
                                <th width='11%'>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach($exchanges as $exchange)
                            <tr>
                                <td><input type="checkbox" class="checkSingle iCheck" value="{{$exchange->id}}" name="ids[]" /></td>
                                <td>{{ $exchange->id }}</td>
                                <td><a href='{{ url("admin/exchanges/edit/$exchange->cb_id") }}'>{{ $exchange->name }}</a></td>
                                <td>{{ $exchange->hotline }}</td>
                                <td>{{ $exchange->city_code->name }}</td>
                                <td>{{ $exchange->district_code->name }}</td>
                                <td>{{ $exchange->address }}</td>
                                <td class="text-center">
                                    {{ $exchange->user->ub_title }} <br />
                                    <span>{{$exchange->updated_at->format('d-m-Y H:i')}}</span>
                                </td>
                                <td>
                                    @can('update', app(App\Models\Exchange::class))
                                    <a title="Thay đổi trạng thái" href="javascript:;" data-url="{{ url('admin/exchanges/action') }}" data-id="{{ $exchange->id }}" data-status="{{ $exchange->status }}" class="btn-status label label-sm label-{{ $exchange->status ? 'success' : 'danger' }}">
                                        {{ $exchange->status ? 'Active' : 'Inactive' }}
                                    </a>
                                    @else
                                    <span class="btn-status label label-sm label-{{ $exchange->status ? 'success' : 'danger' }}">{{ $exchange->status ? 'Active' : 'Inactive' }}</span>
                                    @endcan
                                </td>
                                <td>
                                    @can('update', app(App\Models\Exchange::class))
                                    <a href="{{ url("/admin/exchanges/edit/{$exchange->cb_id}") }}" type="button" class="btn btn-sm btn-info" title="Sửa"><i class="fa fa-edit"></i></a>
                                    @endcan

                                    @can('delete', app(App\Models\Exchange::class))
                                    <a title="Xóa" href="javascript:;" data-url="{{ url('admin/exchanges/action') }}" data-id="{{ $exchange->id }}" class="btn btn-sm btn-delete btn-danger"><i class="fa fa-trash"></i></a>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
        <!-- /.box-body -->
        <div class="box-footer clearfix">
            <div class="row">
                <div class="col-sm-3">
                    <span class="record-total">Tổng: {{ $exchanges->total() }} bản ghi</span>
                </div>
                <div class="col-sm-6 text-center">
                    <div class="pagination-panel">
                        {{ $exchanges->appends(Request::all())->onEachSide(1)->links() }}
                    </div>
                </div>
                <div class="col-sm-3 text-right">
                    <span class="form-inline">
                        Hiển thị
                        <select name="per_page" class="form-control" data-target="#form-exchange-action">
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


<div class="modal fade" id="modal-delete" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">Thông báo</h4>
            </div>
            <div class="modal-body">
                <p>Bán có chắc chắn muốn thực hiện thao tác không?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger pull-left" data-dismiss="modal">Hủy</button>
                <button type="button" data-action="delete" class="btn btn-primary btn-js-action">Xác nhận</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
@endsection

@section('javascript')

<script>
    $(function() {
    // Chọn tỉnh thành
    get_data_select2({
        object: '#select-city',
        url: '{{ url("/admin/cities/ajax-get-city") }}',
        data_id: 'code',
        data_text: 'name',
        title_default: 'Chọn tỉnh/thành phố'
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
                var results = [{
                        id: '',
                        text: 'Chọn quận/huyện'
                    }];

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

sidebar('exchanges', 'exchange');
</script>
@endsection