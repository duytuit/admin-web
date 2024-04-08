@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        Bình luận
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">{{ $heading }}</li>
    </ol>
</section>


<section class="content">
    <div class="box box-primary">
        <div class="box-body ">
            <?php
            if($type == 'article'){
                $route = 'admin.comments.index';
            }elseif($type == 'event'){
                $route = 'admin.comments.index_event';
            }
            ?>
            @if(in_array($route,@$user_access_router))
                <form id="form-search" action="{{ route($route) }}" method="get">
                    <input type="hidden" name="type" value="{{ $type }}" />

                    <div class="row form-group">
                        <div class="col-sm-8">
                            <span class="btn-group">
                                <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">Tác vụ <span class="caret"></span></button>
                                <ul class="dropdown-menu">
                                    <li><a class="btn-action" data-target="#form-comments" data-method="delete" href="javascript:;"><i class="fa fa-trash"></i> Xóa</a></li>
                                    <li><a class="btn-action" data-target="#form-comments" data-method="active" href="javascript:;"><i class="fa fa-check"></i> Duyệt</a></li>
                                    <li><a class="btn-action" data-target="#form-comments" data-method="inactive" href="javascript:;"><i class="fa fa-times"></i> Bỏ duyệt</a></li>
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
            @endif
            @if(in_array($route,@$user_access_router))
                <form id="form-search-advance" action="{{ route($route) }}" method="get">
                    <input type="hidden" name="type" value="{{ $type }}" />

                    <div id="search-advance" class="search-advance" style="display: {{ $advance ? 'block' : 'none' }};">
                        <div class="row form-group space-5">
                            <div class="col-sm-2">
                                <input type="text" name="title" value="{{ $title }}" placeholder="Bài viết" class="form-control" />
                            </div>
                            <div class="col-sm-2">
                                <input type="text" name="keyword" value="{{ $keyword }}" placeholder="Bình luận" class="form-control" />
                            </div>
                            <div class="col-sm-2">
                                <select name="rating" class="form-control" style="width: 100%;">
                                    <option value="">Đánh giá</option>
                                    <option value="0">0 sao</option>
                                    <option value="1">1 sao</option>
                                    <option value="2">2 sao</option>
                                    <option value="3">3 sao</option>
                                    <option value="4">4 sao</option>
                                    <option value="5">5 sao</option>
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
            @endif
            @if( in_array('admin.comments.action',@$user_access_router))
                <form id="form-comments" action="{{ route('admin.comments.action') }}" method="post">
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
                                    <th width="200">Bài viết</th>
                                    <th>Bình luận</th>
                                    <th width="150">Người viết</th>
                                    <th width="90">Ngày đăng</th>
                                    <th width="70">Status</th>
                                    <th width="90">Tác vụ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($comments as $item)
                                <tr>
                                    <td><input type="checkbox" name="ids[]" value="{{ $item->id }}" class="iCheck checkSingle" /></td>
                                    <td>{{ $item->id }}</td>
                                    @if( in_array('admin.comments.comments',@$user_access_router))
                                        <td><a href="{{ route('admin.comments.comments', ['id' => $item->post_id, '#comment-' . $item->id]) }}" target="_blank">{{ $item->post->title ?? '' }}</a></td>
                                    @else
                                        <td>{{ $item->post->title ?? '' }}</td>
                                    @endif

                                    <td>
                                        <div class="comment-detail">
                                            {!! nl2br($item->content) !!}
                                        </div>
                                    </td>
                                    <td>{{ $item->user->BDCprofile->display_name??$item->user->email??'Unknow Users' }}</td>
                                    <td>{{ $item->updated_at->format('d-m-Y H:i:s') }}</td>
                                    <td>
                                        @if( in_array('admin.comments.action',@$user_access_router))
                                            <a title="Thay đổi trạng thái" href="javascript:;" data-url="{{ url('admin/comments/action') }}" data-id="{{ $item->id }}" data-status="{{ $item->status }}" class="btn-status label label-sm label-{{ $item->status ? 'success' : 'danger' }}">
                                                {{ $item->status ? 'Active' : 'Inactive' }}
                                            </a>
                                        @else
                                            <a title="Thay đổi trạng thái" href="javascript:;" class="btn-status label label-sm label-{{ $item->status ? 'success' : 'danger' }}">
                                                {{ $item->status ? 'Active' : 'Inactive' }}
                                            </a>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($item->parent_id)
                                        <a title="Trả lời bình luận" href="{{ url('admin/comments/' . $item->post_id . '/comments#reply-' . $item->parent_id) }}" class="btn btn-sm btn-reply btn-warning"><i class="fa fa-comments"></i></a>
                                        @else
                                        <a title="Trả lời bình luận" href="{{ url('admin/comments/' . $item->post_id . '/comments#reply-' . $item->id) }}" class="btn btn-sm btn-reply btn-warning"><i class="fa fa-comments"></i></a>
                                        @endif
                                        @if( in_array('admin.comments.action',@$user_access_router))
                                            <a title="Xóa bình luận" href="javascript:;" data-url="{{ url('admin/comments/action') }}" data-id="{{ $item->id }}" class="btn btn-sm btn-delete btn-danger"><i class="fa fa-trash"></i></a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="row mbm">
                        <div class="col-sm-3">
                            <span class="record-total">Tổng: {{ $comments->total() }} bản ghi</span>
                        </div>
                        <div class="col-sm-6 text-center">
                            <div class="pagination-panel">
                                {{ $comments->appends(Request::all())->onEachSide(1)->links() }}
                            </div>
                        </div>
                        <div class="col-sm-3 text-right">
                            <span class="form-inline">
                                Hiển thị
                                <select name="per_page" class="form-control" data-target="#form-comments">
                                    @php $list = [10, 20, 50, 100, 200]; @endphp
                                    @foreach ($list as $num)
                                    <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                                    @endforeach
                                </select>
                            </span>
                        </div>
                    </div>
                </form><!-- END #form-comments -->
            @endif
        </div>
    </div>
</section>

@endsection

@section('javascript')

<script>
    sidebar('{{ $type }}', 'comment');
</script>

@endsection