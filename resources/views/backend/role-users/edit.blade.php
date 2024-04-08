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

<section class="content">
    <form id="form-roles" action="{{ route('admin.roles.users.action', ['id' => $id]) }}" method="post" autocomplete="off">
        @csrf
        <input type="hidden" name="method" value="" />
        <input type="hidden" name="status" value="" />

        <div class="row">
            <div class="col-sm-12">
                <div class="box no-border-top">
                    <div class="box-body no-padding">
                        <div class="nav-tabs-custom no-margin">
                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs">
                                @can('view', app(App\Models\Role::class))
                                <li class=""><a href="{{ route('admin.roles.edit', ['id' => $id]) }}">Tổng quan</a></li>
                                @endcan

                                @can('view', app(App\Models\RoleUser::class))
                                <li class="active"><a href="#users" data-toggle="tab">Nhân viên</a></li>
                                <li class=""><a href="#partners" data-toggle="tab">Đối tác</a></li>
                                @endcan
                            </ul>
                            <!-- Tab panes -->
                            <div class="tab-content">
                                @include('backend.role-users.edit.users')
                                @include('backend.role-users.edit.partners')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>

@endsection

@section('javascript')

<script>
    sidebar('users', 'roles');
</script>

<script>
    $('#keyword').autocomplete({
        minLength: 0,
        source: function(request, response) {
            $.ajax({
                url: "{{ route('admin.roles.users.search') }}",
                dataType: "json",
                data: {
                    user_type: $('#keyword').data('type'),
                    keyword: request.term,
                },
                success: function(json) {
                    response($.map(json.data, function(item, key) {
                        var info = item.user_name ? ' - ' + item.user_name : '';
                        return {
                            value: item.full_name,
                            label: item.full_name + info,
                            data: item,
                        };
                    }));
                }
            });
        },
        select: function(event, ui) {
            var token = $('[name="csrf-token"]').attr('content');
            var url = "{{ route('admin.roles.users.add', ['id' => $id]) }}";
            $.post(url, {
                _token: token,
                user_id: ui.item.data.id,
                user_type: 'user',
                tap: 'users',
                success: function(json){
                    setTimeout(() => {
                        location.reload();
                    }, 400);
                } 
            });
        }
    })
    .on('focus click', function() {
        $(this).data("uiAutocomplete").search($(this).val());
    });

    $('#partner-keyword').autocomplete({
        minLength: 0,
        source: function(request, response) {
            $.ajax({
                url: "{{ route('admin.roles.users.search') }}",
                dataType: "json",
                data: {
                    user_type: $('#partner-keyword').data('type'),
                    keyword: request.term,
                },
                success: function(json) {
                    response($.map(json.data, function(item, key) {
                        var info = item.user_name ? ' - ' + item.user_name : '';
                        return {
                            value: item.full_name,
                            label: item.full_name + info,
                            data: item,
                        };
                    }));
                }
            });
        },
        select: function(event, ui) {
            var token = $('[name="csrf-token"]').attr('content');
            var url = "{{ route('admin.roles.users.add', ['id' => $id]) }}";
            $.post(url, {
                _token: token,
                user_id: ui.item.data.id,
                user_type: 'partner',
                tap: 'partners',
                success: function(json){
                    setTimeout(() => {
                        location.reload();
                    }, 400);
                } 
            });
        }
    })
    .on('focus click', function() {
        $(this).data("uiAutocomplete").search($(this).val());
    });
</script>

@endsection