<table>
    <thead>
    <tr style="background-color: #00c0ef;">
        <th><b>Loại NK</b></th>
        <th><b>Tên căn hộ</b></th>
        <th><b>Chủ hộ</b></th>
        <th><b>Số chứng từ</b></th>
        <th><b>Kỳ kế toán</b></th>
        <th><b>Ngày lập phiếu</b></th>
        <th><b>Ngày hạch toán</b></th>
        <th><b>Diễn giải</b></th>
        <th><b>Nội dung</b></th>
        <th><b>Mã khách hàng-NCC</b></th>
        <th><b>Mã ngân hàng</b></th>
        <th><b>Cty Con - NH cho DXMB vay</b></th>
        <th><b>Mã phòng ban</b></th>
        <th><b>Mã nhân viên</b></th>
        <th><b>Mã phí</b></th>
        <th><b>Hợp đồng</b></th>
        <th><b>Sản phẩm</b></th>
        <th><b>Block</b></th>
        <th><b>Dự án</b></th>
        <th><b>Mã thu</b></th>
        <th><b>Khế ước</b></th>
        <th><b>CP không hợp lệ</b></th>
        <th><b>Mã tài khoản</b></th>
        <th><b>Số tiền</b></th>
        <th><b>Nợ có</b></th>
        <th><b>Ký hiệu hóa đơn</b></th>
        <th><b>Ngày hóa đơn</b></th>
        <th><b>Loại thuế</b></th>
        <th><b>Thuế suất</b></th>
        <th><b>Tiền trước thuế</b></th>
        <th><b>Mã số hóa đơn</b></th>
        <th><b>Người nộp/Nhận tiền</b></th>
        <th><b>Người bán hàng</b></th>
        <th><b>Phiếu cấn trừ</b></th>
        <th><b>Mã chứng từ eApprove</b></th>
        <th><b>Ghi chú</b></th>
        <th><b>Nhóm dịch vụ</b></th>
        <th><b>Mô tả</b></th>
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
                <td>{{ $_content["Kỳ kế toán"] }}</td>
                <td>{{ $_content["Ngày lập phiếu"] }}</td>
                <td>{{ $_content["Ngày hạch toán"] }}</td>
                <td>{{ $_content["Diễn giải"] }}</td>
                <td>{{ $_content["Nội dung"] }}</td>
                <td>{{ $_content["Mã khách hàng-NCC"] }}</td>
                <td>{{ $_content["Mã ngân hàng"] }}</td>
                <td>{{ $_content["Cty Con - NH cho DXMB vay"] }}</td>
                <td>{{ $_content["Mã phòng ban"] }}</td>
                <td>{{ $_content["Mã nhân viên"] }}</td>
                <td>{{ $_content["Mã phí"] }}</td>
                <td>{{ $_content["Hợp đồng"] }}</td>
                <td>{{ $_content["Sản phẩm"] }}</td>
                <td>{{ $_content["Block"] }}</td>
                <td>{{ $_content["Dự án"] }}</td>
                <td>{{ $_content["Mã thu"] }}</td>
                <td>{{ $_content["Khế ước"] }}</td>
                <td>{{ $_content["CP không hợp lệ"] }}</td>
                <td>{{ $_content["Mã tài khoản"] }}</td>
                <td>{{ $_content["Số tiền"] }}</td>
                <td>{{ $_content["Nợ có"] }}</td>
                <td>{{ $_content["Ký hiệu hóa đơn"] }}</td>
                <td>{{ $_content["Ngày hóa đơn"] }}</td>
                <td>{{ $_content["Loại thuế"] }}</td>
                <td>{{ $_content["Thuế suất"] }}</td>
                <td>{{ $_content["Tiền trước thuế"] }}</td>
                <td>{{ $_content["Mã số hóa đơn"] }}</td>
                <td>{{ $_content["Người nộp/Nhận tiền"] }}</td>
                <td>{{ $_content["Người bán hàng"] }}</td>
                <td>{{ $_content["Phiếu cấn trừ"] }}</td>
                <td>{{ $_content["Mã chứng từ eApprove"] }}</td>
                <td>{{ $_content["Ghi chú"] }}</td>
                <td>{{ $_content["Nhóm dịch vụ"] }}</td>
                <td>{{ $_content["Mô tả"] }}</td>
            </tr>
        @else
            <tr>
                <td>{{ $_content["Loại NK"] }}</td>
                <td>{{ $_content["Tên căn hộ"] }}</td>
                <td>{{ $_content["Chủ hộ"] }}</td>
                <td>{{ $_content["Số chứng từ"] }}</td>
                <td>{{ $_content["Kỳ kế toán"] }}</td>
                <td>{{ $_content["Ngày lập phiếu"] }}</td>
                <td>{{ $_content["Ngày hạch toán"] }}</td>
                <td>{{ $_content["Diễn giải"] }}</td>
                <td>{{ $_content["Nội dung"] }}</td>
                <td>{{ $_content["Mã khách hàng-NCC"] }}</td>
                <td>{{ $_content["Mã ngân hàng"] }}</td>
                <td>{{ $_content["Cty Con - NH cho DXMB vay"] }}</td>
                <td>{{ $_content["Mã phòng ban"] }}</td>
                <td>{{ $_content["Mã nhân viên"] }}</td>
                <td>{{ $_content["Mã phí"] }}</td>
                <td>{{ $_content["Hợp đồng"] }}</td>
                <td>{{ $_content["Sản phẩm"] }}</td>
                <td>{{ $_content["Block"] }}</td>
                <td>{{ $_content["Dự án"] }}</td>
                <td>{{ $_content["Mã thu"] }}</td>
                <td>{{ $_content["Khế ước"] }}</td>
                <td>{{ $_content["CP không hợp lệ"] }}</td>
                <td>{{ $_content["Mã tài khoản"] }}</td>
                <td>{{ $_content["Số tiền"] }}</td>
                <td>{{ $_content["Nợ có"] }}</td>
                <td>{{ $_content["Ký hiệu hóa đơn"] }}</td>
                <td>{{ $_content["Ngày hóa đơn"] }}</td>
                <td>{{ $_content["Loại thuế"] }}</td>
                <td>{{ $_content["Thuế suất"] }}</td>
                <td>{{ $_content["Tiền trước thuế"] }}</td>
                <td>{{ $_content["Mã số hóa đơn"] }}</td>
                <td>{{ $_content["Người nộp/Nhận tiền"] }}</td>
                <td>{{ $_content["Người bán hàng"] }}</td>
                <td>{{ $_content["Phiếu cấn trừ"] }}</td>
                <td>{{ $_content["Mã chứng từ eApprove"] }}</td>
                <td>{{ $_content["Ghi chú"] }}</td>
                <td>{{ $_content["Nhóm dịch vụ"] }}</td>
                <td>{{ $_content["Mô tả"] }}</td>
            </tr>
        @endif
    @endforeach
    </tbody>
</table>