@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            Thêm mới đăng ký sửa chữa
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Thêm mới đăng ký sửa chữa</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Thêm mới đăng ký sửa chữa</div>

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
                        <div class="alert alert-danger alert_pop_add" style="display: none;">
                            <ul></ul>
                        </div>
                        <div class="form-group">
                            <form action="{{ route('admin.feedback.repairApartmentStore') }}" method="post" id="form-edit-apartment" enctype="multipart/form-data">
                                {{ csrf_field() }}
                                <input type="hidden" name="bdc_building_id"  class="form-control" placeholder="Tên căn hộ" value="{{$building_id}}">
                                <div class="form-group">
                                    <label for="ip-name">Căn hộ</label>
                                    <select name="bdc_apartment_id" class="form-control apartment-list selectpicker" data-live-search="true">
                                        <option value="" selected>Căn hộ</option>
                                        @if(isset($apartments))
                                            @foreach($apartments as $key => $apartment)
                                                <option value="{{ $key }}"  @if(@$filter['bdc_apartment_id'] ==  $key) selected @endif>{{ $apartment }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="ip-name">Tiêu đề</label>
                                    <input type="text" name="title" id="title" class="form-control" placeholder="Tiêu đề" >
                                </div>
                                <div class="form-group">
                                    <label for="ip-description">Nôi dung</label>
                                    <textarea name="content" id="content" cols="30" rows="5" placeholder="Nội dung" class="form-control"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="ip-floor">File đính kèm</label>
                                    <input type="file" name="attached[]" id="attached" class="form-control" placeholder="file" multiple>
                                </div>
                                <div class="form-group">
                                    <label for="ip-name">Đơn vị thi công</label>
                                    <input type="text" name="unit_name" id="unit_name" class="form-control" placeholder="Đơn vị thi công" >
                                </div>
                                <div class="form-group">
                                    <label for="ip-name">Thời gian bắt đầu</label>
                                    <div class="input-group date">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                        <input type="text" class="form-control date_picker" name="start_time" id="start_time" placeholder="Từ..." autocomplete="off">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="ip-name">Thời gian kết thúc</label>
                                    <div class="input-group date">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                        <input type="text" class="form-control date_picker" name="end_time" id="end_time" placeholder="Đến..." autocomplete="off">
                                    </div>
                                </div>
                                <hr>
                                <div class="form-group">
                                    <label for="ip-name">Họ và tên</label>
                                    <input type="text" name="full_name" id="full_name" class="form-control" placeholder="Họ và tên" >
                                </div>
                                <div class="form-group">
                                    <label for="ip-name">Email</label>
                                    <input type="text" name="email" id="email" class="form-control" placeholder="Email" >
                                </div>
                                <div class="form-group">
                                    <label for="ip-name">Số điện thoại</label>
                                    <input type="text" name="phone" id="phone" class="form-control" placeholder="Số điện thoại" >
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-sm btn-success btn-js-action-add" title="Cập nhật">
                                        <i class="fa fa-save"></i>&nbsp;Thêm mới
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@section('javascript')

    <script>
        $(document).ready(function () {
            //Date picker
            $('input.date_picker').datepicker({
                autoclose: true,
                dateFormat: "dd-mm-yy"
            }).val();
        });
    </script>

@endsection
