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
                    $param = "?user_id_receipt_code=" . @$filter['user_id_receipt_code'] . 
                        "&type_payment=" . @$filter['type_payment'] . 
                        "&receipt_code_type=" . @$filter['receipt_code_type'] . 
                        "&bdc_apartment_id=" . @$filter['bdc_apartment_id'] . 
                        "&from_date=" . @$filter['from_date'] . 
                        "&to_date=" . @$filter['to_date'] .
                        "&created_at_from_date=" . @$filter['created_at_from_date'] .
                        "&created_at_to_date=" . @$filter['created_at_to_date'];
                ?>
                <a href="{{route('admin.receipt.exportFilterThuChi')}}{{$param}}" class="btn btn-success">Xuất ra excel</a>
                <a href="{{route('admin.receipt.exportDetailFilter')}}{{$param}}" class="btn btn-success">Xuất ra chi tiết excel</a>
            </div>
        </div>
        <div class="box-body">
            <form id="form-search-advance" action="{{route('admin.v2.receipt.index_v1')}}" method="get">
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
                                <option value="vi" @if(@$filter['type_payment'] == 'vi') selected @endif>Ví</option>
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <select name="receipt_code_type" class="form-control">
                                <option value="" selected>Kiểu phiếu...</option>
                                <option value="phieu_thu" @if(@$filter['receipt_code_type'] == 'phieu_thu') selected @endif>Phiếu thu</option>
                                <option value="phieu_bao_co" @if(@$filter['receipt_code_type'] == 'phieu_bao_co') selected @endif>Phiếu báo có</option>
                                <option value="phieu_thu_truoc" @if(@$filter['receipt_code_type'] == 'phieu_thu_truoc') selected @endif>Phiếu thu khác</option>
                                <option value="phieu_chi" @if(@$filter['receipt_code_type'] == 'phieu_chi') selected @endif>Phiếu chi</option>
                                <option value="phieu_chi_khac" @if(@$filter['receipt_code_type'] == 'phieu_chi_khac') selected @endif>Phiếu chi khác</option>
                                <option value="phieu_ke_toan" @if(@$filter['receipt_code_type'] == 'phieu_ke_toan') selected @endif>Phiếu kế toán</option>
                            </select>
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
                     <div class="row space-5" style="margin-top: 5px;">
                           <div class="col-sm-2">
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
                        </div>
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
                </div>
            </form>
            <div class="table-responsive">
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
                            <th>Thao tác</th>
                            <th>In phiếu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($receipts as $lock => $receipt)
                            @php
                                $apartment = App\Models\Apartments\Apartments::get_detail_apartment_by_apartment_id($receipt->bdc_apartment_id);
                                $builsingPlace = App\Models\Building\BuildingPlace::get_detail_bulding_place_by_bulding_place_id($apartment->building_place_id);
                                $user = App\Models\PublicUser\Users::get_detail_user_by_user_id($receipt->user_id);
                            @endphp
                            <tr @if($receipt->deleted_at != null) class="danger" style="text-decoration: line-through;" @endif>
                                <td>
                                    <a target="_blank" href="/admin/activity-log/log-action?row_id={{$receipt->id}}"> {{ $receipt->id }}</a>
                                </td>
                                <td>
                                    {{@$receipt->receipt_code}}
                                </td>
                                <td>
                                    @if($receipt->type_payment == 'tien_mat')
                                        Tiền mặt
                                    @elseif ($receipt->type_payment == 'chuyen_khoan')
                                        Chuyển khoản
                                    @elseif ($receipt->type_payment == 'vi')
                                        Ví
                                    @else
                                        VNPay
                                    @endif
                                </td>
                                <td>
                                    @if(@$receipt->type == 'phieu_thu')
                                        Phiếu thu
                                    @elseif(@$receipt->type == 'phieu_thu_truoc')
                                        Phiếu thu khác
                                    @elseif(@$receipt->type == 'phieu_chi')
                                        Phiếu chi
                                    @elseif(@$receipt->type == 'phieu_chi_khac')
                                        Phiếu chi khác
                                    @elseif(@$receipt->type == 'phieu_bao_co')
                                        Phiếu báo có
                                    @else
                                        Phiếu kế toán
                                    @endif
                                </td>
                                <td>{{date('d/m/Y', strtotime(@$receipt->created_at))}}</td>
                                <td>{{ @$receipt->create_date ? date('d/m/Y', strtotime(@$receipt->create_date)) : '--/--/----' }}</td>
                                <td>{{@$apartment->name}}</td>
                                <td>{{@$builsingPlace->code}}</td>
                                <td>{{@$receipt->customer_name}}</td>
                                <td style="text-align: right;">{{number_format(@$receipt->cost)}}</td>
                                <td>{{@$receipt->description}}</td>
                                <td>{{@$user->email ?? @$receipt->pubUserInfo->email }}</td>
                                <td>
                                </td>
                                <td>
                                    <a style="float: left" href="{{url('/admin/v2/receipt/getReceipt/'.$receipt->receipt_code.'?version=1') }}" target="_blank"
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