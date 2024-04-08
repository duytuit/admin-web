<link rel="stylesheet" href="{{ url('adminLTE/plugins/iCheck/square/green.css') }}" />
<table class="table no-margin">
    <thead>
        <tr>
            <th>
                {{-- <input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /> --}}
            </th>
            <th>Dịch vụ</th>
            <th>Sản phẩm</th>
            <th>Đơn giá</th>
            <th>Tổng tiền</th>
            <th>Thanh toán</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($bills as $bill)
            <?php
                // $billDetails = $bill->debitDetail;
                $billDetails = $debitDetails->findMaxVersionByBillId($bill->id);
            ?>
            @foreach ($billDetails as $billDetail)
                <?php
                    // $service = $billDetail->service;
                    $serviceName = $billDetail->title;
                    // $datetime = $billDetail->from_date->format('d/m') . ' - ' . $billDetail->to_date->format('d/m');
                    $datetime = $billDetail->from_date . ' - ' . $billDetail->to_date;
                    $sumery = $billDetail->sumery;
                    $previousOwed = $billDetail->previous_owed;
                    $totalPayment = $sumery + $previousOwed;
                    $paid = $billDetail->paid;
                ?>
                <tr class="checkbox_parent">
                    <td style="text-align: right;">
                        @if($billDetail->new_sumery > 0 && $billDetail->is_free == 0)
                            <input type="checkbox" name="ids[]" onclick="checkService(this)" />
                        @endif
                    </td>
                    <td>{{ $serviceName }}</td>
                    <td>{{ $datetime }}</td>
                    <td>{{ $serviceName }}</td>
                    <td style="text-align: right;">{{ number_format($sumery, 0, '', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($previousOwed, 0, '', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($totalPayment, 0, '', '.') }}</td>
                    <td>
                        @if($billDetail->new_sumery > 0 && $billDetail->is_free == 0)
                            <input type="text" class="total_payment" value="{{ $billDetail->new_sumery }}"/>
                        @else
                            {{ number_format($totalPayment, 0, '', '.') }}
                        @endif
                    </td>
                    <input type="hidden" class="bill_code" value="{{ $bill->bill_code }}"/>
                    <input type="hidden" class="service_id" value="{{ $billDetail->bdc_service_id }}"/>
                    <input type="hidden" class="apartment_service_price_id" value="{{ $billDetail->bdc_apartment_service_price_id }}"/>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>
<script type="text/javascript" src="{{ url('adminLTE/js/main.js') }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/js/custom.js') }}"></script>