
@extends('layouts.app-new')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
             <div class="panel panel-default">
        <div class="panel-heading">Update Package of Order</div>

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
        {{-- {!! Form::open(['url' =>[route('order-package.store') ] , 'method'=> 'POST','files' => true]) !!} --}}
        {!! Form::open(['route' => ['order-package.update', $item->id], 'method'=> 'PUT','files' => true]) !!}
          <div class="form-group">
            {!! Form::label('title', 'Package name') !!}
            {!! Form::text('title', $item->title, ['class' => 'form-control', 'placeholder' => 'Package name']) !!}
          </div>
          {{-- Package name --}}

          <div class="form-group">
            {!! Form::label('description', 'Description') !!}
            {!! Form::textarea('description', $item->description, ['class' => 'form-control', 'placeholder' => 'Package Description']) !!}
          </div>
          {{-- Description--}}

           <div class="form-group">
            {!! Form::label('price', 'Package price') !!}
            {!! Form::text('price', $item->price, ['class' => 'form-control', 'placeholder' => 'Package price']) !!}
          </div>
          {{-- Package price--}}

          <div class="form-group">
            {!! Form::label('status', 'Package status') !!}
            {!! Form::select('status', \App\Repositories\Order\PackageRepository::$packageStatus, $item->status, ['class' => 'form-control']); !!}
          </div>
          {{-- Package type--}}

          <div class="form-group">
            {!! Form::label('option', 'Package option') !!}
            {!! Form::select('option', \App\Repositories\Order\PackageRepository::$ieltsTestOptions, $item->option, ['class' => 'form-control']); !!}
          </div>
          {{-- Package status--}}

          <div class="form-group">
            {!! Form::label('type', 'Package type') !!}
            {!! Form::select('type', \App\Repositories\Order\PackageRepository::$packageType, $item->type, ['class' => 'form-control']); !!}
          </div>
          {{-- Package level--}}



         {{--  <div class="form-group">
            {!! Form::label('file', 'Video File') !!}
            {!! Form::file('file', ['class' => 'form-control']) !!}
          </div> --}}
          {{-- Video File--}}

          <button type="submit" class="btn btn-default">Submit</button>

        {!! Form::close() !!}

        </div>
    </div>
        </div>
    </div>
</div>
@endsection
