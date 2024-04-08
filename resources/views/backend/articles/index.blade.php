@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        Danh sách bài viết
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">{{ $heading }}</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-body ">
            <form id="form-search" action="{{ route('admin.articles.index') }}" method="get">
                <input type="hidden" name="type" value="{{ $type }}" />

                <div class="row form-group">
                    <div class="col-sm-8">
                        <span class="btn-group">
                            <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">Tác
                                vụ <span class="caret"></span></button>
                            <ul class="dropdown-menu">
                                <li><a class="btn-action" data-target="#form-articles" data-method="delete" href="javascript:;"><i class="fa fa-trash"></i> Xóa</a></li>
                                <li><a class="btn-action" data-target="#form-articles" data-method="active" href="javascript:;"><i class="fa fa-check"></i> Active</a></li>
                                <li><a class="btn-action" data-target="#form-articles" data-method="inactive" href="javascript:;"><i class="fa fa-times"></i> Inactive</a></li>
                            </ul>
                        </span>
                        <a href="{{ route('admin.articles.create', ['type' => $type]) }}" class="btn btn-info"><i class="fa fa-edit"></i> Thêm mới</a>
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

            <form id="form-search-advance" action="{{ route('admin.articles.index') }}" method="get">
                <input type="hidden" name="type" value="{{ $type }}" />

                <div id="search-advance" class="search-advance" style="display: {{ $advance ? 'block' : 'none' }};">
                    <div class="row form-group space-5">
                        <div class="col-sm-2">
                            <input type="text" name="keyword" value="{{ $keyword }}" placeholder="Tiêu đề" class="form-control" />
                        </div>
                        <div class="col-sm-2">
                            <input type="text" name="hashtag" value="{{ $hashtag }}" placeholder="Hashtag" class="form-control" />
                        </div>
                        <div class="col-sm-2">
                            <select name="category_id" class="form-control">
                                <option value="">Danh mục</option>
                                @foreach ($categories as $item)
                                <option value="{{ $item->id }}" {{ $item->id == $category_id ? 'selected' : '' }}>{{ $item->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <select name="status" class="form-control">
                                <option value="">Trạng thái</option>
                                <option value="1" {{ $status === '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ $status === '0' ? 'selected' : '' }}>Inactive</option>
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

            <form id="form-articles" action="{{ route('admin.articles.action') }}" method="post">
                @csrf
                <input type="hidden" name="method" value="" />
                <input type="hidden" name="status" value="" />
                <input type="hidden" name="type" value="{{ $type }}" />

                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="bg-primary">
                            <tr>
                                <th width="30"><input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /></th>
                                <th width="30">ID</th>
                                <th>Tiêu đề</th>
                                <th width="120">Danh mục</th>
                                <th width="120">{{ $type == 'voucher' ? 'Hiệu lực' : 'Hashtag' }}</th>
                                <th width="125">Người viết</th>
                                <th width="50">Status</th>
                                @if (in_array($type, ['event', 'voucher']))
                                <th width="170">Tác vụ</th>
                                @else
                                <th width="130">Tác vụ</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($articles as $item)
                            <tr valign="middle">
                                <td><input type="checkbox" name="ids[]" value="{{ $item->id }}" class="iCheck checkSingle" /></td>
                                <td>{{ $item->id }}</td>
                                <td><a href="{{ route('admin.articles.edit', ['id' => $item->id, 'type' => $type]) }}">{{ $item->title }}</a></td>
                                <td>{{ $item->category->title }}</td>
                                <td>
                                    @if ($type == 'voucher')
                                    @php $expired = !($item->end_at && $item->end_at->gte($now)); @endphp
                                    <span class="btn-status label label-sm label-{{ $expired ? 'danger' : 'success' }}">
                                        {{ $expired ? 'Hết hạn' : 'Còn hạn' }}
                                    </span>
                                    @else
                                    {{ $item->hashtag }}
                                    @endif
                                </td>
                                <td>
                                    <small>
                                        {{ $item->user->ub_title }}<br />
                                        {{ $item->updated_at->format('d-m-Y H:i') }}
                                    </small>
                                </td>
                                <td>
                                    <a title="Thay đổi trạng thái" href="javascript:;" data-url="{{ route('admin.articles.action') }}" data-id="{{ $item->id }}" data-status="{{ $item->status }}" class="btn-status label label-sm label-{{ $item->status ? 'success' : 'danger' }}">
                                        {{ $item->status ? 'Active' : 'Inactive' }}
                                    </a>
                                </td>
                                <td>
                                    <a title="Sửa bài viết" href="{{ route('admin.articles.edit', ['id' => $item->id, 'type' => $type]) }}" class="btn btn-sm btn-info"><i class="fa fa-edit"></i></a>
                                    <a title="Xem bình luận" href="{{ route('admin.articles.comments', ['id' => $item->id, 'type' => $type]) }}" class="btn btn-sm btn-warning"><i class="fa fa-comments"></i></a>
                                    @if ($item->type == 'event')
                                    <a title="KQ sự kiện" href="{{ route('admin.articles.events', ['id' => $item->id, 'type' => $type]) }}" class="btn btn-sm btn-success"><i class="fa fa-calendar"></i></a>
                                    @endif
                                    @if ($item->type == 'voucher')
                                    <a title="KQ khuyến mại" href="{{ route('admin.articles.vouchers', ['id' => $item->id, 'type' => $type]) }}" class="btn btn-sm btn-success"><i class="fa fa-gift"></i></a>
                                    @endif
                                    <a title="Xóa bài viết" href="javascript:;" data-url="{{ route('admin.articles.action') }}" data-id="{{ $item->id }}" class="btn btn-sm btn-delete btn-danger"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="row mbm">
                    <div class="col-sm-3">
                        <span class="record-total">Tổng: {{ $articles->total() }} bản ghi</span>
                    </div>
                    <div class="col-sm-6 text-center">
                        <div class="pagination-panel">
                            {{ $articles->appends(Request::all())->onEachSide(1)->links() }}
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
            </form><!-- END #form-articles -->
        </div>
    </div>
</section>

@endsection

@section('javascript')

<script>
    sidebar('{{ $type }}', 'index');
</script>

@endsection