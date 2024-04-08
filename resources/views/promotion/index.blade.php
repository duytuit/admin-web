@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Khuyến mãi</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="clearfix"></div>
                <ul class="nav nav-tabs">
                    <li class="">
                        <a href="/admin/promotion/promotion_manager">
                            Quản lý khuyến mãi
                        </a>
                    </li>
                    <li class="">
                        <a href="/admin/promotion/apartment_promotion_manager">
                            Quản lý khuyến mãi căn hộ
                        </a>
                    </li>
                </ul>
                <div class="tab-content">
                    @yield('tab_content')
                </div>
            </div>
        </div>

    </section>
@endsection

<script>
    document.addEventListener("DOMContentLoaded", () => {
        if (location.href.search('apartment_promotion_manager') != -1) {
            document.querySelectorAll('.nav-tabs li')[1].classList.add('active')
        }
        else{
            document.querySelectorAll('.nav-tabs li')[0].classList.add('active')
        }
    });
</script>
