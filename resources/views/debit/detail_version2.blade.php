@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý kế toán
            <small>Chi tiết công nợ</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Chi tiết công nợ</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-body font-weight-bold">
                <h3>Chi tiết công nợ</h3>
            </div>
            <div class="box-body">
                {{-- @include('layouts.head-building') --}}
            </div>
            <div class="box-body">
                <form id="form-search-advance" action="{{route('admin.debit.detailVersion2')}}" method="get">
                    <div id="search-advance" class="search-advance">
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
                                    <input type="text" class="form-control date_picker" name="from_date" id="from_date" value="{{@$filter['from_date']}}" placeholder="Từ..." autocomplete="off">
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="input-group date">
                                    <div class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </div>
                                    <input type="text" class="form-control date_picker" name="to_date" id="to_date" value="{{@$filter['to_date']}}" placeholder="Đến..." autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Tìm kiếm
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="table-responsive">
                    <div style="padding-top: 10px">
                        <p><strong>Tổng Đầu Kỳ : {{ number_format(@$sumDauky_all) }}</strong></p>
                        <p><strong>Tổng Phát Sinh Trong Kỳ : {{ number_format(@$sumPs_all) }}</strong></p>
                        <p><strong>Tổng Thu Trong Kỳ : {{ number_format(@$sumThu_all) }}</strong></p>
                        <p><strong>Cuối Kỳ : {{ number_format(@$sumDauky_all + @$sumPs_all - @$sumThu_all) }}</strong></p>
                    </div>
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="bg-primary">
                        @php
                            $sumDauky = $debits->sum('dau_ky');
                            $sumPsTrongky = $debits->sum('ps_trongky');
                            $sumThanhToan = $debits->sum('thanhtoan');
                        @endphp
                        <tr>
                            <th>STT</th>
                            <th>Tên KH</th>
                            <th>Căn hộ</th>
                            <th>Ngày</th>
                            <th>Số chứng từ</th>
                            <th>Số bảng kê</th>
                            <th>Nội dung</th>
                            <th>Phát sinh</th>
                            <th>Thu trong kỳ</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(isset($debits) && $debits != null)
                            @foreach($debits as $key => $debit)
                                <?php
                                    $customer = App\Models\Apartments\V2\UserApartments::getPurchaser($debit->bdc_apartment_id, 0);
                                ?>
                                <tr>
                                    <td>{{ @($key + 1) + ($debits->currentpage() - 1) * $debits->perPage()  }}</td>
                                    <td>{{ @$customer->user_info_first->full_name }}</td>
                                    <td>{{ @$debit->name }}</td>
                                    <td>{{ @$debit->created_at }}</td>
                                    <td>{{ @$debit->receipt_code }}</td>
                                    <td>{{ @$debit->bill_code }}</td>
                                    <td>{{ @$debit->description }}</td>
                                    <td style="text-align: right;">{{ number_format(@$debit->sumery) }}</td>
                                    <td style="text-align: right;">{{ number_format(@$debit->cost) }}</td>
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
                    url: '{{route('admin.debit.getApartment')}}',
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
        });
    </script>

@endsection