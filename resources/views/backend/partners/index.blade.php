@extends('backend.layouts.master')

@section('content')
<section class="content-header">
    <h1>
        Đối tác
        <small>Danh sách</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
        <li class="active">Đối tác</li>
    </ol>
</section>

<section class="content" id="content-partner">
    <div class="box box-primary">
        <div class="box-header with-border">
            <form action="" method="get" id="form-search">
                <div class="row">
                    <div class="col-sm-8 col-xs-12 ">
                        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle pull-left" style="margin-right: 10px;">Tác vụ&nbsp;<span class="caret"></span></button>
                        <ul class="dropdown-menu">
                            @can('delete', app(App\Models\Partner::class))
                            <li><a href="javascript:" type="button" class="btn-action" data-target="#form-partner-action" data-method="delete"><i class="fa fa-trash text-danger"></i>&nbsp; Xóa</a></li>
                            @endcan

                            @can('update', app(App\Models\Partner::class))
                            <li>
                                <a href="javascript:" type="button" class="btn-action" data-target="#form-partner-action" data-method="active"><i class="fa fa-check text-success"></i>&nbsp;Activate</a>
                            </li>
                            <li>
                                <a href="javascript:" type="button" class="btn-action" data-target="#form-partner-action" data-method="inactive"><i class="fa fa-close text-warning"></i>&nbsp;Inactivate</a>
                            </li>
                            @endcan
                        </ul>
                        @can('update', app(App\Models\Partner::class))
                        <a href="{{ url('admin/partners/edit/0') }}" type="buttom" class="btn btn-info"><i class="fa fa-plus"></i>&nbsp;&nbsp;Thêm mới</a>
                        @endcan
                    </div>

                    <div class="col-sm-4 col-xs-12">
                        <div class="input-group">
                            <input type="text" class="form-control" name="name" placeholder="Nhập tên đối tác" value="{{ !empty($data_search['name']) ? $data_search['name'] : '' }}">
                            <div class="input-group-btn">
                                <button type="submit" title="Tìm kiếm" class="btn btn-info"><i class="fa fa-search"></i></button>
                                <button type="button" title="Tìm kiếm nâng cao" class="btn btn-warning" data-toggle="collapse" data-target="#search-advance"><i class="fa fa-filter"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="clearfix" style="height: 15px;"></div>

                <div class="collapse row" id="search-advance">
                    <div class="col-sm-1 col-xs-12 pull-right">
                        <button type="submit" form="form-search" class="btn btn-warning"><i class="fa fa-search"></i>Tìm kiếm</button>
                    </div>
                    <div class="col-sm-3 col-xs-12 pull-right no-pd-rt">
                        <div class="input-group">
                            <span class="input-group-addon" style="padding: 0px; border: none;">
                                <div class="form-group">
                                    <select class="form-control input-group-select select2" name="field" style="width: 130px;">
                                        <option value="">Tìm kiếm theo</option>
                                        @foreach( $searches as $key => $search)
                                        <option value="{{$key}}" @if( $data_search && ($data_search["field"]==$key) ) selected @endif>{{$search}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </span>
                            <input type="text" name="partner_search" value="{{ !empty($data_search['partner_search'])? $data_search['partner_search'] : ''}}" placeholder="Nhập từ khóa" class="form-control" />
                        </div>
                    </div>

                    <div class="form-group col-sm-2 col-xs-12 no-pd-rt pull-right">
                        <select class="form-control select2" name="status" style="width: 100%;">
                            <option value=""> Chọn trạng thái </option>
                            <option value="1" @if($data_search && $data_search['status']===1) selected @endif>
                                Hoạt động
                            </option>
                            <option value="0" @if($data_search && $data_search['status']===0) selected @endif>
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
                <form action="{{ url("/admin/partners/action") }}" method="post" id="form-partner-action">
                    {{ csrf_field() }}
                    @method('post')
                    <input type="hidden" name="method" value="" />
                    <input type="hidden" name="status" value="" />

                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr class="bg-primary">
                                <th width='20px'>
                                    <input class="iCheck checkAll" type="checkbox" data-target=".checkSingle" />
                                </th>
                                <th width='20px'>#</th>
                                <th>Đối tác</th>
                                <th width='10%'>Logo</th>
                                <th width='10%'>Người đại diện</th>
                                <th width='7%'>Hotline</th>
                                <th width='7%'>Chi nhánh</th>
                                <th width='12%'>Công ty</th>
                                <th width='12%' class="text-center">Last update</th>
                                <th width='7%'>Trạng thái</th>
                                <th width='9%'>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach($partners as $partner)
                            <tr>
                                <td><input type="checkbox" class="iCheck checkSingle" value="{{$partner->id}}" name="ids[]" /></td>
                                <td>{{ $partner->id }}</td>
                                <td>
                                    <a href="{{ url("/admin/partners/edit/{$partner->id}") }}"> {{ $partner->name }} </a>
                                </td>
                                <td>
                                    <img src="{{ $partner->logo }}" alt="Logo đối tác" width="150px">
                                </td>
                                <td>{{ $partner->representative }}</td>
                                <td>{{ $partner->hotline }}</td>
                                <td class="text-center">
                                    <a href="#" data-toggle="modal" data-target="#modal-show-branch" class="js-btn-show-branch" data-partner_id="{{ $partner->id }}">
                                        <span class="badge bg-aqua">{{ count($partner->branches) }}</span>
                                    </a>
                                </td>
                                <td>{!! $partner->company_name !!}</td>
                                <td class="text-center">
                                    {{ $partner->user_name }} <br />
                                    <span>{{$partner->updated_at->format('d-m-Y H:i')}}</span>
                                </td>
                                <td>
                                    @can('update', $partner)
                                    <a title="Thay đổi trạng thái" href="javascript:;" data-url="{{ url('admin/partners/action') }}" data-id="{{ $partner->id }}" data-status="{{ $partner->status }}" class="btn-status label label-sm label-{{ $partner->status ? 'success' : 'danger' }}">
                                        {{ $partner->status ? 'Active' : 'Inactive' }}
                                    </a>
                                    @else
                                    <span class="btn-status label label-sm label-{{ $partner->status ? 'success' : 'danger' }}">
                                        {{ $partner->status ? 'Active' : 'Inactive' }}
                                    </span>
                                    @endcan
                                </td>
                                <td>
                                    @can( 'update', app(App\Models\Partner::class))
                                    <a href="{{ url("/admin/partners/edit/{$partner->id}") }}" type="button" class="btn btn-sm btn-info" title="Sửa"><i class="fa fa-edit"></i></a>
                                    @endcan

                                    @can( 'delete', app(App\Models\Partner::class))
                                    <a title="Xóa" href="javascript:;" data-url="{{ url('admin/partners/action') }}" data-id="{{ $partner->id }}" class="btn btn-sm btn-delete btn-danger"><i class="fa fa-trash"></i></a>
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
                    <span class="record-total">Tổng: {{ $partners->total() }} bản ghi</span>
                </div>
                <div class="col-sm-6 text-center">
                    <div class="pagination-panel">
                        {{ $partners->appends(Request::all())->onEachSide(1)->links() }}
                    </div>
                </div>
                <div class="col-sm-3 text-right">
                    <span class="form-inline">
                        Hiển thị
                        <select name="per_page" class="form-control" data-target="#form-partner-action">
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

<!-- Hiển thị danh sách chi nhánh -->
@can( 'index', app(App\Models\Branch::class))
<div class="modal fade" id="modal-show-branch" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gray">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">Danh sách chi nhánh</h4>
            </div>
            <div class="modal-body js-modal-show-branch">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger pull-right" data-dismiss="modal">Hủy</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
@endcan
@endsection

@section('javascript')
<script>
    $(document).ready(function() {
    $('.js-btn-show-branch').click(function() {
        var partner_id = $(this).data('partner_id');
        $.get('{{ url("/admin/partners/ajax-show-branch") }}', {
            partner_id: partner_id
        }, function(data) {
            $('#modal-show-branch .modal-body').html(data);
        });
    });
});

sidebar('partners', 'partner');
</script>
@endsection