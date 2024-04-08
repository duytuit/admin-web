<?php $converMoney = App\Services\ConvertMoney::NumberToWords($receipt['cost']); ?>
<div class="information">
    <table width="100%">
        <tr>
            <td align="left" class="text-header">
                <p class="text-building">BQL TÒA NHÀ</p>
                <p>{{ @$building->name }}</p>
            </td>
            <td align="right" class="text-header">
                <p>PT số : {{$receipt['receipt_code'] }}</p>
            </td>
        </tr>
    </table>
</div>
<div class="invoice">
    <div style=" text-align: center;">
      
        @if($receipt['type'] == 'phieu_thu')
            <p class="text-invoice">Phiếu thu</p>
        @elseif($receipt['type'] == 'phieu_thu_truoc')
            <p class="text-invoice">Phiếu thu khác</p>
        @elseif($receipt['type'] == 'phieu_chi')
            <p class="text-invoice">Phiếu chi</p>
        @elseif($receipt['type'] == 'phieu_chi_khac')
            <p class="text-invoice">Phiếu chi khác</p>
        @elseif($receipt['type'] == 'phieu_bao_co')
            <p class="text-invoice">Phiếu báo có</p>
        @else
            <p class="text-invoice">Phiếu kế toán</p>
        @endif
            <p class="date-invoice">Ngày {{ date('d', strtotime($receipt['create_date']))}} tháng {{ date('m', strtotime($receipt['create_date'])) }} năm {{ date('Y', strtotime($receipt['create_date'])) }}</p>

    </div>
    <div class="content_text">
        <div class="list_content_text">
            <table width="70%">
                <thead>
                <tr>
                    <td></td>
                    <td></td>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td align="left">Người nộp tiền:</td>
                    <td width="65%"><b>{{ $receipt['customer_name'] }}</b></td>
                </tr>
                <tr>
                    <td align="left">Căn hộ:</td>
                    <td width="65%">{{ @$apartment->name }}</td>
                </tr>
                <tr>
                    <td align="left">Hình thức:</td>
                    <td width="65%">{{ $receipt['type_payment_name'] }}</td>
                </tr>
                @if($receipt['type'] == 'phieu_thu' || $receipt['type'] == 'phieu_thu_truoc' || $receipt['type'] == 'phieu_chi' || $receipt['type'] == 'phieu_chi_khac' || $receipt['type'] == 'phieu_bao_co')
                <tr>
                    <td align="left">Số tiền:</td>
                    <td width="65%"><b>{{ number_format($receipt['cost'], 0, '', '.') }}</b> <b>VND</b></td>
                </tr>
                <tr>
                    <td align="left">Bằng chữ:</td>
                    <td width="65%">{{ucfirst(@$converMoney)}} đồng</td>
                </tr>
                @endif
                <tr>
                    <td align="left">Nội dung:</td>
                    <td width="65%">{{ $receipt['description'] }}</td>
                </tr>
                </tbody>
            </table>
            <div class="pull-right" style="padding-right: 15px;">
                <div class="text_address">
                </div>
            </div>
        </div>
    </div>
    <div class="footer">
        <table style="width: 100%;text-align: center;font-size: 11px">
            <tbody>
            <tr>
                <td><b>Giám đốc</b></td>
                <td><b>Kế toán</b></td>
                <td><b>Thủ quỹ</b></td>
                <td><b>Người lập</b></td>
                <td><b>Người nộp</b></td>
            </tr>
            <tr>
                <td>(Ký, ghi rõ họ tên)</td>
                <td>(Ký, ghi rõ họ tên)</td>
                <td>(Ký, ghi rõ họ tên)</td>
                <td>(Ký, ghi rõ họ tên)</td>
                <td>(Ký, ghi rõ họ tên)</td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="content_text">
        <div class="list_service">
          @if(!empty($listService))
                <table>
                    <thead>
                    <tr>
                        <td>STT</td>
                        <td>Dịch vụ</td>
                        <td>Sản phẩm</td>
                        <td>Phát sinh</td>
                        <td>Thời gian</td>
                        <td>Thanh toán</td>
                    </tr>
                    </thead>
                    <tbody>
                
                        @foreach ($listService as $key => $_listService)
                            @if (@$_listService['cost'])
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ @$_listService['name'] }}</td>
                                        <td>{{ @$_listService['title'] }}</td>
                                        <td>{{ number_format($_listService['sumery'], 0, '', '.') }}</td>
                                        <td>{{ date('d/m/Y', strtotime(@$_listService['from_date'])) . "-" . date('d/m/Y', strtotime(@$_listService['to_date'])) }}</td>
                                        <td style="text-align: right;">{{ number_format($_listService['cost'], 0, '', '.') }}</td>
                                    </tr>
                            @else
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td colspan="4">Tiền thừa</td>
                                        <td style="text-align: right;">{{ number_format($_listService['cost_paid'], 0, '', '.') }}</td>
                                    </tr>
                            @endif
                        @endforeach
                
                    </tbody>
                </table>
             @endif
        </div>
    </div>
</div>
<style type="text/css">
    * {
        font-size: 15px;
    }

    .padding-tb {
        padding-top: 10px;
        padding-bottom: 10px;
        padding-left: 3px;
    }

    /* .text-building {
        padding-left: 5px;
    } */

    td.text-header {
        font-weight: bold;
        margin-bottom: 0px;
        width: 40%;
    }

    p.text-invoice {
        text-transform: uppercase;
        font-weight: bold;
        font-size: 18px;
    }

    p.date-invoice {
        font-style: italic;
    }

    .list_content_text {
        padding-left: 10px !important;
        width: 100%;
    }

    a {
        color: #fff;
        text-decoration: none;
    }

    table {
        font-size: 15px;
    }

    tfoot tr td {
        font-weight: bold;
        font-size: 15px;
    }

    .list_content_text tbody td {
        padding-left: 25px;
    }

    .invoice h3 {
        margin-left: 15px;
    }

    .information table {
        padding-left: 60px;
        padding-right: 60px;
        padding-top: 20px;
    }
    .footer {
        display: inline-block;
        width: 100%;
        margin: 20px 0;
    }

    .list_service table{
        width: 100%
    }

    .list_service table td{
        border-collapse: collapse;
        border: 1px solid black;
    }

    .list_service thead td{
        font-weight: bold;
    }

    .list_service td{
        padding: 10px;
    }
</style>