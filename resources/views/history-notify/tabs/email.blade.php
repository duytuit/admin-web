<div id="email"
    class="tab-pane">
    <div class="box-header with-border">
        <form action="" method="get" id="form-search">
            <div class="clearfix"></div>
            <div id="search-advance" class="search-advance">
                <div class="row form-group space-5">
                    <div class="col-sm-2">
                        <input type="text" name="email_keyword" class="form-control"
                            placeholder="Nhập nội dung tìm kiếm" value="{{ $email_keyword }}">
                    </div>
                    <div class="col-sm-2" style="padding-left:0">
                                <div class="input-group date">
                                    <div class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </div>
                                    <input type="text" class="form-control date_picker" name="email_date" id="email_date" value="{{ $email_date }}" placeholder="Ngày..." autocomplete="off">
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
    <form action="{{ route('admin.history-notify.action') }}" method="post" id="form-email">
        {{ csrf_field() }}
        @method('post')
        <div class="box-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr class="bg-primary">
                            <th width='20px'>STT</th>
                            <th width='15%'>Email</th>
                            <th width='30%'>Message</th>
                            <th width='40%'>Content</th>
                            <th width='10%'>Thời gian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!$emails->isEmpty())
                        @foreach($emails as $key => $value)
                        <tr>
                            <td>{{ @($key + 1) + ($emails->currentPage() - 1) * $emails->perPage() }}</td>
                            <td>{{ $value->email }}</td>
                            <td>{{ $value->message }}</td>
                            <td>{{ $value->content }}</td>
                            <td>{{ $value->created_at }}</td>
                           
                        </tr>
                        @endforeach
                        @else
                        <tr>
                            <td colspan="7" class="text-center">
                                <p>Chưa có lịch sử gửi email nào</p>
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
                    <span class="record-total">Tổng: {{ $emails->count() }} / {{ $emails->total() }} bản
                        ghi</span>
                </div>
                <div class="col-sm-6 text-center">
                    <div class="pagination-panel">
                        {{ $emails->appends(Request::all())->onEachSide(1)->links() }}
                    </div>
                </div>
                <div class="col-sm-3 text-right">
                    <span class="form-inline">
                        Hiển thị
                        <select name="per_page_email" class="form-control" data-target="#form-email">
                            @php $list = [10, 20, 50, 100, 200]; @endphp
                            @foreach ($list as $num)
                            <option value="{{ $num }}" {{ $num == $per_page_email ? 'selected' : '' }}>{{ $num }}
                            </option>
                            @endforeach
                        </select>
                    </span>
                </div>
            </div>
        </div>
    </form>
</div>