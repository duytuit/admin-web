@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        Danh sách tài khoản ngân hàng
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Tài khoản ngân hàng</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-body ">
            
                <form id="form-search" action="{{ route('admin.building.info.index') }}" method="get">

                    <div class="row form-group">
                        <div class="col-sm-8">
                            @if(in_array('admin.building.info.action',@$user_access_router))
                                <span class="btn-group">
                                    <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">Tác vụ <span class="caret"></span></button>
                                    <ul class="dropdown-menu">
                                        <li><a class="btn-action" data-target="#form-payment_info" data-method="delete" href="javascript:;"><i class="fa fa-trash"></i> Xóa</a></li>
                                    </ul>
                                </span>
                            @endif
                            @if( in_array('admin.building.info.create',@$user_access_router))
                                <a href="{{ route('admin.building.info.create') }}" class="btn btn-info"><i class="fa fa-edit"></i> Thêm mới</a>
                                <a href="{{ route('admin.building.info.export',Request::all()) }}" class="btn btn-success"><i class="fa fa-edit"></i> Export</a>
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

                <form id="form-payment_info" action="{{ route('admin.building.info.action') }}" method="post">
                    @csrf
                    <input type="hidden" name="method" value="" />
                    <input type="hidden" name="status" value="" />

                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-bordered">
                            <thead class="bg-primary">
                                <tr>
                                    <th width="3%"><input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /></th>
                                    <th width="3%">ID</th>
                                    <th width="8%">Mã TK</th>
                                    <th width="8%">Số TK</th>
                                    <th width="30%">Ngân hàng</th>
                                    <th width="8%">Chủ tài khoản</th>
                                    <th width="8%">Chi nhánh</th>
                                    <th width="15%">Người cập nhật</th>
                                    <th width="8%">Mặc định</th>
                                    <th width="8%">Hiển thị App</th>
                                    <th width="15">Tác vụ</th>
                                </tr>
                            </thead>
                            <tbody>

                                @foreach ($payment_info as $item)
                                <tr>
                                    <td><input type="checkbox" name="ids[]" value="{{ $item->id }}" class="iCheck checkSingle" /></td>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->code }}</td>
                                    <td>{{ $item->bank_account }}</td>
                                    <td>{{ $item->bank_name }}</td>
                                    <td>{{ $item->holder_name }}</td>
                                    <td>{{ $item->branch }}</td>
                                    <td>
                                        <small>
                                            {{ @$item->user->email }}<br />
                                            {{ $item->updated_at->format('d-m-Y H:i') }}
                                        </small>
                                    </td>
                                    <td>
                                        <a title="Thay đổi trạng thái" href="javascript:;" data-url="{{ route('admin.building.info.action') }}" data-method="web_status" data-id="{{ $item->id }}" data-status="{{ $item->web_status }}" class="btn-status label label-sm label-{{ @$item->web_status == 1 ? 'success' : 'danger' }}">
                                            {{ $item->web_status == 1 ? 'Active' : 'Inactive' }}
                                        </a>
                                    </td>
                                    <td>
                                        <a title="Thay đổi trạng thái" href="javascript:;" data-url="{{ route('admin.building.info.action') }}" data-method="app_status" data-id="{{ $item->id }}" data-status="{{ $item->app_status }}" class="btn-status label label-sm label-{{ @$item->app_status == 1 ? 'success' : 'danger' }}">
                                            {{ $item->app_status == 1 ? 'Active' : 'Inactive' }}
                                        </a>
                                    </td>
                                    <td>
                                        @if( in_array('admin.building.info.edit',@$user_access_router))
                                            <a title="Sửa danh mục" href="{{ route('admin.building.info.edit', ['id' => $item->id]) }}" class="btn btn-sm btn-info"><i class="fa fa-edit"></i></a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="row mbm">
                        <div class="col-sm-3">
                            <span class="record-total">Tổng: {{ $payment_info->total() }} bản ghi</span>
                        </div>
                        <div class="col-sm-6 text-center">
                            <div class="pagination-panel">
                                {{ $payment_info->appends(Request::all())->onEachSide(1)->links() }}
                            </div>
                        </div>
                        <div class="col-sm-3 text-right">
                            <span class="form-inline">
                                Hiển thị
                                <select name="per_page" class="form-control" data-target="#form-payment_info">
                                    @php $list = [10, 20, 50, 100, 200]; @endphp
                                    @foreach ($list as $num)
                                    <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                                    @endforeach
                                </select>
                            </span>
                        </div>
                    </div>
                </form><!-- END #form-payment_info -->
        </div>
    </div>
</section>

@endsection