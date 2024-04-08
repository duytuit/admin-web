<div id="payment"
    class="tab-pane">
    <div class="box-header with-border">
        <form action="" method="get" id="form-search">
            <div class="clearfix"></div>
            <div id="search-advance" class="search-advance">
                <div class="row form-group space-5">
                    <div class="col-sm-2">
                        <input type="text" name="payment_keyword" class="form-control"
                            placeholder="Nhập nội dung tìm kiếm" value="{{ $payment_keyword }}">
                    </div>
                    <div class="col-sm-2" style="padding-left:0">
                                <div class="input-group date">
                                    <div class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </div>
                                    <input type="text" class="form-control date_picker" name="payment_date" id="payment_date" value="{{ $payment_date }}" placeholder="Ngày..." autocomplete="off">
                                </div>
                    </div>
                    <div class="col-sm-2">
                        <button class="btn btn-info search-asset"><i class="fa fa-search"></i></button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <!-- /.box-header -->
    <form action="{{ route('admin.history-notify.action') }}" method="post" id="form-payment">
        {{ csrf_field() }}
        @method('post')
        <div class="box-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr class="bg-primary">
                            <th width='20px'>STT</th>
                            <th width='15%' >Receipt Code</th>
                            <th width='30%'>Message</th>
                            <th width='40%'>Content</th>
                            <th width='10%'>Thời gian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!$payments->isEmpty())
                        @foreach($payments as $key => $value)
                        <tr>
                            <td>{{ @($key + 1) + ($payments->currentPage() - 1) * $payments->perPage() }}</td>
                            <td style="width:400px;display: flow-root;word-wrap: break-word;" >{{ $value->receipt_code }}</td>
                            <td>{{ $value->messages }}</td>
                            <td>{{ $value->content }}</td>
                            <td>{{ $value->created_at }}</td>
                           
                        </tr>
                        @endforeach
                        @else
                        <tr>
                            <td colspan="7" class="text-center">
                                <p>Chưa có lịch sử gửi payments nào</p>
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
                <input type="submit" class="js-submit-form-index hidden" value="" />
            </div>
        </div>
        <!-- /.box-body -->
        <div class="box-footer clearfix">
            <div class="row">
                <div class="col-sm-3">
                    <span class="record-total">Tổng: {{ $payments->count() }} / {{ $payments->total() }} bản
                        ghi</span>
                </div>
                <div class="col-sm-6 text-center">
                    <div class="pagination-panel">
                        {{ $payments->appends(Request::all())->onEachSide(1)->links() }}
                    </div>
                </div>
                <div class="col-sm-3 text-right">
                    <span class="form-inline">
                        Hiển thị
                        <select name="per_page_payment" class="form-control" data-target="#form-payment">
                            @php $list = [10, 20, 50, 100, 200]; @endphp
                            @foreach ($list as $num)
                            <option value="{{ $num }}" {{ $num == $per_page_payment ? 'selected' : '' }}>{{ $num }}
                            </option>
                            @endforeach
                        </select>
                    </span>
                </div>
            </div>
        </div>
    </form>
</div>