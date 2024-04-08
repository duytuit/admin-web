@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            QL bình chọn
            <small>Danh sách</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
            <li class="active">Câu hỏi bình chọn</li>
        </ol>
    </section>

    <section class="content" id="content-poll_option">
        <div class="box box-primary">
            <div class="box-header with-border">
                <form action="" method="get" id="form-search">
                    <div class="row">
                        <div class="col-sm-8 col-xs-12 ">
                            <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle pull-left" style="margin-right: 10px;">Tác vụ&nbsp;<span class="caret"></span></button>
                            <ul class="dropdown-menu">
                                <li><a href="javascript:" type="button" class="btn-action" data-target="#form-poll-option-action" data-method="delete"><i class="fa fa-trash text-danger"></i>&nbsp; Xóa</a></li>

                                <li>
                                    <a href="javascript:" type="button" class="btn-action" data-target="#form-poll-option-action" data-method="active"><i class="fa fa-check text-success"></i>&nbsp;Activate</a>
                                </li>
                                <li>
                                    <a href="javascript:" type="button" class="btn-action" data-target="#form-poll-option-action" data-method="inactive"><i class="fa fa-close text-warning"></i>&nbsp;Inactivate</a>
                                </li>
                            </ul>
                            @if( in_array('admin.polloptions.create',@$user_access_router))
                                <a href="{{ route('admin.polloptions.create') }}" type="buttom" class="btn btn-info"><i class="fa fa-plus"></i>&nbsp;&nbsp;Thêm mới</a>
                            @endif
                        </div>

                        <div class="col-sm-4 col-xs-12">
                            <div class="input-group">
                                <input type="text" class="form-control" name="title" placeholder="Nhập nội dung câu hỏi" value="{{ !empty($data_search['title']) ? $data_search['title'] : '' }}">
                                <div class="input-group-btn">
                                    <button type="submit" title="Tìm kiếm" class="btn btn-info"><i class="fa fa-search"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="clearfix" style="height: 15px;"></div>
                </form>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="table-responsive">
                    @if( in_array('admin.polloptions.action',@$user_access_router))
                        <form action="{{ route('admin.polloptions.action') }}" method="post" id="form-poll-option-action">
                            {{ csrf_field() }}
                            @method('post')
                            <input type="hidden" name="method" value="" />
                            <input type="hidden" name="status" value="" />

                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                <tr class="bg-primary">
                                    <th width='20px'>
                                        <input class="iCheck checkAll" type="checkbox" data-target=".checkSingle" />
                                    </th>
                                    <th width='20px'>#</th>
                                    <th>Câu hỏi</th>
                                    <th width='25%'>Bình chọn</th>
                                    <th width='25%'>Bài viết liên quan</th>
                                    <th width='10%' class="text-center">Last update</th>
                                    <th width='5%'>Trạng thái</th>
                                    <th width='10%'>Thao tác</th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach($poll_options as $poll_option)
                                    <tr>
                                        <td><input type="checkbox" class="iCheck checkSingle" value="{{ $poll_option->id }}" name="ids[]" /></td>
                                        <td>{{ $poll_option->id }}</td>
                                        <td>
                                            <a href="{{ route('admin.polloptions.edit',['poll_id' => $poll_option->id]) }}"> {{ $poll_option->title }} </a>
                                        </td>
                                        <td>
                                            <ul>
                                                @foreach ($poll_option->total_poll as $option)
                                                    <li>{{ $option['poll_title'] }} <span style="color: mediumblue">({{ $option['total'] }})</span></li>
                                                @endforeach
                                            </ul>
                                        </td>

                                        <td>{{ $poll_option->post ? $poll_option->post->title : '' }}</td>

                                        <td class="text-center">
                                            {{ @$poll_option->user->name }} <br />
                                            <span>{{$poll_option->updated_at->format('d-m-Y H:i')}}</span>
                                        </td>
                                        <td>
                                            @if( in_array('admin.polloptions.action',@$user_access_router))
                                                <a title="Thay đổi trạng thái" href="javascript:;" data-url="{{ route('admin.polloptions.action') }}" data-id="{{ $poll_option->id }}" data-status="{{ $poll_option->status }}" class="btn-status label label-sm label-{{ $poll_option->status ? 'success' : 'danger' }}">
                                                    {{ $poll_option->status ? 'Active' : 'Inactive' }}
                                                </a>
                                            @else
                                                <a title="Thay đổi trạng thái" href="javascript:;" class="btn-status label label-sm label-{{ $poll_option->status ? 'success' : 'danger' }}">
                                                    {{ $poll_option->status ? 'Active' : 'Inactive' }}
                                                </a>
                                            @endif
                                        </td>
                                        <td>
                                            @if( in_array('admin.polloptions.edit',@$user_access_router))
                                                <a href="{{ route('admin.polloptions.edit',['id' => $poll_option->id]) }}" type="button" class="btn btn-sm btn-info" title="Sửa"><i class="fa fa-edit"></i></a>
                                            @endif
                                            <a title="Danh sách bình chọn" href="{{ route('admin.polloptions.postPoll', ['id' => $poll_option->id]) }}" class="btn btn-sm btn-success"><i class="fa fa-calendar"></i></a>
                                            @if( in_array('admin.polloptions.action',@$user_access_router))
                                                <a title="Xóa" href="javascript:;" data-url="{{ route('admin.polloptions.action') }}" data-id="{{ $poll_option->id }}" class="btn btn-sm btn-delete btn-danger"><i class="fa fa-trash"></i></a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            <input type="submit" class="js-submit-form-index hidden" value="" />
                        </form>
                    @endif
                </div>
            </div>
            <!-- /.box-body -->
            <div class="box-footer clearfix">
                <div class="row">
                    <div class="col-sm-3">
                        <span class="record-total">Tổng: {{ $poll_options->total() }} bản ghi</span>
                    </div>
                    <div class="col-sm-6 text-center">
                        <div class="pagination-panel">
                            {{ $poll_options->appends(Request::all())->onEachSide(1)->links() }}
                        </div>
                    </div>
                    <div class="col-sm-3 text-right">
                    <span class="form-inline">
                        Hiển thị
                        <select name="per_page" class="form-control" data-target="#form-poll-option-action">
                            @php $list = [10, 20, 50, 100, 200]; @endphp
                            @foreach ($list as $num)
                                <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                            @endforeach
                        </select>
                    </span>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('javascript')
    <script>
        sidebar('poll-options', 'index');
    </script>
@endsection