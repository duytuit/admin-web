<div class="form-group">
    <div class="col-md-12">
        <input type="hidden" id="receiptIdXoa" value="{{@$receiptId}}" />
        <p><strong>Căn hộ: {{@$apartment->name}}</strong></p>
        <br/>

        <p><strong>Mã phiếu: {{@$data->receipt_code}}     &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;         Tổng tiền: {{number_format(@$data->cost)}} VND</strong></p>

        <br/>
        <table class="table table-striped">
            <thead>
            <tr>
                <th>STT</th>
                <th>Dịch vụ</th>
                <th>Sản phẩm</th>
                <th>Phát sinh</th>
                <th>Thời gian</th>
                <th>Thanh toán</th>
            </tr>
            </thead>
            <tbody>
            <?php
                $index = 1;
                ?>
            @foreach($dataDetail as $item)
                <tr>
                    <td>{{$index}}</td>
                    <td>{{$item['dichvu']}}</td>
                    <td>{{$item['phuongtien']}}</td>
                    <td>{{$item['phatsinh'] ? number_format($item['phatsinh']) : ""}}</td>
                    <td>{{$item['thoigian']}}</td>
                    <td>{{$item['thanhtoan'] ? number_format($item['thanhtoan']) : ""}}</td>
                </tr>
                <?php
                $index++;
                ?>
            @endforeach
            </tbody>
        </table>
        <textarea class="form-control mceEditor" id="note" placeholder="Lý do hủy phiếu thu"></textarea>
        <br/>
        <br/>
    </div>
</div>
<div class="form-group">
    <div class="card-body phan_bo_list">
    </div>
</div>
<script type="text/javascript" src="{{ url('adminLTE/js/main.js') }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/js/custom.js') }}"></script>