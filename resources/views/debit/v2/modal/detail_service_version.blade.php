@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý kế toán
            <small>Chi tiết version bảng kê - Dịch vụ</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Chi tiết version bảng kê - Dịch vụ</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-body font-weight-bold">
                <h3>Chi tiết version bảng kê - Dịch vụ
                </h3>
            </div>
            <div class="box-body">
                {{-- @include('layouts.head-building') --}}
            </div>
            <div class="box-body">
            <form>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-bordered">
                            <thead class="bg-primary">
                            <tr>
                                <th>STT</th>
                                <th>Mã BK</th>
                                <th>Kì BK</th>
                                <th>Căn hộ</th>
                                <th>Dịch vụ</th>
                                <th>Đơn giá</th>
                                <th>SL</th>
                                <th>Sumery</th>
                                <th>New sumery</th>
                                <th>Thời gian</th>
                                <th>Version</th>
                                <th style="width: 90px;">Thao tác</th>
                            </tr>
                            </thead>
                            <tbody>
                            
                           @if($billDetail->count() > 0)
                                @foreach($billDetail as $key => $debit)
                                <tr>
                                    <td>{{ @($key + 1) }}</td>
                                    <td>
                                       {{ @$debit->bill->bill_code }}
                                    </td>
                                    <td>{{ @$debit->bill->cycle_name }}</td>
                                    <td>{{ @$debit->apartment->name }}</td>
                                    <td>{{ @$debit->service->name }}</td>
                                    <td align="right">{{ number_format($debit->price)  }}</td>
                                    <td align="right">{{ $debit->quantity  }}</td>
                                    <td align="right">{{ number_format($debit->sumery) }}</td>
                                    <td align="right">{{ number_format($debit->new_sumery)}}</td>
                                    @if($debit->bdc_price_type_id==2)
                                    <td>{{ date('d/m/Y', strtotime($debit->from_date)).' - '.date('d/m/Y', strtotime($debit->to_date)) }}</td>
                                    @else
                                    <td>{{ date('d/m/Y', strtotime($debit->from_date)).' - '.date('d/m/Y', strtotime($debit->to_date  . ' - 1 days')) }}</td>
                                    @endif
                                    <td>{{ $debit->version }}</td>
                                    <td>
                                        @if( in_array('admin.v2.debit.detailDebit.edit',@$user_access_router))
                                         <a data-id="{{ $debit->id }}" data-action="{{ route('admin.v2.debit.detailDebit.edit') }}"
                                             class="btn btn-sm btn-success editService" title="Sửa thông tin">
                                            <i class="fa fa-edit"></i>
                                         </a>
                                       @endif
                                        @if( in_array('admin.v2.debit.detailDebit.delete.version',@$user_access_router) && collect($billDetail)->where('bdc_service_id',$debit->bdc_service_id)->max('version') == $debit->version)
                                                <a href="{{ route('admin.v2.debit.detailDebit.delete.version',['id'=>$debit->id]) }}" class="btn btn-sm btn-danger" title="xóa" onclick="return confirm('Bạn có chắc chắn muốn xóa không?')"><i class="fa fa-times"></i></a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            @endif
                            </tbody>
                        </table>
                    </div>
                   
                </form>
            </div>
        </div>
        <div class="modal-insert">

        </div>
    </section>
@endsection
@section('javascript')
    <script>
        showModalForm('.editService', '#showModal');
        submitAjaxForm('#update-debit-detail', '#edit-debit-detail', '.div_', '.message_zone');
    </script>
@endsection