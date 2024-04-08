@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        Danh sách ký gửi BĐS
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">{{ $heading }}</li>
    </ol>
</section>
<section class="content">
    <div class="box box-primary">
        <div class="box-body ">
                <form id="form-search-advance" action="{{ route('admin.product-deposit.index') }}" method="GET">
                    <div id="search-advance" class="search-advance">
                        <div class="row form-group space-5">
                            <div class="col-sm-2" style="padding-right: 0;">
                                <select class="form-control" name="status" id="select-status" style="width: 100%;">
                                    <option value="">Chọn trạng thái</option>
                                    @foreach(\App\Models\ProductDeposit\ProductDeposit::NEEDED as $key => $item)
                                        <option value="{{ $key }}" @if($data_search['status'] == $key) selected="selected" @endif>{{ $item }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <select class="form-control" name="type" id="select-status" style="width: 100%;">
                                    <option value="">Chọn loại</option>
                                    @foreach(\App\Models\ProductDeposit\ProductDeposit::TYPE as $key => $item)
                                        <option value="{{ $key }}" @if($data_search['type'] == $key) selected="selected" @endif>{{ $item }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" name="direction" placeholder="Hướng" value="{{ !empty($data_search['direction']) ? $data_search['direction'] : '' }}">
                            </div>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" name="name" placeholder="Nhập tên sản phẩm" value="{{ !empty($data_search['name']) ? $data_search['name'] : '' }}">
                            </div>
                            <div class="col-sm-2">
                                <button class="btn btn-warning btn-block">Tìm kiếm</button>
                            </div>
                        </div>
                    </div>
                </form><!-- END #form-search-advance -->

                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="bg-primary">
                        <tr>
                            <th width="30">ID</th>
                            <th width="180">Tên</th>
                            <th>Địa chỉ</th>
                            <th>Mô tả</th>
                            <th>Hướng</th>
                            <th>Loại</th>
                            <th>Nhu cầu</th>
                            <th>Diện tích</th>
                            <th>Giá</th>
                            <th>Trạng thái</th>
                            <th>Ảnh</th>
                            <th>Tác vụ</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($productDeposits as $_productDeposit)
                            <tr>
                                <td>{{ $_productDeposit->id }}</td>
                                <td>{{ $_productDeposit->name }}</td>
                                <td>{{ $_productDeposit->address }}</td>
                                <td>{{ $_productDeposit->description ?? '' }}</td>
                                <td>{{ $_productDeposit->direction ?? 0 }}</td>
                                <td>{{ $_productDeposit->type ?? '' }}</td>
                                <td>{{ $_productDeposit->needed ?? '' }}</td>
                                <td>{{ $_productDeposit->acreage ?? '' }}</td>
                                <td>{{ $_productDeposit->price ? number_format((int)str_replace('.000', '', $_productDeposit->price), 0, '.', ',').' đ' : 0 }}</td>
                                <td>
                                    <select name="change_product_status" class="form-control change_product_status" style="width: 100%;" data-id="{{ $_productDeposit->id }}">
                                        <option value="0" {{ $_productDeposit->status === 0 ? 'selected' : '' }}>Chưa duyệt</option>
                                        <option value="1" {{ $_productDeposit->status === 1 ? 'selected' : '' }}>Đã duyệt</option>
                                    </select>
                                </td>
                                <td class="popup-gallery-{{$key}}">
                                    @if(is_array(json_decode($_productDeposit->images, true)))
                                        @forelse(json_decode($_productDeposit->images, true) as $key => $img)
                                            <a href="{{ env('MEDIA_URL') . $img}}">
                                                <img src="{{env('MEDIA_URL').$img}}" alt="Ảnh {{$_productDeposit->name}} {{$key+1}}" width="40">
                                            </a>
                                        @empty
                                            Không có hình ảnh
                                        @endforelse
                                    @else
                                        Không có hình ảnh
                                    @endif
                                </td>
                                <td>
                                    <a title="Xóa bài viết" href="javascript:;" data-url="{{ route('admin.product-deposit.destroy') }}" data-id="{{ $_productDeposit->id }}" class="btn btn-sm btn-danger delete_product_deposit"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="row mbm">
                    <div class="col-sm-3">
                        <span class="record-total">Tổng: {{ $productDeposits->total() }} bản ghi</span>
                    </div>
                    <div class="col-sm-6 text-center">
                        <div class="pagination-panel">
                            {{ $productDeposits->appends(Request::all())->onEachSide(1)->links() }}
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
        </div>
    </div>
</section>

@endsection

@section('javascript')

<script>
    $(function () {
        $('.change_product_status').change(function() {
            var _this = $(this);
            var _token = $('meta[name="csrf-token"]').attr('content');
            var id = _this.data('id');
            var status = _this.val();
            $.ajax({
                type: 'POST',
                url: '{{ route('admin.product-deposit.changeStatus') }}',
                data: {
                    _token: _token,
                    status: status,
                    id: id
                },
                success: function(data){ 
                    toastr.success(data.msg);
                },
                dataType: 'json'
            });
        });

        $('.delete_product_deposit').click(function () {
            if (confirm('Có chắc bạn muốn xóa?')) {
                var id = $(this).data('id');
                var url = $(this).data('url');
                var _token = $('meta[name="csrf-token"]').attr('content');
                var data = {
                    _token: _token,
                    method: 'delete',
                    id: id
                };

                $.post(url, data, function (json) {
                    location.reload();
                });
            }
        });
    });
</script>

@endsection