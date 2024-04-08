@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        Danh sách ngân hàng
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">{{ $heading }}</li>
    </ol>
</section>
<section class="content">
    <div class="box box-primary">
        <div class="box-body ">
            @if( in_array('admin.banks.index',@$user_access_router))
                <form id="form-search" action="{{ route('admin.banks.index') }}" method="post">
                    {{ csrf_field() }}
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
                        <span class="btn-group">
                            <a href="{{ route('admin.banks.create') }}" class="btn btn-success"><i class="fa fa-plus"></i>&nbsp;&nbsp;Thêm mới</a>
                        </span>
                        </div>
                        <div class="col-sm-4 text-right">
                            <div class="input-group">
                                <input type="text" name="keyword" value="{{ !empty($data_search['keyword'])?$data_search['keyword']:'' }}" placeholder="Nhập từ khóa" class="form-control" />
                                <div class="input-group-btn">
                                    <button type="submit" class="btn btn-info"><span class="fa fa-search"></span></button>
                                    <button type="button" class="btn btn-warning btn-search-advance" data-toggle="show" data-target=".search-advance"><span class="fa fa-filter"></span></button>
                                </div>

                            </div>
                        </div>
                    </div>
                </form><!-- END #form-search -->
            @endif
            @if( in_array('admin.banks.index',@$user_access_router))
                    <form id="form-search-advance" action="{{ route('admin.banks.index') }}" method="post">
                        {{ csrf_field() }}
                        <div id="search-advance" class="search-advance" style="display: {{ $advance ? 'block' : 'none' }};">
                            <div class="row form-group space-5">
                                <div class="col-sm-2">
                                    <input type="text" name="keyword" value="{{ !empty($data_search['keyword'])?$data_search['keyword']:'' }}" placeholder="Nhập từ khóa" class="form-control" />
                                </div>
                                <div class="col-sm-2">
                                    <select name="status" class="form-control" style="width: 100%;">
                                        <option value="">Trạng thái</option>
                                        <?php $status = !empty($data_search['status'])?$data_search['status']:'';?>
                                        <option value="1" {{ $status === '1' ? 'selected' : '' }}>Đã duyệt</option>
                                        <option value="0" {{ $status === '0' ? 'selected' : '' }}>Chưa duyệt</option>
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    <button class="btn btn-warning btn-block">Tìm kiếm</button>
                                </div>
                            </div>
                        </div>
                    </form><!-- END #form-search-advance -->
            @endif
            @if( in_array('admin.banks.action',@$user_access_router))
                    <form id="form-feedback" action="{{ route('admin.banks.action') }}" method="post">
                        @csrf
                        <input type="hidden" name="method" value="" />
                        <input type="hidden" name="status" value="" />

                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-bordered">
                                <thead class="bg-primary">
                                <tr>
                                    <th width="30"><input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /></th>
                                    <th width="30">ID</th>
                                    <th width="180">Tên ngân hàng</th>
                                    <th width="180">Tên gọi</th>
                                    <th>Link internet banking</th>
                                    <th>Logo</th>
                                    <th width="70">Status</th>
                                    <th width="125">Tác vụ</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($lists as $item)
                                    <tr>
                                        <td><input type="checkbox" name="ids[]" value="{{ $item->id }}" class="iCheck checkSingle" /></td>
                                        <td>{{ $item->id }}</td>
                                        <td>{{ @$item->title }}</td>
                                        <td>{{ @$item->alias }}</td>
                                        <td>
                                            <a href="{{ $item->url }}">Internet banking {{ @$item->alias }}</a>
                                        </td>
                                        <td>
                                            <img src="{{ url('/').'/'.$item->logo }}" alt="{{ @$item->alias }}" width="150" height="100">
                                        </td>
                                        <td>
                                            @if( in_array('admin.banks.action',@$user_access_router))
                                                <a title="Thay đổi trạng thái" href="javascript:;" data-url="{{ route('admin.banks.action') }}" data-id="{{ $item->id }}" data-status="{{ $item->status }}" class="btn-status label label-sm label-{{ $item->status ? 'success' : 'danger' }}">
                                                    {{ $item->status ? 'Ẩn' : 'Hiện' }}
                                                </a>
                                            @else
                                                <a title="Thay đổi trạng thái" href="javascript:;" class="btn-status label label-sm label-{{ $item->status ? 'success' : 'danger' }}">
                                                    {{ $item->status ? 'Ẩn' : 'Hiện' }}
                                                </a>
                                            @endif

                                        </td>
                                        <td>
                                            @if( in_array('admin.banks.action',@$user_access_router))
                                                <a title="Xóa ý kiến" href="javascript:;" data-url="{{ route('admin.banks.action') }}" data-id="{{ $item->id }}" class="btn btn-sm btn-delete btn-danger"><i class="fa fa-trash"></i></a>
                                            @endif
                                        </td>
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
                            <select name="per_page" class="form-control" data-target="#form-feedback">
                                @php $list = [10, 20, 50, 100, 200]; @endphp
                                @foreach ($list as $num)
                                    <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                                @endforeach
                            </select>
                        </span>
                            </div>
                        </div>
                    </form><!-- END #form-feedback -->
            @endif
        </div>
    </div>
</section>

@endsection

@section('javascript')

<script>
    $(function () {
        get_data_select({
            object: '#sel-name',
            url: '{{ route('admin.feedback.ajax_get_profile') }}',
            data_id: 'id',
            data_text: 'display_name',
            title_default: 'Người gửi'
        });
        get_data_select({
            object: '#ip-apartment,#ip-ap_id',
            url: '{{ url('admin/apartments/ajax_get_apartment') }}',
            data_id: 'id',
            data_text: 'name',
            title_default: 'Chọn căn hộ'
        });
        function get_data_select(options) {
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

    });
    sidebar('feedback');
</script>

@endsection