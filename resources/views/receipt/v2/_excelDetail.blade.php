<table>
    <thead>
    <tr style="background-color: #00c0ef;">
        <th><b>Loại NK</b></th>
        <th><b>Tên căn hộ</b></th>
        <th><b>Chủ hộ</b></th>
        <th><b>Số chứng từ</b></th>
        <th><b>Hình thức</b></th>
        <th><b>Kỳ bảng kê</b></th>
        <th><b>Kỳ kế toán</b></th>
        <th><b>Ngày lập phiếu</b></th>
        <th><b>Ngày hạch toán</b></th>
        <th><b>Diễn giải</b></th>
        <th><b>Biển số xe</b></th>
        <th><b>Mã khách hàng-NCC</b></th>
        <th><b>Mã phí</b></th>
        <th><b>Sản phẩm</b></th>
        <th><b>Block</b></th>
        <th><b>Dự án</b></th>
        <th><b>Mã thu</b></th>
        <th><b>Mã tài khoản</b></th>
        <th><b>Số tiền</b></th>
        <th><b>Mã khách hàng</b></th>
        <th><b>Tên khách hàng</b></th>
        <th><b>Người nộp/Nhận tiền</b></th>
        <th><b>Ghi chú</b></th>
        <th><b>Nhóm dịch vụ</b></th>
    </tr>
    </thead>
    <tbody>
    @foreach($content as $_content)
        @if($_content["receipt_type"] == 'phieu_ke_toan' && $_content["Số tiền"] < 0)
            <tr style="background-color: #FE2E2E;">
                <td>{{ $_content["Loại NK"] }}</td>
                <td>{{ $_content["Tên căn hộ"] }}</td>
                <td>{{ $_content["Chủ hộ"] }}</td>
                <td>{{ $_content["Số chứng từ"] }}</td>
                <td>{{ $_content["Hình thức"] }}</td>
                <td>{{ $_content["Kỳ bảng kê"] }}</td>
                <td>{{ $_content["Kỳ kế toán"] }}</td>
                <td>{{ $_content["Ngày lập phiếu"] }}</td>
                <td>{{ $_content["Ngày hạch toán"] }}</td>
                <td>{{ $_content["Diễn giải"] }}</td>
                <td>{{ $_content["Biển số xe"] }}</td>
                <td>{{ $_content["Mã khách hàng-NCC"] }}</td>
                <td>{{ $_content["Mã phí"] }}</td>
                <td>{{ $_content["Sản phẩm"] }}</td>
                <td>{{ $_content["Block"] }}</td>
                <td>{{ $_content["Dự án"] }}</td>
                <td>{{ $_content["Mã thu"] }}</td>
                <td>{{ $_content["Mã tài khoản"] }}</td>
                <td>{{ $_content["Số tiền"] }}</td>
                <td>{{ $_content["Mã khách hàng"] }}</td>
                <td>{{ $_content["Tên khách hàng"] }}</td>
                <td>{{ $_content["Người nộp/Nhận tiền"] }}</td>
                <td>{{ $_content["Ghi chú"] }}</td>
                <td>{{ $_content["Nhóm dịch vụ"] }}</td>
            </tr>
        @else
            <tr>
                <td>{{ $_content["Loại NK"] }}</td>
                <td>{{ $_content["Tên căn hộ"] }}</td>
                <td>{{ $_content["Chủ hộ"] }}</td>
                <td>{{ $_content["Số chứng từ"] }}</td>
                <td>{{ $_content["Hình thức"] }}</td>
                <td>{{ $_content["Kỳ bảng kê"] }}</td>
                <td>{{ $_content["Kỳ kế toán"] }}</td>
                <td>{{ $_content["Ngày lập phiếu"] }}</td>
                <td>{{ $_content["Ngày hạch toán"] }}</td>
                <td>{{ $_content["Diễn giải"] }}</td>
                <td>{{ $_content["Biển số xe"] }}</td>
                <td>{{ $_content["Mã khách hàng-NCC"] }}</td>
                <td>{{ $_content["Mã phí"] }}</td>
                <td>{{ $_content["Sản phẩm"] }}</td>
                <td>{{ $_content["Block"] }}</td>
                <td>{{ $_content["Dự án"] }}</td>
                <td>{{ $_content["Mã thu"] }}</td>
                <td>{{ $_content["Mã tài khoản"] }}</td>
                <td>{{ $_content["Số tiền"] }}</td>
                <td>{{ $_content["Mã khách hàng"] }}</td>
                <td>{{ $_content["Tên khách hàng"] }}</td>
                <td>{{ $_content["Người nộp/Nhận tiền"] }}</td>
                <td>{{ $_content["Ghi chú"] }}</td>
                <td>{{ $_content["Nhóm dịch vụ"] }}</td>
            </tr>
        @endif
    @endforeach
    </tbody>
</table>