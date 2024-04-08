@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý
            <small>Tài sản-Lịch bảo trì</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Quản lý tài sản</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="box-body">
                <div class="col-md-12">
                    <!-- Custom Tabs -->
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs">
                            <li class="{{ is_null(session('tab')) ? ($tab == 'asset' ? 'active' : '') : (session('tab') == 'asset' ? 'active' : '') }}"><a href="#asset" data-toggle="tab" aria-expanded="true">Quản lý tài sản</a></li>
                            <li class="{{ is_null(session('tab')) ? ($tab == 'maintenance_asset' ? 'active' : '') : (session('tab') == 'maintenance_asset' ? 'active' : '') }}"><a href="#maintenance_asset" data-toggle="tab" aria-expanded="false">Lịch bảo trì</a></li>
                        </ul>
                        <div class="tab-content">
                            @include('assets.tabs.asset')
                            @include('assets.tabs.maintenance')
                        </div>
                        <!-- /.tab-content -->
                    </div>
                    <!-- nav-tabs-custom -->
                </div>
            </div>
        </div>
    </section>
@endsection

@section('javascript')
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        //delete single asset
        deleteSubmit('.delete-asset');

        //delete multi asset
        $(document).on('click', '#delete-multi-assets', function (e) {
            var ids = [];
            var div = $('div.icheckbox_square-green[aria-checked="true"]');
            div.each(function (index, value) {
                var id = $(value).find('input.checkSingle').val();
                if (id) {
                    ids.push(id);
                }
            });
            if (ids.length == 0) {
                toastr.error('Vui lòng chọn tài sản để thực hiện tác vụ này');
            } else {
                if (!confirm('Bạn có chắc chắn muốn xóa những tài sản này?')) {
                    e.preventDefault();
                } else {
                    $.ajax({
                        url: $(this).attr('data-action'),
                        type: 'POST',
                        data: {
                            ids: ids
                        },
                        success: function (response) {
                            if (response.success == true) {
                                toastr.success(response.message);

                                setTimeout(() => {
                                    location.reload()
                                }, 2000)
                            } else {
                                toastr.error('Không thể xóa những tài sản này!');
                            }
                        }
                    })
                }
            }
        })

        //submit search
        $(document).on('click', '.search-asset', function (e) {
            e.preventDefault();
            var keyword = $('input[name="keyword"]').val();
            var type = $('select[name="bdc_assets_type_id"]').val();
            var period = $('select[name="bdc_period_id"]').val();
            var searchParams = new URLSearchParams(window.location.search);
            if (!keyword && !type && !period) {
                if (searchParams.has('keyword') && searchParams.has('bdc_assets_type_id') && searchParams.has('bdc_period_id')) {
                    window.location.href = $('#form-search-advance').attr('action');
                } else {
                    toastr.error('Vui lòng điền thông tin nào nó để tìm kiếm');
                }
            } else if (keyword == searchParams.get('keyword') && type == searchParams.get('bdc_assets_type_id')
                        && period == searchParams.get('bdc_period_id')) {
                e.preventDefault();
            } else {
                $('#form-search-advance').submit();
            }
        })

        $(document).on('change', 'select[name="per_page_maintenance"]', function () {
            $('#form-maintanence').submit();
        })

        //submit search
        $(document).on('click', '.search-maintenance', function (e) {
            e.preventDefault();
            var keyword = $('input[name="keyword_maintain"]').val();
            var maintenance_time = $('input[name="maintenance_time"]').val();
            var status = $('select[name="status"]').val();
            var searchParams = new URLSearchParams(window.location.search);
            if (!keyword && !maintenance_time && !status) {
                if (searchParams.has('keyword_maintain') && searchParams.has('maintenance_time') && searchParams.has('status')) {
                    window.location.href = $('#form-search-maintenance').attr('action');
                } else {
                    toastr.error('Vui lòng điền thông tin nào nó để tìm kiếm');
                }
            } else if (keyword == searchParams.get('keyword_maintain') && maintenance_time == searchParams.get('maintenance_time') && maintenance_time == searchParams.get('status')) {
                e.preventDefault();
            } else {
                $('#form-search-maintenance').submit();
            }
        })

        $('input.date_picker').datepicker({
            autoclose: true,
            dateFormat: "dd-mm-yy"
        }).val();
    </script>
@endsection