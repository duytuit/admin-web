@extends('backend.layouts.master')

@section('content')
<section class="content-header">
    <h1>
        Quản lý kế toán
        <small>Sổ ký quỹ</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Sổ ký quỹ</li>
    </ol>
</section>


<section class="content">
    <div class="box box-primary">
        <div class="box-body font-weight-bold">
            <h3 style="float: left;margin:0">Sổ ký quỹ</h3>
            <div class="pull-right">
                <a href="{{route('admin.v2.receipttotal.exportReceiptDeposit',Request::all())}}" class="btn btn-success">Xuất ra excel</a>
                <a href="{{ route('admin.v2.provisionalreceipt.create',['type'=>'receipt_deposit']) }}" class="btn btn-warning">Lập phiếu thu ký quỹ</a>
                <a href="{{ route('admin.v2.provisionalreceipt.createPaymentSlip',['type'=>'receipt_payment_deposit']) }}" class="btn btn-warning">Lập phiếu hoàn ký quỹ</a>
            </div>
        </div>
        <div class="box-body">
            <form id="form-search-advance" action="{{route('admin.v2.receipttotal.reportReceiptDeposit')}}" method="GET">
                <div class="row form-group space-5">
                    <div class="col-sm-2" style="padding-left:0">
                        <select name="ip_place_id" id="ip-place_id" class="form-control" style="width: 100%;">
                            <option value="">Chọn tòa nhà</option>
                            <?php $place_building = isset($get_place_building) ? $get_place_building : '' ?>
                            @if($place_building)
                            <option value="{{$place_building->id}}" selected>{{$place_building->name}}</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-sm-1" style="padding-left:0">
                        <select name="bdc_apartment_id" id="ip-apartment"  class="form-control">
                            <option value="">Căn hộ</option>
                                <?php $apartment = isset($get_apartment) ? $get_apartment: '' ?>
                            @if($apartment)
                            <option value="{{$apartment->id}}" selected>{{$apartment->name}}</option>
                            @endif
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
            </form>
            <div class="table-responsive">
                <div class="">
                    {{-- <p><strong>Đầu kỳ : {{ number_format(@$dauKyTotals) }}</strong></p> --}}
                    {{-- <strong><strong>Nợ phát sinh : {{ number_format(@$phatSinhTotals) }}</strong></strong> --}}
                    {{-- <p><strong>Thu trong kỳ : {{ number_format(@$thuTotals) }}</strong></p>
                    <p><strong>Chi trong kỳ : {{ number_format(@$chiTotals) }}</strong></p>
                    <p><strong>Cuối kỳ : {{ number_format(@$cuoiKyTotals) }}</strong></p> --}}
                </div>
                <table class="table table-hover table-striped table-bordered">
                    <thead class="bg-primary">
                        <tr>
                            <th >STT</th>
                            <th>Căn hộ</th>
                            <th>Đã thu</th>
                            <th>Đã chi</th>
                            <th>Còn nợ</th>
                            <th>Mã SP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($receiptDeposit as $key => $receiptTotal)
                            <tr>
                                <td>{{ ($key + 1) }}</td>
                                <td>{{ $receiptTotal->name }}</td>
                                <td>{{ number_format($receiptTotal->thu_tien) }}</td>
                                <td>{{ number_format($receiptTotal->chi_tien) }}</td>
                                <td>{{ number_format($receiptTotal->thu_tien - $receiptTotal->chi_tien) }}</td>
                                <td>{{ $receiptTotal->code }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <form id="page-receipt-total" action="{{route('admin.service.company.action')}}" method="post">
                @csrf
                <input type="hidden" name="method" value="">
                <div class="row mbm">
                    <div class="col-sm-3">
                        <span class="record-total">Tổng: {{ $receiptDeposit->total() }} bản ghi</span>
                    </div>
                    <div class="col-sm-6 text-center">
                        <div class="pagination-panel">
                            {{ $receiptDeposit->appends(Request::all())->onEachSide(1)->links() }}
                        </div>
                    </div>
                    <div class="col-sm-3 text-right">
                    <span class="form-inline">
                        Hiển thị
                        <select name="per_page" class="form-control" data-target="#page-receipt-total">
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
     $(function(){
             get_data_select_apartment1({
                object: '#ip-place_id',
                url: '{{ url('admin/apartments/ajax_get_building_place') }}',
                data_id: 'id',
                data_text: 'name',
                data_code: 'code',
                title_default: 'Chọn tòa nhà'
            });
            function get_data_select_apartment1(options) {
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
                                    text: item[options.data_text]+' - '+item[options.data_code]
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
           get_data_select({
                object: '#ip-apartment',
                url: '{{ url('admin/apartments/ajax_get_apartment') }}',
                data_id: 'id',
                data_text: 'name',
                title_default: 'Chọn căn hộ'
            });
            $("#ip-place_id").on('change', function(){ 
                if($("#ip-place_id").val()){
                    get_data_select({
                    object: '#ip-apartment',
                    url: '{{ url('admin/apartments/ajax_get_apartment_with_place') }}',
                    data_id: 'id',
                    data_text: 'name',
                    title_default: 'Chọn căn hộ'
                    });
                }
            });
            function get_data_select(options) {
                    $(options.object).select2({
                        ajax: {
                            url: options.url,
                            dataType: 'json',
                            data: function(params) {
                                var query = {
                                    search: params.term,
                                    place_id: $("#ip-place_id").val(),
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
           
        })
</script>
@endsection