<link rel="stylesheet" href="{{ url('adminLTE/plugins/iCheck/square/green.css') }}" />
<table class="table no-margin">
    <thead>
        <tr>
            <th>
                {{-- <input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /> --}}
            </th>
            <th>Dịch vụ</th>
            <th style="width: 20%">Thời gian</th>
            <th>Sản phẩm</th>
            <th>Phát sinh</th>
            {{-- <th>Nợ cũ</th> --}}
            <th>Tổng tiền</th>
            <th>Thanh toán</th>
        </tr>
    </thead>
    <tbody class="list_info">
        @foreach ($debitDetails as $billDetail)
            <?php
                $bill = $billRepository->find($billDetail->bdc_bill_id);
                $service = $serviceRepository->getServiceById($billDetail->bdc_service_id);
                $serviceName = $billDetail->title;
                $service->servicePriceDefault->bdc_price_type_id == 2 || $service->servicePriceDefault->bdc_price_type_id == 3 ? $datetime = date('d/m/y', strtotime($billDetail->from_date)) . ' - ' . date('d/m/y', strtotime($billDetail->to_date)):$datetime = date('d/m/y', strtotime($billDetail->from_date)) . ' - ' . date('d/m/y', strtotime($billDetail->to_date . ' - 1 days'));
                // $datetime = $billDetail->from_date . ' - ' . $billDetail->to_date;
                $sumery = $billDetail->sumery;
                $previousOwed = $billDetail->previous_owed;
                $totalPayment = $billDetail->is_free == 1 ? 0 : $sumery + $previousOwed;
                $paid = $billDetail->paid;
            ?>
            @if (isset($bill) && isset($service))
                <tr class="checkbox_parent">
                    <td>
                        @if($billDetail->new_sumery != 0 && $billDetail->is_free == 0)
                            <input type="checkbox" name="ids[]" id="check_box_debit_receipt" onclick="checkServiceV2(this)" />
                        @endif
                    </td>
                    <td>{{ $service->name }}</td>
                    <td>{{ $datetime }}</td>
                    <td>{{ $serviceName }}</td>
                    <td style="text-align: right;">{{ number_format($sumery, 0, '', ',') }}</td>
                    {{-- <td>{{ number_format($previousOwed, 0, '', '.') }}</td> --}}
                    <td style="text-align: right;">{{ number_format($totalPayment, 0, '', ',') }}</td>
                    <td>
                        @if($billDetail->new_sumery != 0 && $billDetail->is_free == 0)
                            <input type="text" class="total_payment" value="{{ number_format($billDetail->new_sumery - $billDetail->paid_v3, 0, '', ',') }}"/>
                            <input type="hidden" class="total_payment_current" value="{{$billDetail->new_sumery - $billDetail->paid_v3}}"/>
                        @else
                            0 (Miễn phí)
                        @endif
                    </td>
                    <input type="hidden" class="bill_code" value="{{ $bill->bill_code }}"/>
                    <input type="hidden" class="service_id" value="{{ $billDetail->bdc_service_id }}"/>
                    <input type="hidden" class="apartment_service_price_id" value="{{ $billDetail->bdc_apartment_service_price_id }}"/>
                    <input type="hidden" class="debit_version" value="{{ $billDetail->version }}"/>
                    <input type="hidden" class="debit_id" value="{{ $billDetail->id }}"/>
                </tr>
            @endif
        @endforeach
    </tbody>
</table>
<script type="text/javascript" src="{{ url('adminLTE/js/main.js') }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/js/custom.js') }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/js/format-currency.js') }}"></script>
