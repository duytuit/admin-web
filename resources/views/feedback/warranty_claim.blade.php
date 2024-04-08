@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        Đăng ký bảo hành tài sản căn hộ
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">{{ $heading }}</li>
    </ol>
</section>
@php
    $route = 'admin.feedback.warrantyClaim';    
@endphp
<section class="content">
    <div class="box box-primary">
        <div class="box-body ">
            @if(in_array($route,@$user_access_router))
                <form id="form-search" action="{{ route($route) }}" method="get">
                    {{ csrf_field() }}
                    <div class="row form-group">
                        <div class="col-sm-8">
                            <span class="btn-group">
                                <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">Tác vụ <span class="caret"></span></button>
                                <ul class="dropdown-menu">
                                    <li><a class="btn-action" data-target="#form-feedback" data-method="delete" href="javascript:;"><i class="fa fa-trash"></i> Xóa</a></li>
                                    <li><a class="btn-action" data-target="#form-feedback" data-method="active" href="javascript:;"><i class="fa fa-check"></i> Active</a></li>
                                    <li><a class="btn-action" data-target="#form-feedback" data-method="inactive" href="javascript:;"><i class="fa fa-times"></i> Inactive</a></li>
                                </ul>
                            </span>
                            <span class="btn-group">
                                <a href="{{ route('admin.feedback.warrantyClaimCreate') }}" class="btn btn-success">Thêm mới</a>
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
                    <form id="form-search-advance" action="{{ route($route) }}" method="get">
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
                                        @php 
                                            $search_floor = !empty($data_search['floor']) ? $data_search['floor'] : ''; 
                                        @endphp
                                        @foreach ($floors as $item)
                                            <option value="{{ $item['floor'] }}" @if($item['floor'] == $search_floor) selected @endif >Tầng {{ $item['floor'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    <select name="repair_status" class="form-control" style="width: 100%;">
                                        <option value="">Trạng thái</option>
                                        @php 
                                            $status = !empty($data_search['repair_status']) ? $data_search['repair_status'] : '';
                                        @endphp
                                        <option value="chua_xu_ly" {{ $status === 'chua_xu_ly' ? 'selected' : '' }}>Chưa xử lý</option>
                                        <option value="bql_da_tiep_nhan" {{ $status === 'bql_da_tiep_nhan' ? 'selected' : '' }}>BQL đã tiếp nhận</option>
                                        <option value="bql_da_nhan_ho_so" {{ $status === 'bql_da_nhan_ho_so' ? 'selected' : '' }}>BQL đã nhận hồ sơ</option>
                                        <option value="cho_cdt_phan_hoi" {{ $status === 'cho_cdt_phan_hoi' ? 'selected' : '' }}>Chờ CĐT phản hồi</option>
                                        <option value="yc_bo_sung" {{ $status === 'yc_bo_sung' ? 'selected' : '' }}>Yêu cầu bổ sung</option>
                                        <option value="cdt_tu_choi" {{ $status === 'cdt_tu_choi' ? 'selected' : '' }}>CĐT từ chối</option>
                                        <option value="cdt_duyet_yc_coc" {{ $status === 'cdt_duyet_yc_coc' ? 'selected' : '' }}>CĐT duyệt-Y.C cọc</option>
                                        <option value="da_coc" {{ $status === 'da_coc' ? 'selected' : '' }}>Đã cọc</option>
                                        <option value="dang_thi_cong" {{ $status === 'dang_thi_cong' ? 'selected' : '' }}>Đang thi công</option>
                                        <option value="tam_dung" {{ $status === 'tam_dung' ? 'selected' : '' }}>Tạm dừng</option>
                                        <option value="hoan_thanh" {{ $status === 'hoan_thanh' ? 'selected' : '' }}>Hoàn thành</option>
                                    </select>
                                </div>
                                {{-- <div class="col-sm-2">
                                    <select name="name" id="sel-name" class="form-control" style="width: 100%;">
                                        <option value="">Người gửi</option>
                                        @php 
                                            $name = !empty($data_search['name']) ? $data_search['name'] : ''; 
                                        @endphp
                                        @if($name)
                                            <option value="{{$name}}" selected>{{!empty($data_search['name_profile']) ? $data_search['name_profile'] : ''}}</option>
                                        @endif
                                    </select>
                                </div> --}}
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
                                <th>Mã tài sản</th>
                                <th>Tên tài sản</th>
                                <th>Căn hộ</th>
                                <th width="125">Người đăng ký</th>
                                <th width="70">Email</th>
                                <th width="70">Số điện thoại</th>
                                <th>Ảnh/tài liệu</th>
                                <th>Thời gian</th>
                                <th>Trạng thái</th>
                                <th width="125">Tác vụ</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($feedback as $item)
                                <tr>
                                    <td><input type="checkbox" name="ids[]" value="{{ $item->id }}" class="iCheck checkSingle" /></td>
                                    <td>{{ $item->id }}</td>
                                    @if( in_array('admin.feedback.detail',@$user_access_router))
                                        <td><a href="{{ route('admin.feedback.detail', ['id' => $item->id]) }}">{{ $item->title }}</a></td>
                                    @else
                                        <td>{{ $item->title }}</td>
                                    @endif
                                    <td>
                                        {{ @$item->asset_apartment->code }}
                                    </td>
                                    <td>
                                        {{ @$item->asset_apartment->name }}
                                    </td>
                                    <td>
                                        {{ $item->bdcApartment->name ?? '' }}
                                    </td>
                                    {{-- <td>
                                        {{ $item->bdcApartment->floor ?? 0 }}
                                    </td>
                                    <td>
                                        {{ $item->bdcApartment->buildingPlace->name ?? '' }}
                                    </td> --}}
                                    <td>
                                        {{ $item->full_name }}
                                    </td>
                                    <td>
                                        {{ $item->email }}
                                    </td>
                                    <td>
                                        {{ $item->phone }}
                                    </td>
                                    <td>
                                        <?php
                                             $list_file = [];
                                             if(isset($item->attached)){
                                                 $item_file = json_decode($item->attached,true);
                                                 foreach ($item_file['files'] as $key => $value) {
                                                    array_push($list_file,$value);
                                                 }
                                                 foreach ($item_file['images'] as $key => $value) {
                                                    array_push($list_file,$value);
                                                 }
                                             }
                                        ?>
                                         <a href="{{ url('admin/feedback/detail/' . $item->id . '#reply') }}" title="xem chi tiết tài liệu">{{count($list_file)}} đính kèm</a>
                                    </td>
                                    <td>
                                        {{ $item->start_time != '0000-00-00 00:00:00' ? date('d-m-Y', strtotime($item->start_time )) : '' }} {{ $item->start_time != '0000-00-00 00:00:00' ? '=>' : '' }} {{ $item->end_time != '0000-00-00 00:00:00' ? date('d-m-Y', strtotime($item->end_time )) : ''}}
                                    </td>
                                    <td>
                                        @php
                                            $status = "";
                                            switch ($item->repair_status) {
                                                case 'chua_xu_ly':
                                                    $status = "Chưa xử lý";
                                                    break;
                                                case 'bql_da_tiep_nhan':
                                                    $status = "BQL đã tiếp nhận";
                                                    break;
                                                case 'bql_da_nhan_ho_so':
                                                    $status = "BQL đã nhận hồ sơ";
                                                    break;
                                                case 'cho_cdt_phan_hoi':
                                                    $status = "Chờ CĐT phản hồi";
                                                    break;
                                                case 'yc_bo_sung':
                                                    $status = "Yêu cầu bổ sung";
                                                    break;
                                                case 'cdt_tu_choi':
                                                    $status = "CĐT từ chối";
                                                    break;
                                                case 'cdt_duyet_yc_coc':
                                                    $status = "CĐT duyệt-Y.C cọc";
                                                    break;
                                                case 'da_coc':
                                                    $status = "Đã cọc";
                                                    break;
                                                case 'dang_thi_cong':
                                                    $status = "Đang thi công";
                                                    break;
                                                case 'tam_dung':
                                                    $status = "Tạm dừng";
                                                    break;
                                                case 'hoan_thanh':
                                                    $status = "Hoàn thành";
                                                    break;
                                            }    
                                        @endphp
                                        <select name="change_repair_status" class="form-control change_repair_status" style="width: 100%;" data-id="{{ $item->id }}">
                                            <option value="chua_xu_ly" {{ $item->repair_status === 'chua_xu_ly' ? 'selected' : '' }}>Chưa xử lý</option>
                                            <option value="bql_da_tiep_nhan" {{ $item->repair_status === 'bql_da_tiep_nhan' ? 'selected' : '' }}>BQL đã tiếp nhận</option>
                                            <option value="bql_da_nhan_ho_so" {{ $item->repair_status === 'bql_da_nhan_ho_so' ? 'selected' : '' }}>BQL đã nhận hồ sơ</option>
                                            <option value="cho_cdt_phan_hoi" {{ $item->repair_status === 'cho_cdt_phan_hoi' ? 'selected' : '' }}>Chờ CĐT phản hồi</option>
                                            <option value="yc_bo_sung" {{ $item->repair_status === 'yc_bo_sung' ? 'selected' : '' }}>Yêu cầu bổ sung</option>
                                            <option value="cdt_tu_choi" {{ $item->repair_status === 'cdt_tu_choi' ? 'selected' : '' }}>CĐT từ chối</option>
                                            <option value="cdt_duyet_yc_coc" {{ $item->repair_status === 'cdt_duyet_yc_coc' ? 'selected' : '' }}>CĐT duyệt-Y.C cọc</option>
                                            <option value="da_coc" {{ $item->repair_status === 'da_coc' ? 'selected' : '' }}>Đã cọc</option>
                                            <option value="dang_thi_cong" {{ $item->repair_status === 'dang_thi_cong' ? 'selected' : '' }}>Đang thi công</option>
                                            <option value="tam_dung" {{ $item->repair_status === 'tam_dung' ? 'selected' : '' }}>Tạm dừng</option>
                                            <option value="hoan_thanh" {{ $item->repair_status === 'hoan_thanh' ? 'selected' : '' }}>Hoàn thành</option>
                                        </select>
                                    </td>
                                    <td>
                                        @if( in_array('admin.feedback.detail',@$user_access_router))
{{--                                                <a title="Xem ý kiến" href="{{ route('admin.feedback.detail', ['id' => $item->id]) }}" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a>--}}
                                            <a title="Trả lời ý kiến" href="{{ url('admin/feedback/detail/' . $item->id .'?type=warranty_claim') }}" class="btn btn-sm btn-reply btn-warning"><i class="fa fa-comments"></i></a>
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
        $('.change_repair_status').change(function() {
            var _this = $(this);
            var _token = $('meta[name="csrf-token"]').attr('content');
            var id = _this.data('id');
            var status = _this.val();
            $.ajax({
                type: 'POST',
                url: '{{ route('admin.feedback.repairChangeStatusV2') }}',
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
    });
    sidebar('feedback');
</script>

@endsection