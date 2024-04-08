<table class="table">
    <thead>
        <tr>
            <th>Mã phiếu</th>
            <th>Căn hộ</th>
            <th>Kỳ</th>
            <th>Bảng kê</th>
            <th>Công nợ dịch vụ</th>
            <th>Số tiền</th>
            <th>Người thu</th>
        </tr>
    </thead>
    <tbody class="list_info">
        @foreach ($detail_receipt as $value)
            <tr >
                <td>{{ @$value->receipt->receipt_code }}</td>
                <td>{{ @$value->apartment->name }}</td>
                <td>{{ @$value->debitdetail->cycle_name }}</td>
                <td>{{ @$value->debitdetail->bill->bill_code }}</td>
                <td>{{ @$value->debitdetail->title }}</td>
                <td>{{ number_format(@$value->cost_paid) }}</td>
                <td>
                    <small>
                        {{ @$value->user->email }}<br />
                        {{ $value->created_at->format('d-m-Y H:i') }}
                   </small>
               </td>
            </tr>
        @endforeach
        <div class="col-md-12">
            <div class="col-md-3 ">
                <h4><strong>Số tiền nộp:</strong></h4>
            </div>
            <div class="col-md-3">
                <h4><strong>{{number_format($detail_receipt[0]->receipt->cost)}}</strong></h4>
            </div>
            <div class="col-md-3">
                <h4><strong>Tiền thừa:</strong></h4>
            </div>
            <div class="col-md-3">
                <h4><strong>{{number_format($detail_receipt[0]->receipt->account_balance)}}</strong></h4>
            </div>
        </div>
    </tbody>
</table>
