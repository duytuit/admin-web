@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            Cập nhật bài viết
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">{{ $heading }}</li>
        </ol>
    </section>

    <section class="content">
        @if( in_array('admin.posts.save',@$user_access_router))
            <form id="form-posts" action="{{ route('admin.posts.save', ['id' => $id, 'type' => $type]) }}" method="post" autocomplete="off">
                @csrf
                @method('POST')

                @php
                    $old = old();
                @endphp

                <div class="row">
                    <div class="col-sm-8">
                        <div class="box no-border-top">
                            <div class="box-body no-padding">
                                <div class="nav-tabs-custom no-margin">
                                    <!-- Nav tabs -->
                                    <ul class="nav nav-tabs">
                                        <li class="active"><a href="#general" data-toggle="tab">Tổng quan</a></li>
                                        @if ($type == 'event')
                                            <li class=""><a href="#event" data-toggle="tab">Sự kiện</a></li>
                                        @endif
                                        @if ($type == 'voucher')
                                            <li class=""><a href="#voucher" data-toggle="tab">Khuyến mại</a></li>
                                            <li class=""><a href="#images" data-toggle="tab">Hình ảnh</a></li>
                                        @endif
                                        @if ($type != 'voucher' && $type != 'event')
                                            <li class=""><a href="#poll_options" data-toggle="tab">Thăm dò ý kiến</a></li>
                                        @endif
                                        <li class=""><a href="#attaches" data-toggle="tab">File đính kèm</a></li>
                                    </ul>
                                    <!-- Tab panes -->
                                    <div class="tab-content">

                                        @include('backend.posts.edit.general')

                                        @if ($type == 'event')
                                            @include('backend.posts.edit.event')
                                        @endif

                                        @if ($type == 'voucher')
                                            @include('backend.posts.edit.voucher')
                                            @include('backend.posts.edit.images')
                                        @endif

                                        @if ($type != 'voucher')
                                            @include('backend.posts.edit.poll_options')
                                        @endif

                                        @include('backend.posts.edit.attaches')
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        @include('backend.posts.edit.info')
                    </div>
                </div>
            </form>
        @endif
    </section>

@endsection

@section('stylesheet')

    <link rel="stylesheet" href="/adminLTE/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />

@endsection

@section('javascript')

    <!-- Datetime Picker -->
    <script src="/adminLTE/plugins/moment/moment.min.js"></script>
    <script src="/adminLTE/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
    <!-- TinyMCE -->
    <!-- <script src="/adminLTE/plugins/tinymce/tinymce.min.js"></script>
    <script src="/adminLTE/plugins/tinymce/config.js"></script> -->

    @include('backend.posts.edit.js-poll_options')
    @include('backend.posts.edit.js-images')
    @include('backend.posts.edit.js-attaches')
    @include('backend.posts.edit.js-notify')

    <script>
        @if($id > 0)
        sidebar('{{ $type }}', 'index');
        @else
        sidebar('{{ $type }}', 'create');
        @endif

        function downloadURI(element) {
            var url = $(element).closest('.checkbox_parent').find('input.input-attach').val();
            var link = document.createElement("a");
            link.href = url;
            link.target = "_blank";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            delete link;
        }
        $('.upload_file_image').on('change', function(e) {
            if (e.target.files[0]) {
                let formData = new FormData();
                formData.append('file',e.target.files[0]);
                formData.append('folder',"{{auth()->user() ? auth()->user()->id : null}}");
                $.ajax({
                        url: "{{route('api.v1.upload.upload_v2')}}",
                        type: 'POST',
                        data: formData,
                        contentType: false, //tell jquery to avoid some checks
                        processData: false,
                        success: function (response) {
                            console.log(response);
                            if (response.success == true) {
                                $('#upload_file_image_input').val(response.location);
                                toastr.success(response.msg);

                            } else {
                                toastr.error('thất bại');
                            }
                        },
                        error: function(response) {
                            toastr.error('đã có lỗi xảy ra.');
                        }
                });
                
            }
        
        });
    </script>

@endsection