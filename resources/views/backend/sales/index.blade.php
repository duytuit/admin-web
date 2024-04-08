@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        Khách hàng
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">{{ $heading }}</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-body ">
            <form id="form-search" class="hidden" action="{{ route('admin.sales.index') }}" method="get">
                <div class="row form-group">
                    <div class="col-sm-8">
                        <span class="btn-group">
                            <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">Tác
                                vụ <span class="caret"></span></button>
                            <ul class="dropdown-menu">
                                <li><a class="btn-action" data-target="#form-sales" data-method="delete" href="javascript:;"><i class="fa fa-trash"></i> Xóa</a></li>
                                <li><a class="btn-action" data-target="#form-sales" data-method="active" href="javascript:;"><i class="fa fa-check"></i> Active</a></li>
                                <li><a class="btn-action" data-target="#form-sales" data-method="inactive" href="javascript:;"><i class="fa fa-times"></i> Inactive</a></li>
                            </ul>
                        </span>
                    </div>
                    <div class="col-sm-4 text-right">
                        <div class="input-group">
                            <input type="text" name="name" value="{{ $name }}" placeholder="Nhập từ khóa" class="form-control" />
                            <div class="input-group-btn">
                                <button type="submit" class="btn btn-info"><span class="fa fa-search"></span></button>
                                <button type="button" class="btn btn-warning btn-search-advance" data-toggle="show" data-target=".search-advance"><span class="fa fa-filter"></span></button>
                            </div>

                        </div>
                    </div>
                </div>
            </form><!-- END #form-search -->

            <form id="form-search-advance" action="{{ route('admin.sales.index') }}" method="get">
                <div id="search-advance" class="search-advance">
                    <div class="row form-group space-5">
                        <div class="col-sm-3">
                            <input type="text" name="name" value="{{ $name }}" placeholder="Khách hàng" class="form-control" />
                        </div>
                        <div class="col-sm-3">
                            <input type="text" name="phone" value="{{ $phone }}" placeholder="Điện thoại" class="form-control" />
                        </div>
                        <div class="col-sm-3">
                            <input type="text" name="email" value="{{ $email }}" placeholder="Email" class="form-control" />
                        </div>
                        <div class="col-sm-3">
                            <button class="btn btn-warning btn-block">Tìm kiếm</button>
                        </div>
                    </div>
                </div>
            </form><!-- END #form-search-advance -->
            <form id="form-sales" action="{{ route('admin.sales.action') }}" method="post">
                @csrf
                <input type="hidden" name="method" value="" />
                <input type="hidden" name="status" value="" />

                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="bg-primary">
                            <tr>
                                <th width="30"><input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /></th>
                                <th width="30">ID</th>
                                <th>Chiến dịch</th>
                                <th width="140">Khách hàng</th>
                                <th width="140">Điện thoại</th>
                                <th width="180">Email</th>
                                <th width="140">Cập nhật</th>
                                <th width="100">Tác vụ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($customers as $item)
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="{{ $item->id }}" class="iCheck checkSingle" /></td>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->campaign->title }}</td>
                                <td>{{ $item->customer_name }}</td>
                                <td>{{ $item->customer_phone }}</td>
                                <td>{{ $item->customer_email }}</td>
                                <td>{{ $item->updated_at ? $item->updated_at->format('d-m-Y H:i') : '' }}</td>
                                <td>
                                    <a title="Thêm vào KH của tôi" href="{{ route('admin.sales.add', ['id' => $item->id]) }}" class="btn btn-sm btn-success"><i class="fa fa-plus"></i></a>
                                    <a title="Viết nhật ký" href="{{ route('admin.sales.diary', ['id' => $item->id]) }}" class="btn btn-sm btn-info"><i class="fa fa-edit"></i></a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="row mbm">
                    <div class="col-sm-3">
                        <span class="record-total">Tổng: {{ $customers->total() }} bản ghi</span>
                    </div>
                    <div class="col-sm-6 text-center">
                        <div class="pagination-panel">
                            {{ $customers->appends(Request::all())->onEachSide(1)->links() }}
                        </div>
                    </div>
                    <div class="col-sm-3 text-right">
                        <span class="form-inline">
                            Hiển thị
                            <select name="per_page" class="form-control" data-target="#form-sales">
                                @php $list = [10, 20, 50, 100, 200]; @endphp
                                @foreach ($list as $num)
                                <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                                @endforeach
                            </select>
                        </span>
                    </div>
                </div>
            </form><!-- END #form-sales -->
        </div>
    </div>
</section>

@endsection

@section('javascript')

<script>
    sidebar('sales', 'index');
</script>

@endsection