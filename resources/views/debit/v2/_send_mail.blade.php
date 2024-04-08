<h4>{!! $message !!}</h4>
<table style="border: 1px solid #f4f4f4; width: 100%; margin-bottom: 20px;">
    <thead style="color: #fff; background-color: #3c8dbc;">
    <tr>
        <th>Tên KH</th>
        <th>Căn hộ</th>
        <th>Dịch vụ</th>
        <th>Dư nợ cuối kỳ</th>
    </tr>
    </thead>
    <tbody>
        <tr style="background-color: rgb(211, 185, 185)">
            <td>{{ @$customer['pub_user_profile']['display_name'] }}</td>
            <td>{{ @$apartment->name }}</td>
            <td></td>
            <td style="text-align: right;"></td>
        </tr>
        @foreach ($debitDetails as $debitDetail)    
            <tr>
                <td></td>
                <td></td>
                <td>{{ @$debitDetail->service_name }}</td>
                <td style="text-align: right;">{{ number_format(@$debitDetail->dau_ky + @$debitDetail->ps_trongky - @$debitDetail->thanh_toan) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>