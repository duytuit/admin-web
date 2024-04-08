


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
                    <div class="form-group">
                        <form action="{{ route('admin.demo.save_debit') }}" method="post" id="form-import-debit" autocomplete="off" enctype="multipart/form-data">
                            {{ csrf_field() }}
                            <div class="form-group">
                                <label for="ip-name">Chọn file</label>
                                <input type="file" name="file_import" id="ip-file_import">
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-sm btn-success" id="submit-form" title="Thêm mới" form="form-import-debit">
                                    <i class="fa fa-save"></i>&nbsp;&nbsp;Thêm mới debit
                                </button>
                            </div>
                        </form>
                    </div>
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
