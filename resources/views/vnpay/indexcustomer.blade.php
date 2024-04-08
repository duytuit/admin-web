<div class="box box-primary" style="font-size: 30px;">
    <div class="box-body ">
        <div class="header clearfix">
            <h3 class="text-muted" style="background-color: blue;color: white;text-align: center;padding: 5px;">VNPAY RESPONSE</h3>
        </div>
        <div class="table-responsive">
            <div class="form-group">
                <label >Mã đơn hàng:</label>

                <label>{{$info['vnp_TxnRef']}}</label>
            </div>
            <div class="form-group">

                <label >Số tiền:</label>
                <label>{{$info['vnp_Amount']}}</label>
            </div>
            <div class="form-group">
                <label >Nội dung thanh toán:</label>
                <label>{{$info['vnp_OrderInfo']}}</label>
            </div>
            {{--<div class="form-group">
                <label >Mã phản hồi (vnp_ResponseCode):</label>
                <label>{{$info['vnp_ResponseCode']}}</label>
            </div>--}}
            <div class="form-group">
                <label >Mã GD Tại VNPAY:</label>
                <label>{{$info['vnp_TransactionNo']}}</label>
            </div>
            <div class="form-group">
                <label >Mã Ngân hàng:</label>
                <label>{{$info['vnp_BankCode']}}</label>
            </div>
            {{--<div class="form-group">
                <label >Thời gian thanh toán:</label>
                <label>{{$info['vnp_PayDate']}}</label>
            </div>--}}
            <div class="form-group">
                <label >Kết quả:</label>
                <label>
                    <?php
                        if ($info['vnp_ResponseCode'] == '00') {
                            echo "GD Thanh cong";
                        } else {
                            echo "GD Khong thanh cong";
                        }
                    ?>

                </label>
            </div>
        </div>
    </div>
</div>