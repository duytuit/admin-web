<link rel="stylesheet" href="{{ url('adminLTE/plugins/iCheck/square/green.css') }}" />
<table class="table no-margin">
    <thead>
        <tr>
            <th>
                {{-- <input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /> --}}
            </th>
            <th>Mã hóa đơn</th>
            <th>Số tiền</th>
            <th>Thanh toán</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($bills as $bill)
        <tr class="checkbox_parent">
            <td>
                <?php
                    // $array = unserialize($strBillIds);
                    $isExisting = false;
                    foreach($receipts as $receipt) {
                        if(strpos($receipt->bdc_bill_id, $bill->bill_code)) {
                            $isExisting = true;
                            break;
                        }
                    }
                ?>
                @if (!$isExisting)
                    <input type="checkbox" name="ids[]" onclick="checkService(this)" />
                @endif
            </td>
            <?php
                $billDetails = $bill->debitDetail;
                $total_price = $billDetails->sum('sumery');
            ?>
            <td>{{ $bill->bill_code }}</td>
            <td style="text-align: right;">{{ number_format($total_price, 0, '', '.') }}</td>
            <td  style="text-align: right;">
                {{ number_format($total_price, 0, '', '.') }}
                <input type="hidden" class="total_payment" value="{{ $total_price }}"/>
                <input type="hidden" class="bill_code" value="{{ $bill->bill_code }}"/>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
<script type="text/javascript" src="{{ url('adminLTE/js/main.js') }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/js/custom.js') }}"></script>