@extends('backend.layouts.master')

@section('content')
<section class="content-header">
    <h1>
        Nhóm menu
        <small>Cập nhật</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('/admin') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">{{ $heading }}</li>
    </ol>
</section>

<section class="content">
    @php
    $old = old();
    @endphp

    <form action="{{ route('admin.app_projects.update', ['id' => $id]) }}" method="post" id="form-edit-group-menu" class="form-validate">
        @csrf
        <div class="row">
            <div class="col-sm-8 col-xs-12">
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="row">
                            <div class="col-sm-6 col-xs-12 form-group {{ $errors->has('name') ? 'has-error': '' }}">
                                <label class="control-label">Tên App project <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" placeholder="Tên app project" value="{{ $app_project->name ?? old('name') ?? '' }}" />
                                @if ($errors->has('name'))
                                <em class="help-block">{{ $errors->first('name') }}</em>
                                @endif
                            </div>

                            <div class="col-sm-6 col-xs-12 form-group {{ $errors->has('code') ? 'has-error': '' }}">
                                <label class="control-label">Mã app project <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="code" placeholder="Mã app project" value="{{ $app_project->code ?? old('code') ?? '' }}" />
                                @if ($errors->has('code'))
                                <em class="help-block">{{ $errors->first('code') }}</em>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label">Admin url</label>
                            <input type="text" class="form-control" name="admin_url" placeholder="Admin url" value="{{ $app_project->admin_url ?? old('admin_url') ?? '' }}" />
                        </div>

                        <div class="form-group">
                            <label class="control-label">Ghi chú</label>
                            <textarea class="form-control" name="description" placeholder="Ghi chú" rows="5"></textarea>
                        </div>
                    </div>
                    <div class="box-footer">
                        @can('update', app(App\Models\Exchange::class))
                        <button type="submit" class="btn btn-sm btn-success" title="Cập nhật" form="form-edit-group-menu">
                            <i class="fa fa-save"></i>&nbsp;&nbsp;{{  $id ? 'Cập nhật' : 'Thêm mới'}}
                        </button>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
@endsection

@section('javascript')
<script>
    sidebar('app-projects', 'add');
</script>
@endsection