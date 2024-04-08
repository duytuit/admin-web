@extends('backend.layouts.master')

@section('content')
<section class="content-header">
    <h1>
        Đối tác
        <small>Chi nhánh</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Chi nhánh</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <form action="" method="get" id="form-search">
                <div class="row">
                    <div class="col-md-8 col-sm-8 col-xs-12 ">
                        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle pull-left" style="margin-right: 10px;">Tác vụ&nbsp;<span class="caret"></span></button>
                        <ul class="dropdown-menu">
                            @can('delete', app(App\Models\Branch::class))
                            <li>
                                <a href="javascript:" type="button" class="btn-action" data-target="#form-branch-action" data-method="delete"><i class="fa fa-trash text-danger"></i>&nbsp; Xóa</a>
                            </li>
                            @endcan

                            @can('update', app(App\Models\Branch::class))
                            <li>
                                <a href="javascript:" type="button" class="btn-action" data-target="#form-branch-action" data-method="active"><i class="fa fa-check text-success"></i>&nbsp;Activate</a>
                            </li>
                            <li>
                                <a href="javascript:" type="button" class="btn-action" data-target="#form-branch-action" data-method="inactive"><i class="fa fa-close text-warning"></i>&nbsp;Inactivate</a>
                            </li>
                            @endcan
                        </ul>

                        @can('update', app(App\Models\Branch::class))
                        <a href="{{ url('admin/branches/edit/0') }}" type="buttom" class="btn btn-info"><i class="fa fa-plus"></i>&nbsp;&nbsp;Thêm mới</a>
                        @endcan
                    </div>

                    <div class="col-md-4 col-sm-4 col-xs-12">
                        <div class="input-group">
                            <input type="text" class="form-control" name="title" value="{{ !empty($data_search['title']) ? $data_search['title']: '' }}" placeholder="Nhập tên chi nhánh">
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
                    <div class="col-sm-3 col-xs-12 form-group pull-right no-pd-rt">
                        <input type="text" name="hotline" class="form-control" placeholder="Nhập thông tin hotline" value="{{ !empty($data_search['hotline']) ? $data_search['hotline']: '' }}" />
                    </div>

                    <div class="col-sm-2 col-xs-12 form-group pull-right no-pd-rt">
                        <select class="form-control" name="branch_id" id="select-branch" style="width: 100%;">
                            <option value="">Chọn chi nhánh</option>
                            @if(!empty($data_search['branch']))
                            <option value="{{ $data_search['branch']['id'] }}" selected>{{ $data_search['branch']['title']}}</option>
                            @endif
                        </select>
                    </div>

                    <div class="col-sm-2 col-xs-12 form-group pull-right no-pd-rt">
                        <select class="form-control select2" name="partner_id" id="select-partner" style="width: 100%;">
                            <option value="">Chọn đối tác</option>
                            @foreach($partners as $partner)
                            <option value="{{$partner->id}}" @if($data_search['partner_id']==$partner->id) selected @endif >{{$partner->name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-sm-2 col-xs-12 no-pd-rt pull-right">
                        <select class="form-control select2" name="status" style="width: 100%;">
                            <option value=""> Chọn trạng thái </option>
                            <option value="1" @if($data_search['status']==1) selected @endif>
                                Hoạt động
                            </option>
                            <option value="0" @if($data_search['status']=='0' ) selected @endif>
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
                <form action="{{url("/admin/branches/action")}}" method="post" id="form-branch-action">
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
                                <th>Chi nhánh</th>
                                <th width='20%'>Đối tác</th>
                                <th width='10%'>hotline</th>
                                <th width='13%' class="text-center">Last update</th>
                                <th width='7%'>Trạng thái</th>
                                <th width='11%'>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($branches as $branch)
                            <tr>
                                <td><input type="checkbox" class="checkSingle iCheck" value="{{$branch->id}}" name="ids[]" /></td>
                                <td>{{ $branch->id }}</td>
                                <td><a href="{{ url("/admin/branches/edit/{$branch->id}") }}">{{ $branch->title }}</a></td>
                                <td>{{ $branch->partner_name }}</td>
                                <td>{{ $branch->hotline }}</td>
                                <td class="text-center">
                                    {{ $branch->user_name }} <br />
                                    <span>{{$branch->updated_at->format('d-m-Y H:i')}}</span>
                                </td>
                                <td>
                                    @can('update', app(App\Models\Branch::class))
                                    <a title="Thay đổi trạng thái" href="javascript:;" data-url="{{ url('admin/branches/action') }}" data-id="{{ $branch->id }}" data-status="{{ $branch->status }}" class="btn-status label label-sm label-{{ $branch->status ? 'success' : 'danger' }}">
                                        {{ $branch->status ? 'Active' : 'Inactive' }}
                                    </a>
                                    @else
                                    <span class="btn-status label label-sm label-{{ $branch->status ? 'success' : 'danger' }}">{{ $branch->status ? 'Active' : 'Inactive' }}</span>
                                    @endcan
                                </td>
                                <td>
                                    @can('update', app(App\Models\Branch::class))
                                    <a href="{{ url("/admin/branches/edit/{$branch->id}") }}" type="button" class="btn btn-sm btn-info" title="Sửa"><i class="fa fa-edit"></i></a>
                                    @endcan

                                    @can('delete', app(App\Models\Branch::class))
                                    <a title="Xóa" href="javascript:;" data-url="{{ url('admin/branches/action') }}" data-id="{{ $branch->id }}" class="btn btn-sm btn-delete btn-danger"><i class="fa fa-trash"></i></a>
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
                    <span class="record-total">Tổng: {{ $branches->total() }} bản ghi</span>
                </div>
                <div class="col-sm-6 text-center">
                    <div class="pagination-panel">
                        {{ $branches->appends(Request::all())->onEachSide(1)->links() }}
                    </div>
                </div>
                <div class="col-sm-3 text-right">
                    <span class="form-inline">
                        Hiển thị
                        <select name="per_page" class="form-control" data-target="#form-branch-action">
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
    var selectBranch = $('#select-branch');

    selectBranch.select2({
        ajax: {
            url: '{{ url("/admin/branches/ajax/search-partner-branch") }}',
            dataType: 'json',
            data: function(params) {
                var partner_id = $('#select-partner').val();
                var query = {
                    search: params.term,
                    partner_id: partner_id
                }
                return query;
            },
            processResults: function(data, params) {
                var results = [];
                for (i in data.data) {
                    var item = data.data[i];
                    results.push({
                        id: item.id,
                        text: item.title
                    });
                }
                return {
                    results: results
                };
            },
        }
    });
});

sidebar('partners', 'branch');
</script>
@endsection