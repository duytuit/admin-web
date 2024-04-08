<div class="table-responsive">
    <table class="table table-hover table-striped table-bordered">
        <thead class="bg-primary">
        <tr>
            <th>STT</th>
            <th>Tên KH</th>
            <th>Căn hộ</th>
            <th>Dịch vụ</th>
            <th>Đầu kỳ</th>
            <th>Phát sinh trong kỳ</th>
            <th>Thanh toán</th>
            <th>Dư nợ cuối kỳ</th>
        </tr>
        </thead>
        <tbody>
        @if(isset($debits) && $debits != null)
            @php
                $apartmentIds = $debits->map(function($debit) {
                    // return collect($debit)->only('bdc_apartment_id')->all();
                    return @$debit->bdc_apartment_id;
                });
                $apartmentIds = implode(",", $apartmentIds->toArray());
                if($from_date != null && $to_date != null)
                {
                    $debitDetails = $debitDetailRepository->GeneralAccountantDetails($building_id, $apartmentIds, $from_date, $to_date);
                }
                else
                {
                    $debitDetails = $debitDetailRepository->GeneralAccountantDetailAlls($building_id, $apartmentIds);
                }
                $debitDetails = collect($debitDetails);
                // dd(collect($debitDetails)->where('bdc_apartment_id', 3018));
            @endphp
            @foreach($debits as $key => $debit)
                <?php
                    $customer = App\Models\Apartments\V2\UserApartments::getPurchaser(@$debit->bdc_apartment_id, 0);
                ?>
                <tr>
                    <td>{{ @($key + 1) }}</td>
                    <td>{{ @$customer->user_info_first->full_name }}</td>
                    <td>{{ @$debit->name }}</td>
                    <td></td>
                    <td style="text-align: right;">{{ @$debit->dau_ky }}</td>
                    <td style="text-align: right;">{{ @$debit->ps_trongky }}</td>
                    <td style="text-align: right;">{{ @$debit->thanh_toan }}</td>
                    <td style="text-align: right;">{{ @$debit->dau_ky + @$debit->ps_trongky - @$debit->thanh_toan }}</td>
                </tr>
                @php
                    $_debitDetails = @$debitDetails->where('bdc_apartment_id', @$debit->bdc_apartment_id);
                @endphp
                @foreach ($_debitDetails as $debitDetail)
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>{{ @$debitDetail->service_name }}</td>
                        <td style="text-align: right;">{{ @$debitDetail->dau_ky }}</td>
                        <td style="text-align: right;">{{ @$debitDetail->ps_trongky }}</td>
                        <td style="text-align: right;">{{ @$debitDetail->thanh_toan }}</td>
                        <td style="text-align: right;">{{ @$debitDetail->dau_ky + @$debitDetail->ps_trongky - @$debitDetail->thanh_toan }}</td>
                    </tr>
                @endforeach
            @endforeach
        @endif
        </tbody>
    </table>
</div>