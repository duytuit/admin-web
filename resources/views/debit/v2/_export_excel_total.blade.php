<div class="box-body">
    <div class="table-responsive">
        <table>
            <tr>
                <td>Tổng Đầu kỳ :</td>
                <td>{{ @$sumDayKy_all }}</td>
            </tr>
            <tr>
                <td>Tổng Phát Sinh :</td>
                <td>{{ @$sumPsTrongKy_all }}</td>
            </tr>
            <tr>
                <td>Tổng Thanh Toán :</td>
                <td >{{ @$sumThanhToan_all }}</td>
            </tr>
            <tr>
                <td>Tổng Dư Nợ Cuối Kỳ :</td>
                <td>{{ @$sumDayKy_all + @$sumPsTrongKy_all - @$sumThanhToan_all }}</td>
            </tr>
        </table>
        <table class="table table-hover table-striped table-bordered">
            <thead class="bg-primary">
            @php
                $sumDauky = $debits->sum('dau_ky');
                $sumPsTrongky = $debits->sum('ps_trongky');
                $sumThanhToan = $debits->sum('thanh_toan');
            @endphp
            <tr>
                <th></th>
                <th></th>
                <th></th>
                <th style="text-align: right;">{{ $sumDauky }}</th>
                <th style="text-align: right;">{{ $sumPsTrongky }}</th>
                <th style="text-align: right;">{{ $sumThanhToan }}</th>
                <th style="text-align: right;">{{ $sumDauky + $sumPsTrongky - $sumThanhToan }}</th>
            </tr>
            <tr>
                <th>STT</th>
                <th>Tên KH</th>
                <th>Căn hộ</th>
                {{-- <th>Tòa nhà</th> --}}
                <th>Đầu kỳ</th>
                <th>Phát sinh trong kỳ</th>
                <th>Thanh toán</th>
                <th>Dư nợ cuối kỳ</th>
            </tr>
            </thead>
            <tbody>
            @if(isset($debits) && $debits != null)
                @foreach($debits as $key => $debit)
                    <?php
                        $customer = App\Models\Apartments\V2\UserApartments::getPurchaser($debit->bdc_apartment_id, 0);
                    ?>
                    <tr>
                        <td>{{ @($key + 1) }}</td>
                        <td>{{ @$customer->user_info_first->full_name }}</td>
                        <td>{{ @$debit->name }}</td>
                        {{-- <td>{{ @$debit->name }}</td> --}}
                        <td style="text-align: right;">{{ @$debit->dau_ky }}</td>
                        <td style="text-align: right;">{{ @$debit->ps_trongky }}</td>
                        <td style="text-align: right;">{{ @$debit->thanh_toan }}</td>
                        <td style="text-align: right;">{{ @$debit->dau_ky + @$debit->ps_trongky - @$debit->thanh_toan }}</td>
                    </tr>
                @endforeach
            @endif
            </tbody>
        </table>
    </div>
</div>