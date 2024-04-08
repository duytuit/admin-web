@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        Danh sách Ý kiến
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">{{ $heading }}</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-body ">
            <form id="form-search" action="{{ route('admin.feedback.index') }}" method="get">
                <div class="row form-group">
                    <div class="col-sm-8">
                        <span class="btn-group">
                            <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">Tác
                                vụ <span class="caret"></span></button>
                            <ul class="dropdown-menu">
                                <li><a class="btn-action" data-target="#form-feedback" data-method="delete" href="javascript:;"><i class="fa fa-trash"></i> Xóa</a></li>
                                <li><a class="btn-action" data-target="#form-feedback" data-method="active" href="javascript:;"><i class="fa fa-check"></i> Active</a></li>
                                <li><a class="btn-action" data-target="#form-feedback" data-method="inactive" href="javascript:;"><i class="fa fa-times"></i> Inactive</a></li>
                            </ul>
                        </span>
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

            <form id="form-search-advance" action="{{ route('admin.feedback.index') }}" method="get">
                <div id="search-advance" class="search-advance" style="display: {{ $advance ? 'block' : 'none' }};">
                    <div class="row form-group space-5">
                        <div class="col-sm-2">
                            <input type="text" name="keyword" value="{{ $keyword }}" placeholder="Nhập từ khóa" class="form-control" />
                        </div>
                        <div class="col-sm-2">
                            <select name="type" class="form-control">
                                <option value="">Phân loại</option>
                                <option value="product" {{ $type == 'product' ? 'selected' : '' }}>Sản phẩm</option>
                                <option value="service" {{ $type == 'service' ? 'selected' : '' }}>Dịch vụ</option>
                                <option value="user" {{ $type == 'user' ? 'selected' : '' }}>Nhân viên</option>
                                <option value="other" {{ $type == 'other' ? 'selected' : '' }}>Khác</option>
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <select name="rating" class="form-control" style="width: 100%;">
                                <option value="">Đánh giá</option>
                                <option value="0 {{ $rating === '0' ? 'selected' : '' }}">0 sao</option>
                                <option value="1 {{ $rating === '1' ? 'selected' : '' }}">1 sao</option>
                                <option value="2 {{ $rating === '2' ? 'selected' : '' }}">2 sao</option>
                                <option value="3 {{ $rating === '3' ? 'selected' : '' }}">3 sao</option>
                                <option value="4 {{ $rating === '4' ? 'selected' : '' }}">4 sao</option>
                                <option value="5 {{ $rating === '5' ? 'selected' : '' }}">5 sao</option>
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <select name="status" class="form-control" style="width: 100%;">
                                <option value="">Trạng thái</option>
                                <option value="1" {{ $status === '1' ? 'selected' : '' }}>Đã duyệt</option>
                                <option value="0" {{ $status === '0' ? 'selected' : '' }}>Chưa duyệt</option>
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <input type="text" name="name" value="{{ $name }}" placeholder="Người viết" class="form-control" />
                        </div>
                        <div class="col-sm-2">
                            <button class="btn btn-warning btn-block">Tìm kiếm</button>
                        </div>
                    </div>
                </div>
            </form><!-- END #form-search-advance -->

            <form id="form-feedback" action="{{ route('admin.feedback.action') }}" method="post">
                @csrf
                <input type="hidden" name="method" value="" />
                <input type="hidden" name="status" value="" />

                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="bg-primary">
                            <tr>
                                <th width="30"><input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /></th>
                                <th width="30">ID</th>
                                <th width="180">Tiêu đề</th>
                                <th>Ý kiến</th>
                                <th width="90">Phân loại</th>
                                <th width="100">Đánh giá</th>
                                <th width="125">Người viết</th>
                                <th width="70">Status</th>
                                <th width="125">Tác vụ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($feedback as $item)
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="{{ $item->id }}" class="iCheck checkSingle" /></td>
                                <td>{{ $item->id }}</td>
                                <td><a href="{{ route('admin.feedback.detail', ['id' => $item->id]) }}">{{ $item->title }}</a></td>
                                <td>
                                    <div class="comment-detail">
                                        {{ $item->content }}
                                    </div>
                                </td>
                                <td>
                                    <strong>{{ $types[$item->type] ?? '' }}</strong>
                                </td>
                                <td>
                                    @php
                                    $rating = (int)$item->rating;
                                    $empty = 5 - $rating;
                                    @endphp
                                    <span class="rating">
                                        @for($i=1; $i<=$rating; $i++) <i class="fa fa-star"></i> @endfor
                                            @for($i=1; $i<=$empty; $i++) <i class="fa fa-star-o"></i> @endfor
                                    </span>
                                </td>
                                <td>
                                    <small>
                                        {{ $item->customer ? $item->customer->name : 'Không rõ' }}<br />
                                        {{ $item->updated_at->format('d-m-Y H:i') }}
                                    </small>
                                </td>
                                <td>
                                    <a title="Thay đổi trạng thái" href="javascript:;" data-url="{{ url('admin/feedback/action') }}" data-id="{{ $item->id }}" data-status="{{ $item->status }}" class="btn-status label label-sm label-{{ $item->status ? 'success' : 'danger' }}">
                                        {{ $item->status ? 'Active' : 'Inactive' }}
                                    </a>
                                </td>
                                <td>
                                    <a title="Xem ý kiến" href="{{ route('admin.feedback.detail', ['id' => $item->id]) }}" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a>
                                    <a title="Trả lời ý kiến" href="{{ url('admin/feedback/detail/' . $item->id . '#reply') }}" class="btn btn-sm btn-reply btn-warning"><i class="fa fa-comments"></i></a>
                                    <a title="Xóa ý kiến" href="javascript:;" data-url="{{ route('admin.feedback.action') }}" data-id="{{ $item->id }}" class="btn btn-sm btn-delete btn-danger"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="row mbm">
                    <div class="col-sm-3">
                        <span class="record-total">Tổng: {{ $feedback->total() }} bản ghi</span>
                    </div>
                    <div class="col-sm-6 text-center">
                        <div class="pagination-panel">
                            {{ $feedback->appends(Request::all())->onEachSide(1)->links() }}
                        </div>
                    </div>
                    <div class="col-sm-3 text-right">
                        <span class="form-inline">
                            Hiển thị
                            <select name="per_page" class="form-control" data-target="#form-articles">
                                @php $list = [10, 20, 50, 100, 200]; @endphp
                                @foreach ($list as $num)
                                <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                                @endforeach
                            </select>
                        </span>
                    </div>
                </div>
            </form><!-- END #form-feedback -->
        </div>
    </div>
</section>

@endsection

@section('javascript')

<script>
    sidebar('feedback');
</script>

@endsection