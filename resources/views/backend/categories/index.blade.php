@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        Danh sách Danh mục
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">{{ $heading }}</li>
    </ol>
</section>
@php
$user = \Auth::user();
$per_update = $type. '.update';
$per_delete = $type. '.delete';
@endphp

<section class="content">
    <div class="box box-primary">
        <div class="box-body ">
            
                <form id="form-search" action="{{ route('admin.categories.index') }}" method="get">
                    <input type="hidden" name="type" value="{{ $type }}" />

                    <div class="row form-group">
                        <div class="col-sm-8">
                            <span class="btn-group">
                                <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">Tác vụ <span class="caret"></span></button>
                                <ul class="dropdown-menu">
                                    <li><a class="btn-action" data-target="#form-categories" data-method="delete" href="javascript:;"><i class="fa fa-trash"></i> Xóa</a></li>
                                    <li><a class="btn-action" data-target="#form-categories" data-method="active" href="javascript:;"><i class="fa fa-check"></i> Active</a></li>
                                    <li><a class="btn-action" data-target="#form-categories" data-method="inactive" href="javascript:;"><i class="fa fa-times"></i> Inactive</a></li>
                                </ul>
                            </span>
                            @if( in_array('admin.categories.create',@$user_access_router))
                                <a href="{{ route('admin.categories.create', ['type' => $type]) }}" class="btn btn-info"><i class="fa fa-edit"></i> Thêm mới</a>
                                <a href="{{ route('admin.categories.export', ['type' => $type]) }}" class="btn btn-success"><i class="fa fa-edit"></i> Export</a>
                            @endif
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
           
                <form id="form-search-advance" action="{{ route('admin.categories.index') }}" method="get" class="hidden">
                    <input type="hidden" name="type" value="{{ $type }}" />

                    <div id="search-advance" class="search-advance" style="display: {{ $advance ? 'block' : 'none' }};">
                        <div class="row form-group space-5">
                            <div class="col-sm-3">
                                <input type="text" name="keyword" value="{{ $keyword }}" placeholder="Nhập từ khóa" class="form-control" />
                            </div>
                            <div class="col-sm-3">
                                <input type="text" placeholder="Người tạo" class="form-control" />
                            </div>
                            <div class="col-sm-3">
                                <select name="status" class="form-control" style="width: 100%;">
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

            @if( in_array('admin.categories.action',@$user_access_router))
                <form id="form-categories" action="{{ route('admin.categories.action') }}" method="post">
                    @csrf
                    <input type="hidden" name="method" value="" />
                    <input type="hidden" name="status" value="" />
                    <input type="hidden" name="type" value="{{ $type }}" />

                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-bordered">
                            <thead class="bg-primary">
                                <tr>
                                    <th width="3%"><input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /></th>
                                    <th width="3%">ID</th>
                                    @if ($type == 'service')
                                       <th>Loại dịch vụ</th>
                                    @elseif ($type == 'receipt')
                                       <th>Kiểu phiếu</th>
                                    @endif
                                    <th width="8%">Phân loại</th>
                                    <th width="140">Cập nhật</th>
                                    <th width="7%">Status</th>
                                    <th width="100">Tác vụ</th>
                                </tr>
                            </thead>
                            <tbody>

                                @foreach ($categories as $item)
                                <tr>
                                    <td><input type="checkbox" name="ids[]" value="{{ $item->id }}" class="iCheck checkSingle" /></td>
                                    <td>{{ $item->id }}</td>
                                    @if( in_array('admin.categories.edit',@$user_access_router))
                                        <td><a href="{{ route('admin.categories.edit', ['id' => $item->id, 'type' => $type]) }}">{{ $item->title }}</a></td>
                                    @else
                                        <td>{{ $item->title }}</td>
                                    @endif
                                    <td>
                                        <strong class="text-success">{{ App\Models\Category::types[$item->type] }}</strong>
                                    </td>
                                    <td>
                                        <small>
                                            {{ @$item->user->email }}<br />
                                            {{ $item->updated_at->format('d-m-Y H:i') }}
                                        </small>
                                    </td>
                                    <td>
                                        @if( in_array('admin.categories.action',@$user_access_router))
                                            <a title="Thay đổi trạng thái" href="javascript:;" data-url="{{ route('admin.categories.action') }}" data-id="{{ $item->id }}" data-status="{{ $item->status }}" class="btn-status label label-sm label-{{ $item->status ? 'success' : 'danger' }}">
                                                {{ $item->status ? 'Active' : 'Inactive' }}
                                            </a>
                                        @else
                                            <a title="Thay đổi trạng thái" href="javascript:;" class="btn-status label label-sm label-{{ $item->status ? 'success' : 'danger' }}">
                                                {{ $item->status ? 'Active' : 'Inactive' }}
                                            </a>
                                        @endif

                                    </td>
                                    <td>
                                        @if( in_array('admin.categories.edit',@$user_access_router))
                                            <a title="Sửa danh mục" href="{{ route('admin.categories.edit', ['id' => $item->id, 'type' => $type]) }}" class="btn btn-sm btn-info"><i class="fa fa-edit"></i></a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="row mbm">
                        <div class="col-sm-3">
                            <span class="record-total">Tổng: {{ $categories->total() }} bản ghi</span>
                        </div>
                        <div class="col-sm-6 text-center">
                            <div class="pagination-panel">
                                {{ $categories->appends(Request::all())->onEachSide(1)->links() }}
                            </div>
                        </div>
                        <div class="col-sm-3 text-right">
                            <span class="form-inline">
                                Hiển thị
                                <select name="per_page" class="form-control" data-target="#form-categories">
                                    @php $list = [10, 20, 50, 100, 200]; @endphp
                                    @foreach ($list as $num)
                                    <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                                    @endforeach
                                </select>
                            </span>
                        </div>
                    </div>
                </form><!-- END #form-categories -->
            @endif
        </div>
    </div>
</section>

@endsection

@section('javascript')

<script>
    sidebar('{{ $type }}', 'category');
</script>

@endsection