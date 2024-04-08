@extends('backend.layouts.master')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
             <div class="panel panel-default">
        <div class="panel-heading">Create Menu</div>

        <div class="panel-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        {!! Form::open(['url' =>[route('admin.system.menu.store') ] , 'method'=> 'POST','files' => true]) !!}
          <div class="form-group">
            {!! Form::label('name', 'Menu name') !!}
            {!! Form::text('name', old('name'), ['class' => 'form-control', 'placeholder' => 'Menu name']) !!}
          </div>
          {{-- name --}}

          <div class="form-group">
            {!! Form::label('description', 'Description') !!}
            {!! Form::text('description', old('description'), ['class' => 'form-control', 'placeholder' => 'Description']) !!}
          </div>
            <div class="form-group">
                <label class="control-label">Icon Web</label>
                <div class="input-group">
                    <input id="icon_web" type="text" name="icon_web" value="{{ old('icon_web') }}" class="form-control" readonly><span class="input-group-btn"><button type="button" class="btn btn-primary" data-font="fontawesome" data-target="#icon_web">Chọn</button></span>
                </div>
                <div class="icon-preview">
                    @if (old('icon_web'))
                        <span style="font-size: 32px;">
                            <i class="fa {{ old('icon_web') }}"></i>
                        </span>
                    @endif
                </div>
            </div>
          {{-- description --}}
            @if($count<1)
                <div class="form-group">
                    <label for="">Menu APP</label>
                    <div class="radio">
                        <label for="type_no"> <input name="type" type="radio" value="0" id="type_no" {{ old('type') == 0 ? 'checked' : ''}}> No</label>
                    </div>
                    <div class="radio">
                        <label for="type_yes"><input name="type" type="radio" value="1" id="type_yes" {{ old('type') == 1 ? 'checked' : ''}}> Yes</label>
                    </div>
                </div>
            @endif
            {{-- type app --}}
          <button type="submit" class="btn btn-default">Submit</button>

        {!! Form::close() !!}

        </div>
    </div>
        </div>
    </div>
</div>
<div id="modal-icons" class="modal fade" data-target="">
    <div class="modal-dialog modal-lg" style="overflow: hidden;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <div class="input-group" style="width: 280px;">
                    <input type="text" name="keyword" class="form-control" placeholder="Nhập tên icon" autocomplete="off">
                    <span class="input-group-btn">
                        <button type="button" class="btn btn-primary"><i class="fa fa-search"></i></button>
                    </span>
                </div>
            </div>
            <div class="modal-body" style="height: 450px; overflow: auto;">
                <div id="icons" class="row"></div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('stylesheet')
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://unpkg.com/ionicons@4.5.5/dist/css/ionicons.min.css">

@endsection

@section('javascript')

<script>
sidebar('event', 'index');
var ionicons = '';
var fontawesome = '';

$.getJSON('/adminLTE/js/ionicons.json', function(json) {
    $.each(json, function(index, value) {
        ionicons += '<div class="col-sm-3"><div class="icon-btn" data-icon="' + value + '"><span class="icon-demo"><i class="icon ion-md-' + value + '"></i></span> <span class="icon-name">' + value + '</span></div></div>';
    });
});

$.getJSON('/adminLTE/js/fontawesome.json', function(json) {
    $.each(json, function(index, value) {
        fontawesome += '<div class="col-sm-3"><div class="icon-btn" data-icon="' + value + '"><span class="icon-demo"><i class="fa ' + value + '"></i></span> <span class="icon-name">' + value + '</span></div></div>';
    });
});

var $icons = $('#icons');
var $modal_icons = $('#modal-icons');
var $icon_search = $('input[name=keyword]', $modal_icons);
var $icon_target;

$('body').on('click', '[data-font]', function() {
    var font = $(this).data('font');
    var target = $(this).data('target');

    if (font == 'ionicons') {
        $icons.html(ionicons)
    } else if (font == 'fontawesome') {
        $icons.html(fontawesome)
    }
    $icon_target = $(target);
    $icon_search.val('');
    $modal_icons.modal('show');
});

$('body').on('click', '[data-icon]', function() {
    var icon = $(this).data('icon');
    $icon_target.val(icon);
    $('div.icon-preview').html('<i class="fa '+ icon +'"></i>');
    $modal_icons.modal('hide');
});

$icon_search.on('input change', function() {
    var keyword = $(this).val();

    $('#icons > div', $modal_icons).hide();

    $('.icon-btn', $modal_icons).each(function() {
        var icon = $(this).data('icon');
        if (icon.indexOf(keyword) > -1) {
            $(this).parent().show();
        }
    });
});
</script>

@endsection