@extends('backend.layouts.master')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
             <div class="panel panel-default">
        <div class="panel-heading">Sửa bảng giá</div>

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
        {!! Form::open(['route' => ['admin.pricetype.update', $item->id], 'method'=> 'PUT', 'files' => true]) !!}
          <div class="form-group">
            {!! Form::label('name', 'Tên bảng giá') !!}
            {!! Form::text('name', $item->name, ['class' => 'form-control', 'placeholder' => 'Tên bảng giá']) !!}
          </div>
          {{-- Package name --}}
          <button type="submit" class="btn btn-success">Cập nhật</button>
        {!! Form::close() !!}

        </div>
    </div>
        </div>
    </div>
</div>
@endsection

@section('javascript')

<script>
sidebar('event', 'index');
</script>

@endsection