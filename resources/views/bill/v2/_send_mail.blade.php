<table style="border: 1px solid #f4f4f4; width: 100%; margin-bottom: 20px;">
    <thead style="color: #fff; background-color: #3c8dbc;">
    <tr>
        <th>STT</th>
        <th>Tên dịch vụ</th>
        <th>Dư nợ đầu kỳ</th>
        <th>Phát sinh trong kỳ</th>
        <th>Thanh toán</th>
        <th>Dư nợ cuối kỳ</th>
    </tr>
    </thead>
    <tbody>
        @php
        $total_dau_ky =0;
        $total_trong_ky=0;
        $total_thanh_toan=0;
        $total_cuoi_ky=0;
        @endphp 
        @foreach ($debitsTotal as $key => $value)   
            @php
                $total_dau_ky +=@$value['dau_ky'];
                $total_trong_ky+=@$value['trong_ky'];
                $total_thanh_toan+=@$value['thanh_toan'];
                $total_cuoi_ky+=@$value['cuoi_ky'];
            @endphp 
            <tr>
                <td>{{($key + 1)}}</td>
                <td>{{ @$value['dich_vu'] }}</td>
                <td style="text-align: right;">{{ number_format(@$value['dau_ky']) }}</td>
                <td style="text-align: right;">{{ number_format(@$value['trong_ky']) }}</td>
                <td style="text-align: right;">{{ number_format(@$value['thanh_toan']) }}</td>
                <td style="text-align: right;">{{ number_format(@$value['cuoi_ky']) }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="2" style="font-weight: bold;text-align: center">Tổng</td>
            <td style="text-align: right;font-weight: bold">{{ number_format(@$total_dau_ky) }}</td>
            <td style="text-align: right;font-weight: bold">{{ number_format(@$total_trong_ky) }}</td>
            <td style="text-align: right;font-weight: bold">{{ number_format(@$total_thanh_toan) }}</td>
            <td style="text-align: right;font-weight: bold">{{ number_format(@$total_cuoi_ky) }}</td>
        </tr>
    </tbody>
</table>