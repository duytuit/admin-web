@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        Bảng giá
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Bảng giá</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
               <div class="panel-heading">
                   <a class="btn btn-success" href="{{ route('admin.progressive.create') }}"> Tạo mới</a>
                   {{-- <a class="btn btn-info" href="{{ route('admin.progressive.importexcel') }}"> Import Điện Nước</a> --}}
                </div>
                <div class="panel-body">
                        <form id="form-search-advance" action="{{route('admin.progressive.index')}}" method="get">
                            <div id="search-advance" class="search-advance">
                                <div class="row space-5">
                                    <div class="col-sm-3">
                                        <select name="bdc_service_id" class="form-control select2">
                                            <option value="" selected>Dịch vụ</option>
                                            @foreach($service as $value)
                                                <option value="{{ $value->id }}"  @if(@$filter['bdc_service_id'] ==  $value->id) selected @endif>{{ $value->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-sm-2">
                                        <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Tìm kiếm
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    <hr>
                    <form id="form-progressive-list" action="{{ route('admin.progressive.action') }}" method="post">
                        <input type="hidden" name="method" value="" />
                        <table class="table table-striped">
                                <thead class="bg-primary">
                                <tr>
                                    <th>Mã</th>
                                    <th>Tên</th>
                                    <th>Loại</th>
                                    <th>Dịch vụ</th>
                                    <th>Ngày áp dụng</th>
                                    <th>Chức năng</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($progressive as $key => $item)
                                    @php
                                        $service = \App\Models\Service\Service::get_detail_bdc_service_by_bdc_service_id($item->bdc_service_id);
                                    @endphp
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->bdc_price_type_id == 1 ? "Một giá" : "Lũy tiến" }}</td>
                                    <td>{{ @$service->name}}</td>
                                    <td>{{ date('d-m-Y',strtotime(@$item->applicable_date))}}</td>
                                    <td>
                                        <a title="Sửa" href="{{ route('admin.progressive.edit', ['id' => $item->id]) }}" class="not-underline">
                                            <i class="fa fa-edit fw"></i>
                                        </a>
                                        <a title="Xóa" data-url="{{ route('admin.progressive.delete', ['id' => $item->id]) }}" class="not-underline delete-progressive">
                                            <i class="fa fa-trash fa-fw text-danger"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                                </tbody>
                            </table>
                            <div class="row mbm">
                                <div class="col-sm-3">
                                    <span class="record-total">Hiển thị: {{$count_display}} / {{ $progressive->total() }} Kết quả</span>
                                </div>
                                <div class="col-sm-6 text-center">
                                    <div class="pagination-panel">
                                        {{ $progressive->appends(Request::all())->onEachSide(1)->links() }}
                                    </div>
                                </div>
                                <div class="col-sm-3 text-right">
                                        <span class="form-inline">
                                            Hiển thị
                                            <select name="per_page" class="form-control" data-target="#form-progressive-list">
                                                @php $list = [5,10, 20, 50, 100, 200]; @endphp
                                                @foreach ($list as $num)
                                                    <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                                                @endforeach
                                            </select>
                                        </span>
                                </div>
                            </div>
                  </form><!-- END #form-users -->
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@section('javascript')

<script>
sidebar('event', 'index');
</script>

<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $(document).on('click', '.delete-progressive', function (e) {
        if(!confirm('Bạn có chắc chắn muốn xóa?')) {
            e.preventDefault();
        } else {
            $.ajax({
                url: $(this).attr('data-url'),
                type: 'DELETE',
                success: function (response) {
                    if (response.error_code == 200) {
                        toastr.success(response.message);
                        setTimeout(() => {
                            location.reload();
                        }, 2000)
                    } else {
                        toastr.error('Không thể xóa tài sản này!');
                    }
                }
            })
        }

    });
</script>

@endsection
