@extends('backend.layouts.master')

@section('content')
<section class="content-header">
    <h1>
        Quản lý kế toán
        <small>Quản lý phiếu thu</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Quản lý phiếu thu</li>
    </ol>
</section>


<section class="content">
    <div class="box box-primary">
        <div class="box-body font-weight-bold">
            <h3 style="float: left">Quản lý phiếu thu</h3>
            <div class="pull-right">
                <?php
                    $param = "?user_id_receipt_code=".@$filter['receipt_code']."&receipt_code_type=".@$filter['receipt_code_type']."&bdc_apartment_id=".@$filter['bdc_apartment_id']."&from_date=".@$filter['from_date']."&to_date=".@$filter['to_date'];
                ?>
                <a href="{{route('admin.v2.receipt.exportFilterReceiptDeposit')}}{{$param}}" class="btn btn-success">Xuất ra excel</a>
                {{-- <a href="{{route('admin.v2.receipt.exportDetailFilter')}}{{$param}}" class="btn btn-success">Xuất ra chi tiết excel</a> --}}
                {{-- <a href="{{ route('admin.v2.receipt.create_old') }}" class="btn btn-warning">Lập phiếu thu</a> --}}
                <a href="{{ route('admin.provisionalreceipt.create',['type'=>'receipt_deposit']) }}" class="btn btn-warning">Lập phiếu thu ký quỹ</a>
                {{-- <a href="{{ route('admin.v2.receipt.create_oldPhieuChi') }}" class="btn btn-warning">Lập phiếu chi</a> --}}
                <a href="{{ route('admin.provisionalreceipt.createPaymentSlip',['type'=>'receipt_payment_deposit']) }}" class="btn btn-warning">Lập phiếu hoàn ký quỹ</a>
            </div>
        </div>
        {{-- <div class="box-body">
            @include('layouts.head-building')
        </div> --}}
        <div class="box-body">
            {{-- <div class="row form-group pull-right">
                <div class=" col-md-12 ">

                </div>
            </div> --}}
            <form id="form-search-advance" action="{{route('admin.v2.receipt.kyquy')}}" method="get">
                <div id="search-advance" class="search-advance">
                    <div class="row space-5">
                        <div class="col-sm-2">
                            <input class="form-control" type="text" placeholder="Tìm kiếm từ khóa..." name="user_id_receipt_code"
                                value="{{ @$filter['receipt_code'] }}" />
                        </div>
                        <div class="col-sm-2">
                            <select name="type_payment" class="form-control">
                                <option value="" selected>Hình thức...</option>
                                <option value="tien_mat" @if(@$filter['type_payment'] == 'tien_mat') selected @endif>Tiền mặt</option>
                                <option value="chuyen_khoan" @if(@$filter['type_payment'] == 'chuyen_khoan') selected @endif>Chuyển khoản</option>
                                <option value="vnpay" @if(@$filter['type_payment'] == 'vnpay') selected @endif>VNPay</option>
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <select name="receipt_code_type" class="form-control">
                                <option value="" selected>Kiểu phiếu...</option>
                                <option value="phieu_thu_ky_quy" @if(@$filter['receipt_code_type'] == 'phieu_thu_ky_quy') selected @endif>Phiếu thu ký quỹ</option>
                                <option value="phieu_hoan_ky_quy" @if(@$filter['receipt_code_type'] == 'phieu_hoan_ky_quy') selected @endif>Phiếu hoàn ký quỹ</option>
                            </select>
                        </div>
                        <div class="col-sm-2" style="padding-left:0">
                            <select id="ip-place_id" class="form-control" style="width: 100%;">
                                <option value="">Chọn tòa nhà</option>
                            </select>
                        </div>
                        <div class="col-sm-2" style="padding-left:0">
                            <select name="bdc_apartment_id" id="ip-apartment"  class="form-control">
                                <option value="">Căn hộ</option>
                                    <?php $apartment = isset($data_search['apartment'])?$data_search['apartment']:'' ?>
                                @if($apartment)
                                <option value="{{$apartment->id}}" selected>{{$apartment->name}}</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-sm-2" style="padding-left:0">
                            <select class="form-control" name="config_id">
                                <option value="">Danh mục...</option>
                                @foreach ($configs as $_config)
                                    <option value="{{$_config->id}}">{{$_config->title}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row space-5" style="margin-top: 5px;">
                        {{-- <div class="col-sm-2">
                             <label for="">Ngày hạch toán</label>
                            <div class="input-group date">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" class="form-control pull-right date_picker" name="from_date"
                                    value="{{ @$filter['from_date'] }}" placeholder="Từ..." autocomplete="off">
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="input-group date" style="margin-top: 25px;">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" class="form-control pull-right date_picker" name="to_date"
                                    value="{{ @$filter['to_date'] }}" placeholder="Đến..." autocomplete="off">
                            </div>
                        </div> --}}
                         <div class="col-sm-2">
                            <label for="">Ngày lập phiếu</label>
                            <div class="input-group date">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" class="form-control pull-right date_picker" name="created_at_from_date"
                                    value="{{ @$filter['created_at_from_date'] }}" placeholder="Từ..." autocomplete="off">
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="input-group date" style="margin-top: 25px;">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" class="form-control pull-right date_picker" name="created_at_to_date"
                                    value="{{ @$filter['created_at_to_date'] }}" placeholder="Đến..." autocomplete="off">
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <button type="submit" class="btn btn-info" style="margin-top: 25px;"><i class="fa fa-search"></i> Tìm kiếm
                            </button>
                        </div>
                    </div>
                    <div class="row space-5" style="margin-top: 5px; margin-left: 10px">
                        <strong>Tổng đầu kỳ:</strong> {{ $totalDauKy }} <br>
                        <strong>Tổng Thu trong kỳ:</strong> {{ $totalPhieuThuTruoc }} <br>
                        <strong>Tổng chi trong kỳ:</strong> {{ $totalPhieuChiKhac }} <br>
                        <strong>Cuối kỳ:</strong> {{ $totalCuoiKy }} <br>
                    </div>
                </div>
            </form>
            <div class="table-responsive">
                {{-- <div>
                    <p><strong>Tổng giá trị : {{ number_format(@$sumPriceTotal) }}</strong></p>
                    <p><strong>Tổng giá trị theo trang : {{ number_format(@$sumPrice) }}</strong></p>
                </div> --}}
                <p></p>
                <table class="table table-hover table-striped table-bordered">
                    <thead class="bg-primary">
                        <tr>
                            <th>STT</th>
                            <th>Mã chứng từ</th>
                            <th>Hình thức</th>
                            <th>Loại phiếu</th>
                            <th>Ngày lập phiếu</th>
                            <th>Ngày hạch toán</th>
                            <th>Căn hộ</th>
                            <th>Tòa</th>
                            <th>Khách hàng</th>
                            <th>Số tiền</th>
                            <th>Nội dung</th>
                            <th>Người tạo</th>
                            <th>Danh mục</th>
                            <th>Thao tác</th>
                            <th>In phiếu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($receipts as $lock => $receipt)
                            <tr @if($receipt->deleted_at != null) class="danger" style="text-decoration: line-through;" @endif>
                                <td>{{ ($lock + 1) + (@$receipts->currentpage() - 1) * @$receipts->perPage()  }}</td>
                                <td>
                                    {{@$receipt->receipt_code}}
                                </td>
                                <td>
                                    @if($receipt->type_payment == 'tien_mat')
                                        Tiền mặt
                                    @elseif ($receipt->type_payment == 'chuyen_khoan')
                                        Chuyển khoản
                                    @else
                                        VNPay
                                    @endif
                                </td>
                                <td>
                                    @if(@$receipt->type == 'phieu_thu_ky_quy')
                                        Phiếu thu ký quỹ
                                    @endif
                                    @if(@$receipt->type == 'phieu_hoan_ky_quy')
                                        Phiếu hoàn ký quỹ
                                    @endif
                                </td>
                                <td>{{date('d/m/Y', strtotime(@$receipt->created_at))}}</td>
                                <td>{{ @$receipt->create_date ? date('d/m/Y', strtotime(@$receipt->create_date)) : '--/--/----' }}</td>
                                <td>{{@$receipt->apartment->name}}</td>
                                <td>{{@$receipt->apartment->buildingPlace->code}}</td>
                                <td>{{@$receipt->customer_name}}</td>
                                <td style="text-align: right;">{{number_format(@$receipt->cost)}}</td>
                                <td>{{@$receipt->description}}</td>
                                <td>{{@$receipt->pubUser->email}}</td>
                                <td>{{@$receipt->pubConfig->title}}</td>
                                <td>
                                    @if($receipt->deleted_at == null) 
                                        <a style="float: left" href="{{route('admin.v2.receipt.edit',$receipt->id)}}" class="btn btn-sm btn-warning margin-r-5">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                    @endif
                                      @if( in_array('admin.v2.receipt.destroy',@$user_access_router) && $receipt->deleted_at == null)
                                        <form action="{{route('admin.v2.receipt.destroy',$receipt->id)}}" method="POST">
                                            @method('DELETE')
                                            @csrf
                                            <button onclick="return confirm('Bạn muốn xóa phiếu thu này này ?');" class=" btn btn-sm btn-danger">
                                                <i class="fa fa-trash" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                      @endif
                                </td>
                                <td>
                                    <a style="float: left" href="{{ route('admin.v2.receipt.receiptCode',$receipt->receipt_code) }}" target="_blank"
                                       class="btn btn-sm btn-info margin-r-5"><i class="fa fa-print"></i></a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <form id="form-service-company" action="{{route('admin.service.company.action')}}" method="post">
                @csrf
                <input type="hidden" name="method" value="">
                <div class="row mbm">
                    <div class="col-sm-3">
                        <span class="record-total">Tổng: {{ $receipts->total() }} bản ghi</span>
                    </div>
                    <div class="col-sm-6 text-center">
                        <div class="pagination-panel">
                            {{ $receipts->appends(Request::all())->onEachSide(1)->links() }}
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