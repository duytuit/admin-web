@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý kế toán
            <small>Chi tiết bảng kê - Dịch vụ</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Chi tiết bảng kê - Dịch vụ</li>
        </ol>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-body font-weight-bold">
                <h3>Chi tiết bảng kê - Dịch vụ
                    <small>(Đã xử lý công nợ kì hiện tại - <i class="text-red" style="font-weight: bolder">{{\Carbon\Carbon::now()->month}}/{{\Carbon\Carbon::now()->year}}</i>)</small>
                </h3>
            </div>
            <div class="box-body">
                <form id="form-search-advance" action="{{route('admin.v2.debit.detailDebit')}}" method="get">
                    <div id="search-advance" class="search-advance">
                        <div class="row space-5 form-group">
                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="bill_code" id="bill_code" value="{{@$filter['bill_code']}}" placeholder="Mã BK...">
                            </div>
                            <div class="col-sm-2">
                                <input type="number" class="form-control" name="new_sumery" id="new_sumery" value="{{@$filter['new_sumery']}}" placeholder="Còn nợ...">
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
                            <div class="col-sm-2" style="padding-left:0">
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
                                <select name="cycle_name[]" class="form-control select2" id="select_cycle_name" multiple>
                                        <option value="" selected>Kỳ bảng kê...</option>
                                        @foreach($cycle_names as $cycle_name)
                                            <option value="{{ $cycle_name }}" >{{ $cycle_name }}</option>
                                        @endforeach
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <select name="bdc_service_id" class="form-control select2">
                                    <option value="" selected>Dịch vụ...</option>
                                    @foreach($serviceBuildingFilter as $serviceBuilding)
                                        <option value="{{ $serviceBuilding->id }}"  @if(@$filter['bdc_service_id'] ==  $serviceBuilding->id) selected @endif>{{ $serviceBuilding->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <select name="service_group" class="form-control">
                                    <option value="" selected>Phí dịch vụ...</option>
                                    <option value="1" @if(@$filter['service_group'] ==  1) selected @endif>Phí công ty</option>
                                    <option value="2" @if(@$filter['service_group'] ==  2) selected @endif>Phí thu hộ</option>
                                    <option value="3" @if(@$filter['service_group'] ==  3) selected @endif>Phí chủ đầu tư</option>
                                    <option value="4" @if(@$filter['service_group'] ==  4) selected @endif>Phí ban quản trị</option>
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <select class="form-control" name="type_service">
                                    <option value="" selected>-- Chọn loại dịch vụ --</option>
                                    <option value="0" @if(isset($filter['type_service']) && $filter['type_service'] == 0) selected @endif>Phí khác</option>  
                                    <option value="5" @if(isset($filter['type_service']) && $filter['type_service'] == 5) selected @endif>Điện</option>
                                    <option value="2" @if(isset($filter['type_service']) && $filter['type_service'] == 2) selected @endif>Phí dịch vụ</option>
                                    <option value="3" @if(isset($filter['type_service']) && $filter['type_service'] == 3) selected @endif>Nước</option> 
                                    <option value="4" @if(isset($filter['type_service']) && $filter['type_service'] == 4) selected @endif>Phương tiện</option>                                        
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Tìm kiếm</button>
                                <a class="btn btn-info" href="{{ route('admin.v2.debit.exportExcel',Request::all()) }}">Export</a>
                                <a class="btn btn-info" href="{{ route('admin.v2.debit.exportExcel_v2',Request::all()) }}">Export Tổng hợp phải thu phí</a>
                            </div>
                            @if((Auth::user()->isadmin == 1) || (in_array('admin.v2.ho.checkboxdelete',@$user_access_router)))
                            <div class="col-sm-1">
                                <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle"
                                    style="margin-right: 10px;">Tác vụ&nbsp;<span class="caret"></span></button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a href="javascript:" type="button" class="btn-action"
                                            data-target="#form-permission" data-method="del_debit">
                                            <i class="fa fa-trash text-danger"></i>&nbsp; Xóa
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        @endif
                        </div>
                    </div>
                </form>
                <form id="form-permission" action="{{ route('admin.v2.debit.detail-service.action') }}" method="post">
                    @csrf
                    <input type="hidden" name="method" value="" />
                    <div class="table-responsive">
                        <div style="padding-top: 10px">
                            <p><strong>Tổng Tiền:  {{ number_format(@$sum_total->tong_tien + @$sum_total->chiet_khau) }}</strong></p>
                            <p><strong>Tổng Giảm trừ:  {{ number_format(@$sum_total->chiet_khau) }}</strong></p>
                            <p><strong>Tổng Thành Tiền:  {{ number_format(@$sum_total->thanh_tien) }}</strong></p>
                            <p><strong>Đã hạch toán:  {{ number_format(@$sum_total->thanh_toan) }}</strong></p>
                            <p><strong>Còn nợ:  {{ number_format(@$sum_total->thanh_tien - @$sum_total->thanh_toan) }}</strong></p>
                        </div>
                        <table class="table table-hover table-striped table-bordered">
                            <thead class="bg-primary">
                            <tr>
                                @if((Auth::user()->isadmin == 1) || (in_array('admin.v2.ho.checkboxdelete',@$user_access_router)))
                                   <th><input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /></th>
                                    <th>Dự án</th>
                                @endif
                                <th></th>
                                <th>STT</th>
                                <th>Mã BK</th>
                                <th>Kì BK</th>
                                <th>Căn hộ</th>
                                <th>Mã Căn hộ</th>
                                <th>Dịch vụ</th>
                                <th>Sản phẩm</th>
                                <th>Mã thu</th>
                                <th>Đơn giá</th>
                                <th>SL</th>
                                <th>Tổng</th>
                                <th>Giảm trừ</th>
                                <th>Thành tiền</th>
                                <th>Đã hạch toán</th>
                                <th>Còn nợ</th>
                                <th>Phiếu thu</th>
                                <th>Ngày chốt</th>
                                <th>Ngày lập</th>
                                <th>Hạn thanh toán</th>
                                <th>Thời gian</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if($debits->count() > 0)
                                @foreach($debits as $key => $debit)
                                @php
                                    $apartmentServicePrice = @$debit->bdc_apartment_service_price_id != 0 ? App\Models\BdcApartmentServicePrice\ApartmentServicePrice::get_detail_bdc_apartment_service_price_by_apartment_id($debit->bdc_apartment_service_price_id) : null;
                                    $service = @$debit->bdc_apartment_service_price_id != 0 ? App\Models\Service\Service::get_detail_bdc_service_by_bdc_service_id($apartmentServicePrice->bdc_service_id) : null;
                                    $vehicle = @$apartmentServicePrice->bdc_vehicle_id > 0 ? App\Models\Vehicles\Vehicles::get_detail_vehicle_by_id($apartmentServicePrice->bdc_vehicle_id) : null;
                                    $apartment = App\Models\Apartments\Apartments::get_detail_apartment_by_apartment_id($debit->bdc_apartment_id);
                                    $bill = App\Models\BdcBills\Bills::get_detail_bill_by_apartment_id($debit->bdc_bill_id);
                                    $receipt = App\Models\BdcReceipts\Receipts::get_detail_receipt_by_debit_id($debit->id);
                                    $_building = \App\Models\Building\Building::get_detail_building_by_building_id($debit->bdc_building_id);
                                @endphp
                                <tr>
                                    @if((Auth::user()->isadmin == 1) || (in_array('admin.v2.ho.checkboxdelete',@$user_access_router)))
                                    <td>
                                            @if($debit->paid == 0)
                                                <input type="checkbox" name="ids[]" value="{{$debit->id}}" class="iCheck checkSingle" />
                                            @endif
                                    </td>
                                        <td>{{@$_building->name}}</td>
                                    @endif
                                    <td>
                                        @if($debit->paid == 0 || \Auth::user()->isadmin == 1)
                                            @if($debit->paid == 0)
                                                @if( in_array('admin.v2.debit.detailDebit.delete',@$user_access_router))
                                                    <a href="{{ route('admin.v2.debit.detailDebit.delete',['id'=> $debit->id]) }}"
                                                        onclick="return confirm('Bạn có chắc chắn muốn xóa không?')" class="btn btn-xs btn-danger" title="Xóa thông tin">
                                                        <i class="fa fa-times"></i>
                                                    </a>
                                                @endif
                                            @endif
                                            @if( in_array('admin.v2.debit.detailDebit.edit',@$user_access_router))
                                                <a data-id="{{ $debit->id }}" data-action="{{ route('admin.v2.debit.detailDebit.edit') }}"
                                                    class="btn btn-xs btn-info editService" title="Sửa thông tin">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                
                                            @endif
                                       @endif
                                    </td>
                                    @if(\Auth::user()->isadmin == 1)
                                       <td>
                                           <a target="_blank" href="/admin/activity-log/log-action?row_id={{$debit->id}}"> {{ $debit->id }}</a>
                                       </td>
                                    @else
                                       <td>
                                           <a target="_blank" href="/admin/activity-log/log-action?row_id={{$debit->id}}"> {{ $debit->id }}</a>
                                       </td>
                                    @endif
                                    <td>
                                        <a>
                                            {{ @$bill->bill_code }}
                                        </a>
                                    </td>
                                    <td>{{ @$debit->cycle_name }}</td>
                                    <td>{{ @$apartment->name }}</td>
                                    <td>{{ @$apartment->code }}</td>
                                    <td>{{ @$service->name }}</td>
                                    <td>{{ @$vehicle->number }}</td>
                                    <td>{{ @$service->code_receipt }}</td>
                                    <td align="right">{{ number_format(@$debit->price)  }}</td>
                                    <td>{{ @$debit->quantity  }}</td>
                                    <td align="right">{{ number_format(@$debit->sumery + @$debit->discount) }}</td>
                                    <td align="right">{{number_format(@$debit->discount)}}</td>
                                    <td align="right">{{number_format(@$debit->sumery)}}</td>
                                    <td align="right">{{ number_format(@$debit->paid)}}</td>
                                    <td align="right">{{ number_format(@$debit->sumery - @$debit->paid)}}</td>
                                    <td>
                                        @if (@$receipt)
                                            @foreach ($receipt as $item)
                                                <div>{!!$item!!}</div>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td>{{ @$service->bill_date }}</td>
                                    <td>{{ date('d/m/Y', strtotime(@$bill->created_at)) }}</td>
                                    <td>{{ date('d/m/Y', strtotime(@$bill->deadline)) }}</td>
                                    @if(@$debit->apartmentServicePrice->bdc_price_type_id==2 || @$debit->apartmentServicePrice->bdc_price_type_id==3)
                                      <td>{{ date('d/m/Y', strtotime(@$debit->from_date)).' - '.date('d/m/Y', strtotime($debit->to_date)) }}</td>
                                    @else
                                      <td>{{ date('d/m/Y', strtotime(@$debit->from_date)).' - '.date('d/m/Y', strtotime($debit->to_date  . ' - 1 days')) }}</td>
                                    @endif
                                </tr>
                            @endforeach
                            @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="row mbm">
                        <div class="col-sm-3">
                            <span class="record-total">Hiển thị {{ $debits->count() }} / {{ $debits->total() }} kết quả</span>
                        </div>
                        <div class="col-sm-6 text-center">
                            <div class="pagination-panel">
                                {{ $debits->appends(request()->input())->links() }}
                            </div>
                        </div>
                        <div class="col-sm-3 text-right">
                        <span class="form-inline">
                            Hiển thị
                            <select name="per_page" class="form-control" data-target="#form-permission">
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
        <input type="hidden" value="{{isset($filter) ? json_encode(@$filter['cycle_name']) : ''}}" id="list_cycle_name">
        <div class="modal-insert">

        </div>
    </section>
@endsection
@section('javascript')
    <!-- TinyMCE -->
    <script src="/adminLTE/plugins/tinymce/tinymce.min.js"></script>
    <script src="/adminLTE/plugins/tinymce/config.js"></script>
    <script type="text/javascript" src="{{ url('adminLTE/js/function_dxmb.js') . "?v=" . \Carbon\Carbon::now()->timestamp }}"></script>
    <script>

        $('input.date_picker').datepicker({
            autoclose: true,
            dateFormat: "dd-mm-yy"
        }).val();
        $('.modal-insert').on('keyup','input.customer_paid_string', function(e){   
            $(this).val(formatCurrency(this));
        }).on('keypress','input.customer_paid_string',function(e){
            if(!$.isNumeric(String.fromCharCode(e.which))) e.preventDefault();
        });
        $('.modal-insert').on('change','input.change_from_date',function(e){

            console.log("input.change_from_date");

            let price = $('.modal-insert').find('input.price').val();
            let to_date = $('.modal-insert').find('input.change_to_date').val();
            let ngay_chuyen_doi = $('.modal-insert').find('#ngay_chuyen_doi').val();
            let apartmentServicePrice = $('.modal-insert').find('#apartmentServicePrice').val();
            let apartmentServiceDiscount = $('.modal-insert').find('#apartmentServiceDiscount').val();
            let apartmentServiceSumery = $('.modal-insert').find('#apartmentServiceSumery').val();
            let apartmentServiceType = $('.modal-insert').find('#apartmentServiceType').val();
            let from_date =  $(this).val();
            const date1 = new Date(from_date);
            const date2 = new Date(to_date);
            const currentYear = date1.getFullYear();
            const currentMonth = date1.getMonth()+1; 
            const daysOfMonth = daysInMonth(currentMonth, currentYear);
            const diffTime = Math.abs(date2 - date1);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 
            let discount = $('.modal-insert').find('input.discount').val().replace(/,/g, "");
            discount = parseInt(discount);
            let sumery =Math.ceil(parseInt(apartmentServicePrice)/daysOfMonth*diffDays);
            console.log(apartmentServicePrice+'|'+daysOfMonth+'|'+diffDays);
            console.log(diffDays +'|'+ price);
            if(apartmentServiceType == 1 && sumery < discount){
                alert('phát sinh không được nhỏ hơn Giảm trừ.');
                $('.modal-insert').find('input.discount').val(formatCurrencyV2(apartmentServiceDiscount));
                $('.modal-insert').find('input.sumery').val(formatCurrencyV2(apartmentServiceSumery));
                 return;
            }
            sumery = parseInt(sumery-discount);
            ngay_chuyen_doi = parseInt(ngay_chuyen_doi);
            if(ngay_chuyen_doi > 0){
               if(ngay_chuyen_doi > diffDays){
                sumery = parseInt(apartmentServicePrice/2-discount);
               }else{
                sumery = parseInt(apartmentServicePrice-discount);
               }
            }
            $('.modal-insert').find('input.sumery').val(formatCurrencyV2(sumery.toString()));
        });
        $('.modal-insert').on('change','input.change_to_date',function(e){
            console.log("input.change_to_date");
            let price = $('.modal-insert').find('input.price').val();
            let from_date = $('.modal-insert').find('input.change_from_date').val();
            let ngay_chuyen_doi = $('.modal-insert').find('#ngay_chuyen_doi').val();
            let apartmentServicePrice = $('.modal-insert').find('#apartmentServicePrice').val();
            let apartmentServiceDiscount = $('.modal-insert').find('#apartmentServiceDiscount').val();
            let apartmentServiceSumery = $('.modal-insert').find('#apartmentServiceSumery').val();
            let to_date =  $(this).val();
            const date1 = new Date(from_date);
            const date2 = new Date(to_date);
            const currentYear = date1.getFullYear();
            const currentMonth = date1.getMonth()+1; 
            const currentMonthToDate = date2.getMonth()+1; 
            const daysOfMonth = daysInMonth(currentMonth, currentYear);
            const diffTime = Math.abs(date2 - date1);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 
            let discount = $('.modal-insert').find('input.discount').val().replace(/,/g, "");
            discount = parseInt(discount);
            let sumery =Math.ceil(parseInt(apartmentServicePrice)/daysOfMonth*diffDays);
            console.log(apartmentServicePrice+'|'+daysOfMonth+'|'+diffDays);
            console.log(diffDays +'|'+ price);
            if(apartmentServiceType == 1 && sumery < discount){
                alert('phát sinh không được nhỏ hơn Giảm trừ.');
                $('.modal-insert').find('input.discount').val(formatCurrencyV2(apartmentServiceDiscount));
                $('.modal-insert').find('input.sumery').val(formatCurrencyV2(apartmentServiceSumery));
                 return;
            }
            sumery = parseInt(sumery-discount);
            ngay_chuyen_doi = parseInt(ngay_chuyen_doi);
            if(ngay_chuyen_doi > 0){
                let date_ngay_chuyen_doi = null;
                let chuyen_doi_from_date = new Date(date1.getFullYear()+'/'+currentMonth+'/'+ngay_chuyen_doi);
                let chuyen_doi_to_date = new Date(date2.getFullYear()+'/'+currentMonthToDate+'/'+ngay_chuyen_doi);
                if ((chuyen_doi_from_date >= date1) && (chuyen_doi_from_date <= date2)) {
                    date_ngay_chuyen_doi = chuyen_doi_from_date;
                } else {
                    date_ngay_chuyen_doi = chuyen_doi_to_date;
                }
                let last_time = $('.modal-insert').find('#last_time_pay').val().trim();
                let last_time_pay = new Date(last_time);
                if (date_ngay_chuyen_doi > date2) {
                    sumery = parseInt(apartmentServicePrice/2-discount);
                } else {                                                               // tính cả tháng
                    sumery = parseInt(apartmentServicePrice-discount);
                }
            }
            $('.modal-insert').find('input.sumery').val(formatCurrencyV2(sumery.toString()));
        });
        $('.modal-insert').on('keyup','input.discount',function(e){
            console.log("input.discount");
            let phatsinh = $('.modal-insert').find('input#phatsinh').val().replace(/,/g, "");
            let apartmentServiceDiscount = $('.modal-insert').find('#apartmentServiceDiscount').val();
            let apartmentServiceSumery = $('.modal-insert').find('#apartmentServiceSumery').val();
            let apartmentServicePaid = $('.modal-insert').find('#apartmentServicePaid').val().replace(/,/g, "");
            phatsinh = parseInt(phatsinh);
            apartmentServicePaid = parseInt(apartmentServicePaid);
            let discount2 = $(this).val().replace(/,/g, "");
            discount2 = parseInt(discount2);

            console.log(discount2+'|'+phatsinh);

            let sumery = phatsinh - discount2;
            
            if(sumery < apartmentServicePaid){
                alert('không được giảm quá số tiền còn nợ.');
                $(this).val(formatCurrencyV2(apartmentServiceDiscount));
                $('.modal-insert').find('input.sumery').val(formatCurrencyV2(apartmentServiceSumery));
                return;
            }
            if(sumery < 0){
                alert('phát sinh không được nhỏ hơn Giảm trừ.');
                $(this).val(formatCurrencyV2(apartmentServiceDiscount));
                $('.modal-insert').find('input.sumery').val(formatCurrencyV2(apartmentServiceSumery));
                return;
            }
            $('.modal-insert').find('input.sumery').val(formatCurrencyV2(sumery.toString()));
            return;
        });
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
        function daysInMonth (month, year) {
            return new Date(year, month, 0).getDate();
        }
    </script>
    <script>
        $(document).on('change', '.building-list', function (e) {
            e.preventDefault();
            var id = $(this).children(":selected").val();
            $.ajax({
                url: '{{route('admin.v2.debit.getApartment')}}',
                type: 'POST',
                data: {
                    id: id
                },
                success: function (response) {
                    var $apartment = $('.apartment-list');
                    $apartment.empty();
                    $apartment.append('<option value="" selected>Căn hộ</option>');
                    $.each(response, function (index, val) {
                        if (index != 'debug') {
                            $apartment.append('<option value="' + index + '">' + val + '</option>')
                        }
                    });
                }
            })
        });
        $(document).ready(function () {
            $('#myCheckAll').change(function () {
                if ($(this).is(":checked")) {
                    $('.checkboxes').prop("checked", true);
                    $('.checkboxes').val(1);
                } else {
                    $('.checkboxes').prop("checked", false);
                    $('.checkboxes').val(0);
                }
            });
            //Date picker
            $('input.date_picker').datepicker({
                autoclose: true,
                dateFormat: "dd-mm-yy"
            }).val();

            $('.frees').change(function () {
                if (this.checked) {
                    $(this).val(1);
                } else {
                    $(this).val(0);
                }
            });
            if($('#list_cycle_name').val() != '[]'){
                let list_cycle_name = $('#list_cycle_name').val();
                let obj_list_cycle_name = Object.values(JSON.parse(list_cycle_name));
                let new_list_cycle_names = [];
                for (let index = 0; index < obj_list_cycle_name.length; index++) {
                    let cycle_name = obj_list_cycle_name[index];
                    new_list_cycle_names.push(
                        cycle_name
                    )
                }
                console.log(new_list_cycle_names);
                $('#select_cycle_name').val(new_list_cycle_names).trigger('change');
            }
           
        });

        showModalForm('.editService', '#showModal');

        submitAjaxForm('#update-debit-detail', '#edit-debit-detail', '.div_', '.message_zone');

        function formatNumber(num) {
            return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
        }
    </script>
@endsection