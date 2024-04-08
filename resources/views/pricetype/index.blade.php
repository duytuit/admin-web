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
                   <a class="btn btn-success" href="{{ route('admin.pricetype.create') }}"> Tạo mới</a>
                </div>

                <div class="panel-body">
                    <hr>
                   <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>Mã</th>
                            <th>Tên</th>
                            <th>Chức năng</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($data as $key => $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->name }}</td>
                            <td>
                                <a title="Sửa" href="{{ route('admin.pricetype.edit', ['id' => $item->id]) }}" class="not-underline">
                                    <i class="fa fa-edit fw"></i>
                                </a>
                                <a title="Xóa" data-url="{{ route('admin.pricetype.delete', ['id' => $item->id]) }}" class="not-underline delete-pricetype">
                                    <i class="fa fa-trash fa-fw text-danger"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>

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
    $(document).on('click', '.delete-pricetype', function (e) {
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
