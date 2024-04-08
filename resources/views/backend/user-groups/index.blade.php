@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        Danh sách nhân viên
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Nhân viên</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-body ">
            <form id="form-search-advance" action="{{ route('admin.user-groups.index') }}" method="get">
                <div id="search-advance" class="search-advance">
                    <div class="row form-group space-5">
                        <div class="col-sm-3">
                            <input type="text" name="keyword" value="{{ $keyword }}" placeholder="Tên phòng ban" class="form-control" />
                        </div>
                        <div class="col-sm-3">
                            <input type="text" name="code" value="{{ $code }}" placeholder="Mã Tavico" class="form-control" />
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

            <form id="form-groups" action="{{ route('admin.user-groups.action') }}" method="post">
                @csrf
                <input type="hidden" name="method" value="" />
                <input type="hidden" name="status" value="" />

                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="bg-primary">
                            <tr>
                                <th width="30"><input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /></th>
                                <th width="30">ID</th>
                                <th>Phòng ban</th>
                                <th>Mô tả</th>
                                <th width="140">Mã Tavico</th>
                                <th width="50">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($groups as $item)
                            <tr valign="middle">
                                <td><input type="checkbox" name="ids[]" value="{{ $item->id }}" class="iCheck checkSingle" /></td>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->gb_title }}</td>
                                <td>{{ $item->gb_description }}</td>
                                <td>{{ $item->reference_code }}</td>
                                <td class="text-center">
                                    <a title="Trạng thái" href="javascript:;" class="btn-status label label-sm label-{{ $item->gb_status ? 'success' : 'danger' }}">
                                        {{ $item->gb_status ? 'Active' : 'Inactive' }}
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="row mbm">
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
                            <select name="per_page" class="form-control" data-target="#form-groups">
                                @php $list = [10, 20, 50, 100, 200]; @endphp
                                @foreach ($list as $num)
                                <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                                @endforeach
                            </select>
                        </span>
                    </div>
                </div>
            </form><!-- END #form-groups -->
        </div>
    </div>
</section>

@endsection

@section('javascript')

<script>
    sidebar('users', 'groups');
</script>

@endsection