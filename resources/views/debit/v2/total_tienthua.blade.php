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
            <small>Tổng hợp tiền thừa</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Tổng hợp tiền thừa</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-body font-weight-bold">
                <h3>Tổng hợp tiền thừa</h3>
            </div>
            <div class="box-body">
                <form id="form-search-advance" action="{{route('admin.v2.debit.total_tienthua')}}" method="get">
                    <div id="search-advance" class="search-advance">
                        <div class="form-group pull-right">
                            <a href="{{ route('admin.v2.debit.export_total_excess_cash') }}" class="btn bg-olive">
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
                            <div class="col-md-1">
                                <select name="bdc_apartment_id" id="ip-apartment" class="form-control" style="width: 100%">
                                    <option value="" selected>Căn hộ</option>
                                    <?php $apartment = isset($get_apartment) ? $get_apartment: '' ?>
                                    @if($apartment)
                                    <option value="{{$apartment->id}}" selected>{{$apartment->name}}</option>
                                    @endif
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
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Tìm kiếm
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                <form id="form-permission" action="{{ route('admin.v2.debit.total.action') }}" method="get">
                <div class="table-responsive">
                    <div style="padding-top: 10px">
                        <p><strong>Tổng tiền thừa : {{ number_format(@$sum_all) }}</strong></p>
                    </div>
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="bg-primary">
                            <tr>
                                <th>STT</th>
                                <th >Căn hộ</th>
                                <th >Mã căn hộ</th>
                                <th >Tòa</th>
                                <th >Khách hàng</th>
                                <th >Tổng</th>
                                <th >Dịch vụ</th>
                                <th >Tiền thừa hiện tại</th>
                                <th >Tác vụ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($total_tienthua) && $total_tienthua != null)
                                @php
                                    $apartmentId = null;
                                @endphp
                                @foreach($total_tienthua as $key => $value)
                                    <tr>
                                        @php
                                            $_customer = App\Repositories\Customers\CustomersRespository::findApartmentIdV2($value->bdc_apartment_id, 0);
                                            $pubUserProfile = @$_customer ? App\Repositories\PublicUsers\PublicUsersProfileRespository::getInfoUserById($_customer->user_info_id) : null;
                                            $apartmentServicePrice = @$value->bdc_apartment_service_price_id != 0 ? App\Models\BdcApartmentServicePrice\ApartmentServicePrice::get_detail_bdc_apartment_service_price_by_apartment_id($value->bdc_apartment_service_price_id) : null;
                                            $service = @$value->bdc_apartment_service_price_id != 0 ? App\Models\Service\Service::get_detail_bdc_service_by_bdc_service_id($apartmentServicePrice->bdc_service_id) : null;
                                            $vehicle = @$apartmentServicePrice->bdc_vehicle_id > 0 ? App\Models\Vehicles\Vehicles::get_detail_vehicle_by_id($apartmentServicePrice->bdc_vehicle_id) : null;
                                            $apartment = App\Models\Apartments\Apartments::get_detail_apartment_by_apartment_id($value->bdc_apartment_id);
                                            $builsingPlace = App\Models\Building\BuildingPlace::get_detail_bulding_place_by_bulding_place_id($apartment->building_place_id);
                                        @endphp
                                        <td>{{ @($key + 1) + ($total_tienthua->currentpage() - 1) * $total_tienthua->perPage()  }}</td>
                                        @if ($apartmentId != $value->bdc_apartment_id)
                                            <td>{{ @$apartment->name }}</td>
                                            <td>{{ @$apartment->code }}</td>
                                            <td>{{ @$builsingPlace->name }}</td>
                                            <td>{{ @$pubUserProfile->full_name }}</td>
                                          
                                            @php
                                                if($apartmentId != $value->bdc_apartment_id){
                                                    $apartmentId = $value->bdc_apartment_id;
                                                }
                                                $sum_total = App\Repositories\BdcCoin\BdcCoinRepository::getCoinTotal($value->bdc_apartment_id);
                                            @endphp
                                              <td class="text-right">{{ number_format(@$sum_total) }}</td>
                                        @else
                                             <td colspan="5"></td>
                                        @endif 
                                        <td>{{ $value->bdc_apartment_service_price_id != 0 ? @$service->name .' - '. @$vehicle->number : 'Chưa chỉ định' }}</td>
                                        <td class="text-right">{{number_format (@$value->coin) }}</td>
                                        <td>
                                            <a href="javascript:;" data-url="{{route('admin.v2.debit.show_tienthua',['apartment_id'=>$value->bdc_apartment_id])}}" class="btn btn-sm btn-warning phan_bo_can_ho" title="Phân bổ"><i class="fa fa-codepen"></i></a>
                                            <a href="{{ route('admin.v2.debit.detail_tienthua', ['bdc_apartment_id' => $value->bdc_apartment_id]) }}"
                                                class="btn btn-sm btn-success" title="Xem chi tiết"
                                                target="_blank"><i class="fa fa-align-left"></i></a>
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
                    @if(isset($total_tienthua) && $total_tienthua != null)
                        <div class="col-sm-3">
                            <span class="record-total">Hiển thị {{ $total_tienthua->count() }} / {{ $total_tienthua->total() }} kết quả</span>
                        </div>
                        <div class="col-sm-6 text-center">
                            <div class="pagination-panel">
                                {{ $total_tienthua->appends(request()->input())->links() }}
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
        <input type="hidden" id="list_option_tu_chi_dinh" value="" />
        <input type="hidden" id="list_option_den_chi_dinh" value="" />
    </section>

    <div id="form_phan_bo" class="modal fade" role="dialog">
        <div class="modal-dialog">
          <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><strong>Phân bổ tiền thừa</strong></h4>
                </div>
                <div class="modal-body chi_tiet_tien_thua">
                    {{-- <div class="form-group">
                        <div class="col-md-12">
                            <p>Tổng tiền thừa</p>
                            <div> dịch vụ 1</div>
                            <div> dịch vụ 2</div>
                            <div> dịch vụ 3</div>
                            <div> dịch vụ 4</div>
                            <div> dịch vụ 5</div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-sm btn-primary add_phan_bo_item">
                                <i class="fa fa-plus" aria-hidden="true"> Thêm phân bổ mới</i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="card-body phan_bo_list">
                        </div>
                    </div> --}}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success save_phan_bo">Lưu</button>
                </div>
            </div>  
        </div>
    </div>
@endsection
<style>
  .modal-dialog {
            width: 900px !important;
            margin: 30px auto;
  }
</style>
@section('javascript')
    <script src="/adminLTE/plugins/tinymce/tinymce.min.js"></script>
    <script src="/adminLTE/plugins/tinymce/config.js"></script>
    <script src="/adminLTE/plugins/tags-input/bootstrap-tagsinput.js"></script>
    <script type="text/javascript" src="{{ url('adminLTE/js/format-currency.js') }}"></script>
    <script type="text/javascript" src="{{ url('adminLTE/js/function_dxmb.js') . "?v=" . \Carbon\Carbon::now()->timestamp }}"></script>
    <script>
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
           
        })
    </script>
    <script>
        $('.phan_bo_tien_thua').click(function() {
            $('#form_phan_bo').modal('show');
        });
        $('input.date_picker').datepicker({
            autoclose: true,
            dateFormat: "dd-mm-yy"
        }).val();
        $(".chi_tiet_tien_thua").on('click','.add_phan_bo_item',function(e) {
            let list_option = $('#list_option_tu_chi_dinh').val();
            let list_option_den_chi_dinh = $('#list_option_den_chi_dinh').val();
            var html = '<div class="form-row phan_bo_items">'+
                            '<div class="item_detail">'+
                                '<div class="form-group col-md-3">'+
                                    '<label for="content" class="control-label"> Từ </label>'+
                                    '<select name="tu_chi_dinh" class="form-control">'+
                                        list_option+
                                    '</select>'+
                                '</div>'+
                                '<div class="form-group col-md-3">'+
                                    '<label for="content" class="control-label"> Đến </label>'+
                                    '<select name="den_chi_dinh" class="form-control">'+
                                        list_option_den_chi_dinh+
                                    '</select>'+
                                '</div>'+
                                '<div class="form-group col-md-3">'+
                                    '<label for="content" class="control-label">Số tiền</label>'+
                                    '<input class="form-control so_tien_phan_bo" placeholder="Số tiền" name="so_tien" value="0" type="text">'+
                                '</div>'+
                                '<div class="form-group col-md-2">'+
                                    '<label for="content" class="control-label">Ngày hạch toán</label>'+
                                    '<input class="form-control date_picker" placeholder="Ngày hạch toán" name="ngay_hach_toan" type="text">'+
                                '</div>'+
                                '<div class="form-group col-md-1" style="padding: 0;margin-top: 25px;">'+
                                    '<button type="button" data-remove_item="" class="btn btn-danger remove_item">'+
                                        '<i class="fa fa-minus" aria-hidden="true"></i>'+
                                    '</button>'+
                                '</div>'+
                            '</div>'+
                        '</div>';
            $(".phan_bo_list").append(html);
            $(".phan_bo_list").find('input.date_picker').datepicker({
                autoclose: true,
                dateFormat: "dd-mm-yy"
            }).val();
            e.preventDefault();
        });
        $(".chi_tiet_tien_thua").on("click", ".remove_item", function(){
            $(this).parents(".phan_bo_items").remove();
        });
        $('.phan_bo_can_ho').click(function (e) { 
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
                        let option_item = "";
                        if(response.data.dich_vu_chi_dinh != null){
                                response.data.dich_vu_chi_dinh.forEach((item)=>{
                                    option_item += `<option value=${item.value}>${item.name}</option>`;
                                })
                        }
                        let option_item_den_chi_dinh = "";
                        if(response.data.den_chi_dinh != null){
                                response.data.den_chi_dinh.forEach((item)=>{
                                    option_item_den_chi_dinh += `<option value=${item.value}>${item.name}</option>`;
                                })
                        }
                        $('#list_option_tu_chi_dinh').val(option_item);
                        $('#list_option_den_chi_dinh').val(option_item_den_chi_dinh);
                        $('#form_phan_bo').modal('show');
                    }
                }
            });
        });
        $('.save_phan_bo').click(function (e) { 

            e.preventDefault();
            var phan_bo_items = [];
            showLoading();
            let apartmentId = $('#apartment_id').val(); 
            $('.chi_tiet_tien_thua .phan_bo_items').each(function(){
                
                let tu_chi_dinh = $(this).find("[name='tu_chi_dinh']").val();
                let den_chi_dinh = $(this).find("[name='den_chi_dinh']").val();
                let so_tien = $(this).find("[name='so_tien']").val();
                let ngay_hach_toan = $(this).find("[name='ngay_hach_toan']").val();
                phan_bo_items.push({
                        tu_chi_dinh: tu_chi_dinh,
                        den_chi_dinh: den_chi_dinh,
                        so_tien: so_tien,
                        ngay_hach_toan: ngay_hach_toan
                });
              
            })
            if(phan_bo_items.length == 0){
                hideLoading();
                toastr.warning('Chưa có dịch vụ nào được phân bổ');
                return;
            }
            $.ajax({
                url: "{{route('admin.v2.debit.save_phanbo')}}",
                type: 'POST',
                data: {
                    apartmentId: apartmentId,
                    form_list_phan_bo: JSON.stringify(phan_bo_items) 
                },
                success: function (response) {
                    hideLoading();
                    if(response.success == true) {
                        toastr.success(response.message);
                        setTimeout(() => {
                                location.reload()
                            }, 2000)
                    }
                },
                error: function(response) {
                    hideLoading();
                    console.log(response);
                    toastr.error(response.responseJSON.message);
                }
            });
        });
        $('.chi_tiet_tien_thua').on('keyup','input.so_tien_phan_bo', function(e){       
            $(this).val(formatCurrency(this));
        }).on('keypress',function(e){
            if(!$.isNumeric(String.fromCharCode(e.which))) e.preventDefault();
        });
    </script>

@endsection
