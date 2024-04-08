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
    <form id="form-articles" action="{{ route('admin.articles.save', ['id' => $id, 'type' => $type]) }}" method="post" autocomplete="off">
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
                                @if ($type != 'voucher')
                                <li class=""><a href="#poll_options" data-toggle="tab">Thăm dò ý kiến</a></li>
                                @endif
                                <li class=""><a href="#attaches" data-toggle="tab">File đính kèm</a></li>
                            </ul>
                            <!-- Tab panes -->
                            <div class="tab-content">

                                @include('backend.articles.edit.general')

                                @if ($type == 'event')
                                @include('backend.articles.edit.event')
                                @endif

                                @if ($type == 'voucher')
                                @include('backend.articles.edit.voucher')
                                @include('backend.articles.edit.images')
                                @endif

                                @if ($type != 'voucher')
                                @include('backend.articles.edit.poll_options')
                                @endif

                                @include('backend.articles.edit.attaches')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                @include('backend.articles.edit.info')
            </div>
        </div>
    </form>
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
<script src="/adminLTE/plugins/tinymce/tinymce.min.js"></script>
<script src="/adminLTE/plugins/tinymce/config.js"></script>

@include('backend.articles.edit.js-poll_options')
@include('backend.articles.edit.js-images')
@include('backend.articles.edit.js-attaches')
@include('backend.articles.edit.js-notify')

<script>
    @if($id > 0)
    sidebar('{{ $type }}', 'index');
    @else
    sidebar('{{ $type }}', 'create');
    @endif
</script>

@endsection