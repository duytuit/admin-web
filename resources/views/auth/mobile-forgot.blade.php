@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('') }}</div>

                <div class="card-body">
                    @if (\Session::has('success'))
                        {{ \Session::get('success') }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
