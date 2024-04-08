@extends('backend.layouts.master')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Menu Of Staff </div>

                <div class="panel-body">
                    {!! Form::open(['route' => ['system.permission.update', $staff_id], 'method'=> 'PUT','files' => true]) !!}
                    <button type="submit" class="btn btn-default">Submit</button>
                        <table class="table table-striped">
                        <thead>
                        <tr>
                            <th colspan="" rowspan="" headers="">#</th>
                            <th colspan="" rowspan="" headers="">Has Permision</th>
                            <th colspan="" rowspan="" headers="">Name</th>
                            <th colspan="" rowspan="" headers="">Router name</th>
                            <th colspan="" rowspan="" headers="">link</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($data as $key=>$item)
                        <tr>
                            <td colspan="" rowspan="" headers="">No.{{ $key+1 }}</td>
                            <td colspan="" rowspan="" headers="">{!!Form::checkbox('permision[]', $item->id, $item->hasMenu ? true: false ) !!} {!! $item->hasMenu !!}</td>
                            <td colspan="" rowspan="" headers="">{{ $item->title }}</td>
                            <td colspan="" rowspan="" headers="">{{ $item->router_name }}</td>
                            <td colspan="" rowspan="" headers="">{{ route($item->router_name) }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-default">Submit</button>
                    {!! Form::close() !!}
                    {{-- .pager --}}
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