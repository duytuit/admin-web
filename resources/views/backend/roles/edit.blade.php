@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        Cập nhật Nhóm quyền
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">{{ $heading }}</li>
    </ol>
</section>

@can('view', app(App\Models\Role::class))
<section class="content">
    <form id="form-roles" action="{{ route('admin.roles.save', ['id' => $id]) }}" method="post" autocomplete="off">
        @csrf
        @method('POST')

        @php
        $old = old();
        @endphp

        <div class="row">
            <div class="col-sm-8">
                <div class="box no-border-top">
                    <div class="box-body no-padding">
                        <div class="nav-tabs-custom no-margin">
                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#general" data-toggle="tab">Tổng quan</a></li>
                                @if($id)
                                @can('view', app(App\Models\RoleUser::class))
                                <li class=""><a href="{{ route('admin.roles.users.edit', ['id' => $id]) }}">Nhân viên</a></li>
                                <li class=""><a href="{{ route('admin.roles.users.edit', ['id' => $id]) }}#partners">Đối tác</a></li>
                                @endcan
                                @endif
                            </ul>
                            <!-- Tab panes -->
                            <div class="tab-content">
                                @include('backend.roles.edit.general')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
@endcan

@endsection

@section('javascript')

<script type="text/javascript" src="/adminLTE/plugins/treegrid/jquery.treegrid.js"></script>
<script type="text/javascript" src="/adminLTE/plugins/treegrid/jquery.treegrid.bootstrap3.js"></script>
<link rel="stylesheet" href="/adminLTE/plugins/treegrid/jquery.treegrid.css">

<script type="text/javascript">
    $('.tree').treegrid();
</script>

<script>
    sidebar('users', 'roles');
</script>

<script>
    $('#roles input').iCheck({
        checkboxClass: 'icheckbox_square-green',
        radioClass: 'iradio_square-green',
        increaseArea: '20%' // optional
    });

    $('#roles .role-permission').on('ifToggled', function(e) {
        //all_checked();
    });

    $('#roles input[data-target]').on('ifToggled', function(e) {
        var $this = $(this);
        var target = $this.data('target');
        var $target = $(target);

        if (this.checked) {
            $target.iCheck('check');
        } else {
            $target.iCheck('uncheck');
        }

        //all_checked();
    });

    all_checked();

    function all_checked() {
        parent_checked('.role-group');
        parent_checked('.role-item');
        parent_checked('.role-all');
    }

    function parent_checked(selector) {
        $(selector).each(function() {
            var $this = $(this);
            var target = $this.data('target');
            if ($(target).length == $(target + ':checked').length) {
                $this.iCheck('check');
            } else {
                $this.iCheck('uncheck');
            }
        });
    }
</script>

@endsection