@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        Danh sách cộng tác viên
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Cộng tác viên</li>
    </ol>
</section>

@can('index', app(App\Models\BoUser::class))
<section class="content">
    <div class="box box-primary">
        <div class="box-body ">
            <form id="form-search-advance" action="{{ route('admin.users.index_ctv') }}" method="get">
                <div id="search-advance" class="search-advance">
                    <div class="row form-group space-5">
                        <div class="col-sm-3">
                            <input type="text" name="keyword" value="{{ $keyword }}" placeholder="Tên, Email, SĐT, Tên đăng nhập" class="form-control" />
                        </div>
                        <div class="col-sm-3">
                            <select name="group_ids" class="form-control select2" style="width: 100%;">
                                <option value="">Phòng ban</option>
                                @foreach ($groups as $item)
                                    @if($item->gb_id == 1536572739)
                                        <option value="{{ $item->gb_id }}" {{ $item->gb_id == $group_ids ? 'selected' : '' }}>{{ $item->gb_title }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-3">
                            <select name="status" class="form-control">
                                <option value="">Trạng thái</option>
                                <option value="1" {{ $status === '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ $status === '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-sm-3">
                            <button class="btn btn-warning btn-block">Tìm kiếm</button>
                        </div>
                    </div>
                </div>
            </form><!-- END #form-search-advance -->

            <form id="form-users" action="{{ route('admin.users.action') }}" method="post">
                @csrf
                <input type="hidden" name="method" value="" />
                <input type="hidden" name="status" value="" />

                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="bg-primary">
                            <tr>
                                <th width="30"><input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /></th>
                                <th width="30">ID</th>
                                <th width="160">Họ tên</th>
                                <th width="140">Tên đăng nhập</th>
                                <th width="140">Mã nhân viên</th>
                                <th width="140">Email</th>
                                <th width="110">SĐT</th>
                                <th>Phòng ban</th>
                                <th width="50">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $item)
                            <tr valign="middle">
                                <td><input type="checkbox" name="ids[]" value="{{ $item->id }}" class="iCheck checkSingle" /></td>
                                <td>{{ $item->id }}</td>
                                @can('view', app(App\Models\BoUser::class))
                                <td>
                                    <a href="{{ route('admin.users.profile_ctv', ['id'=>$item->id]) }}">{{ $item->ub_title }}</a>
                                </td>
                                @else
                                <td>{{ $item->ub_title }}</td>
                                @endcan
                                
                                <td>{{ $item->ub_account_tvc }}</td>
                                <td>{{ $item->ub_staff_code?:'' }}</td>
                                <td>{{ $item->email }}</td>
                                <td>{{ $item->phone }}</td>
                                <td>{{ $item->group->gb_title ?? '' }}</td>
                                <td class="text-center">
                                    <span title="Trạng thái" class="btn-status label label-sm label-{{ $item->ub_status ? 'success' : 'danger' }}">
                                        {{ $item->ub_status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="row mbm">
                    <div class="col-sm-3">
                        <span class="record-total">Tổng: {{ $users->total() }} bản ghi</span>
                    </div>
                    <div class="col-sm-6 text-center">
                        <div class="pagination-panel">
                            {{ $users->appends(Request::all())->onEachSide(1)->links() }}
                        </div>
                    </div>
                    <div class="col-sm-3 text-right">
                        <span class="form-inline">
                            Hiển thị
                            <select name="per_page" class="form-control" data-target="#form-users">
                                @php $list = [10, 20, 50, 100, 200]; @endphp
                                @foreach ($list as $num)
                                <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                                @endforeach
                            </select>
                        </span>
                    </div>
                </div>
            </form><!-- END #form-users -->
        </div>
    </div>
</section>
@endcan
@endsection

@section('javascript')

<script>
    sidebar('campaign_assigns', 'view');
</script>

@endsection