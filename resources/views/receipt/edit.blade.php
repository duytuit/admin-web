@extends('backend.layouts.master')

@section('content')
<section class="content-header">
    <h1>
        Quản lý kế toán
        <small>Sửa phiếu thu</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Quản lý kế toán</li>
    </ol>
</section>
<section class="content">
    <div class="box box-primary">
        <section class="invoice border-bill">
            <form action="{{route('admin.receipt.update', $receipt->id)}}" method="POST">
                @method('PUT')
                @csrf
    <div class="row">
        <div class="col-xs-12">
            <div class="table-responsive" style="padding-left: 20px">
                <table width="100%">
                    <tbody>
                        <tr>
                            <td width='80%'>
                                <b class="text-blue">
                                    BQL Tòa nhà
                                </b>
                                <br>
                                <b>
                                    {{@$receipt->building->name}}
                                </b>
                            </td>
                            <td width='20%'>Số PT: {{@$receipt->receipt_code}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-xs-12">
            @if($receipt['type'] == 'phieu_thu')
                <h2 class="text-invoice text-center">Phiếu thu</h2>
            @elseif($receipt['type'] == 'phieu_thu_truoc')
                <h2 class="text-invoice text-center">Phiếu thu khác</h2>
            @elseif($receipt['type'] == 'phieu_chi')
                <h2 class="text-invoice text-center">Phiếu chi</h2>
            @elseif($receipt['type'] == 'phieu_chi_khac')
                <h2 class="text-invoice text-center">Phiếu chi khác</h2>
            @elseif($receipt['type'] == 'phieu_bao_co')
                <h2 class="text-invoice text-center">Phiếu báo có</h2>
            @else
                <h2 class="text-invoice text-center">Phiếu kế toán</h2>
            @endif
            <h5 class="text-center"><i>Ngày {{date("d")}} tháng {{date("m")}} năm {{date("Y")}}</i></h5>
        </div>
        <!-- /.col -->
    </div>
    <div class="table-responsive" style="padding-left: 20px">
        <table width="100%">
            <thead>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td width='20%'>Người nộp tiền :</td>
                    <td width="80%">
                        <div class="form-group {{ $errors->has('customer_name') ? ' has-error' : '' }}">
                            <input type="text" name="customer_name" class="form-control"
                                value="{{ $receipt->customer_name }}">
                            @if ($errors->has('customer_name'))
                            <span class="help-block">
                                <strong>{{ $errors->first('customer_name') }}</strong>
                            </span>
                            @endif
                        </div>
                    </td>
                </tr>
                <tr>
                    <td width='20%'>Căn hộ :</td>
                    <td width="80%"><a
                            href="{{route('admin.apartments.edit', ['id' => $receipt->bdc_apartment_id])}}">{{@$receipt->apartment->name}}</a>
                    </td>
                </tr>
                <tr>
                    <td width='20%'>Hình thức :</td>
                    <td width="80%">
                        <div class="form-group {{ $errors->has('type_payment') ? ' has-error' : '' }}">
                            <select class="form-control" name="type_payment">
                                <option value="tien_mat" @if(@$receipt->type_payment == 'tien_mat') selected @endif>Tiền mặt</option>
                                <option value="chuyen_khoan" @if(@$receipt->type_payment == 'chuyen_khoan') selected @endif>Chuyển khoản
                                </option>
                                <option value="vnpay" @if(@$receipt->type_payment == 'vnpay') selected @endif>VNPay</option>
                            </select>
                            @if ($errors->has('type_payment'))
                            <span class="help-block">
                                <strong>{{ $errors->first('type_payment') }}</strong>
                            </span>
                            @endif
                        </div>
                    </td>
                </tr>
                <tr>
                    <td width='20%'>Số tiền nộp :</td>
                    <td width="80%">
                        <div class="form-group {{ $errors->has('cost') ? ' has-error' : '' }}">
                            <input type="text" name="cost" class="form-control" value="{{ number_format($receipt->cost) }}" >
                            @if ($errors->has('cost'))
                            <span class="help-block">
                                <strong>{{ $errors->first('cost') }}</strong>
                            </span>
                            @endif
                        </div>
                    </td>
                </tr>
                @if( in_array('admin.receipt.edit_create_date',@$user_access_router))
                    <tr>
                        <td width='20%'>Ngày hạch toán:</td>
                        <td width="80%">
                            <div class="form-group {{ $errors->has('create_date') ? ' has-error' : '' }}">
                                <input type="text" name="create_date" class="form-control date_picker" placeholder="Ngày hạch toán..." value="{{ $receipt->create_date }}">
                                @if ($errors->has('create_date'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('create_date') }}</strong>
                                </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endif
                <tr>
                    <td width='20%'>Nội dung:</td>
                    <td width="80%">
                        <div class="form-group {{ $errors->has('description') ? ' has-error' : '' }}">
                            <input type="text" name="description" class="form-control" value="{{ $receipt->description }}">
                            @if ($errors->has('description'))
                            <span class="help-block">
                                <strong>{{ $errors->first('description') }}</strong>
                            </span>
                            @endif
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="box-footer">
        <a href="{{ route('admin.receipt.index') }}" type="button" class="btn btn-default pull-left">Quay
            lại</a>
        <button type="submit" class="btn btn-success pull-right">Lưu</button>
    </div>
    {{-- </div> --}}
    </form>
</section>
</div>
</section>
@endsection

@section('javascript')
<script>
    $("#datepicker").datepicker({
            format: "mm-yyyy",
            autoclose: true,
            viewMode: "months",
            minViewMode: "months"
        }).val();
    $('input.date_picker').datepicker({
        autoclose: true,
        dateFormat: "dd-mm-yy"
    }).val();
</script>
@endsection