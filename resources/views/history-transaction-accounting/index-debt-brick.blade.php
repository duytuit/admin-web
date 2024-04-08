@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            Danh sách quản lý gạch nợ PDV và Phí xe tòa imperial từ tháng 12-2022 trở về trước
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">quản lý gạch nợ</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-body ">

                <form id="form-search" action="{{ route('admin.history-transaction-accounting.indexDebtBrick') }}" method="get">

                    <div class="row form-group">
                        <div class="col-sm-6">
                                <span class="btn-group">
                                    <button type="button" data-toggle="dropdown"
                                            class="btn btn-primary dropdown-toggle">Tác vụ <span
                                                class="caret"></span></button>
                                    <ul class="dropdown-menu">
                                        <li><a class="btn-action" data-target="#form-history-transaction-accounting"
                                               data-method="capnhat_ngay_hach_toan" href="javascript:;"><i
                                                        class="fa fa-plus"></i>Tạo phiếu kế toán</a></li>
                                    </ul>
                                </span>
                                <a href="{{ route('admin.export_debt_brick',Request::all()) }}"
                                   class="btn btn-success"><i class="fa fa-edit"></i> Export</a>
                            <div class="col-sm-6" style="padding-left:0">
                                <div class="input-group date">
                                    <div class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </div>
                                    <input type="text"
                                           class="form-control date_picker history_transaction_accounting_from_date"
                                           name="from_date" id="from_date" value="{{@$filter['from_date']}}"
                                           placeholder="Chọn ngày..." autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="col-sm-12">
                                <div class="col-sm-3">
                                    <select name="bdc_apartment_id" id="ip-apartment" class="form-control">
                                        <option value="">Căn hộ</option>
                                        <?php $apartment = @$filter['apartment'] ? @$filter['apartment'] : '' ?>
                                        @if($apartment)
                                            <option value="{{$apartment->id}}" selected>{{$apartment->name}}</option>
                                        @endif
                                    </select>
                                </div>

                                <div class="col-sm-2 text-right">
                                    <button type="submit" class="btn btn-info"><span class="fa fa-search"></span> Lọc</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form><!-- END #form-search -->
                <form id="form-history-transaction-accounting"
                      action="{{ route('admin.action.debt_brick') }}" method="post">
                    @csrf
                    <input type="hidden" name="method" value=""/>
                    <input type="hidden" name="status" value=""/>

                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-bordered">
                            <thead class="bg-primary">
                            <tr>
                                <th width="3%"><input type="checkbox" class="iCheck checkAll"
                                                      data-target=".checkSingle"/></th>
                                <th style="text-align: center">Căn hộ</th>
                                <th style="text-align: center">Thông tin hạch toán</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($apartment_imperial as $item)
                                @php
                                   $apart = App\Models\Apartments\Apartments::get_detail_apartment_by_apartment_id($item->apartment_id);
                                @endphp
                                <tr>
                                    <td><input type="checkbox" name="ids[]" value="{{ json_encode($item->list)  }}"
                                               class="iCheck checkSingle"/></td>
                                    <td>
                                        {{$apart->name}}
                                    </td>
                                        @if ($item->list)
                                            <td>
                                                @php
                                                    $detail = $item->list;
                                                @endphp
                                                <table class="table table-striped table-bordered">
                                                    <thead class="bg-primary">
                                                    <tr>
                                                        <th>Dịch vụ</th>
                                                        <th width="150">Kỳ</th>
                                                        <th width="150">Thời gian</th>
                                                        <th>Phát sinh</th>
                                                        <th>Phải trả</th>
                                                    </tr>
                                                    </thead>
                                                    @php
                                                       $count_total=0;
                                                    @endphp
                                                    @foreach ($detail as $value)
                                                        @php
                                                            $apartmentServicePrice = @$value->bdc_apartment_service_price_id != 0 ? App\Models\BdcApartmentServicePrice\ApartmentServicePrice::get_detail_bdc_apartment_service_price_by_apartment_id($value->bdc_apartment_service_price_id) : null;
                                                            $service = @$value->bdc_apartment_service_price_id != 0 ? App\Models\Service\Service::get_detail_bdc_service_by_bdc_service_id($apartmentServicePrice->bdc_service_id) : null;
                                                            $vehicle = @$apartmentServicePrice->bdc_vehicle_id > 0 ? App\Models\Vehicles\Vehicles::get_detail_vehicle_by_id($apartmentServicePrice->bdc_vehicle_id) : null;
                                                             $count_total+=@$value->sumery - @$value->paid;
                                                        @endphp
                                                            <tr>
                                                                <td style="font-weight: bold;">{{@$service->name ."-".@$vehicle->number}}</td>
                                                                <td>{{$value->cycle_name}}</td>
                                                                <td>{{date('d/m/y', strtotime(@$value->from_date))}}
                                                                    - {{date('d/m/y', strtotime(@$value->to_date))}}</td>
                                                                <td style="text-align: right">{{number_format(@$value->sumery)}}</td>
                                                                <td style="text-align: right">{{number_format(@$value->sumery - @$value->paid)}}</td>
                                                            </tr>
                                                    @endforeach
                                                    <tr>
                                                        <td colspan="4"><b>Tổng tiền</b></td>
                                                        <td align="right">{{number_format($count_total)}}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        @endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="row mbm">
                        <div class="col-sm-3">
                            <span class="record-total">Tổng: {{ $debits->total() }} bản ghi</span>
                        </div>
                        <div class="col-sm-6 text-center">
                            <div class="pagination-panel">
                                {{ $debits->appends(Request::all())->onEachSide(1)->links() }}
                            </div>
                        </div>
                        <div class="col-sm-3 text-right">
                            <span class="form-inline">
                                Hiển thị
                                <select name="per_page" class="form-control"
                                        data-target="#form-history-transaction-accounting">
                                    @php $list = [10, 20, 50, 100, 200]; @endphp
                                    @foreach ($list as $num)
                                        <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                                    @endforeach
                                </select>
                            </span>
                        </div>
                    </div>
                </form><!-- END #form-history-transaction-accounting -->
            </div>
        </div>
        <div id="view_confirm_apartment" class="modal fade" role="dialog">
            <div class="modal-dialog custom-dialog">
                <!-- Modal content-->
                <form id="form_view_confirm_apartment">
                    <div class="modal-content">
                        <div class="modal-header bg-primary">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Xác nhận giao dịch của căn hộ</h4>
                        </div>
                        <input type="hidden" id="id_history">
                        <input type="hidden" id="id_trans">
                        <input type="hidden" id="building_id">
                        <input type="hidden" id="type_payment">
                        <input type="hidden" id="cycle_name_current" value="{{\Carbon\Carbon::now()->format('Ym')}}">
                        <div class="modal-body">
                            <div class="alert alert-danger alert_pop_add_resident" style="display: none;">
                                <ul></ul>
                            </div>
                            <div class="row">
                                {{-- <label>Xác nhận giao dịch cho căn hộ: </label>
                                <p>Căn hộ gợi ý:</p>
                                <div class="_list_aparmtent form-group"></div> --}}

                                <div class="panel-body" style="font-weight: bold;">
                                    <div class="col-sm-6">
                                        <div class="row" style="padding: 10px 0;">
                                            <div style="display: inline-table;">
                                                Căn hộ
                                            </div>
                                            <div style="display: inline-table;width: 75%;">
                                                <select name="bdc_apartment_id" id="ip_apartment" class="form-control"
                                                        style="width: 100%"></select>
                                            </div>
                                        </div>
                                        <div class="row cost_paid" style="display: inline-table;">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="row account_name" style="padding: 16px 0;">
                                        </div>
                                        <div class="row detail_type_payment" style="display: inline-table;">
                                            Hình thức: Chuyển khoản
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                    <div class="col-sm-6">
                                        <div class="row" style="padding: 10px 0;">
                                            <div style="display: inline-table;">
                                                Kiểu phiếu
                                            </div>
                                            <div style="display: inline-table;width: 69%;">
                                                <select name="type" id="type_receipt" class="form-control">
                                                    <option value="phieu_bao_co" selected>Phiếu báo có</option>
                                                    <option value="phieu_chi_khac">Phiếu chi khác</option>
                                                    <option value="phieu_thu_truoc">Phiếu thu khác</option>
                                                    <option value="phieu_thu">Phiếu thu</option>
                                                    <option value="phieu_thu_ky_quy">Phiếu thu ký quỹ</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="row remark_payment" style="padding: 16px 0;">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 form-group detail_debit" style="overflow: auto;
                            max-height: 500px;">
                                    <table class="table table-striped table-bordered">
                                        <thead class="bg-primary">
                                        <tr>
                                            <th>Xóa</th>
                                            <th>Dịch vụ</th>
                                            <th>Sản phẩm</th>
                                            <th width="250">Thời gian</th>
                                            <th width="50">Phát sinh</th>
                                            <th>Phải trả</th>
                                            <th>Khuyến mại</th>
                                            <th width="150">Số tiền hạch toán</th>
                                        </tr>
                                        </thead>
                                        <tbody class="list_debit_payment">
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-sm-12 form-group excess_money">
                                    <div class="col-sm-3">
                                        <a class="btn btn-success add_item_debit_payment"><i class="fa fa-plush"></i>Thêm
                                            chỉ định tiền thừa</a>
                                    </div>
                                    <div class="col-sm-9">
                                        <div style="display: inline-table;">
                                            <strong>Tiền thừa </strong> <span>(Ví không chỉ định):</span>
                                        </div>
                                        <div style="display: inline-table;width:50%;">
                                            <input type="text" readonly
                                                   class="form-control customer_paid_string excess_money_no_service"
                                                   value="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <button type="button" class="btn btn-primary pull-right save_status"><i
                                                class="fa fa-save"></i> Xác nhận
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

@endsection
<style>
    .custom-dialog {
        width: 1000px !important;
    }
</style>
@section('javascript')

    <script type="text/javascript"
            src="{{ url('adminLTE/js/function_dxmb.js') . '?v=' . \Carbon\Carbon::now()->timestamp }}">

    </script>
    <script>
        $('input.date_picker').datepicker({
            autoclose: true,
            dateFormat: "dd-mm-yy"
        }).val();
        var list_temp_servive_apartment = null;
        var list_temp_debit = null;
        var cost_total = 0;
        $('.detail_debit').on('input', 'input.customer_paid_string', function (e) {
            let sumery_paid = 0;
            $(this).val(formatCurrency(this));
            // console.log(formatCurrency(this));
            $('.list_debit_payment > tr').each(function (index, tr) {
                let get_cost = $(this).find(".customer_paid_string").val().replace(/,/g, "");
                sumery_paid += parseInt(get_cost);
                let account_balance = cost_total - sumery_paid;
                $('.excess_money_no_service').val(formatCurrencyV2(account_balance.toString() ?? '0'));
            })

        }).on('keypress', 'input.customer_paid_string', function (e) {
            if (!$.isNumeric(String.fromCharCode(e.which))) e.preventDefault();

        }).on('paste', 'input.customer_paid_string', function (e) {
            var cb = e.originalEvent.clipboardData || window.clipboardData;
            if (!$.isNumeric(cb.getData('text'))) e.preventDefault();
        });
        $('.excess_money').on('input', 'input.customer_paid_string', function (e) {
            $(this).val(formatCurrency(this));
        }).on('keypress', 'input.customer_paid_string', function (e) {
            if (!$.isNumeric(String.fromCharCode(e.which))) e.preventDefault();
        }).on('paste', 'input.customer_paid_string', function (e) {
            var cb = e.originalEvent.clipboardData || window.clipboardData;
            if (!$.isNumeric(cb.getData('text'))) e.preventDefault();
        });
        $('.change_status').click(function (e) {
            e.preventDefault();
            $('#view_confirm_apartment').modal('show');
            var apartments = $(this).attr('data-apartments');
            let object_apartments = apartments ? JSON.parse(apartments) : null;
            $('#id_history').val($(this).attr('data-id'));
            $('#id_trans').val($(this).attr('data-trans_id'));
            $('#building_id').val($(this).attr('data-bdc_building_id'));
            $('#type_payment').val($(this).attr('data-type_payment'));
            $('.detail_type_payment').text($('#type_payment').val() == 'tien_mat' ? 'Hình thức: Tiền mặt' : 'Hình thức: Chuyển khoản');
            $('.remark_payment').text('Nội dung chuyển khoản: ' + $(this).attr('data-remark_payment'));
            $('.account_name').text('Chủ hộ: ' + $(this).attr('data-account_name'));
            $('.cost_paid').text('Số tiền: ' + formatCurrencyV2($(this).attr('data-cost') ?? '0') + ' (VND)');

            let check_type_receipt = $('#type_receipt').val();

            if (object_apartments && object_apartments.length > 0) {
                $.each(object_apartments, function (i, item) {
                    $('._list_aparmtent').append(('<span class="label label-sm label-info"> ' + item.name + '</span>'));
                });
            }
            $('#ip_apartment').val('').change();
            get_data_select({
                object: '#ip-apartment,#ip_apartment',
                url: '{{ url('admin/apartments/ajax_get_apartment') }}',
                data_id: 'id',
                data_text: 'name',
                title_default: 'Chọn căn hộ'
            });
            $(".list_debit_payment").html('');
            $(".excess_money_no_service").val('');
            if ($(this).attr('data-apartment_id') && $(this).attr('data-apartment_name')) {
                var push_select_apartment = [];
                push_select_apartment.push({
                    id: $(this).attr('data-apartment_id'),
                    text: $(this).attr('data-apartment_name')
                });
                $('#ip_apartment').select2({data: push_select_apartment});
                $('#ip_apartment').find('option').attr('selected', true);
                $('#ip_apartment').select2();
                //$('#ip_apartment').append($("<option></option>").attr("selected",true).attr("value", $(this).attr('data-apartment_id')).text($(this).attr('data-apartment_name')));
                if ($('#ip_apartment').val()) {
                    getDebitApartment($('#id_trans').val(), $('#ip_apartment').val(), $('#id_history').val());
                }
            }

        });
        $('#ip_apartment').change(function (e) {
            list_temp_servive_apartment = null;
            e.preventDefault();
            getDebitApartment($('#id_trans').val(), $(this).val(), $('#id_history').val());
        });
        $(document).ready(function () {
            console.log(window.localStorage.getItem("elementIdScroll"));
            if (window.localStorage.getItem("elementIdScroll")) {
                setTimeout(function () {
                    const element = document.getElementById('key_' + window.localStorage.getItem("elementIdScroll"));
                    element.style.backgroundColor = '#a7ecb4';
                    element.scrollIntoView();
                    window.localStorage.removeItem("elementIdScroll");
                }, 1000);
            }
            list_temp_servive_apartment = null;

        });
        $('.add_item_debit_payment').click(function (e) {
            e.preventDefault();
            if (list_temp_servive_apartment) {
                let temp = list_temp_servive_apartment;
                let options = '<option value="" selected>--Chọn--</option>';
                Object.entries(list_temp_servive_apartment).forEach(([key, val]) => {
                    options += "<option value=" + val.id + " >" + val.name + " (tiền thừa) </option>";
                });
                var html = ' <tr class="_item_debit">' +
                    '<td>' +
                    '<div style="text-align: center;">' +
                    '<i class="fa fa-remove remove_item" onclick="removeDebit(this)" style="cursor: pointer; font-size: x-large; color: rgb(255, 0, 0);"></i>' +
                    '</div>' +
                    '</td>' +
                    '<td colspan="5"><select name="name_service_aparmtent" onChange="choseService(this)" class="form-control">' + options + '</select></td>' +
                    '<td class="service_promotion"></td>' +
                    '<td><input type="text" class="form-control customer_paid_string" data-type="new" name="bdc_apartment_service_price_id" value="0"></td>' +
                    '</tr>';
                $(".list_debit_payment").append(html);
            } else {
                alert('không tìm thấy dịch vụ của căn hộ');
            }
        });

        function removeDebit(element) {
            $(element).closest('._item_debit').remove();
        }

        $('#type_receipt').change(function (e) {
            e.preventDefault();
            if ($('#ip_apartment').val()) {
                getDebitApartment($('#id_trans').val(), $('#ip_apartment').val(), $('#id_history').val());
            }
        });
        var promotion = null

        function format_date(from_date, to_date) {
            from_date.replaceAll("-", "/")
            to_date.replaceAll("-", "/")
            const sort_date = from_date.split("-");
            const t = sort_date[0];
            sort_date[0] = sort_date[2];
            sort_date[2] = t;
            const sort_date2 = to_date.split("-");
            const t2 = sort_date2[0];
            sort_date2[0] = sort_date2[2];
            sort_date2[2] = t;
            return sort_date.join("/") + '-' + sort_date2.join("/");
        }

        function chosePromotion(even) {
            let split_event = $(even).val().split('_');
            let id_promotion = split_event[0];
            let sumery = split_event[1];
            let _promotion = promotion.find(s => s.id == id_promotion);
            //let f =  $(even).parent().parent().find("td:eq(3)").text();
            if (_promotion) {
                $('.list_debit_payment > tr').each(function (index, tr) {
                    let service_id = $(this).find(".promotion_apartment").data('promotion');
                    if (service_id == _promotion.id) {
                        $(this).parent().find('.chose_service').val('').change();
                        $(this).find(".promotion_apartment").html('');
                    }
                })
                let sumery_discount = parseInt(sumery);
                if (_promotion.type_discount == 0) {
                    sumery_discounttest = parseInt(_promotion.discount * _promotion.number_discount);
                    sumery_discount = parseInt(_promotion.condition) * sumery_discount - sumery_discounttest;
                } else {
                    let _discount = ((parseInt(_promotion.discount) * sumery_discount) / 100) * parseInt(_promotion.number_discount);
                    sumery_discount = parseInt(_promotion.condition) * sumery_discount - _discount;
                }
                html = '<div>Thời gian áp dụng</div>';
                html += '<div>' + format_date_no_time(_promotion.begin) + ' đến ' + format_date_no_time(_promotion.end) + '</div>';
                html += '<div> Số tiền cần nộp là:</div>';
                html += '<div>' + formatCurrencyV2(parseInt(sumery_discount).toString()) + ' VND </div>';
                $(even).parent().find('.promotion_apartment').attr('data-promotion', _promotion.id).html(html);
                $(even).parent().find('.promotion_apartment').attr('data-promotion_price', sumery_discount);
            }
        }

        $('.save_status').click(function (e) {
            e.preventDefault();
            // window.localStorage.setItem("elementIdScroll",998);
            //     setTimeout(function(){
            //         location.reload();
            //     }, 1000);
            $(this).prop('disabled', true);
            let check_type_receipt = $('#type_receipt').val();
            let check = true;
            if (check_type_receipt == 'phieu_bao_co') {
                let excess_money_no_service = $('.excess_money_no_service').val().replace(/,/g, "");
                if (excess_money_no_service < 0) {
                    alert('Vượt quá số tiền giao dịch.');
                    $(this).prop('disabled', false);
                    return;
                }

                $('.list_debit_payment > tr').each(function (index, tr) {
                    let bdc_apartment_service_price_id_cost = $(this).find("[name='bdc_apartment_service_price_id']").val();
                    let bdc_apartment_service_price_id = $(this).find("[name='bdc_apartment_service_price_id']").attr("data-bdc_apartment_service_price_id");
                    let cycle_name = $(this).find(".cycle_name").val();
                    let id_cost = $(this).find("[name='id']").val();
                    let new_id_cost =id_cost ? parseInt(id_cost.replace(/,/g, "")): null;
                    let id = $(this).find("[name='id']").attr("data-id");
                    let promotion_id = $(this).find(".promotion_apartment").attr("data-promotion");
                    let promotion_price = $(this).find(".promotion_apartment").attr("data-promotion_price");
                    if (new_id_cost && new_id_cost < promotion_price) {
                        let service_name = $(this).find("td:eq(1)").text();
                        alert(service_name + ' số tiền nộp không đủ để áp dụng khuyến mại')
                        check = false;
                        $('.save_status').prop('disabled', false);
                    }
                    let cycle_name_current = parseInt($('#cycle_name_current').val()) + 1;
                    // if (cycle_name_current > parseInt(cycle_name)) {
                    //     let service_name = $(this).find("td:eq(1)").text();
                    //     alert(service_name + ' Kỳ áp dụng khuyến mại phải lớn hơn hoặc =' + cycle_name_current);
                    //     check = false;
                    //     $('.save_status').prop('disabled', false);
                    // }
                    if (id) {
                        if (list_temp_debit && list_temp_debit.length > 0) {
                            Object.entries(list_temp_debit).forEach(([key, val]) => {
                                if (val.id == id) {
                                    list_temp_debit[key].new_paid = new_id_cost;
                                    if (promotion_id) {
                                        list_temp_debit[key].new_promotion_id = parseInt(promotion_id);
                                    }
                                }
                            })
                        }
                    }
                    if (bdc_apartment_service_price_id) {
                        if (parseInt(bdc_apartment_service_price_id_cost) < promotion_price) {
                            let service_name = $(this).find("td:eq(1)").text();
                            alert(service_name + ' số tiền nộp không đủ để áp dụng khuyến mại')
                            check = false;
                            $('.save_status').prop('disabled', false);
                        }
                        bdc_apartment_service_price_id_cost = bdc_apartment_service_price_id_cost.replace(/,/g, "");
                        if (list_temp_debit && list_temp_debit.length > 0) {
                            Object.entries(list_temp_debit).forEach(([key, val]) => {
                                if (val.bdc_apartment_service_price_id == bdc_apartment_service_price_id && val.hasOwnProperty("type") && bdc_apartment_service_price_id > 0) {
                                    list_temp_debit[key].coin = parseInt(bdc_apartment_service_price_id_cost);
                                    if (promotion_id) {
                                        list_temp_debit[key].new_promotion_id = parseInt(promotion_id);
                                    }
                                }
                            })
                        }
                        let check_new_excess_money = $(this).find("[name='bdc_apartment_service_price_id']").attr("data-type");
                        if (check_new_excess_money == 'new' && bdc_apartment_service_price_id_cost > 0) {
                            list_temp_debit.push({
                                bdc_apartment_id: $('#ip_apartment').val(),
                                bdc_apartment_service_price_id: bdc_apartment_service_price_id,
                                bdc_building_id: $('#building_id').val(),
                                coin: parseInt(bdc_apartment_service_price_id_cost),
                                cycle_name: "202209",
                                name: list_temp_servive_apartment ? list_temp_servive_apartment.find(x => x.id == bdc_apartment_service_price_id).name : null,
                                type: 1,
                                new_promotion_id: promotion_id ? parseInt(promotion_id) : null
                            });
                        }
                    }
                })
            }

            let param = {
                id: $('#id_history').val(),
                building_id: $('#building_id').val(),
                apartment_id: $('#ip_apartment').val(),
                account_balance: parseInt($('.excess_money_no_service').val().replace(/,/g, "")),
                type_payment: $('#type_payment').val(),
                type: check_type_receipt,
                detail: list_temp_debit.length > 0 ? JSON.stringify(list_temp_debit) : JSON.stringify([])
            };
            console.log(list_temp_debit);
            if (check == true) {
                postDebitApartment(param);
            }
        });

        async function postDebitApartment(param) {
            console.log(param);
            let method = 'post';
            let _result = await call_api(method, 'payment/handleAccountingV2', param);
            toastr.success(_result.mess);
            $('.save_status').prop('disabled', false);
            window.localStorage.setItem("elementIdScroll", param.id);
            setTimeout(function () {
                location.reload();
            }, 1000);

        }

        function choseService(element) {
            let check = false;
            $('.list_debit_payment > tr').each(function (index, tr) {
                let bdc_apartment_service_price_id = $(this).find("[name='bdc_apartment_service_price_id']").attr("data-bdc_apartment_service_price_id");
                if (bdc_apartment_service_price_id == $(element).val()) {
                    check = true;
                }
            })
            if (check == true) {
                alert('Đã tồn tại dịch vụ tiền thừa này.');
                $(element).val('').change();
            } else {
                $(element).closest('._item_debit').find("[name='bdc_apartment_service_price_id']").attr("data-bdc_apartment_service_price_id", $(element).val());
                let promotion_options = '<option value="" selected>--Chọn--</option>';
                let servive_apartment = list_temp_servive_apartment.find(s => s.id == $(element).val());
                if (servive_apartment) {
                    Object.entries(promotion).forEach(([key1, val1]) => {
                        if (val1.service_id == servive_apartment.bdc_service_id) {
                            promotion_options += "<option value=" + val1.id + '_' + servive_apartment.price + " >" + val1.name + "</option>";
                        }
                    });
                }
                $(element).parent().parent().find('.service_promotion').html('<select onChange="chosePromotion(this)" class="form-control chose_service">' + promotion_options + '</select>' +
                    '<div class="promotion_apartment"></div>')
            }
        }

        get_data_select({
            object: '#ip-apartment,#ip_apartment',
            url: '{{ url('admin/apartments/ajax_get_apartment') }}',
            data_id: 'id',
            data_text: 'name',
            title_default: 'Chọn căn hộ'
        });
        $("#ip-place_id").on('change', function () {
            if ($("#ip-place_id").val()) {
                get_data_select({
                    object: '#ip-apartment,#ip_apartment',
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
                    data: function (params) {
                        var query = {
                            search: params.term,
                            place_id: $("#ip-place_id").val(),
                        }
                        return query;
                    },
                    processResults: function (json, params) {
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
    </script>
@endsection