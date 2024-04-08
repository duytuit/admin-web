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
    <?php
    if($type == 'fback'){
        $route = 'admin.feedback.index';
    }elseif($type == 'request'){
        $route = 'admin.feedback.index_request';
    }
    ?>
<section class="content">
    <div class="box box-primary">
        <div class="box-body ">
            @if(in_array($route,@$user_access_router))
                <form id="form-search" action="{{ route($route) }}" method="post">
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
            @if(in_array($route,@$user_access_router))
                    <form id="form-search-advance" action="{{ route($route) }}" method="post">
                        {{ csrf_field() }}
                        <div id="search-advance" class="search-advance" style="display: {{ $advance ? 'block' : 'none' }};">
                            <div class="row form-group space-5">
                                <div class="col-sm-2">
                                    <input type="text" name="keyword" value="{{ !empty($data_search['keyword'])?$data_search['keyword']:'' }}" placeholder="Nhập từ khóa" class="form-control" />
                                </div>

                                <div class="col-sm-2 hidden">
                                    <select name="rating" class="form-control" style="width: 100%;">
                                        <option value="">Đánh giá</option>
                                        <?php $rating = !empty($data_search['rating'])?$data_search['rating']:'';?>
                                        <option value="0 {{ $rating === '0' ? 'selected' : '' }}">0 sao</option>
                                        <option value="1 {{ $rating === '1' ? 'selected' : '' }}">1 sao</option>
                                        <option value="2 {{ $rating === '2' ? 'selected' : '' }}">2 sao</option>
                                        <option value="3 {{ $rating === '3' ? 'selected' : '' }}">3 sao</option>
                                        <option value="4 {{ $rating === '4' ? 'selected' : '' }}">4 sao</option>
                                        <option value="5 {{ $rating === '5' ? 'selected' : '' }}">5 sao</option>
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    <select name="apartment_id" id="ip-apartment"  class="form-control" style="width: 100%;">
                                        <option value="">Chọn căn hộ</option>
                                        <?php $search_apt = !empty($data_search['apartment'])?$data_search['apartment']:''; ?>
                                        @if($search_apt)
                                            <option value="{{$search_apt->id}}" selected>{{$search_apt->name}}</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    <select name="floor" id="ip-floor"  class="form-control select2" style="width: 100%;">
                                        <option value="">Tầng</option>
                                        <?php $search_floor = !empty($data_search['floor'])?$data_search['floor']:''; ?>
                                        @foreach ($floors as $item)
                                            <option value="{{ $item['floor'] }}" @if($item['floor'] == $search_floor) selected @endif >Tầng {{ $item['floor'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    <select name="status" class="form-control" style="width: 100%;">
                                        <option value="" selected>Trạng thái</option>
                                        <?php $status = !empty($data_search['status'])?$data_search['status']:'';?>
                                        <option value="1" {{ $status === 1 ? 'selected' : '' }}>Hoàn thành</option>
                                        <option value="2" {{ $status === 2 ? 'selected' : '' }}>Đã tiếp nhận</option>
                                        <option value="0" {{ $status === 0 ? 'selected' : '' }}>Chờ phản hồi</option>
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    <select name="name" id="sel-name" class="form-control" style="width: 100%;">
                                        <option value="">Người gửi</option>
                                        <?php $name = !empty($data_search['name']) ? $data_search['name'] : '';?>
                                        @if($name)
                                            <option value="{{$name}}" selected>{{!empty($data_search['name_profile']) ? $data_search['name_profile'] : ''}}</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    <button class="btn btn-warning btn-block">Tìm kiếm</button>
                                </div>
                            </div>
                        </div>
                    </form><!-- END #form-search-advance -->
            @endif
            @if( in_array('admin.feedback.action',@$user_access_router))
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
                                    <th>Căn hộ</th>
                                    <th>Tầng</th>
                                    <th>Tòa nhà</th>
                                    <th width="125">Người viết</th>
                                    <th width="200">Status</th>
                                    <th width="125">Tác vụ</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($feedback as $item)
                                    @php
                                    if($item->new == 1){
                                        $user_info_3 = App\Models\PublicUser\V2\UserInfo::where('user_id',$item->user_id)->first();
                                    }else{
                                        $user_info_3 = App\Models\PublicUser\UserInfo::find($item->pub_user_profile_id);
                                    }
                                    @endphp
                                    <tr>
                                        <td><input type="checkbox" name="ids[]" value="{{ $item->id }}" class="iCheck checkSingle" /></td>
                                        <td>{{ $item->id }}</td>
                                        @if( in_array('admin.feedback.detail',@$user_access_router))
                                            <td><a href="{{ route('admin.feedback.detail', ['id' => $item->id]) }}">{{ $item->title }}</a></td>
                                        @else
                                            <td>{{ $item->title }}</td>
                                        @endif
                                        <td>
                                            <div class="comment-detail">
                                                {{ $item->content }}
                                            </div>
                                        </td>
                                        <td>
                                            {{ $item->bdcApartment->name??'' }}
                                        </td>
                                        <td>
                                            {{ $item->bdcApartment->floor??0 }}
                                        </td>
                                        <td>
                                            {{ $item->bdcApartment->buildingPlace->name??'' }}
                                        </td>
                                        <td>
                                            <small>
                                                {{ @$user_info_3->display_name??@$user_info_3->full_name?? 'không rõ' }}<br />
                                                {{ $item->updated_at->format('d/m/Y H:i') }}
                                            </small>
                                        </td>
                                        <td>
                                            @if ($item->status === 0 || $item->status === 4)
                                                    <label class="label label-sm label-warning">Chờ BQL xử lý</label>
                                            @elseif($item->status === 1)
                                                    <label class="label label-sm label-success">Đã Hoàn thành</label>
                                            @elseif($item->status === 2)
                                                    <label class="label label-sm label-info">Chờ cư dân phản hồi</label>
                                            @elseif($item->status === 3)
                                                    <label class="label label-sm label-primary">BQL đang xử lý</label> 
                                            @endif
                                        </td>
                                        <td>
                                            @if( in_array('admin.feedback.detail',@$user_access_router))
                                                <a title="Trả lời ý kiến" href="{{ url('admin/feedback/detail/' . $item->id . '#reply') }}" class="btn btn-sm btn-reply btn-warning"><i class="fa fa-comments"></i></a>
                                            @endif
                                            @if( in_array('admin.feedback.action',@$user_access_router))
                                                <a title="Xóa ý kiến" href="javascript:;" data-url="{{ route('admin.feedback.action') }}" data-id="{{ $item->id }}" class="btn btn-sm btn-delete btn-danger"><i class="fa fa-trash"></i></a>
                                            @endif
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
    $('.change_status').change(function() {
            var _this = $(this);
            var _token = $('meta[name="csrf-token"]').attr('content');
            var id = _this.data('id');
            var status = _this.val();
            $.ajax({
                type: 'POST',
                url: '{{ route('admin.feedback.repairChangeStatusV3') }}',
                data: {
                    _token: _token,
                    repair_status: status,
                    ids: [id]
                },
                success: function(data){
                    toastr.success(data.msg);
                },
                dataType: 'json'
            });
    });
    sidebar('feedback');
</script>

@endsection