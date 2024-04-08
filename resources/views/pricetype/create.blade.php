@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        Tạo bảng giá
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Tạo bảng giá</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
             <div class="panel panel-default">
        <div class="panel-heading">Tạo bảng giá</div>

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
        {!! Form::open(['url' =>[route('admin.pricetype.store') ] , 'method'=> 'POST','files' => true]) !!}
          <div class="form-group">
            {!! Form::label('name', 'Tên') !!}
            {!! Form::text('name', old('name'), ['class' => 'form-control', 'placeholder' => 'Tên bảng giá']) !!}
          </div>

          <button type="submit" class="btn btn-success">Đồng ý</button>

        {!! Form::close() !!}

        </div>
    </div>
        </div>
    </div>
</div>
</section>

@endsection

@section('javascript')

<script>
sidebar('event', 'index');
</script>

@endsection
