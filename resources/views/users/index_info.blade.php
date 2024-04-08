@inject('request', 'Illuminate\Http\Request')
@extends('backend.layouts.master')

@section('content')
<section class="content-header">
    <h1>
        Thông tin tài khoản
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="box-body">
            <div class="col-md-12">
                <!-- Custom Tabs -->
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li><a data-toggle="tab" href="#user">User</a></li>
                        <li><a data-toggle="tab" href="#profile">Profile</a></li>
                    </ul>
                    <div class="tab-content">
                        @include('users.tabs.user')
                        @include('users.tabs.profile')
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
@section('javascript')
<script>
    $('input.date_picker').datepicker({
                autoclose: true,
                dateFormat: "dd-mm-yy"
            }).val();
</script>
@endsection
