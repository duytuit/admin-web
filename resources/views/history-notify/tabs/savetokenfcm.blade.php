<div id="savetokenfcm"
    class="tab-pane">
    <div class="box-header with-border">
        <form action="" method="get" id="form-search">
            <div class="clearfix"></div>
            <div id="search-advance" class="search-advance">
                <div class="row form-group space-5">
                    <div class="col-sm-2">
                        <input type="text" name="tokenfcm_keyword" class="form-control"
                            placeholder="Nhập nội dung tìm kiếm" value="{{ $tokenfcm_keyword }}">
                    </div>
                    <div class="col-sm-2" style="padding-left:0">
                                <div class="input-group date">
                                    <div class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </div>
                                    <input type="text" class="form-control date_picker" name="tokenfcm_date" id="tokenfcm_date" value="{{ $tokenfcm_date }}" placeholder="Ngày..." autocomplete="off">
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
                            <th width='10%' >User_id</th>
                            <th width='20%'>Token</th>
                            <th width='20%'>User_type</th>
                            <th width='20%'>Device_id</th>
                            <th width='20%'>Update</th>
                            <th >Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!$tokenfcms->isEmpty())
                        @foreach($tokenfcms as $key => $value)
                        <tr>
                            <td>{{ @($key + 1) + ($tokenfcms->currentPage() - 1) * $tokenfcms->perPage() }}</td>
                            <td >{{ $value->user_id }}</td>
                            {{--<td>{{ str_limit($value->token, $limit = 50, $end = '...')}}</td>--}}
                            <td>{{$value->token}}</td>
                            <td>{{ $value->user_type }}</td>
                            <td>{{ $value->device_id }}</td>
                            <td>{{ $value->updated_at }}</td>
                            <td>
                                 <a title="Xóa" href="javascript:;"
                                    data-url="{{ route('admin.history-notify.delete') }}" data-id="{{ $value->id }}"
                                    class="btn btn-sm btn-delete btn-danger"><i class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                        @endforeach
                        @else
                        <tr>
                            <td colspan="7" class="text-center">
                                <p>Chưa có lịch sử gửi tokenfcms nào</p>
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
                    <span class="record-total">Tổng: {{ $tokenfcms->count() }} / {{ $tokenfcms->total() }} bản
                        ghi</span>
                </div>
                <div class="col-sm-6 text-center">
                    <div class="pagination-panel">
                        {{ $tokenfcms->appends(Request::all())->onEachSide(1)->links() }}
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