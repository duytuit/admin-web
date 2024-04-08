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
            <th>Còn nợ</th>
            <th>Chi trả</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($debitDetails as $billDetail)
            <?php
                $bill = $billRepository->find($billDetail->bdc_bill_id);
                $service = $serviceRepository->getServiceById($billDetail->bdc_service_id);
                $serviceName = $billDetail->title;
                if(!isset($service->servicePriceDefault)) {
                    continue;
                }
                @$service->servicePriceDefault->bdc_price_type_id == 2 
                    ? $datetime = date('d/m', strtotime($billDetail->from_date)) . ' - ' . date('d/m', strtotime($billDetail->to_date))
                    : $datetime = date('d/m', strtotime($billDetail->from_date)) . ' - ' . date('d/m', strtotime($billDetail->to_date . ' - 1 days'));
                // $datetime = $billDetail->from_date . ' - ' . $billDetail->to_date;
                $sumery = $billDetail->sumery;
                $previousOwed = $billDetail->previous_owed;
                $totalPayment = $billDetail->is_free == 1 ? 0 : $sumery + $previousOwed;
                $paid = $billDetail->paid;
            ?>
            <tr class="checkbox_parent">
                <td>
                    <input type="checkbox" name="ids[]" onclick="checkServicePhieuChi(this)" />
                </td>
                <td>{{ @$service->name }}</td>
                <td>{{ $datetime }}</td>
                <td>{{ $serviceName }}</td>
                <td style="text-align: right;">{{ number_format($sumery, 0, '', '.') }}</td>
                {{-- <td>{{ number_format($previousOwed, 0, '', '.') }}</td> --}}
                <td style="text-align: right;">{{ number_format($billDetail->new_sumery, 0, '', '.') }}</td>
                <td>
                    <input type="text" class="total_payment" value="{{ number_format(0, 0, '', ',') }}"/>
                </td>
                <input type="hidden" class="bill_code" value="{{ $bill->bill_code }}"/>
                <input type="hidden" class="service_id" value="{{ $billDetail->bdc_service_id }}"/>
                <input type="hidden" class="apartment_service_price_id" value="{{ $billDetail->bdc_apartment_service_price_id }}"/>
                <input type="hidden" class="debit_version" value="{{ $billDetail->version }}"/>
            </tr>
        @endforeach
    </tbody>
</table>
<script type="text/javascript" src="{{ url('adminLTE/js/main.js') }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/js/custom.js') }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/js/format-currency.js') }}"></script>
