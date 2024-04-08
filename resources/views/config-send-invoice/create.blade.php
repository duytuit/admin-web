@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Thiết lập gửi mail hóa đơn.
            <small>Tạo mới</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
            <li class="active">Tạo mới mẫu</li>
        </ol>
    </section>

    <section class="content">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form
                action="{{ ($id == 0) ? action('System\ConfigSendInvoiceController@store') : action('System\ConfigSendInvoiceController@update', ['id' => $id]) }}"
                method="POST" id="" class="form-validate">
            {{ csrf_field() }}
            @if ($id != 0)
            <input name="_method" type="hidden" value="PUT">
            @endif
            <div class="row">
                <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
                    <div class="box">
                        <div class="box-body no-padding">
                            <div class="nav-tabs-custom no-margin">
                                <div class="tab-content">
                                    <!-- Thông tin cẩm nang -->
                                    <div class="tab-pane active" id="partner">
                                        <div class="row">
                                            <div class="col-sm-12 col-xs-12 form-group div_title">
                                                <label class="control-label">Chọn trạng thái hóa đơn <span
                                                            class="text-danger">*</span></label>
                                                <select class="form-control" name="status">
                                                    <option value="" selected>Chọn</option>
                                                    @foreach($remainStatus as $statusKey => $statusValue)
                                                        <option value="{{ $statusKey }}" @if($setting->status == $statusKey) selected @endif>{{ $statusValue }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-sm-12 col-xs-12 form-group div_title">
                                                <label class="control-label">Chọn mẫu <span
                                                            class="text-danger">*</span></label>
                                                <select class="form-control" name="mail_template_id">
                                                    <option value="" selected>Chọn</option>
                                                    @foreach($templates as $key => $template)
                                                        <option value="{{ $template->id }}" @if($setting->mail_template_id == $template->id) selected @endif>{{ $template->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <input type="hidden" name="bdc_building_id" value="{{ $bdc_building_id }}">
                                        <input type="hidden" name="type" value="1">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer d-flex justify-content-center">
                            <button type="submit" class="btn btn-primary add">{{ $id == 0 ? 'Thêm mới' : 'Cập nhật' }}</button>
                        </div>
                    </div>
                </div>

            </div>
        </form>
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

      //submit form save bai viet
      submitAjaxForm('#save_handbook', '#create_handbook', '.div_', '.message_zone');
    </script>
@endsection
