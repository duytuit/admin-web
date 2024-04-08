@extends('backend.layouts.master')

@section('content')
<section class="content-header">
    <h1>
        Quản lý kế toán
        <small>Thu chi tổng hợp</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Thu chi tổng hợp</li>
    </ol>
</section>


<section class="content">
    <div class="box box-primary">
        <div class="box-body font-weight-bold">
            <h3>Thu chi tổng hợp
            </h3>
        </div>
        {{-- <div class="box-body">
            @include('layouts.head-building')
        </div> --}}
        <div class="box-body">
            <form id="form-search-advance" action="{{route('admin.receipttotal.index')}}" method="GET">
                <div id="search-advance" class="search-advance">
                    <div class="row form-group pull-right">
                        <div class=" col-md-12 ">
                            <a href="{{route('admin.receipt.export')}}" class="btn btn-success">Xuất ra excel</a>
                            <a href="{{ route('admin.receipt.create') }}" class="btn btn-warning">Lập phiếu thu</a>
                            <a href="{{ route('admin.provisionalreceipt.create') }}" class="btn btn-warning">Lập Phiếu thu khác</a>
                            <a href="{{ route('admin.provisionalreceipt.createPaymentSlip') }}" class="btn btn-warning">Lập phiếu chi khác</a>
                        </div>
                    </div>
                    <div class="row space-5">
                        <div class="col-sm-2">
                            <select name="bdc_apartment_id" class="form-control">
                                <option value="" selected>Căn hộ</option>
                                @foreach($apartments as $apartment)
                                <option value="{{ $apartment->id }}" @if(@$filter['bdc_apartment_id']==$apartment->id)
                                    selected @endif>{{ $apartment->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-1">
                            <select name="kieu_chi_thu" class="form-control">
                                <option value="0" selected>Kiểu chi thu...</option>
                                <option value="1" @if(@$filter['kieu_chi_thu'] == '1') selected @endif>Phát sinh</option>
                                <option value="2" @if(@$filter['kieu_chi_thu'] == '2') selected @endif>Thu</option>
                                <option value="3" @if(@$filter['kieu_chi_thu'] == '3') selected @endif>Chi</option>
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <div class="input-group date">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" class="form-control pull-right date_picker" name="from_date"
                                    value="{{ @$filter['from_date'] }}" placeholder="Từ..." autocomplete="off">
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="input-group date">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" class="form-control pull-right date_picker" name="to_date"
                                    value="{{ @$filter['to_date'] }}" placeholder="Đến..." autocomplete="off">
                            </div>
                        </div>
                        <div class="col-sm-1">
                            <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Tìm kiếm
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            <div class="table-responsive">
                <div class="">
                    <p><strong>Đầu kỳ : {{ number_format(@$dauKyTotals) }}</strong></p>
                    <p><strong>Nợ phát sinh : {{ number_format(@$phatSinhTotals) }}</strong></p>
                    <p><strong>Thu trong kỳ : {{ number_format(@$thuTotals) }}</strong></p>
                    <p><strong>Chi trong kỳ : {{ number_format(@$chiTotals) }}</strong></p>
                    <p><strong>Cuối kỳ : {{ number_format(@$cuoiKyTotals) }}</strong></p>
                </div>
                <table class="table table-hover table-striped table-bordered">
                    <thead class="bg-primary">
                        <tr>
                            <th>STT</th>
                            <th>Căn hộ</th>
                            <th>Mã chứng từ</th>
                            <th>Số hóa đơn</th>
                            <th>Ngày lập</th>
                            <th>Nợ phát sinh</th>
                            <th>Thu trong kỳ</th>
                            <th>Chi trong kỳ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($receiptTotals as $lock => $receiptTotal)
                            <tr>
                                <td>{{ ($lock + 1) }}</td>
                                <td>{{ @$receiptTotal->name }}</td>
                                <td>{{ @$receiptTotal->receipt_code }}</td>
                                <td>{{ @$receiptTotal->bill_code }}</td>
                                <td>{{ date('d/m/Y', strtotime(@$receiptTotal->created_at)) }}</td>
                                <td style="text-align: right;">{{ number_format(@$receiptTotal->sumery) }}</td>
                                <td style="text-align: right;">{{ @$receiptTotal->type == "phieu_thu_truoc" || @$receiptTotal->type == "phieu_thu" ? number_format(@$receiptTotal->cost) : 0 }}</td>
                                <td style="text-align: right;">{{ @$receiptTotal->type == "phieu_chi" ? number_format(@$receiptTotal->cost) : 0 }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <form id="form-service-company" action="{{route('admin.receipttotal.index')}}" method="post">
                @csrf
                <input type="hidden" name="method" value="">
                <div class="row mbm">
                    <div class="col-sm-3">
                        <span class="record-total">Tổng: {{ $receiptTotals->total() }} bản ghi</span>
                    </div>
                    <div class="col-sm-6 text-center">
                        <div class="pagination-panel">
                            {{ $receiptTotals->appends(Request::all())->onEachSide(1)->links() }}
                        </div>
                    </div>
                    <div class="col-sm-3 text-right">
                        <span class="form-inline">
                            Hiển thị
                            <select name="per_page" class="form-control" data-target="#form-service-company">
                                @php $list = [10, 20, 50, 100, 200]; @endphp
                                @foreach ($list as $num)
                                    <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                                @endforeach
                            </select>
                        </span>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
@section('javascript')
<script>
    $('input.date_picker').datepicker({
        autoclose: true,
        dateFormat: "dd-mm-yy"
    }).val();
</script>
@endsection