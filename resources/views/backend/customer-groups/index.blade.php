@extends('backend.layouts.master')

@section('stylesheet')
<link rel="stylesheet" href="/adminLTE/plugins/treegrid/jquery.treegrid.css" />
@endsection
@section('content')
<section class="content-header">
    <h1>
        Nhóm khách hàng
        <small>Danh sách</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('/admin') }}"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
        <li class="active">Nhóm khách hàng</li>
    </ol>
</section>

<section class="content" id="content-bo-customer">
    <div class="box box-primary">
        <div class="box-header with-border">
            <form action="" method="get" id="form-search">
                <div class="row">
                    <div class="col-md-8 col-sm-8 col-xs-12 ">
                        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle pull-left" style="margin-right: 10px;">Tác vụ&nbsp;<span class="caret"></span></button>
                        <ul class="dropdown-menu">
                            @can('delete', app(App\Models\CustomerGroup::class))
                            <li>
                                <a href="javascript:" type="button" class="btn-action" data-target="#form-customer-group-action" data-method="delete"><i class="fa fa-trash text-danger"></i>&nbsp; Xóa</a>
                            </li>
                            @endcan

                            @can('update', app(App\Models\CustomerGroup::class))
                            <li>
                                <a href="javascript:" type="button" class="btn-action" data-target="#form-customer-group-action" data-method="active"><i class="fa fa-check text-success"></i>&nbsp;Activate</a>
                            </li>
                            <li>
                                <a href="javascript:" type="button" class="btn-action" data-target="#form-customer-group-action" data-method="inactive"><i class="fa fa-close text-warning"></i>&nbsp;Inactivate</a>
                            </li>
                            @endcan
                            {{-- <li>
                                <a href="javascript:" type="button" class="btn-action"><i class="fa fa-bullhorn text-info"></i>&nbsp;Gửi thông báo</a>
                            </li> --}}
                        </ul>

                        @can('delete', app(App\Models\CustomerGroup::class))
                        <a href="{{ url('admin/customer-groups/edit/0') }}" type="buttom" class="btn btn-info"><i class="fa fa-plus"></i>&nbsp;&nbsp;Thêm mới</a>
                        @endcan
                    </div>

                    <div class="col-md-4 col-sm-4 col-xs-12">
                        <div class="input-group">
                            <input type="text" class="form-control" name="name" placeholder="Nhập tên nhóm" value="{{ !empty($data_search['name']) ? $data_search['name'] : '' }}">
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
                        <button type="submit" form="form-search" class="btn btn-warning"><i class="fa fa-search"></i>&nbsp;&nbsp;Tìm kiếm</button>
                    </div>

                    <div class="col-sm-3 col-xs-12 form-group pull-right no-pd-rt">
                        <select class="form-control" name="user_id" id="search-bo-user" style="width: 100%;">
                            <option value="">Chọn nhân viên</option>
                            @if(!empty($data_search['user']))
                            <option value="{{ $data_search['user']['id'] }}" selected>{{ $data_search['user']['name']}}</option>
                            @endif
                        </select>
                    </div>

                    <div class="form-group col-sm-2 col-xs-12 pull-right no-pd-rt ">
                        <select class="form-control select2" name="status" style="width: 100%;">
                            <option value=""> Chọn trạng thái</option>
                            <option value="1" @if($data_search && $data_search['status']===1) selected @endif>
                                Active
                            </option>
                            <option value="0" @if($data_search && $data_search['status']==='0' ) selected @endif>
                                Inactive
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
                <form action="{{ url("/admin/customer-groups/action") }}" method="post" id="form-customer-group-action">
                    {{ csrf_field() }}
                    @method('post')
                    <input type="hidden" name="method" value="" />
                    <input type="hidden" name="status" value="" />

                    <table class="table tree">
                        <thead>
                            <tr class="bg-primary">
                                <th width='20px'>
                                    <input class="iCheck checkAll" type="checkbox" data-target=".checkSingle" />
                                </th>
                                <th width='20px'>#</th>
                                <th>Tên nhóm</th>
                                <th width='14%'>Số thành viên</th>
                                <th width='14%'>Trạng thái</th>
                                <th width='12%' class="text-center">Last update</th>
                                <th width='9%'>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($groups as $group)

                            <tr>
                                <td><input type="checkbox" class="iCheck checkSingle" value="{{$group->id}}" name="ids[]" /></td>
                                <td>{{ $group->id }}</td>
                                <td>
                                    <a href="{{ url("/admin/customer-groups/edit/{$group->cb_id}") }}"> {{ $group->name }} </a>
                                </td>
                                <td><a href='{{ url("admin/customer-groups/edit/" . $group->cb_id . "#customers") }}'>{{ isset($group['criterion']['count'])?$group['criterion']['count']:'' }}</a></td>
                                <td>
                                    @can('delete', app(App\Models\CustomerGroup::class))
                                    <a title="Thay đổi trạng thái" href="javascript:;" data-url="{{ url('admin/customer-groups/action') }}" data-id="{{ $group->id }}" data-status="{{ $group->status }}" class="btn-status label label-sm label-{{ $group->status == 1 ? 'success' : 'danger' }}">
                                        {{ $group->status == 1 ? 'Active' : 'Inactive' }}
                                    </a>
                                    @else
                                    <span class="btn-status label label-sm label-{{ $group->status == 1 ? 'success' : 'danger' }}">{{ $group->status == 1 ? 'Active' : 'Inactive' }}</span>
                                    @endcan
                                </td>
                                <td class="text-center">
                                    @if($group->user)
                                    {{ $group->user->ub_title }} <br />
                                    <span>{{$group->updated_at->format('d-m-Y H:i')}}</span>
                                    @endif
                                </td>
                                <td>
                                    @can('update', app(App\Models\CustomerGroup::class))
                                    <a href="{{ url("/admin/customer-groups/edit/{$group->cb_id}") }}" type="button" class="btn btn-sm btn-info" title="Sửa"><i class="fa fa-edit"></i></a>
                                    @endcan
                                    @can('delete', app(App\Models\CustomerGroup::class))
                                    <a title="Xóa" href="javascript:;" data-url="{{ url('admin/customer-groups/action') }}" data-id="{{ $group->id }}" class="btn btn-sm btn-delete btn-danger"><i class="fa fa-trash"></i></a>
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
                    <span class="record-total">Tổng: {{ $groups->total() }} bản ghi</span>
                </div>
                <div class="col-sm-6 text-center">
                    <div class="pagination-panel">
                        {{ $groups->appends(Request::all())->onEachSide(1)->links() }}
                    </div>
                </div>
                <div class="col-sm-3 text-right">
                    <span class="form-inline">
                        Hiển thị
                        <select name="per_page" class="form-control" data-target="#form-customer-group-action">
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

@endsection

@section('javascript')

<script type="text/javascript" src="/adminLTE/plugins/treegrid/jquery.treegrid.js"></script>
<script type="text/javascript" src="/adminLTE/plugins/treegrid/jquery.treegrid.bootstrap3.js"></script>
<script>
    function get_group_data_select2(object) {
        $(object).select2({
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
                    return {
                        results: json,
                    };
                },
                minimumInputLength: 3,
            }
        });
    }

    // Chọn nhân viên
    get_group_data_select2('#search-bo-user');

    sidebar('bo-customers', 'group');
</script>
@endsection