@extends('backend.layouts.master')
@section('stylesheet')
<link rel="stylesheet" href="{{ url('adminLTE/plugins/tags-input/bootstrap-tagsinput.css') }}" />
<style>
    .bootstrap-tagsinput {
        width: 100% !important;
    }
</style>
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Quản lý kế toán
            <small>Chi tiết tiền thừa</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Chi tiết tiền thừa</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-body font-weight-bold">
                <h3>Chi tiết tiền thừa</h3>
            </div>
            <div class="box-body">
                <form id="form-search-advance" action="{{route('admin.v2.debit.detail_tienthua')}}" method="get">
                    <div id="search-advance" class="search-advance">
                        <div class="form-group pull-right">
                            <a href="{{ route('admin.v2.debit.export_excess_cash',Request::all())}}" class="btn bg-olive">
                                <i class="fa fa-file-excel-o"></i>
                                Export excel
                            </a>
                        </div>
                        <div class="row space-5">
                            <div class="col-sm-2" style="padding-left:0">
                                <select name="ip_place_id" id="ip-place_id" class="form-control" style="width: 100%;">
                                    <option value="">Chọn tòa nhà</option>
                                    <?php $place_building = isset($get_place_building) ? $get_place_building : '' ?>
                                    @if($place_building)
                                    <option value="{{$place_building->id}}" selected>{{$place_building->name}}</option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="bdc_apartment_id" id="ip-apartment" class="form-control" style="width: 100%">
                                    <option value="" selected>Căn hộ</option>
                                    <?php $apartment = isset($get_apartment) ? $get_apartment: '' ?>
                                    @if($apartment)
                                    <option value="{{$apartment->id}}" selected>{{$apartment->name}}</option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <select name="cycle_name" id="cycle_name" class="form-control select2">
                                    <option value="" selected>Kì bảng kê</option>
                                    @foreach($cycle_names as $cycle_name)
                                        <option value="{{ $cycle_name }}"  @if(@$filter['cycle_name'] ==  $cycle_name) selected @endif>{{ $cycle_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <select name="bdc_service_id" class="form-control select2">
                                    <option value="" selected>Dịch vụ...</option>
                                    <option value="0" >Chưa chỉ định</option>
                                    @foreach($serviceBuildingFilter as $serviceBuilding)
                                        <option value="{{ $serviceBuilding->id }}"  @if(@$filter['bdc_service_id'] ==  $serviceBuilding->id) selected @endif>{{ $serviceBuilding->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Tìm kiếm
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                <form id="form-permission" action="{{ route('admin.v2.debit.total.action') }}" method="get">
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="bg-primary">
                            <tr>
                                <th>STT</th>
                                <th>Ngày tạo</th>
                                <th>Mã chứng từ</th>
                                <th>Khách hàng</th>
                                <th>Căn hộ</th>
                                <th>Kỳ</th>
                                <th>Dịch vụ</th>
                                <th>Diễn giải</th>
                                <th>Tăng trong kỳ</th>
                                <th>Giảm trong kỳ</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($detail_tienthua) && $detail_tienthua != null)
                                @foreach($detail_tienthua as $key => $value)
                                    @php
                                        $_customer = App\Repositories\Customers\CustomersRespository::findApartmentIdV2($value->bdc_apartment_id, 0);
                                        $pubUserProfile = @$_customer ? App\Repositories\PublicUsers\PublicUsersProfileRespository::getInfoUserById($_customer->user_info_id) : null;
                                        $apartmentServicePrice = @$value->bdc_apartment_service_price_id != 0 ? App\Models\BdcApartmentServicePrice\ApartmentServicePrice::get_detail_bdc_apartment_service_price_by_apartment_id($value->bdc_apartment_service_price_id) : null;
                                        $service = @$value->bdc_apartment_service_price_id != 0 ? App\Models\Service\Service::get_detail_bdc_service_by_bdc_service_id($apartmentServicePrice->bdc_service_id) : null;
                                        $vehicle = @$apartmentServicePrice->bdc_vehicle_id > 0 ? App\Models\Vehicles\Vehicles::get_detail_vehicle_by_id($apartmentServicePrice->bdc_vehicle_id) : null;
                                        $apartment = App\Models\Apartments\Apartments::get_detail_apartment_by_apartment_id($value->bdc_apartment_id);
                                        if(@$value->from_type == 4 || @$value->from_type == 3){
                                           $_receipt = App\Models\BdcReceipts\Receipts::get_detail_receipt_by_receipt_id($value->note);
                                           if(!$_receipt){
                                                $payment_detail =  App\Models\BdcV2PaymentDetail\PaymentDetail::where('bdc_log_coin_id',$value->id)->first();
                                                if($payment_detail){
                                                    $_receipt = App\Models\BdcReceipts\Receipts::get_detail_receipt_by_receipt_id($payment_detail->bdc_receipt_id);
                                                }
                                           }
                                        }
                                        $receipt_trashed = @$value->receipt_trashed;
                                    @endphp
                                    <tr @if($value->deleted_at != null) class="danger" style="text-decoration: line-through;" @endif>
                                        <td>
                                            <a target="_blank" href="/admin/activity-log/log-action?row_id={{$value->id}}"> {{ $value->id }}</a>
                                        </td>
                                        <td>{{ date('d/m/Y', strtotime(@$value->created_at)) }}</td>
                                        @if (@$value->from_type == 1 || @$value->from_type == 6 || @$value->from_type == 9 || @$value->from_type == 5)
                                              <td>{{@$receipt_trashed->receipt_code }}
                                                   @if (@$receipt_trashed->deleted_at !=null && Auth::user()->isadmin == 1)
                                                        <a href="javascript:;" data-url="{{route('admin.v2.receipt.show_huyphieuthu',['receiptId'=> $receipt_trashed->id])}}" class="xoa_phieu_thu" >
                                                            (Đã xóa)
                                                        </a>
                                                   @elseif(@$receipt_trashed->deleted_at !=null)
                                                        (Đã xóa)
                                                   @endif
                                              </td>
                                        @elseif(@$value->from_type == 2)
                                              <td>Hạch toán tự động</td>
                                        @elseif(@$value->from_type == 4) 
                                             <td>{{ @$_receipt->receipt_code }}</td>
                                        @else
                                              <td>Phân bổ</td>
                                        @endif
                                      
                                        <td>{{ @$pubUserProfile->full_name }}</td>
                                        <td>{{ @$apartment->name }}</td>
                                        <td>{{ @$value->cycle_name }}</td>
                                        <td>{{ @$value->bdc_apartment_service_price_id != 0 ? @$service->name .' - '. @$vehicle->number : 'Chưa chỉ định' }}</td>
                                        @php
                                            $_service_apartment = null;
                                            if(@$value->from_type == 3 || @$value->from_type==4){
                                               $_service_apartment = App\Models\BdcApartmentServicePrice\ApartmentServicePrice::get_detail_bdc_apartment_service_price_by_apartment_id(@$value->from_id);
                                            }
                                        @endphp
                                        @if (@$value->from_type == 1 || @$value->from_type == 4 || @$value->from_type == 6 || @$value->from_type == 9)
                                                <td>{{ @$value->receipt_trashed->description .'->'. @$_service_apartment->name }}</td>
                                        @elseif(@$value->from_type == 2)
                                                <td>Hạch toán tự động</td>
                                        @elseif(@$value->from_type == 5)
                                            <td>[Huỷ phiếu thu]_{{@$value->receipt_trashed->description .'->'. @$_service_apartment->name}}</td>
                                        @else
                                             <td>{{ @$value->note .'->'. @$_service_apartment->name }}</td>
                                        @endif
                                        <td class="text-right">{{ @$value->type == 1 ? number_format(@$value->coin) : '' }}</td>
                                        <td class="text-right">{{ @$value->type == 0 ? number_format(@$value->coin) : '' }}</td>
                                        <td> 
                                            @if( in_array('admin.v2.receipt.destroy',@$user_access_router) && $value->by == 'auto' && $value->deleted_at == null)
                                                <a href="javascript:;" onclick="return confirm('Bạn có chắc chắn muốn thao tác này không?')" class="btn btn-xs btn-danger del_logcoin" data-id="{{$value->id}}" title="Xóa thông tin"> <i class="fa fa-times"></i> </a>
                                            @endif
                                            @if (@$value->from_type == 3 && @$value->note)
                                                    <a href="javascript:;" data-url="{{route('admin.v2.debit.total.action')}}" data-log_coin="{{$value}}" class="btn btn-sm btn-warning show_ngay_hach_toan" ><i class="fa fa-codepen"></i></a>
                                             @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                            <tr><td colspan="11" class="text-center">Không có kết quả tìm kiếm</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="row mbm">
                    @if(isset($detail_tienthua) && $detail_tienthua != null)
                        <div class="col-sm-3">
                            <span class="record-total">Hiển thị {{ $detail_tienthua->count() }} / {{ $detail_tienthua->total() }} kết quả</span>
                        </div>
                        <div class="col-sm-6 text-center">
                            <div class="pagination-panel">
                                {{ $detail_tienthua->appends(request()->input())->links() }}
                            </div>
                        </div>
                    @endif
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
        <div id="form_xoa_phieu" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title"><strong>Hủy phiếu thu</strong></h4>
                    </div>
                    <div class="modal-body chi_tiet_tien_thua">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-warning" data-dismiss="modal">Đóng</button>
                        <button type="button" class="btn btn-success save_phieu_thu">Hủy phiếu thu</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="form_sua_ngay_hach_toan" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title"><strong>Sửa ngày hạch toán</strong></h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <label class="col-sm-4 control-label">Ngày hạch toán</label>
                            <div class="col-sm-8">
                                <input type="hidden" id="log_coin_b_id" value="">
                                <input type="hidden" id="log_coin_a_id" value="">
                                <input type="text" class="form-control date_picker" id="ngay_hach_toan" placeholder="ngày hạch toán" value="">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success save_ngay_hach_toan">Lưu</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@section('stylesheet')
    <style>
        input.check-check {
            /* Double-sized Checkboxes */
            -ms-transform: scale(2); /* IE */
            -moz-transform: scale(2); /* FF */
            -webkit-transform: scale(2); /* Safari and Chrome */
            -o-transform: scale(2); /* Opera */
            padding: 10px;
        }

    </style>
@endsection
@section('javascript')
    <script src="/adminLTE/plugins/tinymce/tinymce.min.js"></script>
    <script src="/adminLTE/plugins/tinymce/config.js"></script>
    <script src="/adminLTE/plugins/tags-input/bootstrap-tagsinput.js"></script>
    <script>
         $('input.date_picker').datepicker({
            autoclose: true,
            dateFormat: "dd-mm-yy"
        }).val();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
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
            $('.del_logcoin').on('click',function (e) {
                e.preventDefault();
                $.get('{{ url('/admin/dev/handleBackAutoPayment') }}', {
                    logId:  $(this).data('id')
                }, function(data) {
                     toastr.success(data.data.mess);
                });
            });
            $('.show_ngay_hach_toan').click(function (e) {
                 e.preventDefault();
                 let data_coin  = $(this).attr('data-log_coin');
                 data_coin = JSON.parse(data_coin);
                 $('#log_coin_b_id').val(data_coin.id);
                 $('#log_coin_a_id').val(data_coin.note);
                 $('#form_sua_ngay_hach_toan').modal('show');
             });
             $('.save_ngay_hach_toan').click(function (e) {
                 e.preventDefault();
                 showLoading();
                 $.ajax({
                     url: "{{route('admin.v2.debit.total.action')}}",
                     type: 'GET',
                     data: {
                        log_coin_b_id: $('#log_coin_b_id').val(),
                        ngay_hach_toan: $('#ngay_hach_toan').val(),
                        log_coin_a_id: $('#log_coin_a_id').val()
                     },
                     success: function (response) {
                         hideLoading();
                         if (response.success == true) {
                             $('#form_sua_ngay_hach_toan').modal('hide');
                             alert("cập nhật thành công!");
                             setTimeout(() => {
                                location.reload()
                            }, 2000)
                         }else{
                             alert(response.mess);
                         }
                     }
                 });
             });
            $('.xoa_phieu_thu').click(function (e) {
                 e.preventDefault();
                 let url  = $(this).attr('data-url');
                 showLoading();
                 $.ajax({
                     url: url,
                     type: 'GET',
                     success: function (response) {
                         hideLoading();
                         if (response.success == true) {
                             $('.chi_tiet_tien_thua').html(response.data.html);
                             $('#form_xoa_phieu').modal('show');
                             if(response.data.status) $('.save_phieu_thu').show(); else $('.save_phieu_thu').hide();
                         }
                     }
                 });
             });
             $('.save_phieu_thu').click(function (e) {
                    showLoading();
                    $.ajax({
                        url: '{{route('admin.v2.receipt.save_huyphieuthu')}}' ,
                        data: {
                            receiptId: $('#receiptIdXoa').val(),
                            note: $('#note').val(),
                        },
                        type: 'POST',
                        success: function (response) {
                            hideLoading();
                            if(response.data.status === 0) {
                                alert("Hủy thành công!");
                                $('#form_xoa_phieu').modal('hide');
                                location.reload();
                            } else {
                                alert(response.data.mess);
                            }
                        }
                    });
                });
        })
    </script>

@endsection
