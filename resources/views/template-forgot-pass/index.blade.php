@inject('request', 'Illuminate\Http\Request')
@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Mẫu gửi mail quên mật khẩu
            <small>Danh sách</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
            <li class="active">Danh sách</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="box-body">
                <div class="col-md-12">
                    <!-- Custom Tabs -->
                    <div class="nav-tabs-custom">

                        <div class="tab-content">
                            <div id="bai_viet"
                                 class="tab-pane @if(request()->exists('handbook_keyword') || (!request()->exists('handbook_keyword') && !request()->exists('handbook_categories_keyword'))) active @else fade @endif ">
                                <div class="box-header with-border">
                                    <div class="row form-group">
                                        <div class="col-sm-8 col-xs-12 ">
                                            <a href="{{ action('System\TemplateSendFotgotPassController@create') }}" type="buttom" class="btn btn-info"><i
                                                        class="fa fa-plus"></i>&nbsp;&nbsp;Thêm mới </a>
                                        </div>
                                    </div>
                                    <form action="" method="get" id="form-search">
                                        <div class="clearfix"></div>
                                        <div id="search-advance" class="search-advance">
                                            <div class="row form-group space-5">
                                                <div class="col-sm-2">
                                                    <input type="text" name="keyword" class="form-control"
                                                           placeholder="Nhập nội dung tìm kiếm" value="{{ $keyword }}">
                                                </div>
                                                <div class="col-sm-2">
                                                    <button class="btn btn-info search-asset"><i class="fa fa-search"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    <div class="row form-group">
                                        <div class="col-sm-8">
                <span class="btn-group">
                    <a data-action="{{ action('System\TemplateSendFotgotPassController@ajaxDeleteMulti') }}" class="btn btn-danger"
                       id="delete-multi"><i class="fa fa-trash-o"></i> Xóa mục đã chọn</a>
                </span>
                                        </div>
                                    </div>
                                </div>
                                <!-- /.box-header -->
                                <form action="{{ route('admin.building-handbook.action') }}" method="post" id="form-handbook">
                                    {{ csrf_field() }}
                                    @method('post')
                                    <div class="box-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered table-hover">
                                                <thead>
                                                <tr class="bg-primary">
                                                    <th width='20px'>
                                                        <input class="iCheck checkAll" type="checkbox" data-target=".checkSingle" />
                                                    </th>
                                                    <th width='20px'>STT</th>
                                                    <th width='40%'>Tên</th>
                                                    <th width='40%'>Thao tác</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @if($templates->count())
                                                    @foreach($templates as $key => $template)
                                                        <tr>
                                                            <td><input type="checkbox" class="iCheck checkSingle" value="{{$template->id}}" name="ids[]" />
                                                            </td>
                                                            <td>{{ $template->id }}</td>
                                                            <td>{{ $template->name }}</td>
                                                            <td>
                                                                <a href="{{ action('System\TemplateSendFotgotPassController@edit', ['id' => $template->id]) }}" type="button"
                                                                   class="btn btn-sm btn-info" title="Sửa"><i class="fa fa-edit"></i></a>

                                                                <a title="Xóa" href="javascript:;"
                                                                   data-url="{{ action('System\TemplateSendFotgotPassController@destroy', ['id' => $template->id] ) }}" data-id="{{ $template->id }}"
                                                                   class="btn btn-sm btn-delete btn-danger"><i class="fa fa-trash"></i></a>

                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="6" class="text-center">
                                                            <p>Chưa có mẫu mail nào</p>
                                                        </td>
                                                    </tr>
                                                @endif
                                                </tbody>
                                            </table>
                                            <input type="submit" class="js-submit-form-index hidden" value="" />
                                        </div>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@section('javascript')
    <!-- TinyMCE -->
    <script src="/adminLTE/plugins/tinymce/tinymce.min.js"></script>
    <script src="/adminLTE/plugins/tinymce/config.js"></script>
    <script>
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      $(document).ready(function () {
        $('#delete-multi').click(function() {
          var ids = [];
          var div = $('div.icheckbox_square-green[aria-checked="true"]');
          div.each(function (index, value) {
            var id = $(value).find('input.checkSingle').val();
            if (id) {
              ids.push(id);
            }
          });
          console.log(ids);
          if (ids.length == 0) {
            toastr.error('Vui lòng chọn mẫu để thực hiện tác vụ này');
          } else {
            if (!confirm('Bạn có chắc chắn muốn xóa những mẫu này?')) {
              e.preventDefault();
            } else {
              $.ajax({
                url: $(this).attr('data-action'),
                type: 'POST',
                data: {
                  ids: ids
                },
                success: function (response) {
                  if (response.success == true) {
                    toastr.success(response.message);

                    setTimeout(() => {
                      location.reload()
                    }, 1000)
                  } else {
                    toastr.error('Không thể xóa những mẫu này!');
                  }
                }
              })
            }
          }
        });
      });
    </script>
@endsection
