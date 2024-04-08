@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý kế toán
            <small>Chi tiết tổng hợp</small>
            @php
            $apartmentId = @$filter['bdc_apartment_id'];
            $from_date = @$filter['from_date'];
            $to_date = @$filter['to_date'];
            $page = @$filter['page'];
            $cycle_name = @$filter['cycle_name'];
            $cycle_name_more = @$filter['cycle_name_more'];
            $service = @$filter['service'];
            $type_service = @$filter['type_service'];
            $paramExportExcel = "?bdc_apartment_id=$apartmentId&from_date=$from_date&to_date=$to_date&page=$page&cycle_name=$cycle_name&cycle_name_more=$cycle_name_more&service=$service&type_service=$type_service";
            @endphp
            <a href="{{ route('admin.v2.debit.exportExcelGeneralDetailTotalByTypeApartment') . $paramExportExcel }}" class="btn bg-olive">
                <i class="fa fa-file-excel-o"></i>
                Xuất ra tổng hợp phải thu theo căn hộ
            </a>
            <a href="{{ route('admin.v2.debit.exportExcelGeneralDetailTotalByTypeService') . $paramExportExcel }}" class="btn bg-olive">
                <i class="fa fa-file-excel-o"></i>
                Xuất ra tổng hợp phải thu theo dịch vụ
            </a>
            <a href="{{ route('admin.v2.debit.exportExcelGeneralDetail') . $paramExportExcel }}" class="btn bg-olive">
                <i class="fa fa-file-excel-o"></i>
                Xuất excel
            </a>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Chi tiết tổng hợp</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-body">
                <h3>Chi tiết tổng hợp</h3>
            </div>
            <div class="box-body">
                <form id="form-search-advance" action="{{route('admin.v2.debit.generalDetail')}}" method="get" onsubmit="return validateForm()">
                        <div class="row form-group space-5">
                            <div class="col-md-2">
                                <input type="number" class="form-control" name="du_no_cuoi_ky" id="du_no_cuoi_ky" value="{{@$filter['du_no_cuoi_ky']}}" placeholder="Dư nợ cuối kỳ...">
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
                            <div class="col-sm-2">
                                <select name="bdc_apartment_id" id="ip-apartment" class="form-control" >
                                    <option value="">Căn hộ</option>
                                        <?php $apartment = isset($get_apartment) ? $get_apartment: '' ?>
                                    @if($apartment)
                                    <option value="{{$apartment->id}}" selected>{{$apartment->name}}</option>
                                    @endif
                                </select>
                            </div>

                            <div class="col-sm-2">
                                <select name="service" id="ip-service"  class="form-control select2">
                                    <option value="">Tên dịch vụ</option>
                                    @foreach($services as $service)
                                    <option value="{{$service->id}}" @if(@$filter['service'] ==  $service->id) selected @endif>{{$service->name}}</option>
                                    @endforeach
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
                        </div>
                        <div class="row space-5 form-group">
                            <div class="col-sm-2">
                                <select class="form-control" id="filter_custom" name="filter_custom">
                                    <option value="ky_bang_ke" @if(@$filter['filter_custom'] ==  "ky_bang_ke") selected @endif>Một kỳ bảng kê</option>
                                    <option value="ky_bang_ke_more" @if(@$filter['filter_custom'] ==  "ky_bang_ke_more") selected @endif>Một khoảng kỳ bảng kê</option>
                                    <option value="range_time" @if(@$filter['filter_custom'] ==  "range_time") selected @endif>Khoảng Thời Gian</option>
                                </select>
                            </div>
                            <div class="ky_bang_ke">
                                <div class="col-sm-2" style="padding-left:5px;padding-right:5px">
                                    <select name="cycle_name" id="cycle_name" class="form-control">
                                        <option value="" selected>Kỳ bảng kê</option>
                                        @foreach($cycle_names as $cycle_name)
                                            <option value="{{ $cycle_name }}"  @if(@$filter['cycle_name'] ==  $cycle_name) selected @endif>{{ $cycle_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="ky_bang_ke_more" @if(@$filter['filter_custom'] ==  "ky_bang_ke_more") style="display: none" @endif>
                                <div class="col-sm-2"style="padding-left:5px;padding-right:5px">
                                    <select name="cycle_name_more" id="cycle_name_more" class="form-control">
                                        <option value="" selected>Đến Kỳ bảng kê</option>
                                        @foreach($cycle_names as $cycle_name)
                                            <option value="{{ $cycle_name }}"  @if(@$filter['cycle_name_more'] ==  $cycle_name) selected @endif>{{ $cycle_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="range_time" @if(@$filter['filter_custom'] ==  "range_time") style="display: none" @endif>
                                <div class="col-sm-2" style="padding-left:5px;padding-right:5px">
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
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Tìm kiếm
                                </button>
                            </div>
                        </div>
                </form>
                <form id="form-permission" action="{{ route('admin.v2.debit.generalDetail.action') }}" method="get">
                    <div style="padding-top: 10px">
                        <p><strong>Tổng Đầu kỳ : {{ number_format(@$tong_dau_ky) }}</strong></p>
                        <p><strong>Tổng Phát Sinh : {{ number_format(@$tong_trong_ky) }}</strong></p>
                        <p><strong>Tổng Thanh Toán : {{ number_format(@$tong_thanh_toan) }}</strong></p>
                        <p><strong>Tổng Dư Nợ Cuối Kỳ : {{ number_format(@$tong_cuoi_ky) }}</strong></p>
                    </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="bg-primary">
                            <tr>
                                <th></th>
                                <th style="text-align: center;"></th>
                                <th style="text-align: center;"></th>
                                <th style="text-align: center;"></th>
                                <th style="text-align: center;"></th>
                                <th style="text-align: center;"></th>
                                <th colspan="1" style="width: 100px;" class="text-right">{{number_format(@$tong_dau_ky)}}</th>
                                <th colspan="1" style="width: 100px;" class="text-right">{{number_format(@$tong_trong_ky)}}</th>
                                <th colspan="1" style="width: 100px;" class="text-right">{{number_format(@$tong_thanh_toan)}}</th>
                                <th colspan="1" style="width: 100px;" class="text-right">{{number_format(@$tong_cuoi_ky)}}</th>
                            </tr>
                        <tr>
                            <th rowspan="2">STT</th>
                            <th rowspan="2" style="text-align: center;">Tên KH</th>
                            <th rowspan="2" style="text-align: center;">Căn hộ</th>
                            <th rowspan="2" style="text-align: center;">Mã sản phẩm</th>
                            <th rowspan="2" style="text-align: center;">Mã thu</th>
                            <th rowspan="2" style="text-align: center;">Dịch vụ</th>
                            <th colspan="1" style="width: 100px;">Số dư đầu kỳ</th>
                            <th colspan="1" style="width: 100px;">Phát sinh trong kỳ</th>
                            <th colspan="1" style="width: 100px;">Thanh toán</th>
                            <th colspan="1" style="width: 100px;">Số dư cuối kỳ</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(isset($debits) && $debits != null)
                            @foreach($debits as $key => $debit)
                                <tr>
                                    <td>{{ @($key + 1) + ($getServiceApartments->currentpage() - 1) * $getServiceApartments->perPage()  }}</td>
                                    <td>{{ @$debit['ten_khach_hang'] }}</td>
                                    <td>{{ @$debit['can_ho'] }}</td>
                                    <td>{{ @$debit['ma_san_pham'] }}</td>
                                    <td>{{ @$debit['ma_thu'] }}</td>
                                    <td>{{ @$debit['dich_vu'] }}</td>
                                    <td class="text-right">{{ number_format( @$debit['dau_ky']) }}</td>
                                    <td class="text-right">{{ number_format( @$debit['trong_ky']) }}</td>
                                    <td class="text-right">{{ number_format( @$debit['thanh_toan']) }}</td>
                                    <td class="text-right">{{ number_format( @$debit['cuoi_ky']) }}</td>
                                </tr>
                            @endforeach
                         @endif
                        </tbody>
                    </table>
                </div>
                <div class="row mbm">
                    @if(isset($getServiceApartments) && $getServiceApartments != null)
                        <div class="col-sm-3">
                            <span class="record-total">Hiển thị {{ $getServiceApartments->count() }} / {{ $getServiceApartments->total() }} kết quả</span>
                        </div>
                        <div class="col-sm-6 text-center">
                            <div class="pagination-panel">
                                {{ $getServiceApartments->appends(request()->input())->links() }}
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
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $(document).ready(function () {
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
            $("#datepicker").datepicker({
                format: "mm-yyyy",
                autoclose: true,
                viewMode: "months",
                minViewMode: "months"
            }).val();
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
            if($('#filter_custom').val() == "ky_bang_ke"){
                $('.ky_bang_ke_more').hide();
                $('.range_time').hide();
                $('.ky_bang_ke').show();
                document.querySelector("input[name='from_date']").value = "";
                document.querySelector("input[name='to_date']").value = "";
            }
            if($('#filter_custom').val() == "ky_bang_ke_more"){
                $('.ky_bang_ke_more').show();
                $('.ky_bang_ke').show();
                $('.range_time').hide();
                document.querySelector("input[name='from_date']").value = "";
                document.querySelector("input[name='to_date']").value = "";
            }
            if($('#filter_custom').val() == "range_time"){
                $('.range_time').show();
                $('.ky_bang_ke_more').hide();
                $('.ky_bang_ke').hide();
                var cycleNameSelect = document.getElementById("cycle_name");
                var desiredValue = "";
                cycleNameSelect.value = desiredValue;
                var cycleNamemoreSelect = document.getElementById("cycle_name_more");
                var desiredValuemore = "";
                cycleNamemoreSelect.value = desiredValuemore;
            }
        });
        $('#filter_custom').change(function () {
            if ($(this).val() == "ky_bang_ke") {
                $('.ky_bang_ke_more').hide();
                $('.range_time').hide();
                $('.ky_bang_ke').show();
                document.querySelector("input[name='from_date']").value = "";
                document.querySelector("input[name='to_date']").value = "";
            }
            if ($(this).val() == "ky_bang_ke_more") {
                $('.ky_bang_ke_more').show();
                $('.range_time').hide();
                $('.ky_bang_ke').show();
                document.querySelector("input[name='from_date']").value = "";
                document.querySelector("input[name='to_date']").value = "";
            }
            if($(this).val() == "range_time"){
                $('.ky_bang_ke_more').hide();
                $('.ky_bang_ke').hide();
                $('.range_time').show();
                var cycleNameSelect = document.getElementById("cycle_name");
                var desiredValue = "";
                cycleNameSelect.value = desiredValue;
                var cycleNamemoreSelect = document.getElementById("cycle_name_more");
                var desiredValuemore = "";
                cycleNamemoreSelect.value = desiredValuemore;
            }
        });
        function validateForm(){
            if($('#filter_custom').val() == "ky_bang_ke_more" && $('#cycle_name_more').val() < $('#cycle_name').val()){
                alert("Khoảng thời gian kỳ không hợp lệ!");
                return false;
            }
            return true;
        }
    </script>

@endsection