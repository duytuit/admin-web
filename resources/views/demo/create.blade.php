


@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        Demo Post
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Demo Post</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
             <div class="panel panel-default">
        <div class="panel-heading">Create Distributor</div>

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
        {!! Form::open(['url' =>[route('admin.demo.post.store') ] , 'method'=> 'POST','files' => true]) !!}
          <div class="form-group">
            {!! Form::label('title', 'Title') !!}
            {!! Form::text('title', old('title'), ['class' => 'form-control', 'placeholder' => 'title']) !!}
          </div>
          {{-- Package title--}}
          <div class="form-group">
            {!! Form::label('address', 'description') !!}
            {!! Form::text('description', old('description') , ['class' => 'form-control', 'placeholder' => 'description']) !!}
          </div>
          {{-- Package description --}}

          <button type="submit" class="btn btn-default">Submit</button>

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
