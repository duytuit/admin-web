@extends('layouts.app-new')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <h1 class="panel-heading"> {{ $item->title }}  -  [ {{ \App\Repositories\Order\PackageRepository::$packageStatus[$item->status] }} ] </h1>

                <div class="panel-body">
                    <div class="data-viewmore">
                        <a href="{!! url('order-package/'.$item->id.'/edit') !!}">edit</a>
                    </div>

                    <div class="data">
                        <div class="content data-content">
                            {{ $item->description }}
                        </div>
                    </div>

                    <div class="data-info">
                        created by: <a href="{{  (( $item->author <> null ) ? url('staff/'.$item->author->id) : 'unknown' ) }}">
                            {{ ( $item->author <> null ) ? $item->author->email : 'unknown'  }}
                        </a>
                            &nbsp; &nbsp; &nbsp;  last updated by: <a href="{{  (( $item->last_updated_by <> null ) ? url('staff/'.$item->last_updated_by->id) : '#' ) }}">{{ ( $item->last_updated_by <> null ) ? $item->last_updated_by->email : 'unknown' }}</a>

                    </div>

                </div>
            </div>
        </div>
    </div>

    </div>
</div>
@endsection
