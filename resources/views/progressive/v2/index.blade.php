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
                   <a class="btn btn-success" href="{{ route('admin.v2.progressive.create') }}"> Tạo mới</a>
                   {{-- <a class="btn btn-info" href="{{ route('admin.v2.progressive.importexcel') }}"> Import Điện Nước</a> --}}
                </div>

                <div class="panel-body">
                    <hr>
                    <form id="form-progressive-list" action="{{ route('admin.v2.progressive.action') }}" method="post">
                        <input type="hidden" name="method" value="" />
                        <table class="table table-striped">
                                <thead class="bg-primary">
                                <tr>
                                    <th>Mã</th>
                                    <th>Tên</th>
                                    <th>Loại</th>
                                    <th>Chức năng</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($progressive as $key => $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->bdc_price_type_id == 1 ? "Một giá" : "Lũy tiến" }}</td>
                                    <td>
                                        <a title="Sửa" href="{{ route('admin.v2.progressive.edit', ['id' => $item->id]) }}" class="not-underline">
                                            <i class="fa fa-edit fw"></i>
                                        </a>
                                        <a title="Xóa" data-url="{{ route('admin.v2.progressive.delete', ['id' => $item->id]) }}" class="not-underline delete-progressive">
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
