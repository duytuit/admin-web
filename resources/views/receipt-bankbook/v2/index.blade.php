@extends('backend.layouts.master')

@section('content')
<section class="content-header">
    <h1>
        Quản lý kế toán
        <small>Sổ quỹ Ngân Hàng</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Sổ quỹ Ngân Hàng</li>
    </ol>
</section>
<section class="content">
    <div class="box box-primary">
        <div class="box-body font-weight-bold">
            <h3>Sổ quỹ Ngân Hàng</h3>
        </div>
        <div class="box-body">
            <form id="form-search-advance" action="{{route('admin.v2.receiptbankbook.index')}}" method="GET">
                <div id="search-advance" class="search-advance">
                    <div class="row form-group pull-right">
                        <div class=" col-md-12 ">
                            <a target="_blank" href="{{route('admin.v2.receipt.exportFilterSoQuyChuyenKhoan',Request::all())}}" class="btn btn-success">Xuất ra excel</a>
                            <a href=" {{ route('admin.v2.receipt.create') }}" class="btn btn-warning">Lập phiếu thu</a>
                            <a href="{{ route('admin.provisionalreceipt.create') }}" class="btn btn-warning">Lập Phiếu thu khác</a>
                            <a href="{{ route('admin.provisionalreceipt.createPaymentSlip') }}" class="btn btn-warning">Lập phiếu chi khác</a>
                        </div>
                    </div>
                    <div class="row space-5">
                        <div class="col-sm-2">
                            <input type="text" class="form-control" name="receipt_code" id="receipt_code" value="{{@$filter['receipt_code']}}" placeholder="Mã chứng từ...">
                        </div>
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
                    </div>    
                    <div class="row space-5">
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
                        <div class="col-sm-2" style="padding-left:0">
                            <select name="user_id" id="user_id"  class="form-control select2">
                                <option value="">Người thu</option>
                                @foreach (@$user_info as $item)
                                    <option value="{{$item->id}}"  @if(@$filter['user_id'] == $item->id) selected @endif >{{$item->email}}</option>
                                @endforeach
                            </select>
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
                    {{-- <strong><strong>Nợ phát sinh : {{ number_format(@$phatSinhTotals) }}</strong></strong> --}}
                    <p><strong>Thu trong kỳ : {{ number_format(@$thuTotals) }}</strong></p>
                    <p><strong>Chi trong kỳ : {{ number_format(@$chiTotals) }}</strong></p>
                    <p><strong>Cuối kỳ : {{ number_format(@$cuoiKyTotals) }}</strong></p>
                    @if (isset($total_payment_user))
                        <p><strong>Tổng tiền theo người thu tiền : {{ number_format(@$total_payment_user) }}</strong></p>
                    @endif
                </div>
                <table class="table table-hover table-striped table-bordered">
                    <thead class="bg-primary">
                        <tr>
                            <th rowspan="2">STT</th>
                            <th rowspan="2" style="text-align: center;">Căn hộ</th>
                            <th rowspan="2" style="width: 100px;">Ngày, tháng chứng từ</th>
                            <th colspan="2" style="text-align: center;width: 25%;">Số hiệu chứng từ</th>
                            <th rowspan="2" style="width: 20%;text-align: center;">Diễn giải</th>
                            <th colspan="3" style="text-align: center;">Số tiền</th>
                            <th rowspan="2" style="text-align: center;">Người thu</th>
                        </tr>
                        <tr>
                            <th style="text-align: center;">Thu</th>
                            <th style="text-align: center;">Chi</th>
                            <th style="text-align: center;">Thu</th>
                            <th style="text-align: center;">Chi</th>
                            <th style="text-align: center;">Tồn</th>
                        </tr>
                    </thead>
                    <tbody>
                            @php
                            $temp_sum_cuoi_ky = @$cuoiKyTotals_1;
                            @endphp
                            @foreach($receiptTotals as $lock => $receiptTotal)
                            @php
                                $apartment = App\Models\Apartments\Apartments::get_detail_apartment_by_apartment_id($receiptTotal->bdc_apartment_id);
                                $user = App\Models\PublicUser\Users::get_detail_user_by_user_id($receiptTotal->user_id);
                                if($lock !=0){
                                    if(@$receiptTotals[$lock-1]->type != "phieu_chi" && @$receiptTotals[$lock-1]->type != "phieu_chi_khac" && @$receiptTotals[$lock-1]->type != "phieu_hoan_ky_quy"){ // phiếu thu
                                        $temp_sum_cuoi_ky -=$receiptTotals[$lock-1]->cost;
                                    }
                                    else if(@$receiptTotals[$lock-1]->type == "phieu_chi" || @$receiptTotals[$lock-1]->type == "phieu_chi_khac" || @$receiptTotals[$lock-1]->type == "phieu_hoan_ky_quy"){ // phiếu chi
                                        $temp_sum_cuoi_ky +=$receiptTotals[$lock-1]->cost;
                                    }
                                }
                                if($receiptTotals->count()==1){
                                    $temp_sum_cuoi_ky = @$receiptTotal->cost;
                                }
                            @endphp
                            <tr>
                                <td>{{ ($lock + 1) }}</td>
                                <th>{{ @$apartment->name }}</th>
                                <td>{{ date('d/m/Y', strtotime(@$receiptTotal->create_date)) }}</td>
                                <td>{{ @$receiptTotal->type != "phieu_chi" && @$receiptTotal->type != "phieu_chi_khac" && @$receiptTotal->type != "phieu_hoan_ky_quy" ? @$receiptTotal->receipt_code : '' }}</td>
                                <td>{{ @$receiptTotal->type == "phieu_chi" || @$receiptTotal->type == "phieu_chi_khac" || @$receiptTotal->type == "phieu_hoan_ky_quy" ? @$receiptTotal->receipt_code : '' }}</td>
                                <td>{{ @$receiptTotal->description }}</td>
                                <td class="text-right">{{ @$receiptTotal->type != "phieu_chi" && @$receiptTotal->type != "phieu_chi_khac" && @$receiptTotal->type != "phieu_hoan_ky_quy" ? number_format(@$receiptTotal->cost) : 0 }}</td>
                                <td class="text-right">{{ @$receiptTotal->type == "phieu_chi" || @$receiptTotal->type == "phieu_chi_khac" || @$receiptTotal->type == "phieu_hoan_ky_quy" ? number_format(@$receiptTotal->cost) : 0 }}</td>
                                <td class="text-right">{{ number_format(@$temp_sum_cuoi_ky, 0, ",", ",") }}</td>
                                <td>{{ @$user->email }}</td>
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