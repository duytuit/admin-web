@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        Sửa căn hộ
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Sửa căn hộ</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="clearfix"></div>
            <ul class="nav nav-tabs" role="tablist">
                <li class="active"><a href="#general" role="tab" data-toggle="tab">Thông tin chung</a></li>
                <li><a href="#diary_aparment" role="tab" data-toggle="tab">Nhật ký căn hộ</a></li>
                <li><a href="#service" role="tab" data-toggle="tab">Dịch vụ sử dụng</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="general" style="padding: 15px 0;">
                    <div class="panel panel-default">
                        <div class="panel-heading">Sửa căn hộ</div>

                        <div class="panel-body">
                            <div class="alert alert-danger alert_pop_add_edit" style="display: none;">
                                <ul></ul>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <form action="" method="post" id="form-edit-apartment">
                                        {{ csrf_field() }}
                                        <div class="form-group">
                                            <label for="ip-name">Tên căn hộ</label>
                                            <input type="text" name="name" id="ip-name-apt" class="form-control"
                                                placeholder="Tên căn hộ"
                                                value="{{!empty($apatment->name)?$apatment->name:''}}">
                                        </div>
                                        <div class="form-group">
                                            <label for="ip-place">Tòa nhà</label>
                                            <?php $building_place_id = !empty($apatment->building_place_id)?$apatment->building_place_id:''; ?>
                                            <select name="building_place_id" id="ip-place" class="form-control" style="width: 100%">
                                                <option value="">Chọn tòa nhà {{$building_place_id}}</option>
                                                @if($building_place_id)
                                                    <option value="{{$building_place_id}}" selected>{{$building_place->name}} - {{$building_place->code}}</option>
                                                @endif
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="ip-floor">Số tầng</label>
                                            <input type="text" name="floor" id="ip-floor" class="form-control"
                                                placeholder="Tầng căn hộ"
                                                value="{{!empty($apatment->floor)?$apatment->floor:''}}">
                                        </div>
                                        <div class="form-group">
                                            <label for="ip-acreage">Diện tích(m<sup>2</sup>)</label>
                                            <input type="text" name="area" id="ip-acreage" class="form-control"
                                                placeholder="Diện tích căn hộ"
                                                value="{{!empty($apatment->area)?$apatment->area:''}}">
                                        </div>
                                        <div class="form-group">
                                            <label for="ip-description">Mô tả</label>
                                            <textarea name="description" id="id-description" cols="30" rows="5"
                                                placeholder="Mô tả căn hộ"
                                                class="form-control">{{!empty($apatment->description)?$apatment->description:''}}</textarea>
                                        </div>
                                        <div class="form-group">
                                                <label for="ip-acreage">Mã căn</label>
                                                <input type="text" name="code" id="ip-code" class="form-control"
                                                       placeholder="Diện tích căn hộ"
                                                       value="{{!empty($apatment->code)?$apatment->code:''}}">
                                        </div>
                                        <div class="clearfix"></div>
                                        <div class="form-group">
                                            <label for="ip-role">Tình trạng</label>

                                            <select name="status" id="select-ap-role-edit" class="form-control">
                                                <option value="">Chọn Trạng thái</option>
                                                <option value="0" @if($status == '0') selected @endif>Để không</option>
                                                <option value="1" @if($status == '1') selected @endif>Cho thuê</option>
                                                <option value="2" @if($status == '2') selected @endif>Muốn cho thuê</option>
                                                <option value="3" @if($status == '3') selected @endif>Đang ở</option>
                                                <option value="4" @if($status == '4') selected @endif>Mới bàn giao</option>
                                                <option value="5" @if($status == '5') selected @endif>Đang cải tạo</option>
                                                </option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <button type="button" class="btn btn-sm btn-success btn-save-edit"
                                                title="Cập nhật" form="form-edit-apartment">
                                                <i class="fa fa-save"></i>&nbsp;&nbsp;Cập nhật
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="panel-heading">Tập tin <a href="javascript:void(0);" class="btn btn-success"
                                        title="Thêm cư dân" data-toggle="modal" data-target="#add-file"><i
                                            class="fa fa-plus"></i>&nbsp;&nbsp;Thêm</a></div>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Tên file</th>
                                            {{--                                            <th>Hiển thị với cư dân</th>--}}
                                            <th width="130">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($files as $k => $f)
                                        <tr>
                                            <td>{{$k+1}}</td>
                                            <td>{{$f->name}}</td>
                                            {{--<td>
                                                    <div class="tag_check_file" data-id="{{$f->id}}">
                                            @if($f->status == 1)
                                            <span class="tag-relats bg-submain2 s-file"
                                                status="{{$f->status}}">Yes</span>
                                            @elseif($f->status == 0)
                                            <span class="tag-relats bg-submain s-file" status="{{$f->status}}">No</span>
                                            @endif
                            </div>
                            </td>--}}
                            <td>
                                <a href="{{ url($f->url) }}"
                                    class="tag-relats bg-submain2 s-file-trash"><i class="fa fa-eye"></i></a>
                                @if( in_array('admin.systemfiles.download',@$user_access_router))
                                    <a href="{{ route('admin.systemfiles.download',['path'=>$f->url]) }}"
                                       class="tag-relats bg-submain2 s-file-trash"><i class="fa fa-download"></i></a>
                                @endif
                                @if( in_array('admin.systemfiles.download',@$user_access_router))
                                    <a href="{{ route('admin.systemfiles.delete',['id'=> $f->id]) }}"
                                       class="tag-relats bg-submain s-file-trash"><i class="fa fa-trash"></i></a>
                                @endif
                            </td>
                            </tr>
                            @endforeach
                            </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Cư dân <a class="btn btn-success" title="Thêm cư dân" data-toggle="modal"
                            data-target="#add-resident"><i class="fa fa-plus"></i>&nbsp;&nbsp;Thêm</a>
                        <p
                            class="display_mes_summit @if($data_error) error_mes @elseif($data_success) success_mes @endif">
                            {{$data_cus}} </p>
                    </div>
                    <div class="panel-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th colspan="" rowspan="" headers="">Stt</th>
                                    <th colspan="" rowspan="" headers="">Họ và tên</th>
                                    <th colspan="" rowspan="" headers="">Email</th>
                                    <th colspan="" rowspan="" headers="">Mobile</th>
                                    <th colspan="" rowspan="" headers="">Quan hệ</th>
                                    <th colspan="" rowspan="" headers="">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($residents as $key => $item)
                                <tr>
                                    <td colspan="" rowspan="" headers="">{{ $key + 1 }}</td>
                                    <td colspan="" rowspan="" headers=""><a
                                            href="javascript:void(0);">{{ @$item->pubUserProfile->display_name }}</a>
                                    </td>
                                    <td colspan="" rowspan="" headers="">{{ @$item->pubUserProfile->email }}</td>
                                    <td colspan="" rowspan="" headers="">{{ @$item->pubUserProfile->phone }}</td>
                                    <td colspan="" rowspan="" headers="">
                                        @if($item->type == 0)
                                        <span class="tag-relats bg-main" style="">Chủ hộ</span>
                                        @elseif($item->type == 1)
                                        <span class="tag-relats bg-submain" style="">Vợ/Chồng</span>
                                        @elseif($item->type == 2)
                                        <span class="tag-relats bg-submain1" style="">Con</span>
                                        @elseif($item->type == 3)
                                        <span class="tag-relats bg-submai2" style="">Bố mẹ</span>
                                        @elseif($item->type == 4)
                                        <span class="tag-relats bg-submain3" style="">Anh chị em</span>
                                        @elseif($item->type == 5)
                                        <span class="tag-relats bg-submain3" style="">Khác</span>
                                        @elseif($item->type == 6)
                                        <span class="tag-relats bg-submain3" style="">Khách thuê</span>
                                        @elseif($item->type == 7)
                                            <span class="tag-relats bg-submain3" style="">Chủ hộ cũ</span>
                                        @endif
                                    </td>
                                    <td colspan="" rowspan="" headers="">
                                        @if( in_array('admin.customers.edit',@$user_access_router))
                                            <a href="{{ route('admin.customers.edit',['id'=> @$item->pub_user_profile_id]) }}"
                                               class="btn btn-success" title="Chi tiết"><i class="fa fa-share-square-o"></i></a>
                                        @endif
                                        @if( in_array('admin.customers.del_customer',@$user_access_router))
                                            <a href="{{ route('admin.customers.del_customer',['id'=> @$item->pub_user_profile_id,'apartment_id'=>$apatment->id]) }}"
                                               class="btn btn-danger" title="xóa"><i class="fa fa-times"></i></a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Phương tiện <a class="btn btn-success" title="Thêm phương tiện"
                            data-toggle="modal" data-target="#add-vehicle"><i
                                class="fa fa-plus"></i>&nbsp;&nbsp;Thêm</a>
                        <p
                            class="display_mes_summit @if($data_error) error_mes @elseif($data_success) success_mes @endif">
                            {{$data_vhc}}</p>
                    </div>
                    <div class="panel-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th colspan="" rowspan="" headers="">Stt</th>
                                    <th colspan="" rowspan="" headers="">Tên phương tiện</th>
                                    <th colspan="" rowspan="" headers="">Biển số</th>
                                    <th colspan="" rowspan="" headers="">Mô tả</th>
                                    <th colspan="" rowspan="" headers="">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($vehicles as $key => $item)
                                <tr>
                                    <td colspan="" rowspan="" headers="">{{ $key + 1 }}</td>
                                    <td colspan="" rowspan="" headers=""><a
                                            href="javascript:void(0);">{{ $item->name }}</a></td>
                                    <td colspan="" rowspan="" headers="">{{ $item->number }}</td>
                                    <td colspan="" rowspan="" headers="">{{ $item->description }}</td>
                                    <td colspan="" rowspan="" headers="">
                                        @if( in_array('admin.vehicles.edit',@$user_access_router))
                                            <a href="{{ route('admin.vehicles.edit',['id'=> $item->id]) }}"
                                               class="btn btn-success" title="Chi tiết"><i class="fa fa-share-square-o"></i></a>
                                        @endif
                                        @if( in_array('admin.vehicles.del_vehicle',@$user_access_router))
                                            <a href="{{ route('admin.vehicles.del_vehicle',['id'=> $item->id]) }}"
                                               class="btn btn-danger" title="xóa"><i class="fa fa-times"></i></a>
                                        @endif

                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="diary_aparment">
                <div class="box-diary" style="padding: 15px 0;">
                    <form action="" method="post" id="form-search-diary">
                        <div class="box-input-diary" style="width: calc(100% - 50px);display: inline-block">
                            <div class="">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <input type="text" name="keyword" id="ip-keyword-fb" placeholder="Nhập từ khóa"
                                            class="form-control">
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <select name="status" id="ip-status-fb" class="form-control">
                                            <option value="">Tình trạng</option>
                                            <option value="0">Chưa xử lý</option>
                                            <option value="1">Đã xử lý</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <select name="type" id="ip-type-fb" class="form-control">
                                            <option value="">Loại yêu cầu</option>
                                            <option value="fback">Ý kiến</option>
                                            <option value="request">Form yêu cầu</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-btn" style="float: right;">
                            <a class="btn btn-success btn-action-log"><i class="fa fa-search"></i></a>
                        </div>
                    </form>
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th colspan="" rowspan="" headers="">STT</th>
                                        <th colspan="" rowspan="" headers="">Tiêu đề</th>
                                        <th colspan="" rowspan="" headers="">Loại ý kiến</th>
                                        <th colspan="" rowspan="" headers="">Người gửi</th>
                                        <th colspan="" rowspan="" headers="">Ngày gửi</th>
                                        <th colspan="" rowspan="" headers="">Tình trạng</th>
{{--                                        <th colspan="" rowspan="" headers="">File</th>--}}
                                        <th colspan="" rowspan="" headers="">Tác vụ</th>
                                    </tr>
                                </thead>
                                <tbody class="table_fb">
                                    @foreach($feedbacks as $fb)
                                        <tr>
                                            <td colspan="" rowspan="" headers="">{{@$fb->id}}</td>
                                            <td colspan="" rowspan="" headers="">{{@$fb->title}}</td>
                                            <td colspan="" rowspan="" headers="">{{@$fb->type}}</td>
                                            <td colspan="" rowspan="" headers="">{{@$fb->pubUserProfile->display_name}}</td>
                                            <td colspan="" rowspan="" headers="">{{@$fb->created_at}}</td>
                                            <td colspan="" rowspan="" headers="">
                                                <a title="Thay đổi trạng thái" href="javascript:;" class="btn-status label label-sm label-{{ $fb->status ? 'success' : 'danger' }}">
                                                    {{ $fb->status ? 'Đã xử lý' : 'Chưa xử lý' }}
                                                </a>
                                            </td>
                                            {{--                                                <td colspan="" rowspan="" headers=""><a href="{{route('admin.feedback.detail',['id'=>$fb->id])}}">Xem</a></td>--}}
                                            <td colspan="" rowspan="" headers="">
                                                @if( in_array('admin.feedback.detail',@$user_access_router))
                                                    <a href="{{route('admin.feedback.detail',['id'=>$fb->id])}}" class="btn btn-success" title="Chi tiết"><i class="fa fa-share-square-o"></i></a>
                                                    {{--                                                    <a href="javascript:void(0);" class="btn btn-danger" title="xóa"><i class="fa fa-times"></i></a>--}}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="service">
                <div class="col-sm-11 form-control-static">
                    @if( in_array('admin.service.apartment.create',@$user_access_router))
                        <a href="{{ route('admin.service.apartment.create') }}" type="buttom"
                           class="btn btn-success pull-right"><i class="fa fa-plus"></i>&nbsp;&nbsp;Thêm dịch vụ</a>
                    @endif
                </div>
                <div class="box-service" style="padding: 15px 0;">
                    <?php $i=0 ?>
                    @foreach($services as $key => $sv)
                    @if(count($sv->apartmentServicePrices)!=0 )
                    <?php $i++;?>
                    <div class="col-sm-12">
                        <div class="box-title-service" style="display: inline-block;width: 100%;">
                            <div class="col-sm-6">
                                <p style="font-size: 17px;font-weight: bold;">{{$i}}. {{$sv->name}} - Căn hộ
                                    {{$apatment->name}}</p>
                            </div>
                            {{--                                            <div class="col-sm-6"><p>Ngày bắt đầu sử dụng: {{date('d/m/Y',$sv->apartmentServicePrices->first_time_active)}}
                            </p>
                        </div>--}}
                    </div>
                    <div class="box-table-service">
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th width='8%'>Stt</th>
                                            <th width='22%' colspan="2">Tên sản phẩm</th>
                                            <th width='7%'>Số lượng</th>
                                            <th width='20%'>Đơn giá</th>
                                            <th width='10%'>Trạng thái</th>
                                            <th width='3%'>Ngày bắt đầu</th>
                                            <th width='23%'>Ghi chú</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sv->apartmentServicePrices as $keyi => $item)
                                        <tr>
                                            <td width='8%'>{{--<i class="fa fa-times"></i>&nbsp;&nbsp;--}}{{$keyi+1}}
                                            </td>
                                            <td width='13%'>{{$item->name}}</td>
                                            <td width='9%'>{{@$item->vehicle->number}}</td>
                                            <td width='7%'>1</td>
                                            <td width='20%'>{{$item->price}} @if($item->floor_price != 0)
                                                ({{$item->floor_price}} * {{$item->apartment->area}} m<sup>2</sup>)
                                                @endif</td>
                                            <td width='10%'>@if($sv->status == 0) Inactive @else Active @endif</td>
                                            <td width='10%'>{{date("d/m/Y", strtotime(@$item->first_time_active))}}</td>
                                            <td width='23%'>{{$item->description}}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
        </div>
    </div>


    </div>
    </div>
    <div id="add-resident" class="modal fade" role="dialog">
        <div class="modal-dialog  modal-lg">
            <!-- Modal content-->
            @if( in_array('admin.customers.save_user_apartment',@$user_access_router))
                <form action="{{ url('admin/customers/save_user') }}" method="post" id="form-add-resident"
                      class="form-validate form-horizontal">
                    {{ csrf_field() }}
                    <input type="hidden" name="hashtag">
                    <div class="modal-content">
                        <div class="modal-header bg-primary">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Thêm mới Cư dân</h4>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-danger alert_pop_add_resident" style="display: none;">
                                <ul></ul>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <div class="col-sm-2">
                                            <label for="in-re_name">Tên cư dân</label>
                                        </div>
                                        <div class="col-sm-8">
                                            <input type="text" name="name" id="in-re_name" class="form-control"
                                                   placeholder="Tên cư dân">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-2">
                                            <label>Giới tính</label>
                                        </div>
                                        <div class="col-sm-8">
                                            <input type="radio" name="sex" id="in-re_sex" class="" value="1" checked />Nam
                                            <input type="radio" name="sex" id="in-re_sex" class="" value="2" />Nữ
                                        </div>
                                    </div>


                                    <div class="form-group hidden">
                                        <div class="col-sm-2">
                                            <label>Căn hộ</label>
                                        </div>
                                        <div class="col-sm-8">
                                            <input type="hidden" class="form-control" id="in-ap_id" name="bdc_apartment_id"
                                                   value="{{$id}}">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-2">
                                            <label>Nhập email</label>
                                        </div>
                                        <div class="col-sm-8">
                                            <input type="text" name="email" id="in-re_email" class="form-control"
                                                   placeholder="Email cư dân">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-2">
                                            <label>Nhập Số điện thoại</label>
                                        </div>
                                        <div class="col-sm-8">
                                            <input type="text" name="phone" id="in-re_phone" class="form-control"
                                                   placeholder="Điện thoại cư dân">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-2">
                                            <label>Quan hệ</label>
                                        </div>
                                        <div class="col-sm-8">
                                            <select name="type" id="in-re_relationship" class="form-control">
                                                <option value="">Chọn quan hệ</option>
                                                <option value="0">Chủ hộ</option>
                                                <option value="1">Vợ/Chồng</option>
                                                <option value="2">Con</option>
                                                <option value="3">Bố mẹ</option>
                                                <option value="4">Anh chị em</option>
                                                <option value="5">Khác</option>
                                                <option value="6">Khách thuê</option>
                                                <option value="7">Chủ hộ cũ</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i
                                            class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
                                <button type="button" class="btn btn-primary btn-js-action" form="form-add-resident"
                                        style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận</button>
                            </div>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>
    <div id="add-vehicle" class="modal fade" role="dialog">
        <div class="modal-dialog  modal-lg">
            <!-- Modal content-->
            @if( in_array('admin.vehicles.save_vehicle_apartment',@$user_access_router))
                <form action="{{ url('admin/vehicles/save_vehicle') }}" method="post" id="form-add-verhicle"
                      class="form-validate form-horizontal">
                    {{ csrf_field() }}
                    <input type="hidden" name="hashtag">
                    <div class="modal-content">
                        <div class="modal-header bg-primary">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Thêm mới phương tiện</h4>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-danger alert_pop_add_vehicle" style="display: none;">
                                <ul></ul>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <div class="col-sm-2">
                                            <label for="in-re_name">Tên Phương tiện</label>
                                        </div>
                                        <div class="col-sm-8">
                                            <input type="text" name="vc_name" id="in-vc_name" class="form-control"
                                                   placeholder="Tên phương tiện">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="col-sm-2">
                                            <label>Loại phương tiện</label>
                                        </div>
                                        <div class="col-sm-8">
                                            <select name="vehicle_category_id" id="select-vc_type" class="form-control">
                                                <option value="">Chọn phương tiện</option>
                                                @foreach($vehicle_cate as $item)
                                                    <option value="{{$item->id}}">{{$item->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-2">
                                            <label>Biển số</label>
                                        </div>
                                        <div class="col-sm-8">
                                            <input type="text" name="number" id="in-vc_vehicle_number" class="form-control"
                                                   placeholder="Biển số (Nếu có)">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-2">
                                            <label>Mô tả</label>
                                        </div>
                                        <div class="col-sm-8">
                                        <textarea name="description" id="textarea-vc_description" class="form-control"
                                                  cols="30" rows="5" placeholder="Mô tả phương tiện"></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group hidden">
                                        <div class="col-sm-2">
                                            <label>Căn hộ</label>
                                        </div>
                                        <div class="col-sm-8">
                                            <input type="hidden" class="form-control" id="in-ap_id" name="bdc_apartment_id"
                                                   value="{{$id}}">
                                        </div>
                                    </div>
                                    <div class="form-group hidden">
                                        <div class="col-sm-2">
                                            <label>Ảnh</label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="input-group input-image" data-file="image">
                                                <input type="text" name="vc_image" id="in-vc_image" value=""
                                                       class="form-control"><span class="input-group-btn"><button type="button"
                                                                                                                  class="btn btn-primary">Chọn</button></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i
                                        class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
                            <button type="button" class="btn btn-primary btn-js-action-vehicle" form="form-add-verhicle"
                                    style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận</button>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>
    <div id="add-file" class="modal fade" role="dialog">
        <div class="modal-dialog  modal-lg">
            <!-- Modal content-->
            @if( in_array('admin.apartments.createfile',@$user_access_router))
                <form action="{{ route('admin.apartments.createfile',['id'=>$id]) }}" method="POST"
                      id="form-add-file-aparment" class="form-validate form-horizontal" autocomplete="off"
                      enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <div class="modal-content">
                        <div class="modal-header bg-primary">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Thêm mới file</h4>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-danger alert_pop_add_file" style="display: none;">
                                <ul></ul>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <div class="col-sm-2">
                                            <label for="in-re_name">Tên file</label>
                                        </div>
                                        <div class="col-sm-8">
                                            <input type="text" name="name" id="in-name-file" class="form-control"
                                                   placeholder="Tên file">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="col-sm-2">
                                            <label>file</label>
                                        </div>
                                        <div class="col-sm-8">
                                            <input type="file" name="file_apartment" id="ip-file" class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-2">
                                            <label>Mô tả</label>
                                        </div>
                                        <div class="col-sm-8">
                                        <textarea name="description" id="textarea-vc_description" class="form-control"
                                                  cols="30" rows="5" placeholder="Mô tả file"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i
                                        class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
                            <button type="button" class="btn btn-primary btn-js-action-file" form="form-add-file-aparment"
                                    style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận</button>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>
</section>
@endsection

@section('javascript')
<script>
    $(function () {
            get_data_select_apartment({
                object: '#in-ap_id',
                url: '{{ url('admin/apartment/ajax_get_apartment') }}',
                data_id: 'id',
                data_text: 'name',
                title_default: 'Chọn căn hộ'
            });
            get_data_select_apartment1({
                object: '#ip-place',
                url: '{{ url('admin/apartments/ajax_get_building_place') }}',
                data_id: 'id',
                data_text: 'name',
                data_code: 'code',
                title_default: 'Chọn tòa nhà'
            });
            function get_data_select_apartment(options) {
                $(options.object).select2({
                    ajax: {
                        url: options.url,
                        dataType: 'json',
                        data: function(params) {
                            var query = {
                                search: params.term,
                            }
                            return query;
                        },
                        processResults: function(json, params) {
                            var results = [{
                                id: '',
                                text: options.title_default
                            }];

                            for (i in json.data) {
                                var item = json.data[i];
                                results.push({
                                    id: item[options.data_id],
                                    text: item[options.data_text]
                                });
                            }
                            return {
                                results: results,
                            };
                        },
                        minimumInputLength: 3,
                    }
                });
            }

        function get_data_select_apartment1(options) {
            $(options.object).select2({
                ajax: {
                    url: options.url,
                    dataType: 'json',
                    data: function(params) {
                        var query = {
                            search: params.term,
                        }
                        return query;
                    },
                    processResults: function(json, params) {
                        var results = [{
                            id: '',
                            text: options.title_default
                        }];

                        for (i in json.data) {
                            var item = json.data[i];
                            results.push({
                                id: item[options.data_id],
                                text: item[options.data_text]+' - '+item[options.data_code]
                            });
                        }
                        return {
                            results: results,
                        };
                    },
                    minimumInputLength: 3,
                }
            });
        }

        });
        $(".btn-js-action-vehicle").on('click',function () {
            var _this = $(this);
            $(".alert_pop_add_vehicle").hide();
            _this.attr('type','button');
            var vehicle_number = $("#in-vc_vehicle_number").val();
            var name = $("#in-vc_name").val();
            var type = $("#select-vc_type").val();
            var apt = $("#ip-ap_id").val();
            var html = '';
            if(name.length <=2 || name.length >=50){
                html+='<li>Tên phương tiện không được nhỏ hơn 3 hoặc lớn hơn 50 ký tự</li>';
            }if(vehicle_number == '' || vehicle_number.length <=5 || vehicle_number.length >13){
                html+='<li>Biển số không được nhỏ hơn 6 hoặc lớn hơn 12 ký tự</li>';
            }if(apt == ''){
                html+='<li>Trường Căn hộ không được để trống</li>';
            }if(type == ''){
                html+='<li>Trường loại phương tiện không được để trống</li>';
            }

            $.get('{{ route('admin.vehicles.ajax_check_number') }}', {
                type: vehicle_number
            }, function(data) {
                if(data.status == 1){
                    html+='<li>Biển số xe đã tồn tại trên hệ thống vui lòng kiểm tra lại</li>';
                }
            });
            showLoading();
            setTimeout(function(){
                if(html != ''){
                    $(".alert_pop_add_vehicle").show();
                    $(".alert_pop_add_vehicle ul").html(html)
                    hideLoading();
                }else{

                    $('#form-add-verhicle').submit();
                }
            }, 600);
        });
    $('#in-re_phone,#in-re_name,#in-re_relationship,#in-re_email').bind('keyup change',delay_key(function (e) {
        $(".alert_pop_add_resident").hide();
        var email = $("#in-re_email").val();
        var phone_number = $("#in-re_phone").val();
        var name = $("#in-re_name").val();
        var rels = $("#in-re_relationship").val();
        var html  = '';
        if(email== '' || phone_number == '' || name == '' || rels==''){
            $('.btn-js-action').attr({
                type:'button'
            }).removeAttrs('onclick');
        }
        if( name != '' && rels!=''){
            showLoading();
            $.get('{{ route('admin.customers.ajax_check_type') }}', {
                type: rels,
                aparment:  $('#in-ap_id').val()
            }, function(data) {
                if(rels == 0 && (email != '' || phone_number != '')){
                    hideLoading();
                    $('.btn-js-action').attr({
                        type:'submit',
                        onclick:'return confirm("'+data.message+'")',
                    });
                }else if(rels==2 && (email == '' && phone_number == '')){
                    hideLoading();
                    $('.btn-js-action').attr({
                        type:'submit',
                    });
                }else if(rels==5 && (email == '' && phone_number == '')){
                    hideLoading();
                    $('.btn-js-action').attr({
                        type:'submit'
                    });
                }else if(rels == 0 && (email == '' && phone_number == '')){
                    hideLoading();
                    $('.btn-js-action').attr({
                        type:'button'
                    }).removeAttrs('onclick');
                    html+='<li>Căn hộ này đã có chủ hộ, nếu bạn muốn thay đổi phải điền đầu đủ trường Email hoặc Số điện thoại</li>';
                }else if(rels==0 && email != '' && phone_number == ''){
                    hideLoading();
                    $('.btn-js-action').attr({
                        type:'submit',
                        onclick:'return confirm("'+data.message+'")',
                    });
                    // html+='<li>Căn hộ chưa có chủ hộ, nếu muốn thêm phải điền đầy đủ trường Email, số điện thoại và căn hộ</li>';
                }else if(rels==0 && email == '' && phone_number != ''){
                    hideLoading();
                    $('.btn-js-action').attr({
                        type:'submit',
                        onclick:'return confirm("'+data.message+'")',
                    });
                    // html+='<li>Căn hộ chưa có chủ hộ, nếu muốn thêm phải điền đầy đủ trường Email, số điện thoại và căn hộ</li>';
                }else if(rels==0 && (email == '' || phone_number == '')){
                    hideLoading();
                    $('.btn-js-action').attr({
                        type:'button'
                    }).removeAttrs('onclick');
                    html+='<li>Căn hộ chưa có chủ hộ, nếu muốn thêm phải điền đầy đủ trường Email, số điện thoại và căn hộ</li>';
                }else{

                    if(name.length <3 || name.length >=45){
                        html+='<li>Tên dân cư không được nhỏ hơn 3 hoặc lớn hơn 45 ký tự</li>';
                    }if(email != '' && !isValidEmailAddress(email)){
                        html+='<li>Email dân cư không Đúng định dạng</li>';
                    }if(phone_number != '' && isValidEmailAddress(phone_number)){
                        html+='<li>Email dân cư không được nhập ở trường số điện thoại</li>';
                    }if(phone_number != '' && (phone_number.length <=9 || phone_number.length >= 12)){
                        html+='<li>Số điện thoại dân cư không dưới 10 và không lớn hơn 11 ký tự và không để trống</li>';
                    }if(rels == ''){
                        html+='<li>Trường quan hệ không được để trống</li>';
                    }
                    if(!html){
                        hideLoading();
                        $('.btn-js-action').attr({
                            type:'submit'
                        }).removeAttrs('onclick');
                    }
                }
            });
        }
        setTimeout(function(){
            if(html){
                $(".alert_pop_add_resident").show();
                $(".alert_pop_add_resident ul").html(html);
                hideLoading();
            }
        }, 600);

    },600));
        $(".btn-js-action").on('click',function () {
            $(".alert_pop_add_resident").hide();
            var email = $("#in-re_email").val();
            var phone_number = $("#in-re_phone").val();
            var name = $("#in-re_name").val();
            var rels = $("#in-re_relationship").val();
            var html = '';
            if(name.length <3 || name.length >=45){
                html+='<li>Tên dân cư không được nhỏ hơn 3 hoặc lớn hơn 45 ký tự</li>';
            }if(email != '' && !isValidEmailAddress(email)){
                html+='<li>Email dân cư không Đúng định dạng</li>';
            }if(phone_number != '' && isValidEmailAddress(phone_number)){
                html+='<li>Email dân cư không được nhập ở trường số điện thoại</li>';
            }if(phone_number != '' && phone_number.length <=9 || phone_number.length >= 12){
                html+='<li>Số điện thoại dân cư không dưới 10 và không lớn hơn 11 ký tự và không để trống</li>';
            }if(rels == ''){
                html+='<li>Trường quan hệ không được để trống</li>';
            }

            if(html){
                $(".alert_pop_add_resident").show();
                $(".alert_pop_add_resident ul").html(html);
            }
        });
        $(".btn-js-action-file").on('click',function () {
            var _this = $(this);
            $(".alert_pop_add_file").hide();
            var name = $("#in-name-file").val();
            if(name.length <=0){
                $(".alert_pop_add_file").show();
                $(".alert_pop_add_file ul").html('<li>Tên file không được bỏ trống</li>')
            }else if(name.length <=3 || name.length >=255){
                $(".alert_pop_add_file").show();
                $(".alert_pop_add_file ul").html('<li>Tên file không được nhỏ hơn 3 hoặc lớn hơn 255 ký tự</li>')
            }else{
                $("#form-add-file-aparment").submit();
            }
        });
        $(".btn-save-edit").on('click',function () {
            var _this = $(this);
            $(".alert_pop_add_edit").hide();
            var apt = $("#ip-name-apt").val();
            var floor = $("#ip-floor").val();
            var area = $("#ip-acreage").val();
            var status = $("#select-ap-role-edit").val();
            var html = '';
            if(apt.length <1 || apt.length >46) {
                html+='<li>Tên căn hộ không được nhỏ hơn 2 hoặc lớn hơn 45 chữ số</li>';
            }if(floor.length == '' || floor.length >5){
                html+='<li>Trường tầng là 1 số không quá 5 chữ số và không bỏ trống</li>';
            }if(area.length == '' || area.length >8 || $.isNumeric(area) == false){
                html+='<li>Trường diện tích là 1 số không quá 8 chữ số và không bỏ trống</li>';
            }if( status.length <=0){
                html+='<li>Trường tình trạng không được để trống</li>';
            }
            if(html != ''){
                $(".alert_pop_add_edit").show();
                $(".alert_pop_add_edit ul").html(html)
            }else{
                $('#form-edit-apartment').submit();
            }
        });
        $(document).ready(function () {
            $('.tag_check_file').on('click',function () {
                var _this= $(this);
                $.get('{{ route('admin.systemfiles.ajax_change_status') }}', {
                    status: $(this).find('span').attr('status'),
                    id:  $(this).data('id')
                }, function(data) {
                    if(data.status === 1){
                        _this.html('<span class="tag-relats bg-submain2" status="1">Yes</span>');
                    }else{
                        _this.html('<span class="tag-relats bg-submain" status="0">No</span>');
                    }
                });
            });
        });
        $(".btn-action-log").on('click',function () {
            $.get('{{ route('admin.feedback.ajax_search_feedback') }}', {
                keyword: $("#ip-keyword-fb").val(),
                status: $("#ip-status-fb").val(),
                type: $("#ip-type-fb").val(),
                apartment_id: '{{$id}}'
            }, function(data) {
                $(".table_fb").html(data);
            });
        });

        sidebar('apartments', 'edit');
</script>

@endsection