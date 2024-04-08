@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý
            <small>Công ty</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Quản lý công ty</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="box-body">
                <div class="col-md-12">
                    <!-- Custom Tabs -->
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs">
                            <li class="{{ str_contains(url()->current(),'urban-building') ? 'active' : null }}"><a href="{{ route('admin.company.urban-building.index') }}" >Danh mục khu đô thị - dự án</a></li>
                            <li class="{{ !str_contains(url()->current(),'urban-building') ? 'active' : null }}"><a href="{{ route('admin.company.list.index') }}" >Danh mục công ty</a></li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane {{ !str_contains(url()->current(),'urban-building') ? 'active' : null }}" id="{{ route('admin.company.list.index') }}">

                                    <div class="box-header with-border">
                                        <div class="row form-group">
                                            <div class="col-md-2">
                                                    <a href="javascript:;" type="button" class="btn btn-primary show_edit"><i class="fa fa-plus"></i>&nbsp;&nbsp;Thêm công ty</a>
                                            </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-hover table-striped table-bordered">
                                                <thead class="bg-primary">
                                                <tr>
                                                    <th width="10%">STT</th>
                                                    <th>Tên công ty</th>
                                                    <th width="10%">Hành động</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                    @if($companys->count() > 0)
                                                        @foreach($companys as $key => $value)
                                                            <tr>
                                                                <td>{{ $key + 1 }}</td>
                                                                <td>{{ @$value->name }}</td>
                                                                <td>
                                                                    <a href="javascript:;" data-item="{{$value}}" class="btn btn-sm btn-primary show_edit"><i class="fa fa-edit"></i></a>
                                                                    <a href="{{ route('admin.company.list.destroy',['id'=>$value->id]) }}" onclick="return confirm('Bạn có chắc chắn xóa!');" class="btn btn-sm btn-danger" title="Xóa"> <i class="fa fa-trash"></i> </a>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                            </div>
                        </div>
                        <!-- /.tab-content -->
                    </div>
                    <!-- nav-tabs-custom -->
                </div>
            </div>
        </div>
    </section>
    <div class="modal fade" id="createCompany" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" style="display: initial;">Thông tin công ty</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form class="form-horizontal" method="POST" data-action="{{ route('admin.company.list.saveCompany') }}" id="create_company">
                        @csrf
                        <input type="hidden" id="company_id" name="id">
                        <div class="box-body">
                            <div class="form-group data_content">
                                <label for="content" class="col-sm-3 control-label">Tên công ty</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="name" name="name">
                                    <div class="message_zone_data"></div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer d-flex justify-content-center">
                    <button type="button" class="btn btn-primary" id="add_company">Lưu</button>
                    <button type="button" class="btn btn-warning" data-dismiss="modal">Hủy</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script>
        $('.show_edit').click(function (e) { 
            e.preventDefault();
            $('#create_company')[0].reset();
            let item = $(this).data('item');
            if(item){
                $('#name').val(item.name);
                $('#company_id').val(item.id);
            }
            $('#createCompany').modal('show');
        });
        $('#add_company').click(function (e) { 
            var form_data = new FormData($('#create_company')[0]);
            e.preventDefault();
            $.ajax({
                    url: $('#create_company').attr('data-action'),
                    type: 'POST',
                    data: form_data,
                    contentType: false,
                    processData: false, 
                    success: function (response) {
                        if (response.success == true) {
                            toastr.success(response.message);
                        } 
                        setTimeout(() => {
                            location.reload()
                        }, 1000)
                    },
                    error: function (response){
                        console.log(response.responseJSON.errors.name[0]);
                        toastr.error(response.responseJSON.errors.name[0]);
                        setTimeout(() => {
                            location.reload()
                        }, 1000)
                    }
            });
        });
    </script>
@endsection