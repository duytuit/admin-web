@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
           Lịch sử import
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Lịch sử import</li>
        </ol>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-body ">
                <div class="row form-group">
                    <div id="search-advance" class="search-advance">
                        <div class="form-group">
                            <div class="col-sm-12" style="padding: 0;">
                                <div class="col-sm-2">
                                    <form role="form"  action='{{ route('admin.log.import.index') }}'  method="get" >
                                        <!-- select -->
                                        <div class="form-group">
                                            <select name="type" id="ip-gender" onchange='this.form.submit()' class="form-control">
                                                <?php $type = !empty(@$data_search['type'])?@$data_search['type']:''; ?>
                                                <option value="0" @if($type == 0)  selected @endif>Import cư dân</option>
                                                <option value="2" @if($type == 2)  selected @endif>Import giao dịch vietQR</option>
                                            </select>
                                        </div>
                                        <noscript><input type="submit" value="Submit"></noscript>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    @if( in_array('admin.v2.customers.action',@$user_access_router))
                        <form action='{{ route('admin.v2.customers.action') }}' method="post" id="form-customer-action">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="method" value="" />
                                    <table class="table table-hover table-striped table-bordered">
                                        <thead class="bg-primary">
                                        <tr>
                                            <th>Stt</th>
                                            <th>Loại import</th>
                                            <th>data</th>
                                            <th>ghi chú</th>
                                            <th>Trang thái</th>
                                            <th>Người tạo</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($logImport as $key => $value)
                                                <tr>
                                                    <td>{{($key + 1)}}</td>
                                                    <td>{!! App\Commons\Helper::type_import[$value->type]!!} </td>
                                                    <td >{{print_r($value->data)}}</td>
                                                    <td>{{@$value->mess}}</td>
                                                    <td>
                                                        {{@$value->status == 1 ? 'Thành công': 'thất bại'}}
                                                    </td>
                                                    <td>
                                                        <small>
                                                            {{ @$value->import_by }}<br />
                                                            {{@$value->created_at_vn_time }}
                                                      </small>
                                                   </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                            
                            @endif

                        </div>
                        <div class="row mbm">
                            <div class="col-sm-3">
                                <span class="record-total">Hiển thị: {{ $logImport->count()}} / {{ $logImport->total() }} kết quả</span>
                            </div>
                            <div class="col-sm-6 text-center">
                                <div class="pagination-panel">
                                    {{ $logImport->appends(Request::all())->links() }}
                                </div>
                            </div>
                            <div class="col-sm-3 text-right">
                                <span class="form-inline">
                                    Hiển thị
                                    <select name="per_page" class="form-control" data-target="#form-customer-action">
                                        @php $list = [10, 20, 50, 100, 200]; @endphp
                                        @foreach ($list as $num)
                                            <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                                        @endforeach
                                    </select>
                                </span>
                            </div>
                        </div>
                </form>
            </div>
        </div>
@endsection
@section('stylesheet')
    <link rel="stylesheet" href="/adminLTE/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
    <style>
        .custom-dialog{
            top: 200px;
        }
    </style>
@endsection
@section('javascript')
    <script src="/adminLTE/plugins/moment/moment.min.js"></script>
    <script src="/adminLTE/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
    <script>
        sidebar('Customers', 'index');
    </script>


@endsection
