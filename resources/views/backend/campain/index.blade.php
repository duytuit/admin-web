@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Danh sách bài viết
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">{{ $heading }}</li>
        </ol>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-body ">
                <?php $route = 'admin.notification.index'; ?>

                @if (in_array($route, @$user_access_router))
                    <form id="form-search" action="{{ route($route) }}" method="get">

                        <div class="row form-group">
                            <div class="col-sm-8">
                                <div class="col-sm-1">
                                    <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle pull-left"
                                            style="margin-right: 10px;">Tác
                                        vụ&nbsp;<span class="caret"></span></button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a type="button" class="btn-action"
                                               data-target="#form-posts" data-method="recall_email">Gửi lại email</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-sm-4 text-right">
                                <div class="input-group">
                                    <input type="text" name="keyword" value="{{ $keyword }}" placeholder="Nhập từ khóa"
                                        class="form-control" />
                                    <div class="input-group-btn">
                                        <button type="submit" class="btn btn-info"><span
                                                class="fa fa-search"></span></button>
                                        <button type="button" class="btn btn-warning btn-search-advance" data-toggle="show"
                                            data-target=".search-advance"><span class="fa fa-filter"></span></button>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </form><!-- END #form-search -->
                @endif

                @if (in_array($route, @$user_access_router))
                    <form id="form-search-advance" action="{{ route($route) }}" method="get">
                        <div id="search-advance" class="search-advance"
                            style="display: {{ $advance ? 'block' : 'none' }};">
                            <div class="row form-group space-5">
                                <div class="col-sm-2">
                                    <input type="text" name="keyword" value="{{ $keyword }}" placeholder="Tiêu đề"
                                        class="form-control" />
                                </div>
                                <div class="col-sm-2">
                                    <select name="type" class="form-control">
                                        <option value="">Loại thông báo</option>
                                        @foreach (config('typeCampain') as $key => $value)
                                            <option value="{{ $value }}">
                                                {{ $key }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    <div class="input-group date">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                        <input type="text" class="form-control date_picker" name="from_date" id="from_date"
                                               value="{{ @$filter['from_date'] }}" placeholder="Từ..." autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <button class="btn btn-warning btn-block">Tìm kiếm</button>
                                </div>
                            </div>
                        </div>
                    </form><!-- END #form-search-advance -->
                @endif

                <form id="form-posts" action="{{ route('admin.notification.action') }}" method="post">
                    @csrf
                    <input type="hidden" name="method" value="" />
                    <input type="hidden" name="status" value="" />
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-bordered">
                            <thead class="bg-primary">
                                <tr>
                                    <th rowspan="2"><input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /></th>
                                    <th rowspan="2" width="30">ID</th>
                                    <th rowspan="2" class="text-center" >Tiêu đề</th>
                                    <th rowspan="2" class="text-center" width="100">Loai thông báo</th>
                                    <th rowspan="2" class="text-center" width="120">Ngày gửi</th>
                                    <th colspan="6" class="text-center">Thống kê</th>
                                </tr>
                                <tr >
                                    <th class="text-center" width="120">Trạng thái</th>
                                    <th class="text-center" width="120">Phương thức gửi</th>
                                    <th class="text-center" width="50">Tổng</td>
                                    <th class="text-center" width="75">Đã gửi</th>
                                    <th class="text-center" width="75">Thất bại</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($campains as $item)
                                    <tr valign="middle">
                                        <td rowspan="3"><input type="checkbox" name="ids[]" value="{{ $item->id }}"
                                                   class="iCheck checkSingle" /></td>
                                        <td rowspan="3">{{ @$item->id }}</td>
                                        <td rowspan="3"><a href="{{ route('admin.notification.campain-detail', ['id'=>$item->id]) }}" class="w-100 h-100">{{ $item->title }}</a></td>
                                        <td rowspan="3" class="text-center">{{ $item->type }}</td>
                                        <td rowspan="3" class="text-center" >{{ @$item->updated_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                             @if (json_decode($item->total)->app <= $item->sended_app)
                                                  <span class="text-danger">Đã xong</span> 
                                             @else
                                                  <span class="text-primary">Đang gửi</span> 
                                             @endif
                                        </td>
                                        <td>App</td>
                                        <td class="text-right">{{json_decode($item->total)->app}}</td>
                                        <td class="text-right">{{$item->sended_app}}</td>
                                        <td class="text-right"></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            @if (json_decode($item->total)->email <= $item->sended_email)
                                                  <span class="text-danger">Đã xong</span> 
                                            @else
                                                  <span class="text-primary">Đang gửi</span> 
                                            @endif
                                        </td>
                                        <td>Email</td>
                                        <td class="text-right">{{json_decode($item->total)->email}}</td>
                                        <td class="text-right">{{$item->sended_email}}</td>
                                        <td class="text-right"></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            @if (json_decode($item->total)->sms <= $item->sended_sms)
                                                  <span class="text-danger">Đã xong</span> 
                                            @else
                                                  <span class="text-primary">Đang gửi</span> 
                                            @endif
                                        </td>
                                        <td>Sms</td>
                                        <td class="text-right">{{json_decode($item->total)->sms}}</td>
                                        <td class="text-right">{{$item->sended_sms}}</td>
                                        <td class="text-right"></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="row mbm">
                        <div class="col-sm-3">
                            <span class="record-total">Tổng: {{ $campains->total() }} bản ghi</span>
                        </div>
                        <div class="col-sm-6 text-center">
                            <div class="pagination-panel">
                                {{ $campains->appends(Request::all())->onEachSide(1)->links() }}
                            </div>
                        </div>
                        <div class="col-sm-3 text-right">
                            <span class="form-inline">
                                Hiển thị
                                <select name="per_page" class="form-control"  data-target="#form-posts">
                                    @php $list = [10, 20, 50]; @endphp
                                    @foreach ($list as $num)
                                        <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>
                                            {{ $num }}</option>
                                    @endforeach
                                </select>
                            </span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection

@section('javascript')
    <script>
        $('input.date_picker').datepicker({
            autoclose: true,
            dateFormat: "dd-mm-yy"
        }).val();
    </script>
@endsection
