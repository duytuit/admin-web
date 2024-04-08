@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        Danh sách Nhóm quyền
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">{{ $heading }}</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-body ">
            <form id="form-search" action="{{ route('admin.roles.index') }}" method="get">
                <div class="row form-group">
                    <div class="col-sm-8">
                        @can('delete', app(App\Models\Role::class))
                        <span class="btn-group">
                            <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">Tác vụ <span class="caret"></span></button>
                            <ul class="dropdown-menu">
                                <li><a class="btn-action" data-target="#form-roles" data-method="delete" href="javascript:;"><i class="fa fa-trash"></i> Xóa</a></li>
                            </ul>
                        </span>
                        @endcan

                        @can('update', app(App\Models\Role::class))
                        <a href="{{ route('admin.roles.create') }}" class="btn btn-info"><i class="fa fa-edit"></i> Thêm mới</a>
                        @endcan
                    </div>
                    <div class="col-sm-4 text-right">
                        <div class="input-group">
                            <input type="text" name="keyword" value="{{ $keyword }}" placeholder="Nhập từ khóa" class="form-control" />
                            <div class="input-group-btn">
                                <button type="submit" class="btn btn-info"><span class="fa fa-search"></span></button>
                                <button type="button" class="btn btn-warning btn-search-advance" data-toggle="show" data-target=".search-advance"><span class="fa fa-filter"></span></button>
                            </div>

                        </div>
                    </div>
                </div>
            </form><!-- END #form-search -->

            <form id="form-roles" action="{{ route('admin.roles.action') }}" method="post">
                @csrf
                <input type="hidden" name="method" value="" />

                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="bg-primary">
                            <tr>
                                <th width="3%"><input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /></th>
                                <th width="3%">ID</th>
                                <th width="20%">Tiêu đề</th>
                                <th>Mô tả</th>
                                <th width="12%">Cập nhật</th>
                                <th width="12%">Tác vụ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($roles as $item)
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="{{ $item->id }}" class="iCheck checkSingle" /></td>
                                <td>{{ $item->id }}</td>
                                <td><a href="{{ route('admin.roles.edit', ['id' => $item->id]) }}">{{ $item->title }}</a></td>
                                <td>{{ $item->summary }}</td>
                                <td>{{ $item->updated_at->format('d-m-Y H:i') }}</td>
                                <td>
                                    @can('update', app(App\Models\Role::class))
                                    <a title="Sửa nhóm quyền" href="{{ route('admin.roles.edit', ['id' => $item->id]) }}" class="btn btn-sm btn-info"><i class="fa fa-edit"></i></a>
                                    @endcan

                                    @can('approve', app(App\Models\Role::class))
                                    <a title="Phân quyền" href="{{ route('admin.roles.users.edit', ['id' => $item->id]) }}" class="btn btn-sm btn-warning"><i class="fa fa-users"></i></a>
                                    @endcan

                                    @can('delete', app(App\Models\Role::class))
                                    <a title="Xóa nhóm quyền" href="javascript:;" data-url="{{ route('admin.roles.action') }}" data-id="{{ $item->id }}" class="btn btn-sm btn-delete btn-danger"><i class="fa fa-trash"></i></a>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="row mbm">
                    <div class="col-sm-3">
                        <span class="record-total">Tổng: {{ $roles->total() }} bản ghi</span>
                    </div>
                    <div class="col-sm-6 text-center">
                        <div class="pagination-panel">
                            {{ $roles->onEachSide(1)->links() }}
                        </div>
                    </div>
                    <div class="col-sm-3 text-right">
                        <span class="form-inline">
                            Hiển thị
                            <select name="per_page" class="form-control" data-target="#form-roles">
                                @php $list = [10, 20, 50, 100, 200]; @endphp
                                @foreach ($list as $num)
                                <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                                @endforeach
                            </select>
                        </span>
                    </div>
                </div>
            </form><!-- END #form-roles -->
        </div>
    </div>
</section>

@endsection

@section('javascript')

<script>
    sidebar('users', 'roles');
</script>

@endsection