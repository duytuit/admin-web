@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        KQ Sự kiện
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Sự kiện</li>
    </ol>
</section>

<section class="content">
    <h4 class="bg-success" style="margin: 0px 0px 15px; padding: 15px;">KQ Sự kiện: {{ $article->title }} ( <a href="{{ route('admin.articles.edit', ['id' => $article->id, 'type' => $type] ) }}">Chi tiết</a> )</h4>
    <div class="box box-primary">
        <div class="box-body ">
            <form id="form-search" action="{{ route('admin.articles.events', ['id' => $article->id, 'type' => $type]) }}" method="post">
                <div class="row form-group">
                    <div class="col-sm-8">
                        <span class="btn-group">
                            <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">Tác vụ <span class="caret"></span></button>
                            <ul class="dropdown-menu">
                            </ul>
                        </span>
                        @php
                            $param =[
                                'id' => $article->id,
                                'check_in' => $check_in,
                                'keyword' => $keyword,
                                'type' => $type
                            ];
                        @endphp
                        <a href="{{ route('admin.articles.events.export', $param) }}" class="btn btn-success"><i class="fa fa-file-excel-o"></i> Xuất excel</a>
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

            <form id="form-search-advance" action="{{ route('admin.articles.events', ['id' => $article->id, 'type' => $type]) }}" method="get">
                <div id="search-advance" class="search-advance" style="display: none;">
                    <div class="row form-group space-5">
                        <div class="col-sm-5">
                            <input type="text" name="keyword" value="{{ $keyword }}" placeholder="Nhập từ khóa" class="form-control" />
                        </div>
                        <div class="col-sm-3">
                            <input type="text" placeholder="Người đăng ký" class="form-control" />
                        </div>
                        <div class="col-sm-2">
                            <select name="check_in" class="form-control" style="width: 100%;">
                                <option value="">Check In</option>
                                <option value="1">Đã Check In</option>
                                <option value="0">Chưa Check In</option>
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <button class="btn btn-warning btn-block">Tìm kiếm</button>
                        </div>
                    </div>
                </div>
            </form><!-- END #form-search-advance -->

            <form id="form-events" action="{{ route('admin.articles.events.action', ['id' => $article->id, 'type' => $type]) }}" method="post">
                @csrf
                <input type="hidden" name="method" value="" />
                <input type="hidden" name="status" value="" />
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="bg-primary">
                            <tr>
                                <th width="30"><input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /></th>
                                <th width="30">ID</th>
                                <th width="150">Người đăng ký</th>
                                <th width="120">Điện thoại</th>
                                <th width="120">Email</th>
                                <th width="100">Nhóm KH</th>
                                <th width="110">Cập nhật</th>
                                <th width="110">Check In</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($events as $item)
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="{{ $item->id }}" class="iCheck checkSingle" /></td>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->user->name }}</td>
                                <td>{{ $item->user->phone }}</td>
                                <td>{{ $item->user->email }}</td>
                                <td>{{ $item->user_type }}</td>
                                <td>{{ $item->updated_at->format('d-m-Y H:i:s') }}</td>
                                <td>{{ $item->check_in ? $item->check_in->format('d-m-Y H:i:s') : '' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="row mbm">
                    <div class="col-sm-3">
                        <span class="record-total">Tổng: {{ $events->total() }} bản ghi</span>
                    </div>
                    <div class="col-sm-6 text-center">
                        <div class="pagination-panel">
                            {{ $events->appends(Request::all())->onEachSide(1)->links() }}
                        </div>
                    </div>
                    <div class="col-sm-3 text-right">
                        <span class="form-inline">
                            Hiển thị
                            <select name="per_page" class="form-control" data-target="#form-events">
                                @php $list = [10, 20, 50, 100, 200]; @endphp
                                @foreach ($list as $num)
                                <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                                @endforeach
                            </select>
                        </span>
                    </div>
                </div>
            </form><!-- END #form-events -->
        </div>
    </div>
</section>

@endsection

@section('javascript')

<script>
sidebar('event', 'index');
</script>

@endsection
