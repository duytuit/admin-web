@extends('backend.layouts.master')
@inject('request', 'Illuminate\Http\Request')

@section('content')
<section class="content-header">
    <h1>
        Quản lý tòa nhà
        <small>Danh sách cấu hình</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
        <li class="active">Danh sách cấu hình</li>
    </ol>
</section>

<section class="content" id="content-partner">
    <form action="{{ route('admin.configs.view') }}" method="POST" id="form-create-work" enctype="multipart/form-data">
        {{ csrf_field() }}
        <div class="row">
            <div class="col-lg-12">
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="form-group">
                            <textarea name="content" placeholder="Nội dung" rows="10" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="text-center">
                            <button type="submit" class="btn btn-primary">Xem mẫu</button>
                     </div>
                </div>
            </div>
        </div>
    </form>
</section>
@endsection

@section('javascript')
@endsection