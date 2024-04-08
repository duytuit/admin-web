@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            Danh sách bình luận
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">{{ $heading }}</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-body ">
                {{--@if( in_array('admin.posts.listcomments',@$user_access_router))
                    <form id="form-search" action="{{ route($route) }}" method="get">
                        <input type="hidden" name="type" value="{{ $type }}" />

                        <div class="row form-group">
                            <div class="col-sm-8">
                        <span class="btn-group">
                            <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">Tác vụ <span class="caret"></span></button>
                            <ul class="dropdown-menu">
                                <li><a class="btn-action" data-target="#form-posts" data-method="delete" href="javascript:;"><i class="fa fa-trash"></i> Xóa</a></li>

                                <li><a class="btn-action" data-target="#form-posts" data-method="active" href="javascript:;"><i class="fa fa-check"></i> Active</a></li>
                                <li><a class="btn-action" data-target="#form-posts" data-method="inactive" href="javascript:;"><i class="fa fa-times"></i> Inactive</a></li>
                            </ul>
                        </span>
                                @if( in_array('admin.posts.create',@$user_access_router))
                                    <a href="{{ route('admin.posts.create', ['type' => $type]) }}" class="btn btn-info"><i class="fa fa-edit"></i> Thêm mới</a>
                                @endif
                                @if($type == 'voucher')
                                    <a href="javascript:;" class="btn btn-warning" data-toggle="modal" data-target="#check-code"><i class="fa fa-qrcode"></i> Nhập mã</a>
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
                @endif
                @if(in_array($route,@$user_access_router))
                    <form id="form-search-advance" action="{{ route($route) }}" method="get">
                        <input type="hidden" name="type" value="{{ $type }}" />

                        <div id="search-advance" class="search-advance" style="display: {{ $advance ? 'block' : 'none' }};">
                            <div class="row form-group space-5">
                                <div class="col-sm-2">
                                    <input type="text" name="keyword" value="{{ $keyword }}" placeholder="Tiêu đề" class="form-control" />
                                </div>
                                <div class="col-sm-2">
--}}{{--                                    <input type="text" name="hashtag" value="{{ $hashtag }}" placeholder="Hashtag" class="form-control" />--}}{{--
                                    <select name="customer" id="ip-customer" class="form-control" style="width: 100%;">
                                        <option value="">Chọn người viết</option>
                                        @if($customer)
                                            <option value="{{$customer->id}}" selected>{{$customer->display_name}}</option>
                                        @endif
                                    </select>
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
                                <div class="col-sm-2 hidden">
                                    <select name="private" class="form-control">
                                        <option value="">Thông tin</option>
                                        <option value="1" {{ $private === '1' ? 'selected' : '' }}>Nội bộ</option>
                                        <option value="0" {{ $private === '0' ? 'selected' : '' }}>Công khai</option>
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    <button class="btn btn-warning btn-block">Tìm kiếm</button>
                                </div>
                            </div>
                        </div>
                    </form><!-- END #form-search-advance -->
                @endif--}}
                    <form id="form-posts" action="{{ route('admin.posts.action') }}" method="post">
                        @csrf
                        <input type="hidden" name="method" value="" />
                        <input type="hidden" name="status" value="" />
                        <input type="hidden" name="type" value="{{ @$type }}" />

                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-bordered">
                                <thead class="bg-primary">
                                <tr>
                                    <th width="30"><input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /></th>
                                    <th width="30">STT</th>
                                    <th width="30%">Bài viết</th>
                                    <th>Nội dung bình luận</th>
                                    <th width="125">Người bình luận</th>
                                    <th width="140">Thời giạn bình luận</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($lists as $key => $item)
                                    <tr valign="middle">
                                        <td><input type="checkbox" name="ids[]" value="{{ $item->id }}" class="iCheck checkSingle" /></td>
                                        <td>{{ $key+1 }}</td>
                                        <td><a href="{{ route('admin.comments.comments', ['id' => $item->id, 'type' => $item->type]) }}">{{ @$item->post->title }}</a></td>
                                        <td>{{ @$item->content }}</td>
                                        <td>{{ @$item->userInfo->display_name }}</td>
                                        <td>{{ @date('d-m-Y H:i', strtotime($item->created_at))}}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="row mbm">
                            <div class="col-sm-3">
                                <span class="record-total">Tổng: {{ $lists->total() }} bản ghi</span>
                            </div>
                            <div class="col-sm-6 text-center">
                                <div class="pagination-panel">
                                    {{ $lists->appends(Request::all())->onEachSide(1)->links() }}
                                </div>
                            </div>
                            <div class="col-sm-3 text-right">
                            <span class="form-inline">
                                Hiển thị
                                <select name="per_page" class="form-control" data-target="#form-posts">
                                    @php $list = [10, 20, 50, 100, 200]; @endphp
                                    @foreach ($list as $num)
                                        <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                                    @endforeach
                                </select>
                            </span>
                            </div>
                        </div>
                    </form><!-- END #form-posts -->
            </div>
        </div>
    </section>

    <div id="check-code" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <form action="{{--{{ route('admin.posts.add.register', ['type' => $type]) }}--}}" method="post" id="check-in-register" class="form-validate">
            @csrf
            <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Nhập mã</h4>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger print-error-msg" style="display:none">
                            <ul></ul>
                        </div>
                        <div class="alert alert-success print-success-msg" style="display:none">
                            <ul></ul>
                        </div>

                        <div class="form-group">
                            <label class="control-label">Mã khuyến mại<span class="text-danger">*</span></label>
                            <input type="text" required class="form-control" name="code" placeholder="Mã khách hàng" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
                        <button class="btn btn-primary btn-changer-code" style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận</button>
                        <input type="submit" class="btn-submit-code hidden" />
                    </div>
                </div>
            </form>

        </div>
    </div>

@endsection

@section('javascript')

    <script>
        get_data_select_apartment({
            object: '#ip-customer',
            url: '{{ route('admin.customers.ajax_get_cus') }}',
            data_id: 'id',
            data_text: 'display_name',
            title_default: 'Chọn Người viết'
        });
        function get_data_select_apartment(options) {
            $(options.object).select2({
                ajax: {
                    url: options.url,
                    dataType: 'json',
                    data: function(params) {
                        var query = {
                            search: params.term,
                        }
                        return query;
                    },
                    processResults: function(json, params) {
                        var results = [{
                            id: '',
                            text: options.title_default
                        }];

                        for (i in json.data) {
                            var item = json.data[i];
                            results.push({
                                id: item[options.data_id],
                                text: item[options.data_text]
                            });
                        }
                        return {
                            results: results,
                        };
                    },
                    minimumInputLength: 3,
                }
            });
        }
        $(".btn-changer-code").click(function(e) {
            e.preventDefault();

            var _token = $("[name='_token']").val();
            var code = $("input[name='code']").val();

            $.ajax({
                url: "@if( in_array('admin.posts.add.register',@$user_access_router)){{ route('admin.posts.add.register') }}@endif",
                type: 'POST',
                data: {
                    _token: _token,
                    code: code,
                },
                success: function(data) {
                    if ($.isEmptyObject(data.error_code)) {
                        printSuccessMsg(data.msg);
                    } else {
                        printErrorMsg(data.error_code);
                    }
                }
            });
        });

        function printErrorMsg(msg) {
            $(".print-success-msg").find("ul").html('');
            $(".print-error-msg").find("ul").html('');

            $(".print-success-msg").css('display', 'none');
            $(".print-error-msg").css('display', 'block');
            $.each(msg, function(key, value) {
                $(".print-error-msg").find("ul").append('<li>' + value + '</li>');
            });
        }

        function printSuccessMsg(msg) {
            $(".print-success-msg").find("ul").html('');
            $(".print-error-msg").find("ul").html('');

            $(".print-error-msg").css('display', 'none');
            $(".print-success-msg").css('display', 'block');

            $(".print-success-msg").find("ul").append('<li>' + msg + '</li>');
        }
        $(document).on('click', '.onoffswitch-label', function (e) {
            var div = $(this).parents('div.onoffswitch');
            var input = div.find('input');

            var id = input.attr('data-id');
            if (input.attr('checked')) {
                var checked = 0;
            } else {
                var checked = 1;
            }
            if (!requestSend) {
                requestSend = true;
                $.ajax({
                    url: input.attr('data-url'),
                    type: 'put',
                    data: {
                        id: id,
                        status: checked
                    },
                    success: function (response) {
                        if (response.success == true) {
                            toastr.success(response.message);
                        } else {
                            toastr.error('Không thay đổi trạng thái');
                        }
                        requestSend = false;
                    }
                });
            } else {
                e.preventDefault();
            }
        })
    </script>
    <script>
        sidebar('{{ @$type }}', 'index');
    </script>

@endsection