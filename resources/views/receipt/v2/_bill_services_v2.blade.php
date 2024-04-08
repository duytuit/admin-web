<link rel="stylesheet" href="{{ url('adminLTE/plugins/iCheck/square/green.css') }}"/>
<table id="table_receipt_debit" class="table no-margin">
    <thead>
    <tr>
        <th>
            <input type="checkbox" class="checkServiceAll" onclick="checkServiceAll(this)" data-target=".checkSingle"/>
        </th>
        <th>Dịch vụ</th>
        <th style="width: 20%">Thời gian</th>
        <th>Sản phẩm</th>
        <th>Phát sinh</th>
        <th width="150">Giảm trừ</th>
        <th width="250">Khuyến mại</th>
        <th>Phải trả</th>
        <th width="150">Thanh toán</th>
        <th width="250"><a href="javascript:;" style="text-decoration: revert;" id="thanh_toan_tu">Thanh toán từ</a>
        </th>
    </tr>
    </thead>
    <tbody class="list_info">
    @foreach ($debitDetails as $billDetail)
            <?php
            $bill = $billRepository->find($billDetail['bdc_bill_id']);
            $serviceApartment = App\Models\BdcApartmentServicePrice\ApartmentServicePrice::get_detail_bdc_apartment_service_price_by_apartment_id($billDetail['bdc_apartment_service_price_id']);
            $service = App\Models\Service\Service::get_detail_bdc_service_by_bdc_service_id($serviceApartment->bdc_service_id);
            $vehicle = App\Models\Vehicles\Vehicles::get_detail_vehicle_by_id($serviceApartment->bdc_vehicle_id);
            $datetime = $serviceApartment->bdc_price_type_id == 2 || $serviceApartment->bdc_price_type_id == 3 ? date('d/m/y', strtotime($billDetail['from_date'])) . ' - ' . date('d/m/y', strtotime($billDetail['to_date'])) : date('d/m/y', strtotime($billDetail['from_date'])) . ' - ' . date('d/m/y', strtotime($billDetail['to_date'] . ' - 1 days'));
            $sumery = $billDetail['sumery'] - $billDetail['paid'];
            ?>
        @if (isset($bill))
            <tr class="checkbox_parent">
                <input type="hidden" class="service_id" value="{{ $service->id }}"/>
                <input type="hidden" class="service_price" value="{{ $serviceApartment->price }}"/>
                <td>
                    @if($billDetail['sumery'] != 0)
                        <input type="checkbox" name="ids[]" class="checkSingle" id="check_box_debit_receipt"
                               onclick="checkServiceV2(this)"/>
                    @endif
                </td>
                <td>{{$service->name}}</td>
                <td>{{ $datetime }}</td>
                <td>{{@$vehicle->number ?? $service->name}}</td>
                <td>
                    <span class="debit_sumery">{{ number_format($billDetail['sumery'] + $billDetail['discount'], 0, '', ',') }}</span>
                </td>
                <td>
                    <a href="javascript:;" style="color: #3c8dbc"
                       class="debit_discount_current">{{ number_format($billDetail['discount']) }}</a>
                    <input type="text" class="form-control total_payment_money debit_discount" style="display: none"
                           value="{{ number_format($billDetail['discount']) }}"/>
                </td>
                <td class="promotion_apartment_list">
                    {{--                    <a href="javascript:;" style="color: #3c8dbc" onclick="editDiscount(this)"--}}
                    {{--                       class="debit_discount_current">{{ number_format($billDetail['discount']) }}</a>--}}
                    {{--                    <input type="text" class="form-control total_payment_money debit_discount" style="display: none"--}}
                    {{--                           value="{{ number_format($billDetail['discount']) }}"/>--}}
                </td>
                <td><span class="debit_sumery_paid">{{ number_format($sumery) }}</span></td>
                <td>
                    @if($billDetail['sumery'] != 0)
                        <input type="text" class="form-control total_payment_money total_payment"
                               value="{{ number_format($sumery, 0, '', ',') }}"/>
                        <input type="hidden" class="total_payment_current" value="{{$sumery}}"/>
                        <input type="hidden" class="total_payment_old" value="{{$sumery}}"/>
                        <input type="hidden" class="debit_paid" value="{{$billDetail['paid']}}"/>
                    @else
                        0 (Miễn phí)
                    @endif
                </td>
                <td>
                    @if (@$detail_service_so_du)
                        <div class="detail_chi_dinh_hach_toan" style="display: none">
                            <select class="form-control select2 chi_dinh_hach_toan" style="width: 100%">
                                <option value="" selected>Phí dịch vụ...</option>
                                @foreach($detail_service_so_du as $value)
                                    <option value="{{ $value->bdc_apartment_service_price_id }}">{{ $value->bdc_apartment_service_price_id == 0 ? 'Chưa chỉ định'.'('.number_format($value->coin, 0, '', ',').')' : (@$value->apartmentServicePrice->vehicle ? @$value->apartmentServicePrice->vehicle->number.'('.number_format($value->coin, 0, '', ',').')' :  @$value->apartmentServicePrice->service->name.'('.number_format($value->coin, 0, '', ',').')')}}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                </td>
                <input type="hidden" class="bill_code" value="{{ $bill->bill_code }}"/>
                <input type="hidden" class="apartment_service_price_id"
                       value="{{ $billDetail['bdc_apartment_service_price_id'] }}"/>
                <input type="hidden" class="debit_id" value="{{ $billDetail['id'] }}"/>
            </tr>
        @endif
    @endforeach
    </tbody>
</table>
<script type="text/javascript" src="{{ url('adminLTE/js/main.js') }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/js/custom.js') }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/js/format-currency.js') }}"></script>
