@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý chung
            <small>Quản lý bộ phận</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Quản lý bộ phận</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-body">
                <div class="box-header">
                    <form id="form-search-advance" action="#" method="get">
                        <div id="search-advance" class="search-advance">
                            <div class="row form-group col-md-9">
                                <div class="col-md-2">
                                    <select name="bdc_assets_type_id" class="form-control">
                                        <option value="" selected>Căn hộ</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-warning btn-block">Tìm kiếm</button>
                                </div>
                            </div>
                        </div>
                    </form><!-- END #form-search-advance -->
                    <a style="float: left" href="#" class="btn btn-success margin-r-5">Import điện nước</a>
                    <a style="float: left" href="#" class="btn btn-danger margin-r-5">Export excel</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="bg-primary">
                        <tr>
                            <th>STT</th>
                            <th>Tên sản phẩm</th>
                            <th>Số lượng</th>
                            <th>Đơn giá</th>
                            <th>Phí ô tô</th>
                            <th>Phí xe máy</th>
                            <th>Phí dịch vụ</th>
                            <th>Thao tác</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr style="vertical-align: middle">
                            <td style="text-align: center">1</td>
                            <td><a href="#">PH-202</a></td>
                            <td>2000</td>
                            <td>3000</td>
                            <td>2000</td>
                            <td>2000</td>
                            <td>2000</td>
                            <td>2000</td>
                            <td>2000</td>
                            <td>2000</td>
                            <td>2000</td>
                            <td>2000</td>
                            <td>
                                <a style="float: left" href="#" class="btn btn-sm btn-info margin-r-5"><i
                                            class="fa fa-edit"></i></a>
                                <form action="#"
                                      method="POST">
                                    @method('DELETE')
                                    @csrf
                                    <button onclick="return confirm('Bạn muốn xóa dịch vụ này ?');"
                                            class=" btn btn-sm btn-danger"><i class="fa fa-trash"
                                                                              aria-hidden="true"></i></button>
                                </form>
                            </td>
                        </tr>
                        <tr style="vertical-align: middle">
                            <td style="text-align: center">1</td>
                            <td><a href="#">PH-202</a></td>
                            <td>2000</td>
                            <td>3000</td>
                            <td>2000</td>
                            <td>2000</td>
                            <td>2000</td>
                            <td>2000</td>
                            <td>2000</td>
                            <td>2000</td>
                            <td>2000</td>
                            <td>2000</td>
                            <td>
                                <a style="float: left" href="#" class="btn btn-sm btn-info margin-r-5"><i
                                            class="fa fa-edit"></i></a>
                                <form action="#"
                                      method="POST">
                                    @method('DELETE')
                                    @csrf
                                    <button onclick="return confirm('Bạn muốn xóa dịch vụ này ?');"
                                            class=" btn btn-sm btn-danger"><i class="fa fa-trash"
                                                                              aria-hidden="true"></i></button>
                                </form>
                            </td>
                        </tr>
                        <tr style="vertical-align: middle">
                            <td style="text-align: center">1</td>
                            <td><a href="#">PH-202</a></td>
                            <td>2000</td>
                            <td>3000</td>
                            <td>2000</td>
                            <td>2000</td>
                            <td>2000</td>
                            <td>2000</td>
                            <td>2000</td>
                            <td>2000</td>
                            <td>2000</td>
                            <td>2000</td>
                            <td>
                                <a style="float: left" href="#" class="btn btn-sm btn-info margin-r-5"><i
                                            class="fa fa-edit"></i></a>
                                <form action="#"
                                      method="POST">
                                    @method('DELETE')
                                    @csrf
                                    <button onclick="return confirm('Bạn muốn xóa dịch vụ này ?');"
                                            class=" btn btn-sm btn-danger"><i class="fa fa-trash"
                                                                              aria-hidden="true"></i></button>
                                </form>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <div class="text-center">
                        <a href="#" class="btn btn-success margin-r-5">Lưu số liệu</a>
                        <a href="#" class="btn btn-danger margin-r-5">Xác nhận số
                            liệu</a>
                        <a href="#" class="btn btn-info margin-r-5">Gửi thông báo</a>
                    </div>
                </div>
                <div class="row mbm">
                    <div class="col-sm-6">
                        <span class="record-total">Hiển thị {{ $services->count() }} / {{ $services->total() }} kết quả</span>
                    </div>
                    <div class="col-sm-6 text-right">
                        <div class="pagination-panel">
                            {{ $services->links() }}
                        </div>
                    </div>
                </div>
                <br>
            </div>
        </div>
    </section>
@endsection
@section('javascript')
@endsection