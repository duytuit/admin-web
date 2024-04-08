@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Tiến trình xử lý công nợ
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Tiến trình xử lý công nợ</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-body font-weight-bold">
                <h3>Tiến trình xử lý công nợ
                </h3>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="bg-primary">
                        <tr>
                            <th>Mã căn hộ</th>
                            <th>Tên căn hộ</th>
                            <th>Mã dịch vụ</th>
                            <th>Tên dịch vụ</th>
                            <th>Thông báo</th>
                        </tr>
                        </thead>
                        <tbody class="reload_process_debit_detail">
                            @if($data != null)
                                @foreach ($data as $item)
                                    <tr>
                                    <td>{{array_key_exists('apartment_id', $item) ? $item["apartment_id"] : ''}}</td>
                                    <td>{{$item["apartment_name"]}}</td>
                                    <td>{{$item["service_id"]}}</td>
                                    <td>{{$item["service_name"]}}</td>
                                    <td>{{$item["message"]}}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5">Đang xử lý dữ liệu....</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('javascript')
<script>
    setInterval(function(){
        $.ajax({
            url: '{{route('api.debit.reloadProcessDebitDetail')}}',
            type: 'GET',
            success: function (response) {
                $('.reload_process_debit_detail').html(response.data.html);
            }
        });
    }, 3000);
</script>
@endsection