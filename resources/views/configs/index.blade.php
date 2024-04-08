@extends('backend.layouts.master')

@section('content')
<section class="content-header">
    <h1>
        Quản lý tòa nhà
        <small>Danh sách cấu hình</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
        <li class="active">Danh sách cấu hình</li>
    </ol>
</section>

<section class="content" id="content-partner">
    <div class="box box-primary">
        <div class="box-header with-border">
            <div class="row form-group">
                <div class="col-sm-11">
                    <a href="{{ route('admin.configs.create') }}" type="buttom" class="btn btn-info">
                        <i class="fa fa-plus"></i>&nbsp;&nbsp;Thêm mới</a>
                    <a href="{{ route('admin.configs.billPdf') }}" type="buttom" class="btn btn-info">
                        <i class="fa fa-save"></i>&nbsp;&nbsp;Chọn mẫu bảng kê</a>
                    <a href="{{ route('admin.configs.receipt_style') }}" type="buttom" class="btn btn-info">
                        <i class="fa fa-save"></i>&nbsp;&nbsp;Chọn mẫu phiếu thu</a>
                </div>
            </div>

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
    <form method="post" id="form-work-diary">
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
                            <th width='15%'>Giá trị</th>
                            <th width='5%'>Key</th>
                            <th width='5%'>App_id</th>
                            <th width='8%'>Building_id</th>
                            <th width='14%'>Trạng thái</th>
                            <th width='9%'>Hiện</th>
                            <th width='9%'>Người tạo</th>
                            <th width='9%'>Tạo lúc</th>
                            <th width='15%'>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!$configs->isEmpty())
                        @foreach($configs as $key => $config)
                        <tr>
                            <td><input type="checkbox" class="iCheck checkSingle" value="{{$config->id}}"
                                    name="ids[]" />
                            </td>
                            <td>{{ @($key + 1) + ($configs->currentPage() - 1) * $configs->perPage() }}</td>
                            <td>{{@$config->title}}</td>
                            <td>{{@$config->value}}</td>
                            <td>{{@$config->key}}</td>
                            <td>{{@$config->app_id}}</td>
                            <td>{{@$config->bdc_building_id}}</td>
                            <td>
                                @if($config->status<1) <span class="label label-danger">Đã ẩn</span>
                                    @else
                                    <span class="label label-success">Hoạt động</span>
                                    @endif
                            </td>
                            <td>
                                @if($config->publish<1) <span class="label label-danger">Đã ẩn</span>
                                    @else
                                    <span class="label label-success">Hoạt động</span>
                                    @endif
                            </td>
                            <?php 
                             $pub_fullname = \App\Models\V3\User\UserInfo::where('pub_user_id',@$config->created_by)->first();
                            ?>
                            @if (@$config->created_by != 0)
                            <td>{{$pub_fullname->display_name}}</td>
                            @else
                            <td>System</td>
                            @endif
                            <td>{{date("d/m/Y", strtotime($config->created_at)) }}</td>
                            <td>
                                @if($config->publish > 0) 
                                <a href="{{ route('admin.configs.edit', ['id' => $config->id]) }}" type="button" class="btn btn-sm btn-info" title="Sửa">
                                    <i class="fa fa-edit"></i>
                                </a>
                                @endif
                                <a title="Xóa" href="javascript:void" data-url="{{ route('admin.configs.delete') }}"
                                    data-id="{{ $config->id }}"
                                    class="btn btn-sm btn-delete btn-danger delete-configs"><i
                                        class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                        @endforeach
                        @else
                        <tr>
                            <td colspan="12" class="text-center">
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
                    <span class="record-total">Tổng: {{ $configs->count() }} / {{ $configs->total() }} bản ghi</span>
                </div>
                <div class="col-sm-6 text-center">
                    <div class="pagination-panel">
                        {{ $configs->appends(Request::all())->onEachSide(1)->links() }}
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
<script src="/adminLTE/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<!-- TinyMCE -->
<script src="/adminLTE/plugins/tinymce/tinymce.min.js"></script>
<script src="/adminLTE/plugins/tinymce/config.js"></script>

<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
    $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

    $('input[name="date_filter"]').daterangepicker({
        autoUpdateInput: false,
        locale: {
          cancelLabel: 'Clear'
        }
    });

    $('input[name="date_filter"]').on('apply.daterangepicker', function(ev, picker) {
      $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
    });

    $('input[name="date_filter"]').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });

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

            $('.delete-configs').click(function(){
                var url = $(this).attr('data-url');
                var id = $(this).attr('data-id');
                $.ajax({
                    url: url,
                    method: 'POST',
                    data:{
                        id: id,
                    },
                    success: function() {
                        location.reload();
                    }
                })
            });
            
    });

    $(document).on('change', 'select[name="per_page"]', function () {
            $('#form-work-diary').submit();
        });
</script>
@endsection