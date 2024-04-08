@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý tài sản
            <small>Chi tiết tài sản</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Quản lý tài sản</li>
        </ol>
    </section>

    <section class="content">
        <div class="box-body">
            <div class="row box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title bold">Thông tin tài sản-CCDC</h3>
                </div>
                <!-- left column -->
                <div class="col-md-6">
                    <!-- form start -->
                    <div class="box-body">
                        <ul class="list-group list-group-unbordered">
                            <li class="list-group-item">
                                <b>Tên</b> <span class="pull-right">{{ @$asset->name }}</span>
                            </li>
                            <li class="list-group-item">
                                <b>Loại</b> <span class="pull-right">{{ @$asset->type->name }}</span>
                            </li>
                            <li class="list-group-item">
                                <b>Số lượng</b> <span class="pull-right">{{ @$asset->quantity }}</span>
                            </li>
                            <li class="list-group-item">
                                <b>Thời hạn sử dụng</b> <span
                                        class="pull-right">{{ @$asset->using_peroid }} tháng</span>
                            </li>
                            <li class="list-group-item">
                                <b>Hạn bảo hành</b> <span
                                        class="pull-right">{{ date("d/m/Y", strtotime(@$asset->warranty_period)) }}</span>
                            </li>
                        </ul>
                    </div>
                    <!-- /.box -->
                </div>
                <!--/.col (left) -->
                <!-- right column -->
                <div class="col-md-6">
                    <!-- form start -->
                    <div class="box-body">
                        <ul class="list-group list-group-unbordered">
                            <li class="list-group-item">
                                <b>Giá mua</b> <span class="pull-right">{{ @$asset->price }} VND</span>
                            </li>
                            <li class="list-group-item">
                                <b>Ngày mua</b> <span
                                        class="pull-right">{{ date("d/m/Y", strtotime(@$asset->buying_date)) }}</span>
                            </li>
                            <li class="list-group-item">
                                <b>Người mua</b> <span class="pull-right">{{ @$asset->buyer }}</span>
                            </li>
                            <li class="list-group-item">
                                <b>Nơi đặt</b> <span class="pull-right">{{ @$asset->place }} tháng</span>
                            </li>
                        </ul>
                    </div>
                    <!-- /.box-body -->
                    <!-- /.box -->
                </div>
                <!--/.col (right) -->
            </div>
            <div class="row box box-default">
                <div class="form-group">
                    <label>Ghi chú</label>
                    <div class="box-body">
                        {!! @$asset->asset_note !!}
                    </div>
                </div>
            </div>
            <div class="row box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title bold">Thông tin bảo trì</h3>
                </div>
                <div class="col-md-1"></div>
                <div class="col-md-5">
                    <!-- form start -->
                    <div class="box-body">
                        <ul class="list-group list-group-unbordered">
                            <li class="list-group-item">
                                <b>Kì bảo hành</b> <span class="pull-right">{{ @$asset->period->name }}</span>
                            </li>
                        </ul>
                    </div>
                    <!-- /.box-body -->
                    <!-- /.box -->
                </div>
                <div class="col-md-5">
                    <!-- form start -->
                    <div class="box-body">
                        <ul class="list-group list-group-unbordered">
                            <li class="list-group-item">
                                <b>Ngày bắt đầu BH</b> <span
                                        class="pull-right">{{ date("d/m/Y", strtotime(@$asset->maintainance_date)) }}</span>
                            </li>
                        </ul>
                    </div>
                    <!-- /.box-body -->
                    <!-- /.box -->
                </div>
                <div class="col-md-1"></div>
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-bordered">
                            <thead class="bg-primary">
                            <tr>
                                <th>STT</th>
                                <th>Ngày bảo trì</th>
                                <th>Tiêu đề</th>
                                <th>Người đánh dấu bảo trì</th>
                                <th>Trạng thái</th>
                                <th>Nhật kí công việc</th>
                                <th width="10%">Hành động</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if($asset->maintenances()->count() > 0)
                                @foreach($asset->maintenances as $key => $maintenance)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ date("d/m/Y", strtotime($maintenance->maintenance_time)) }}</td>
                                    <td>{{ $maintenance->title }}</td>
                                    <td>{{ @$maintenance->user->BDCprofile->display_name }}</td>
                                    <td>
                                        @if($maintenance->status == 2)
                                            Đã hủy
                                        @else
                                            @if(strtotime(date('d-m-Y')) < strtotime($maintenance->maintenance_time))
                                                Chưa đến ngày bảo trì
                                            @elseif(strtotime(date('d-m-Y')) == strtotime($maintenance->maintenance_time))
                                                {{ $maintenance->status == 0 ? 'Chưa hoàn thành' : 'Đã hoàn thành'  }}
                                            @else
                                                {{ $maintenance->status == 0 ? 'Đã quá hạn '.((strtotime(date('d-m-Y')) - strtotime($maintenance->maintenance_time))/86400) . ' ngày' : 'Đã hoàn thành'  }}
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        @if($maintenance->workdiary->count())
                                            @foreach($maintenance->workdiary as $workd)
                                                <a href="{{route('admin.work-diary.report-work',$workd->id)}}">{{$workd->title}}</a></br>
                                            @endforeach
                                        @else
                                        Chưa có công việc liên quan
                                        @endif
                                    </td>
                                    <td>
                                        @if($maintenance->status != 2)
                                            <a href=""
                                               class="btn btn-xs btn-primary" title="Tạo công việc"><i
                                                        class="fa fa-pencil"></i></a>
                                        @endif
                                        @if(strtotime(date('d-m-Y')) < strtotime($maintenance->maintenance_time) && $maintenance->status == 0)
                                            <a href="{{ route('admin.assets.cancel_check', $maintenance->id) }}"
                                               class="btn btn-xs btn-danger check_done" title="Hủy bảo tri"><i
                                                        class="fa fa-trash"></i></a>
                                        @endif
                                        @if(strtotime(date('d-m-Y')) >= strtotime($maintenance->maintenance_time) && $maintenance->status == 0)
                                            <a href="{{ route('admin.assets.maintain_check', $maintenance->id) }}"
                                               class="btn btn-xs btn-warning check_done" title="Đánh dấu hoàn thành"><i
                                                        class="fa fa-check"></i></a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                                @else
                                <tr><td colspan="6" class="text-center">Chưa có lịch bảo trì</td></tr>
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="box-footer">
                    <a href="{{ route('admin.assets.index') }}" type="button" class="btn btn-default pull-left">Quay
                        lại</a>
                </div>
            </div>
        </div>
    </section>
    @include('assets.modal.maintainence')
@endsection

@section('javascript')
    <script>
        //Date picker
        $('#datepicker').datepicker({
            autoclose: true,
            dateFormat: "dd-mm-yy"
        }).val();

        $('#datepicker2').datepicker({
            autoclose: true,
            dateFormat: "dd-mm-yy"
        }).val();
    </script>
@endsection