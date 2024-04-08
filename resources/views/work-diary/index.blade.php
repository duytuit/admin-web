@extends('backend.layouts.master')

@section('content')
<section class="content-header">
    <h1>
        Quản lý tòa nhà
        <small>Danh sách công việc</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
        <li class="active">Danh sách công việc</li>
    </ol>
</section>

<section class="content" id="content-partner">
    <div class="box box-primary">
        <div class="box-header with-border">
            <div class="row">
                <div class="col-lg-3 col-xs-6">
                    <!-- small box -->
                    <div class="small-box bg-aqua">
                        <div class="inner">
                            <h5>Hôm nay: {{$all_tasks_day}}</h5>
                            <h5>Tuần này: {{$all_tasks_week}}</h5>
                            <h5>Tháng này: {{$all_tasks_month}}</h5>
                        </div>
                        <div class="icon">
                            <i class="fa fa-tasks"></i>
                        </div>
                        <a href="#" class="small-box-footer">Toàn bộ</a>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-xs-6">
                    <!-- small box -->
                    <div class="small-box bg-green">
                        <div class="inner">
                            <h5>Hôm nay: {{$done_tasks_day}}</h5>
                            <h5>Tuần này: {{$done_tasks_week}}</h5>
                            <h5>Tháng này: {{$done_tasks_month}}</h5>
                        </div>
                        <div class="icon">
                            <i class="fa fa-tasks"></i>
                        </div>
                        <a href="#" class="small-box-footer">Đã duyệt</a>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-xs-6">
                    <!-- small box -->
                    <div class="small-box bg-yellow">
                        <div class="inner">
                            <h5>Hôm nay: {{$checked_tasks_day}}</h5>
                            <h5>Tuần này: {{$checked_tasks_week}}</h5>
                            <h5>Tháng này: {{$checked_tasks_month}}</h5>
                        </div>
                        <div class="icon">
                            <i class="fa fa-tasks"></i>
                        </div>
                        <div href="#" class="small-box-footer">Đã kiểm tra</div>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-xs-6">
                    <!-- small box -->
                    <div class="small-box bg-red">
                        <div class="inner">
                            <h5>Hôm nay: {{$unchecked_tasks_day}}</h5>
                            <h5>Tuần này: {{$unchecked_tasks_week}}</h5>
                            <h5>Tháng này: {{$unchecked_tasks_month}}</h5>
                        </div>
                        <div class="icon">
                            <i class="fa fa-tasks"></i>
                        </div>
                        <a href="#" class="small-box-footer">Chưa kiểm tra</a>
                    </div>
                </div>
                <!-- ./col -->
            </div>
            <div class="row form-group">
                <div class="col-sm-1">
                    <span class="btn-group">
                        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">Tác
                            vụ <span class="caret"></span></button>
                        <ul class="dropdown-menu">
                            <li><a class="btn-action" data-target="#form-feedback" id="delete-multi-work-diary"
                                    data-action="{{ route('admin.work-diary.del_multi_work_diary') }}"><i
                                        class="fa fa-trash-o"></i> Xóa các mục đã chọn</a></li>
                        </ul>
                    </span>
                </div>
                <div class="col-sm-11">
                    <a href="{{ route('admin.work-diary.create') }}" type="buttom" class="btn btn-info"><i
                            class="fa fa-plus"></i>&nbsp;&nbsp;Thêm mới</a>
                </div>
            </div>
            <form action="{{ route('admin.work-diary.index') }}" method="post" id="form-search">
                @csrf
                <div class="clearfix"></div>
                <div id="search-advance" class="search-advance">
                    <div class="row form-group space-5">
                        <div class="col-sm-3">
                            <input type="text" name="keyword" class="form-control" placeholder="Nhập nội dung tìm kiếm"
                                value="{{@$filter['keyword']}}">
                        </div>
                        <div class="col-sm-2">
                            <select name="bdc_department_id" class="form-control">
                                <option value="" selected>Bộ phận</option>
                                @foreach($departments as $department)
                                <option value="{{ $department->id }}" @if(@$filter['bdc_department_id']==$department->
                                    id) selected @endif>
                                    {{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-3">
                            {{-- <select name="end_at" class="form-control">
                                <option value="" selected>Thời gian</option>
                                @foreach($deadlines as $deadline)
                                <option value="{{ $deadline }}" @if(@$filter['end_at']==$deadline) selected @endif>
                            {{ $deadline }}</option>
                            @endforeach
                            </select> --}}
                            {{--<div class="input-group date">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" class="form-control pull-right date_picker" name="date_filter"
                                    value="{{@$filter['date_filter']}}" autocomplete="off">
                            </div>--}}
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="input-group datetimepicker" data-format="Y-MM-DD">
                                        <input type="text" name="from_date" id="ip-from_date" class="form-control" placeholder="Từ ngày" value="{{@$filter['from_date']}}"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="input-group datetimepicker" data-format="Y-MM-DD">
                                        <input type="text" name="to_date" id="ip-to_date" class="form-control" placeholder="đến ngày" value="{{@$filter['to_date']}}"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <select name="status" class="form-control">
                                <option value="" selected>Tình trạng</option>
                                <option value="0" @if( @$filter['status']=='0' ) selected @endif> Chưa thực hiện
                                </option>
                                <option value="1" @if( @$filter['status']==1 ) selected @endif> Đang thực hiện</option>
                                <option value="2" @if( @$filter['status']==2 ) selected @endif> Đã thực hiện</option>
                                <option value="3" @if( @$filter['status']==3 ) selected @endif> Cần làm lại</option>
                                <option value="4" @if( @$filter['status']==4 ) selected @endif> Đã kiểm tra</option>
                                <option value="5" @if( @$filter['status']==5 ) selected @endif> Đã duyệt</option>
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <button type="submit" class="btn btn-info search-asset"><i
                                    class="fa fa-search"></i></button>
                        </div>
                    </div>
                </div>
            </form>
            {{-- <div class="row form-group">
                <div class="col-sm-8">
                    <span class="btn-group">
                        <a data-action="{{ route('admin.work-diary.del_multi_work_diary') }}" class="btn btn-danger"
            id="delete-multi-work-diary"><i class="fa fa-trash-o"></i> Xóa mục đã chọn</a>
            </span>
        </div>
    </div> --}}
    </div>
    <!-- /.box-header -->
    <form action="{{ route('admin.work-diary.action') }}" method="post" id="form-work-diary">
        {{ csrf_field() }}
        @method('post')
        <div class="box-body">
            <div class="table-responsive">
                <input type="hidden" name="method" value="" />
                <input type="hidden" name="status" value="" />

                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr class="bg-primary">
                            <th width='20px'>
                                <input class="iCheck checkAll" type="checkbox" data-target=".checkSingle" />
                            </th>
                            <th width='20px'>STT</th>
                            <th width='15%'>Tiêu đề</th>
                            <th width='15%'>Người tạo</th>
                            <th width='10%'>Bộ phận xử lý</th>
                            <th width='10%'>Người xử lý</th>
                            <th width='8%'>Deadline</th>
                            <th width='14%'>Tình trạng thực hiện</th>
                            <th width='9%'>Giám sát</th>
                            <th width='9%'>Duyệt</th>
                            <th width='10%'>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!$tasks->isEmpty())
                        @foreach($tasks as $key => $task)
                        <tr>
                            <td><input type="checkbox" class="iCheck checkSingle" value="{{$task->id}}" name="ids[]" />
                            </td>
                            <td>{{ @($key + 1) + ($tasks->currentPage() - 1) * $tasks->perPage() }}</td>
                            <td>
                                <a href="{{ route('admin.work-diary.report-work', ['id' => $task->id]) }}">
                                    {{ @$task->title }}
                                </a>
                            </td>
                            <td>
                                {{ @$task->pub_profile->display_name }}
                            </td>
                            <td>
                                @if( @$task->department->id == 0 )
                                <p>Chưa có</p>
                                @else
                                {{ @$task->department->name }}
                                @endif
                            </td>
                            <td>
                                @if( @$task->assign_to == 0 )
                                <p>Chưa có</p>
                                @else
                                {{ @$task->people_hand->display_name }}
                                @endif
                            </td>
                            <td>
                                {{ date("d/m/Y", strtotime($task->end_at)) }}
                            </td>
                            <td>
                                @if( $task->status == 0 )
                                <p style="color:{{$colors[0]}}">Chưa thực hiện</p>
                                @elseif( $task->status == 1 )
                                <p style="color:{{$colors[1]}}">Đang thực hiện</p>
                                @else
                                <p style="color:{{$colors[2]}}">Đã thực hiện</p>
                                @endif
                            </td>
                            <td>
                                @if( $task->status < 3 ) @elseif( $task->status == 3 )
                                    <p style="color:{{$colors[3]}}">Cần làm lại</p>
                                    @else
                                    <p style="color:{{$colors[4]}}">Đã kiểm tra</p>
                                    @endif
                            </td>
                            <td>
                                @if( $task->status < 5 ) @elseif( $task->status == 5 )
                                    <p style="color:{{$colors[5]}}">Đã duyệt</p>
                                    @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.work-diary.edit', ['id' => $task->id]) }}" type="button"
                                    class="btn btn-sm btn-info" title="Sửa"><i class="fa fa-edit"></i></a>

                                <a title="Xóa" href="javascript:;" data-url="{{ route('admin.work-diary.delete') }}"
                                    data-id="{{ $task->id }}" class="btn btn-sm btn-delete btn-danger"><i
                                        class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                        @endforeach
                        @else
                        <tr>
                            <td colspan="11" class="text-center">
                                <p>Chưa có danh sách công việc nào</p>
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
                    <span class="record-total">Tổng: {{ $tasks->count() }} / {{ $tasks->total() }} bản ghi</span>
                </div>
                <div class="col-sm-6 text-center">
                    <div class="pagination-panel">
                        {{ $tasks->appends(Request::all())->onEachSide(1)->links() }}
                    </div>
                </div>
                <div class="col-sm-3 text-right">
                    <span class="form-inline">
                        Hiển thị
                        <select name="per_page" class="form-control" data-target="#form-permission">
                            @php $list = [10, 20, 50, 100, 200]; @endphp
                            @foreach ($list as $num)
                            <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}
                            </option>
                            @endforeach
                        </select>
                    </span>
                </div>
            </div>
        </div>
    </form>
    </div>
</section>
@endsection

@section('stylesheet')

<link rel="stylesheet" href="/adminLTE/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />

<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

@endsection

@section('javascript')

<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
 <script src="/adminLTE/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
{{--<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>--}}
<!-- TinyMCE -->
<!-- <script src="/adminLTE/plugins/tinymce/tinymce.min.js"></script>
<script src="/adminLTE/plugins/tinymce/config.js"></script> -->




<script>
    /*$('input[name="date_filter"]').daterangepicker({
        autoUpdateInput: false,
        locale: {
          cancelLabel: 'Clear',
          format: 'DD/MM/YYYY'
        }
    });

    $('input[name="date_filter"]').on('apply.daterangepicker', function(ev, picker) {
      $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
    });

    $('input[name="date_filter"]').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });*/

    $(document).ready(function() {
        $('#delete-multi-work-diary').click(function(e) {
                var ids = [];
                var div = $('div.icheckbox_square-green[aria-checked="true"]');
                div.each(function(index, value) {
                    var id = $(value).find('input.checkSingle').val();
                    if (id) {
                        ids.push(id);
                    }
                });
                if( ids.length == 0 ) {
                    toastr.error('Vui lòng chọn công việc để thực hiện tác vụ này');
                } else {
                    if (!confirm('Bạn có chắc chắn muốn xóa những công việc này?')) {
                    e.preventDefault();
                    } else {
                        $.ajax({
                            url: $(this).attr('data-action'),
                            type: 'POST',
                            data: {
                                ids: ids
                            },
                            success: function(response) {
                                if (response.success == true) {
                                    toastr.success(response.message);

                                    setTimeout(() => {
                                        location.reload()
                                    }, 1000)
                                } else {
                                    toastr.error('Không thể xóa những công việc này');
                                }
                            }
                        })
                    }
                }
            });
            
    });

    $(document).on('change', 'select[name="per_page"]', function () {
            $('#form-work-diary').submit();
        });
</script>
@endsection